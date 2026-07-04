<template>
  <AdminLayout
    page-title="Scan Kehadiran"
    :breadcrumbs="[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Scanner' }]"
  >
    <div class="scanner-page-grid">
      <!-- SCANNER SECTION -->
      <div class="space-y-6">
        <!-- Connectivity & Sync Info -->
        <div class="flex items-center justify-between gap-4 p-4 rounded-2xl border transition-all duration-500" :class="isOfflineMode ? 'bg-amber-50 border-amber-200' : 'bg-emerald-50 border-emerald-200'">
          <div class="flex items-center gap-3">
            <div :class="['h-3 w-3 rounded-full', isOfflineMode ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]']"></div>
            <div>
              <p class="text-xs font-black uppercase tracking-widest" :class="isOfflineMode ? 'text-amber-800' : 'text-emerald-800'">
                Mode {{ isOfflineMode ? 'Offline' : 'Online' }}
              </p>
              <p class="text-[10px] font-medium text-slate-500">{{ isOfflineMode ? 'Menyimpan data di browser (Lokal)' : 'Terhubung langsung ke Cloud' }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button v-if="!isOfflineMode" @click="downloadForOffline" class="btn btn-secondary btn-sm h-9 py-0 px-4 text-[10px] font-bold" :disabled="downloading">
              {{ downloading ? 'Mendownload...' : 'Download Database' }}
            </button>
            <button v-if="offlineQueue.length > 0 && !isOfflineMode" @click="syncOfflineData" class="btn btn-primary btn-sm h-9 py-0 px-4 text-[10px] font-bold shadow-lg shadow-primary/20" :disabled="syncing">
              <ArrowPathIcon v-if="syncing" class="animate-spin -ml-1 mr-2 h-3 w-3 text-white inline" />
              {{ syncing ? 'Sinkronisasi...' : 'Sync ' + offlineQueue.length + ' Data' }}
            </button>
            <div class="h-6 w-px bg-slate-200 mx-1"></div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="isOfflineMode" class="sr-only peer">
              <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
            </label>
          </div>
        </div>

        <div class="card overflow-hidden border-0 shadow-2xl shadow-slate-200/50">
          <div class="card-header bg-slate-50/80 backdrop-blur-sm border-b border-slate-100">
            <div class="flex items-center gap-3">
              <div class="h-9 w-9 rounded-xl bg-primary text-white shadow-lg shadow-primary/30 flex items-center justify-center">
                <CameraIcon class="h-5 w-5" stroke-width="2.5" />
              </div>
              <div>
                <span class="block text-sm font-black text-slate-900 tracking-tight">Kamera Scanner</span>
                <span class="block text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ isScanning ? 'Kamera Aktif' : 'Kamera Mati' }}</span>
              </div>
            </div>
            <div v-if="events.length > 1" class="flex items-center gap-2">
               <select v-model="selectedEventId" class="form-select text-xs py-1.5 px-3 h-auto w-44 rounded-xl border-slate-200 bg-white font-bold">
                 <option v-for="e in props.events" :key="e.id" :value="e.id">{{ e.name }}</option>
               </select>
            </div>
          </div>
          
          <div class="scanner-container relative overflow-hidden bg-slate-900">
            <div v-if="isEventInactive" class="scanner-error p-10">
              <div class="w-16 h-16 bg-rose-500/20 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <ExclamationTriangleIcon class="w-8 h-8" stroke-width="2.5" />
              </div>
              <p class="font-black text-white text-lg tracking-tight">
                {{ selectedEvent?.is_active === false ? 'Event Tidak Aktif' : (isEventExpired ? 'Event Telah Berakhir' : 'Event Belum Dimulai') }}
              </p>
              <p class="text-slate-400 text-sm mt-2 mb-6">
                {{ selectedEvent?.is_active === false ? 'Event ini sedang dinonaktifkan oleh Admin.' : (isEventExpired ? 'Check-in dan check-out tidak lagi tersedia.' : 'Check-in dan check-out belum tersedia.') }}
              </p>
            </div>
            <div v-else-if="cameraError" class="scanner-error p-10">
              <div class="w-16 h-16 bg-rose-500/20 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <ExclamationTriangleIcon class="w-8 h-8" stroke-width="2.5" />
              </div>
              <p class="font-black text-white text-lg tracking-tight">{{ cameraError }}</p>
              <p class="text-slate-400 text-sm mt-2 mb-6">Pastikan izin kamera sudah diberikan di browser Anda.</p>
              <button @click="startScanner" class="btn btn-primary rounded-xl px-8 py-3 font-bold">Coba Lagi</button>
            </div>
            
            <div id="reader" v-show="!cameraError && !isEventInactive" class="w-full"></div>
            
            <!-- Scanner Visual Overlay -->
            <div class="scanner-mask" v-if="isScanning && !cameraError && !isEventInactive">
              <div class="mask-top"></div>
              <div class="mask-bottom"></div>
              <div class="mask-left"></div>
              <div class="mask-right"></div>
              <div class="target-box">
                <div class="corner tl"></div>
                <div class="corner tr"></div>
                <div class="corner bl"></div>
                <div class="corner br"></div>
                <div class="scanner-laser"></div>
              </div>
            </div>

            <!-- SCAN FEEDBACK OVERLAY -->
            <div class="scanner-feedback-wrapper" v-if="lastResult || cooldownActive">
               <div :class="['status-card', lastResult?.success ? 'success' : (lastResult?.success === false ? 'error' : 'cooldown')]">
                  <div class="flex items-center gap-4">
                    <div class="icon-circle">
                      <CheckIcon v-if="lastResult?.success" class="w-6 h-6" stroke-width="3.5" />
                      <XMarkIcon v-else-if="lastResult?.success === false" class="w-6 h-6" stroke-width="3.5" />
                      <ArrowPathIcon v-else class="w-6 h-6 animate-spin" stroke-width="2.5" />
                    </div>
                    <div class="flex-1 text-left">
                      <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-0.5">
                        {{ lastResult?.success ? 'Scan Berhasil' : (lastResult?.success === false ? 'Gagal' : 'Siap Scan Dalam...') }}
                      </p>
                      <p class="text-base font-black tracking-tight leading-tight">
                        {{ lastResult ? lastResult.message : `${cooldownTimer.toFixed(1)} Detik` }}
                      </p>
                    </div>
                  </div>
                  <!-- Progress Bar -->
                  <div class="status-progress-bg" v-if="cooldownActive">
                    <div class="status-progress-bar" :style="{ width: (cooldownTimer / 2.0 * 100) + '%' }"></div>
                  </div>
               </div>
            </div>
          </div>
          
          <div class="card-footer p-5 bg-white border-t border-slate-100 flex justify-between items-center">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-slate-50 rounded-lg">
                <ShieldCheckIcon class="w-4 h-4 text-slate-400" />
              </div>
              <p class="text-xs font-bold text-slate-500">Scan terjeda otomatis selama 2 detik untuk keamanan.</p>
            </div>
            <button @click="toggleScanner" :class="['btn btn-sm h-10 px-5 font-black uppercase tracking-wider text-[11px] rounded-xl transition-all', isScanning ? 'btn-danger' : 'btn-primary']" :disabled="isEventInactive">
              {{ isScanning ? 'Stop Kamera' : 'Buka Kamera' }}
            </button>
          </div>
        </div>

        <!-- Manual Token Input -->
        <div class="card border-0 shadow-xl shadow-slate-200/40">
          <div class="card-header border-b-0 pb-0">
            <div class="flex items-center gap-3">
              <div class="h-9 w-9 rounded-xl bg-amber-500 text-white shadow-lg shadow-amber-500/30 flex items-center justify-center">
                <PencilSquareIcon class="h-5 w-5" stroke-width="2.5" />
              </div>
              <span class="text-sm font-black text-slate-900 tracking-tight">Metode Token</span>
            </div>
          </div>
          <div class="card-body pt-4">
            <form @submit.prevent="processManualToken" class="flex gap-3">
              <input 
                type="text" 
                v-model="manualToken" 
                class="form-input flex-1 h-12 uppercase text-center font-black tracking-[0.5em] text-lg rounded-xl border-slate-200 focus:ring-amber-500 focus:border-amber-500" 
                placeholder="ABC12"
                maxlength="5"
                required
                :disabled="isEventInactive"
              >
              <button 
                type="submit" 
                class="btn btn-primary h-12 px-8 font-black uppercase tracking-wider"
                :disabled="processingToken || cooldownActive || isEventInactive"
              >
                {{ processingToken ? '...' : 'Proses' }}
              </button>
            </form>
          </div>
        </div>

        <div class="guest-search-card card border-0 shadow-xl shadow-slate-200/40">
          <!-- Card Header -->
          <div class="card-header border-b border-slate-100 bg-gradient-to-r from-sky-50 to-indigo-50">
            <div class="flex items-center gap-3">
              <!-- Icon: person with magnifier -->
              <div class="search-card-icon">
                <UsersIcon class="h-5 w-5" />
              </div>
              <div>
                <span class="text-sm font-black text-slate-900 tracking-tight">Cari Nama Tamu</span>
                <span class="block text-[10px] text-slate-500 font-bold uppercase tracking-widest">Pencarian Cepat · Ketik minimal 2 huruf</span>
              </div>
            </div>
            <!-- Chip badge count -->
            <div v-if="searchResults.length > 0" class="flex items-center gap-1.5 px-3 py-1 bg-sky-100 rounded-full">
              <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
              <span class="text-[11px] font-black text-sky-700">{{ searchResults.length }} ditemukan</span>
            </div>
          </div>

          <div class="card-body pt-5">
            <!-- Search Input -->
            <div class="search-input-wrapper">
              <!-- Left icon -->
              <div class="search-input-icon">
                <MagnifyingGlassIcon v-if="!isSearching" class="w-5 h-5" stroke-width="2.5" />
                <ArrowPathIcon v-else class="animate-spin w-5 h-5" stroke-width="2.5" />
              </div>

              <input
                type="text"
                v-model="searchQuery"
                class="search-input-field"
                placeholder="Nama tamu..."
                @input="onSearchInput"
                autocomplete="off"
                :disabled="isEventInactive"
              >

              <!-- Clear button -->
              <button
                v-if="searchQuery"
                @click="searchQuery = ''; searchResults = []"
                class="search-clear-btn"
                title="Hapus pencarian"
              >
                <XMarkIcon class="w-4 h-4" stroke-width="2.5" />
              </button>
            </div>



            <!-- Results list -->
            <div v-if="searchResults.length > 0" class="mt-4 space-y-2 max-h-[340px] overflow-y-auto pr-1">
              <div
                v-for="g in searchResults"
                :key="g.id"
                class="search-result-item"
              >
                <!-- Avatar initial -->
                <div class="search-result-avatar" :class="g.checkin_time ? (g.checkin_status === 'checkout' ? 'avatar-out' : 'avatar-in') : 'avatar-default'">
                  {{ g.name?.charAt(0)?.toUpperCase() }}
                </div>

                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="font-black text-slate-900 text-sm truncate">{{ g.name }}</span>
                    <span class="search-type-chip">{{ g.type }}</span>
                  </div>
                  <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                    <span class="inline-flex items-center gap-1 text-[10px] text-slate-400 font-bold">
                      <TableCellsIcon class="w-3 h-3" />
                      {{ g.table_number || 'TBA' }}
                    </span>
                    <span v-if="g.checkin_time" class="inline-flex items-center gap-1 text-[10px] font-black uppercase" :class="g.checkin_status === 'checkout' ? 'text-rose-500' : 'text-emerald-500'">
                      <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="g.checkin_status === 'checkout' ? 'bg-rose-400' : 'bg-emerald-400'"></span>
                      {{ g.checkin_status === 'checkout' ? 'Sudah Keluar' : 'Sudah Masuk' }}
                    </span>
                    <span v-else class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-300">
                      <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                      Belum hadir
                    </span>
                  </div>
                </div>

                <!-- Action button -->
                <button
                  v-if="!g.checkin_time || g.checkin_status === 'checkout'"
                  @click="processManual(g.id)"
                  class="search-action-btn"
                  :class="[g.checkin_status === 'checkout' ? 'action-reincheck' : 'action-checkin', isEventInactive ? 'opacity-50 cursor-not-allowed' : '']"
                  :disabled="processingManual === g.id || cooldownActive || isEventInactive"
                >
                  <ArrowPathIcon v-if="processingManual === g.id" class="animate-spin w-4 h-4" stroke-width="2.5" />
                  <template v-else>
                    <ArrowRightOnRectangleIcon class="w-3.5 h-3.5" stroke-width="2.5" />
                    <span class="text-[10px] font-black uppercase tracking-wider">
                      {{ g.checkin_status === 'checkout' ? 'In Lagi' : 'Check In' }}
                    </span>
                  </template>
                </button>
              </div>
            </div>

            <!-- Empty state (no result) -->
            <div v-else-if="searchQuery.length >= 2 && !isSearching" class="mt-4 py-8 text-center">
              <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <UserMinusIcon class="w-7 h-7 text-slate-300" />
              </div>
              <p class="text-sm font-black text-slate-500">Tamu tidak ditemukan</p>
              <p class="text-xs text-slate-400 mt-1">Coba kata kunci lain atau periksa ejaan nama</p>
            </div>
          </div>
        </div>

      </div>

      <!-- HISTORY SECTION -->
      <div class="card h-fit sticky top-6 border-0 shadow-2xl shadow-slate-200/50">
        <div class="card-header bg-slate-50/50 backdrop-blur-sm">
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/30 flex items-center justify-center">
              <ClockIcon class="h-5 w-5" stroke-width="2.5" />
            </div>
            <div>
              <span class="block text-sm font-black text-slate-900 tracking-tight">Riwayat Scan</span>
              <span class="block text-[10px] text-slate-500 font-bold uppercase tracking-widest">Aktivitas Terakhir</span>
            </div>
          </div>
          <button @click="history = []" class="text-[10px] font-black text-slate-400 hover:text-rose-500 uppercase tracking-widest transition-colors">Clear</button>
        </div>
        <div class="card-body p-0 max-h-[calc(100vh-250px)] overflow-y-auto">
          <div v-if="history.length > 0" class="divide-y divide-slate-50">
            <div v-for="(item, idx) in history" :key="idx" class="p-5 flex items-start gap-4 hover:bg-slate-50/50 transition-all duration-300">
              <div :class="['h-11 w-11 rounded-2xl flex items-center justify-center shrink-0 shadow-sm', item.success ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600']">
                <CheckIcon v-if="item.success" class="h-6 w-6" stroke-width="2.5" />
                <XMarkIcon v-else class="h-6 w-6" stroke-width="2.5" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start mb-0.5">
                  <span class="font-black text-sm text-slate-900 truncate pr-2 tracking-tight">{{ item.name || 'Tamu Tidak Dikenal' }}</span>
                  <span class="text-[10px] font-bold text-slate-400 tabular-nums uppercase">{{ item.time }}</span>
                </div>
                <p :class="['text-xs font-bold leading-tight', item.success ? 'text-emerald-600' : 'text-rose-600']">{{ item.message }}</p>
                <div v-if="item.guest" class="mt-2.5 flex flex-wrap gap-1.5">
                  <span class="px-2 py-0.5 bg-slate-100 text-[9px] font-black text-slate-500 uppercase rounded-md tracking-wider">Meja {{ item.guest.table_number || '-' }}</span>
                  <span class="px-2 py-0.5 bg-slate-100 text-[9px] font-black text-slate-500 uppercase rounded-md tracking-wider">{{ item.guest.type }}</span>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="p-12 text-center">
            <div class="w-16 h-16 bg-slate-50 text-slate-200 rounded-3xl flex items-center justify-center mx-auto mb-4">
              <ClockIcon class="w-8 h-8" />
            </div>
            <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Belum ada aktivitas</p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue';
import axios from 'axios';
import { debounce } from 'lodash-es';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Html5Qrcode } from 'html5-qrcode';
import { notify } from '@/Utils/SweetAlert';
import { SafeStorage } from '@/Utils/Storage';
import { 
  ArrowPathIcon, 
  CameraIcon, 
  ExclamationTriangleIcon, 
  CheckIcon, 
  XMarkIcon, 
  ShieldCheckIcon,
  PencilSquareIcon,
  MagnifyingGlassIcon,
  ClockIcon,
  UsersIcon,
  TableCellsIcon,
  ArrowRightOnRectangleIcon,
  ArrowLeftOnRectangleIcon,
  UserMinusIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  eventId: [Number, String],
  events: Array,
});

