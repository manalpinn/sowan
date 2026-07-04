<template>
  <AdminLayout
    page-title="Log Kedatangan"
    :breadcrumbs="dynamicBreadcrumbs"
  >
    <EventTabs v-if="active_event" current="checkins" :event-id="active_event.id" />

    <div class="card">
      <!-- Filter Bar -->
      <div class="filter-bar">

        <!-- Kiri: semua filter input (2 baris) -->
        <div class="filter-inputs">
          <!-- Live bar: Always shows LIVE status, optionally shows New entries -->
          <div class="live-bar">
            <span class="live-indicator" :class="isPolling ? 'live-indicator--active' : 'live-indicator--idle'">
              <span class="live-dot"></span>
              {{ isPolling ? 'Live' : 'Paused' }}
            </span>
            
            <transition name="badge-pop">
              <div
                v-if="newCount > 0"
                class="new-badge-minimal"
              >
                <span class="live-dot"></span>
                Data Terbaru
              </div>
            </transition>
          </div>

          <!-- Baris 1: Nama + Tanggal -->
          <div class="filter-row">
            <!-- Search -->
            <div class="filter-field filter-field--search">
              <MagnifyingGlassIcon class="filter-icon h-4 w-4" />
              <input
                type="text"
                v-model="search"
                class="filter-input"
                placeholder="Cari nama tamu..."
                @input="onFilterChange"
              >
            </div>

            <!-- Date -->
            <div class="filter-field">
              <CalendarIcon class="filter-icon h-4 w-4" />
              <input
                type="date"
                v-model="date"
                class="filter-input"
                @change="onFilterChange"
              >
            </div>
          </div>

          <!-- Event Filter moved to Tabs -->

        </div>

        <!-- Kanan: Export buttons — center vertikal -->
        <div class="filter-actions">
          <a :href="exportCsvUrl" class="btn btn-secondary btn-sm" target="_blank">
            <ArrowDownTrayIcon class="w-4 h-4" />
            CSV
          </a>
          <a :href="exportPdfUrl" class="btn btn-secondary btn-sm" target="_blank">
            <DocumentTextIcon class="w-4 h-4" />
            PDF
          </a>
        </div>

      </div>

      <!-- Responsive table -->
      <div class="overflow-x-auto">
        <table class="data-table min-w-full">
          <thead>
            <tr>
              <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase tracking-wider">Tamu</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase tracking-wider hidden md:table-cell">Token</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase tracking-wider hidden lg:table-cell">Event</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase tracking-wider">Waktu Masuk</th>
              <th v-if="hasCheckout" class="px-4 py-3 text-left text-xs font-bold text-muted uppercase tracking-wider hidden sm:table-cell">Waktu Keluar</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase tracking-wider hidden sm:table-cell">Metode</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="log in localCheckins"
              :key="log.id"
              class="transition-colors"
              :class="{ 'row-new': newIds.has(log.id) }"
            >
              <td class="px-4 py-4 align-middle">
                <div class="flex flex-col min-w-[120px]">
                  <span class="font-bold text-sm text-primary">{{ log.guest_name }}</span>
                  <span class="text-xs text-muted">{{ log.guest_type }}</span>
                  <!-- Show event on mobile -->
                  <span class="text-xs text-muted mt-0.5 lg:hidden truncate max-w-[140px]">{{ log.event_name }}</span>
                </div>
              </td>
              <td class="px-4 py-4 align-middle hidden md:table-cell">
                <code class="font-mono font-bold text-primary text-sm tracking-widest">{{ log.guest_token }}</code>
              </td>
              <td class="px-4 py-4 align-middle hidden lg:table-cell">
                <span class="text-sm text-secondary font-medium">{{ log.event_name }}</span>
              </td>
              <!-- Waktu Masuk: dua baris (jam besar + tanggal kecil) -->
              <td class="px-4 py-4 align-middle whitespace-nowrap">
                <div v-if="log.checkin_time" class="flex flex-col">
                  <span class="text-sm font-bold text-primary tabular-nums">{{ log.checkin_time.split(', ')[1] ?? log.checkin_time }}</span>
                  <span class="text-xs text-muted">{{ log.checkin_time.split(', ')[0] }}</span>
                </div>
                <span v-else class="text-muted text-sm">–</span>
              </td>
              <!-- Waktu Keluar -->
              <td v-if="hasCheckout" class="px-4 py-4 align-middle whitespace-nowrap hidden sm:table-cell">
                <div v-if="log.checkout_time" class="flex flex-col">
                  <span class="text-sm font-bold text-primary tabular-nums">{{ log.checkout_time.split(', ')[1] ?? log.checkout_time }}</span>
                  <span class="text-xs text-muted">{{ log.checkout_time.split(', ')[0] }}</span>
                </div>
                <span v-else class="text-muted text-sm">–</span>
              </td>
              <td class="px-4 py-4 align-middle text-left whitespace-nowrap">
                <div class="flex flex-col items-start gap-1">
                  <span class="badge" :class="log.status === 'checkout' ? 'badge-checked_out' : 'badge-checked_in'">
                    {{ log.status === 'checkout' ? 'Keluar' : 'Masuk' }}
                  </span>
                  <span v-if="log.time_range" class="text-[10px] text-slate-400 font-medium ml-1 tabular-nums">{{ log.time_range }}</span>
                </div>
              </td>
              <td class="px-4 py-4 align-middle text-left hidden sm:table-cell whitespace-nowrap">
                <span 
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10.5px] font-bold uppercase tracking-wider transition-colors whitespace-nowrap"
                  :class="{
                    'bg-blue-50 text-blue-600 ring-1 ring-inset ring-blue-500/20': log.method.includes('QR'),
                    'bg-purple-50 text-purple-600 ring-1 ring-inset ring-purple-500/20': log.method.includes('TOKEN'),
                    'bg-amber-50 text-amber-600 ring-1 ring-inset ring-amber-500/20': log.method.includes('MANUAL') && !log.method.includes('QR') && !log.method.includes('TOKEN'),
                    'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20': !log.method.includes('QR') && !log.method.includes('TOKEN') && !log.method.includes('MANUAL')
                  }"
                  :title="
                    log.method.includes('QR') ? 'Scan via QR Code' : 
                    (log.method.includes('TOKEN') ? 'Input Token Manual' : 
                    (log.method.includes('MANUAL') ? 'Check-in oleh Admin' : ''))
                  "
                >
                  {{ log.method }}
                </span>
              </td>
            </tr>
            <tr v-if="localCheckins.length === 0">
              <td :colspan="hasCheckout ? 7 : 6" class="px-8 py-24 text-center">
                <div class="flex flex-col items-center">
                  <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center text-slate-300 mb-4">
                    <ClockIcon class="w-8 h-8" />
                  </div>
                  <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Belum ada data kedatangan</p>
                  <p class="text-xs text-slate-400">Log tamu yang hadir akan muncul di sini secara real-time.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 bg-gray-50/50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-700/50 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-sm text-gray-500 dark:text-slate-400 font-medium">
          Menampilkan <span class="font-bold text-gray-700 dark:text-slate-300">{{ checkins.from || 0 }}</span> - <span class="font-bold text-gray-700 dark:text-slate-300">{{ checkins.to || 0 }}</span> dari <span class="font-bold text-gray-700 dark:text-slate-300">{{ liveTotal ?? checkins.total }}</span> check-in
        </div>
        <Pagination :links="checkins.links" />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import EventTabs from '@/Components/EventTabs.vue';