const selectedEventId = ref(props.eventId || (props.events?.length > 0 ? props.events[0].id : null));
const selectedEvent = computed(() => props.events?.find(e => e.id === selectedEventId.value));
const isEventExpired = computed(() => selectedEvent.value?.is_expired || false);
const isEventNotStarted = computed(() => selectedEvent.value?.is_not_started || false);
const isEventInactive = computed(() => selectedEvent.value?.is_inactive || false);

const html5QrCode = ref(null);
const isScanning = ref(false);
const lastResult = ref(null);
const history = ref([]);
const cameraError = ref(null);

const isOfflineMode = ref(SafeStorage.parseJson(SafeStorage.getItem('offline_mode_active'), false));
const downloading = ref(false);
const syncing = ref(false);
const offlineGuests = ref([]);
const offlineQueue = ref(SafeStorage.parseJson(SafeStorage.getItem('offline_queue'), []));

const manualToken = ref('');
const processingToken = ref(false);
const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const processingManual = ref(null);

const cooldownActive = ref(false);
const cooldownTimer = ref(0);
const lastScannedToken = ref(null);
let isUnmounted = false;

onMounted(() => {
  isUnmounted = false;
  startScanner();
  loadOfflineData();
});

onBeforeUnmount(() => {
  isUnmounted = true;
  stopScanner();
});

watch(isOfflineMode, (newVal) => {
  SafeStorage.setItem('offline_mode_active', JSON.stringify(newVal));
  if (newVal) {
    searchResults.value = []; // clear search on toggle
    searchQuery.value = '';
  }
});

watch(selectedEventId, () => {
  loadOfflineData();
  if (isEventInactive.value && isScanning.value) {
    stopScanner();
  } else if (!isEventInactive.value && !isScanning.value) {
    startScanner();
  }
});

const loadOfflineData = () => {
  if (!selectedEventId.value) return;
  const data = SafeStorage.getItem(`offline_data_${selectedEventId.value}`);
  offlineGuests.value = SafeStorage.parseJson(data, []);
};

const downloadForOffline = async () => {
  if (!selectedEventId.value) return notify.error('Pilih event terlebih dahulu.');
  
  const result = await notify.confirm(
    'Download Database Tamu?',
    'Tindakan ini akan mendownload database tamu ke browser untuk mode offline. Lanjutkan?',
    'Ya, Download',
    'info'
  );
  if (!result.isConfirmed) return;

  downloading.value = true;
  try {
    const response = await axios.post(route('scanner.offline.download'), { event_id: selectedEventId.value });
    offlineGuests.value = response.data.guests;
    SafeStorage.setItem(`offline_data_${selectedEventId.value}`, JSON.stringify(response.data.guests));
    notify.success('Download Berhasil!', `Berhasil mendownload ${response.data.guests.length} data tamu untuk offline.`);
  } catch (err) { 
    console.error(err);
    notify.error('Gagal mendownload data.', 'Pastikan koneksi internet stabil.'); 
  }
  finally { downloading.value = false; }
};