import { debounce } from 'lodash-es';
import { 
  MagnifyingGlassIcon, 
  CalendarIcon, 
  ListBulletIcon, 
  ArrowDownTrayIcon, 
  DocumentTextIcon,
  ClockIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  checkins: Object,
  events: Array,
  filters: Object,
  hasCheckout: Boolean,
  server_time: String,
  active_event: Object,
});

const dynamicBreadcrumbs = computed(() => {
  if (props.active_event) {
    return [
      { label: 'Dashboard', href: route('dashboard') },
      { label: 'Event', href: route('events.index') },
      { label: props.active_event.name, href: route('events.show', props.active_event.id) },
      { label: 'Log Kedatangan' }
    ];
  }
  return [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'Log Kedatangan' }
  ];
});

// --- Filter state ---
const search = ref(props.filters.search || '');
const eventId = ref(props.filters.event_id || '');
const date = ref(props.filters.date || '');

// --- Real-time polling state ---
const localCheckins = ref([...props.checkins.data]);
const newIds = ref(new Set());
const pendingEntries = ref([]);   // buffer: hold new entries until user confirms
const newCount = ref(0);
const liveTotal = ref(props.checkins.total);
const isPolling = ref(true);
// Gunakan server_time dari props (bukan new Date() browser) agar timezone konsisten
let lastPollTime = ref(props.server_time || new Date().toISOString());
let pollTimer = null;
let badgeTimeout = null;
const POLL_INTERVAL = 15000; // 15 detik (lebih rileks)