const syncOfflineData = async () => {
  if (offlineQueue.value.length === 0) return;
  if (!selectedEventId.value) return notify.error('Pilih event terlebih dahulu.');
  
  const result = await notify.confirm(
    'Sinkronisasi Data Offline?',
    `Apakah Anda yakin ingin mengirim ${offlineQueue.value.length} data kehadiran ke server? Pastikan koneksi internet Anda stabil.`,
    'Ya, Sinkronisasikan',
    'info'
  );
  if (!result.isConfirmed) return;
  
  syncing.value = true;
  try {
    const response = await axios.post(route('scanner.offline.sync'), { 
      event_id: selectedEventId.value, 
      checkins: offlineQueue.value 
    });
    
    if (response.data.success) {
      offlineQueue.value = [];
      SafeStorage.setItem('offline_queue', JSON.stringify([]));
      notify.success('Sinkronisasi Berhasil!', `${response.data.results.length} data telah terkirim.`);
    } else {
      throw new Error(response.data.message || 'Gagal sinkronisasi.');
    }
  } catch (err) { 
    console.error(err);
    const msg = err.response?.data?.message || err.message || 'Gagal sinkronisasi. Silakan coba lagi.';
    notify.error('Eror Sinkronisasi', msg); 
  }
  finally { syncing.value = false; }
};

const startScanner = () => {
  if (isEventInactive.value) return;
  cameraError.value = null;
  html5QrCode.value = new Html5Qrcode("reader");
  const config = { 
    fps: 15, 
    qrbox: { width: 250, height: 250 },
    aspectRatio: 1.0
  };
  
  html5QrCode.value.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure)
    .then(() => {
      isScanning.value = true;
      if (isUnmounted) {
        stopScanner();
      }
    })
    .catch(err => {
      console.error(err);
      cameraError.value = "Kamera tidak dapat diakses.";
      isScanning.value = false;
    });
};

const stopScanner = async () => {
  if (html5QrCode.value) {
    try {
      await html5QrCode.value.stop();
    } catch (e) { 
      console.error('Stop scanner error:', e); 
    }
    
    try {
      html5QrCode.value.clear();
    } catch (e) {}
    
    html5QrCode.value = null;
  }
  isScanning.value = false;
};

const toggleScanner = () => isScanning.value ? stopScanner() : startScanner();

const onScanSuccess = (decodedText) => {
  // Prevent duplicate scan if cooldown is active
  if (cooldownActive.value) return;
  
  // Anti-spam: check if it's the same token as last time within a short window
  if (decodedText === lastScannedToken.value && lastResult.value) return;

  processScan(decodedText, 'qr');
};