// Sync localCheckins when Inertia navigates (filter change, pagination)
watch(() => props.checkins.data, (newData) => {
  localCheckins.value = [...newData];
  newIds.value = new Set();
  pendingEntries.value = [];
  newCount.value = 0;
  liveTotal.value = props.checkins.total;
  lastPollTime.value = props.server_time || new Date().toISOString();
});

async function doPoll() {
  if (!isPolling.value) return;
  try {
    const params = new URLSearchParams({ since: lastPollTime.value });
    if (search.value) params.set('search', search.value);
    if (eventId.value) params.set('event_id', eventId.value);
    if (date.value) params.set('date', date.value);

    const res = await fetch(`/checkins/poll?${params}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!res.ok) return;
    const data = await res.json();

    lastPollTime.value = data.server_time;
    liveTotal.value = data.total;

    if (data.entries && data.entries.length > 0) {
      const newcomers = data.entries.filter(e => !localCheckins.value.some(r => r.id === e.id));
      const updates = data.entries.filter(e => localCheckins.value.some(r => r.id === e.id));

      // 1. Silent Update untuk yang sudah ada di layar (In-place)
      if (updates.length > 0) {
        localCheckins.value = localCheckins.value.map(row => {
          const freshData = updates.find(u => u.id === row.id);
          return freshData ? freshData : row;
        });
      }

      // 2. Tamu Baru (Auto-apply)
      if (newcomers.length > 0) {
        pendingEntries.value = newcomers;
        applyNewEntries(); 
      }

      // 3. Highlight HANYA 1 baris yang paling baru (teratas di batch ini)
      const latestId = data.entries[0].id;
      newIds.value = new Set([latestId]);

      // 4. Tampilkan Badge Ungu Sinkron dengan Highlight
      newCount.value = 1; // Sinyal untuk tampilkan badge
      
      if (badgeTimeout) clearTimeout(badgeTimeout);
      badgeTimeout = setTimeout(() => {
        newCount.value = 0;
        newIds.value = new Set();
      }, 4000);
    }
  } catch (e) {
    // Fail silently
  }
}

function applyNewEntries() {
  const incoming = pendingEntries.value;
  if (!incoming.length) return;

  const existingMap = new Map(localCheckins.value.map(r => [r.id, r]));
  
  // Ambil ID paling baru (paling atas)
  const latestId = incoming[0].id;

  incoming.forEach(entry => existingMap.set(entry.id, entry));

  const merged = Array.from(existingMap.values())
    .sort((a, b) => {
      const dateA = new Date(typeof a.updated_at === 'string' ? a.updated_at.replace(' ', 'T') : (a.updated_at ?? 0));
      const dateB = new Date(typeof b.updated_at === 'string' ? b.updated_at.replace(' ', 'T') : (b.updated_at ?? 0));
      return dateB - dateA;
    })
    .slice(0, props.checkins.per_page || 15);

  localCheckins.value = merged;
  
  // Highlight hanya SATU baris paling baru
  newIds.value = new Set([latestId]);
  pendingEntries.value = [];
  newCount.value = 0; // Bersihkan badge setelah klik
}

function startPolling() {
  isPolling.value = true;
  pollTimer = setInterval(doPoll, POLL_INTERVAL);
}
function stopPolling() {
  isPolling.value = false;
  clearInterval(pollTimer);
}

// Pause polling when tab is hidden (save resources)
function onVisibilityChange() {
  if (document.hidden) stopPolling();
  else { startPolling(); doPoll(); }
}

onMounted(() => {
  startPolling();
  document.addEventListener('visibilitychange', onVisibilityChange);
});
onUnmounted(() => {
  stopPolling();
  document.removeEventListener('visibilitychange', onVisibilityChange);
});

// --- Export URLs ---
const exportCsvUrl = computed(() => {
  const params = new URLSearchParams();
  if (search.value) params.set('search', search.value);
  if (eventId.value) params.set('event_id', eventId.value);
  if (date.value) params.set('date', date.value);
  return route('checkins.export.csv') + (params.toString() ? '?' + params.toString() : '');
});

const exportPdfUrl = computed(() => {
  const params = new URLSearchParams();
  if (search.value) params.set('search', search.value);
  if (eventId.value) params.set('event_id', eventId.value);
  if (date.value) params.set('date', date.value);
  return route('checkins.export.pdf') + (params.toString() ? '?' + params.toString() : '');
});

const onFilterChange = debounce(() => {
  router.get(route('checkins.index'), {
    search: search.value,
    event_id: eventId.value,
    date: date.value,
  }, {
    preserveState: true,
    replace: true,
  });
}, 300);

function setEventFilter(id) {
  eventId.value = id;
  onFilterChange();
}
</script>

<style scoped>
.empty-row { padding: 48px; text-align: center; color: var(--text-muted); font-style: italic; }
.table-footer { padding: 16px 24px; border-top: 1px solid var(--border); }

/* ================================
   REAL-TIME UI (LIVE + NEW)
================================ */
.live-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
}

.live-indicator {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 4px 10px;
  border-radius: 6px;
  border: 1px solid;
  transition: all 0.3s ease;
}

.live-indicator--active {
  color: #059669;
  border-color: #d1fae5;
  background: #f0fdf4;
}

.live-indicator--idle {
  color: #94a3b8;
  border-color: #e2e8f0;
  background: #f8fafc;
}

.live-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
}

.live-indicator--active .live-dot {
  animation: live-pulse 1.5s ease-in-out infinite;
}

@keyframes live-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(0.8); }
}

.new-badge-minimal {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 6px;
  background: #f5f3ff;
  color: #6d28d9;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border: 1px solid #ddd6fe;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(109, 40, 217, 0.1);
  transition: all 0.2s;
}

.new-badge-minimal:hover {
  background: #ede9fe;
  border-color: #c4b5fd;
  transform: translateY(-1px);
}

.new-badge-minimal .live-dot {
  background: #7c3aed;
  animation: live-pulse 1.5s ease-in-out infinite;
}

/* Row Highlight: Hanya satu baris, halus, sementara */
.row-new {
  background-color: #f0fdf4 !important;
  transition: background-color 0.5s ease;
}

/* Badge pop transition */
.badge-pop-enter-active { animation: badge-pop-in 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.badge-pop-leave-active { transition: all 0.2s ease; }
.badge-pop-leave-to { opacity: 0; transform: translateX(-10px); }

@keyframes badge-pop-in {
  0% { transform: scale(0.5); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

/* ================================
   FILTER BAR
================================ */
.filter-bar {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 14px 20px;
  border-bottom: 1px solid var(--border);
}
@media (min-width: 560px) {
  .filter-bar {
    flex-direction: row;
    align-items: center;   /* center vertikal tombol terhadap 2 baris input */
    gap: 12px;
  }
}

/* Kiri: wrapper 2 baris filter input */
.filter-inputs {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
  min-width: 0;
}

/* Tiap baris dalam filter-inputs */
.filter-row {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
@media (min-width: 560px) {
  .filter-row {
    flex-direction: row;
    align-items: center;
    gap: 8px;
  }
}

/* Group of filter inputs */
.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
  min-width: 0;
}
@media (min-width: 560px) {
  .filter-group {
    flex-direction: row;
    flex-wrap: wrap;
    align-items: center;
  }
}

/* Each filter field wrapper */
.filter-field {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
}
@media (min-width: 560px) {
  .filter-field           { width: 155px; flex-shrink: 0; }
  .filter-field--search   { width: 190px; }
  /* Event field: lebar = search + date + gap (agar sejajar baris atas) */
  .filter-field--event    { width: calc(190px + 155px + 8px); flex-shrink: 0; }
}
@media (min-width: 1024px) {
  .filter-field           { width: 165px; }
  .filter-field--search   { width: 210px; }
  .filter-field--event    { width: calc(210px + 165px + 8px); }
}

/* Icon inside field */
.filter-icon {
  position: absolute;
  left: 10px;
  width: 14px;
  height: 14px;
  color: var(--text-muted);
  pointer-events: none;
  flex-shrink: 0;
  z-index: 1;
}

/* Unified input/select style */
.filter-input {
  width: 100%;
  height: 38px;
  padding: 0 10px 0 32px;
  border-radius: 9px;
  border: 1.5px solid var(--border);
  background: var(--card-bg);
  color: var(--text-primary);
  font-size: 13px;
  font-family: inherit;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
  line-height: 1;
}
.filter-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}
.filter-input::placeholder { color: var(--text-muted); }

/* Select arrow */
.filter-select {
  appearance: none;
  -webkit-appearance: none;
  padding-right: 28px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 8px center;
  background-size: 14px;
  cursor: pointer;
}

/* Date picker icon */
input[type='date'].filter-input::-webkit-calendar-picker-indicator {
  opacity: 0.5;
  cursor: pointer;
}

/* Export buttons */
.filter-actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}
</style>