const onScanFailure = () => {};

const processScan = async (qrCode, method = 'qr') => {
  if (!selectedEventId.value) return handleError({ message: 'Pilih event terlebih dahulu.' });
  
  // Audio Feedback
  try {
    const context = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = context.createOscillator();
    const gain = context.createGain();
    oscillator.connect(gain); gain.connect(context.destination);
    oscillator.type = 'sine'; 
    oscillator.frequency.setValueAtTime(isOfflineMode.value ? 660 : 880, context.currentTime);
    gain.gain.setValueAtTime(0.05, context.currentTime);
    oscillator.start(); oscillator.stop(context.currentTime + 0.1);
  } catch (e) {}

  lastScannedToken.value = qrCode;

  if (isOfflineMode.value) return processOfflineScan(qrCode, method);

  lastResult.value = { processing: true, message: 'Memproses...' };
  try {
    const response = await axios.post(route('scanner.scan'), { 
      qr_code: qrCode, 
      event_id: selectedEventId.value,
      method: method
    });
    handleSuccess(response.data);
    startCooldown();
  } catch (err) {
    handleError(err.response?.data || { message: 'Gagal diproses.' });
    startCooldown(1500);
  }
};

const processOfflineScan = (qrCode, method = 'qr') => {
  // Check in offline database
  const guest = offlineGuests.value.find(g => g.qr_code === qrCode);
  
  if (!guest) {
    return handleError({ message: 'Tamu tidak ditemukan di Database Lokal.' });
  }

  // Check if already in queue to prevent double scan offline
  // except for checkout which is allowed, but for simplicity of UI we just let the backend handle duplicates/checkout
  // Since offline doesn't know attendance type perfectly, we just push it.
  // We'll allow multiple pushes to queue for the same qr, as long as it's not spam
  
  // Optimistic UI update
  const timeStr = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  if (!guest.checkin_time) {
      guest.checkin_time = timeStr;
      guest.checkin_status = 'checkin';
  } else if (guest.checkin_status !== 'checkout') {
      guest.checkout_time = timeStr;
      guest.checkin_status = 'checkout';
  } else {
      guest.checkin_time = timeStr;
      guest.checkout_time = null;
      guest.checkin_status = 'checkin';
  }
  
  SafeStorage.setItem(`offline_data_${selectedEventId.value}`, JSON.stringify(offlineGuests.value));

  const result = { 
    guest, 
    message: `(Offline) Berhasil: ${guest.name}` 
  };
  
  offlineQueue.value.push({ 
    qr_code: qrCode, 
    time: new Date().toISOString(),
    method: method // simpan method offline juga
  });
  
  SafeStorage.setItem('offline_queue', JSON.stringify(offlineQueue.value));
  handleSuccess(result);
  startCooldown(1500);
};

const startCooldown = (ms = 2000) => {
  cooldownActive.value = true;
  cooldownTimer.value = ms / 1000;
  
  const interval = setInterval(() => {
    cooldownTimer.value -= 0.1;
    if (cooldownTimer.value <= 0) { 
      clearInterval(interval); 
      cooldownActive.value = false; 
      lastScannedToken.value = null;
    }
  }, 100);
};

const handleSuccess = (data) => {
  lastResult.value = { success: true, message: data.message };
  history.value.unshift({ 
    success: true, 
    name: data.guest?.name || '-', 
    message: data.message, 
    time: new Date().toLocaleTimeString('id-ID'), 
    guest: data.guest 
  });
  
  // Clear result banner after 3s
  setTimeout(() => { 
    if (lastResult.value?.message === data.message) lastResult.value = null; 
  }, 3000);
};

const handleError = (data) => {
  lastResult.value = { success: false, message: data.message };
  history.value.unshift({ 
    success: false, 
    name: 'Gagal Scan', 
    message: data.message, 
    time: new Date().toLocaleTimeString('id-ID') 
  });
  
  setTimeout(() => { 
    if (lastResult.value?.message === data.message) lastResult.value = null; 
  }, 3000);
};

const processManualToken = async () => {
  if (cooldownActive.value) return;
  const token = manualToken.value.trim().toUpperCase();
  if (token.length !== 5) return handleError({ message: 'Token harus 5 karakter.' });
  
  processingToken.value = true;
  try { 
    await processScan(token, 'token'); 
    manualToken.value = ''; 
  } finally { 
    processingToken.value = false; 
  }
};

const onSearchInput = debounce(async () => {
  if (searchQuery.value.length < 2) { 
    searchResults.value = []; 
    return; 
  }
  isSearching.value = true;
  
  if (isOfflineMode.value) {
    const q = searchQuery.value.toLowerCase();
    searchResults.value = offlineGuests.value
      .filter(g => g.name.toLowerCase().includes(q))
      .slice(0, 10);
    isSearching.value = false;
    return;
  }
  
  try {
    const response = await axios.post(route('scanner.search'), { 
      name: searchQuery.value, 
      event_id: selectedEventId.value 
    });
    searchResults.value = response.data.guests;
  } catch (err) {
    console.error(err);
  } finally { 
    isSearching.value = false; 
  }
}, 300);

const processManual = async (guestId) => {
  if (cooldownActive.value) return;
  
  processingManual.value = guestId;
  
  if (isOfflineMode.value) {
    const guest = searchResults.value.find(g => g.id === guestId);
    if (guest) {
      processOfflineScan(guest.qr_code, 'manual');
    }
    processingManual.value = null;
    return;
  }

  try {
    const response = await axios.post(route('scanner.manual'), { guest_id: guestId });
    handleSuccess(response.data);
    onSearchInput(); // Refresh results to show new status
    startCooldown(1500);
  } catch (err) { 
    handleError(err.response?.data || { message: 'Gagal memproses manual.' }); 
  }
  finally { processingManual.value = null; }
};
</script>

<style scoped>
/* ===========================
   GUEST SEARCH CARD
   =========================== */

/* Icon header - gradient blue-to-indigo */
.search-card-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
}

/* Input wrapper */
.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input-icon {
  position: absolute;
  left: 14px;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  pointer-events: none;
  transition: color 0.2s;
  z-index: 1;
}
.search-input-icon svg { width: 18px; height: 18px; }

.search-input-field {
  width: 100%;
  height: 48px;
  padding-left: 44px;
  padding-right: 44px;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
  background: #fff;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.search-input-field::placeholder { color: #94a3b8; font-weight: 500; }
.search-input-field:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12), 0 2px 8px rgba(99, 102, 241, 0.08);
}
.search-input-wrapper:focus-within .search-input-icon { color: #6366f1; }

/* Clear button */
.search-clear-btn {
  position: absolute;
  right: 12px;
  width: 28px;
  height: 28px;
  border-radius: 8px;
  border: none;
  background: #f1f5f9;
  color: #94a3b8;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s, color 0.15s;
}
.search-clear-btn:hover { background: #fee2e2; color: #ef4444; }
.search-clear-btn svg { width: 12px; height: 12px; }

/* Hint chips */
.search-hint-label {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
.search-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 20px;
  border: 1.5px solid #e2e8f0;
  background: #f8fafc;
  color: #475569;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
}
.search-chip:hover {
  border-color: #6366f1;
  background: #eef2ff;
  color: #4f46e5;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.15);
}

/* Result items */
.search-result-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: #f8fafc;
  border: 1.5px solid #f1f5f9;
  border-radius: 14px;
  transition: all 0.15s;
}
.search-result-item:hover {
  background: #ffffff;
  border-color: #c7d2fe;
  box-shadow: 0 2px 10px rgba(99, 102, 241, 0.1);
}

/* Avatar */
.search-result-avatar {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  font-weight: 900;
  flex-shrink: 0;
  letter-spacing: -0.5px;
}
.avatar-default { background: #f1f5f9; color: #64748b; border: 1.5px solid #e2e8f0; }
.avatar-in { background: #ecfdf5; color: #059669; border: 1.5px solid #a7f3d0; }
.avatar-out { background: #fff1f2; color: #e11d48; border: 1.5px solid #fecdd3; }

/* Type chip */
.search-type-chip {
  padding: 2px 7px;
  border-radius: 6px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  font-size: 9px;
  font-weight: 900;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 0.05em;
  flex-shrink: 0;
}

/* Action buttons */
.search-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 0 12px;
  height: 36px;
  border-radius: 10px;
  border: none;
  font-size: 10px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  cursor: pointer;
  flex-shrink: 0;
  transition: all 0.15s;
}
.search-action-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.action-checkin { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; box-shadow: 0 3px 10px rgba(99, 102, 241, 0.35); }
.action-checkin:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(99, 102, 241, 0.45); }
.action-checkout { background: linear-gradient(135deg, #f59e0b, #f97316); color: white; box-shadow: 0 3px 10px rgba(245, 158, 11, 0.35); }
.action-checkout:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(245, 158, 11, 0.45); }
.action-reincheck { background: #f1f5f9; color: #64748b; border: 1.5px solid #e2e8f0; }
.action-reincheck:hover:not(:disabled) { background: #e2e8f0; color: #1e293b; }

.scanner-page-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 1024px) {
  .scanner-page-grid {
    grid-template-columns: 1fr 1fr;
    align-items: start;
  }
}

.scanner-container { 
  position: relative; 
  background: #0f172a; 
  min-height: 400px; 
  display: flex; 
  align-items: center; 
  justify-content: center; 
}

#reader { 
  width: 100% !important; 
  border: none !important; 
}

#reader video { 
  border-radius: 0 !important; 
  object-fit: cover !important;
}

/* Custom Scanner Mask */
.scanner-mask {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 10;
  pointer-events: none;
}

.mask-top, .mask-bottom {
  position: absolute;
  left: 0;
  right: 0;
  background: rgba(15, 23, 42, 0.7);
}

.mask-top { top: 0; height: calc(50% - 125px); }
.mask-bottom { bottom: 0; height: calc(50% - 125px); }

.mask-left, .mask-right {
  position: absolute;
  top: calc(50% - 125px);
  height: 250px;
  background: rgba(15, 23, 42, 0.7);
}

.mask-left { left: 0; width: calc(50% - 125px); }
.mask-right { right: 0; width: calc(50% - 125px); }

.target-box {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 250px;
  height: 250px;
  transform: translate(-50%, -50%);
  border: 2px solid rgba(255, 255, 255, 0.1);
  overflow: hidden;
}

.corner {
  position: absolute;
  width: 20px;
  height: 20px;
  border: 4px solid var(--primary);
}

.tl { top: 0; left: 0; border-right: 0; border-bottom: 0; border-top-left-radius: 12px; }
.tr { top: 0; right: 0; border-left: 0; border-bottom: 0; border-top-right-radius: 12px; }
.bl { bottom: 0; left: 0; border-right: 0; border-top: 0; border-bottom-left-radius: 12px; }
.br { bottom: 0; right: 0; border-left: 0; border-top: 0; border-bottom-right-radius: 12px; }

.scanner-laser { 
  width: 100%; 
  height: 2px; 
  background: linear-gradient(to right, transparent, var(--primary), transparent);
  box-shadow: 0 0 15px var(--primary); 
  animation: laser 2.5s infinite ease-in-out; 
  position: absolute; 
  z-index: 11;
}

@keyframes laser { 
  0% { transform: translateY(0); opacity: 0; } 
  10% { opacity: 1; }
  50% { transform: translateY(250px); } 
  90% { opacity: 1; }
  100% { transform: translateY(0); opacity: 0; } 
}

/* FEEDBACK OVERLAY */
.scanner-feedback-wrapper { 
  position: absolute; 
  bottom: 30px; 
  left: 30px; 
  right: 30px; 
  z-index: 50; 
}

.status-card { 
  padding: 16px 24px; 
  border-radius: 24px; 
  color: white; 
  box-shadow: 0 10px 40px rgba(0,0,0,0.5); 
  animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
  position: relative;
  overflow: hidden;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

.status-card.success { background: rgba(16, 185, 129, 0.95); }
.status-card.error { background: rgba(244, 63, 94, 0.95); }
.status-card.cooldown { background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); }

.icon-circle {
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.status-progress-bg {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: rgba(255, 255, 255, 0.1);
}

.status-progress-bar {
  height: 100%;
  background: var(--primary);
  transition: width 0.1s linear;
}

@keyframes slideUp { 
  from { transform: translateY(40px); opacity: 0; scale: 0.9; } 
  to { transform: translateY(0); opacity: 1; scale: 1; } 
}

.scanner-error svg { 
  filter: drop-shadow(0 0 10px rgba(244, 63, 94, 0.3)); 
}
</style>
