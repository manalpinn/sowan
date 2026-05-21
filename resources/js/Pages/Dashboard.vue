<template>
  <AdminLayout page-title="Dashboard" :breadcrumbs="[{ label: 'Dashboard' }]">
    <div class="py-8 space-y-10 max-w-7xl mx-auto">
      <!-- Welcome Header -->
      <header class="dashboard-header">
        <div class="dashboard-header__info">
          <h2 class="dashboard-header__title">Selamat Datang, <span>{{ $page.props.auth.user.name }}!</span></h2>
          <p class="dashboard-header__sub">
            <span class="live-dot"></span>
            Dashboard Overview &bull; {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
          </p>
        </div>
        <div class="dashboard-header__actions">
          <!-- Refresh button -->
          <button
            @click="doRefresh"
            class="refresh-btn"
            :class="{ 'is-loading': isRefreshing }"
            :disabled="isRefreshing"
            title="Refresh Data"
          >
            <ArrowPathIcon class="refresh-icon" stroke-width="2.5" />
            <span class="refresh-label">{{ isRefreshing ? 'Memuat...' : 'Refresh' }}</span>
          </button>

          <!-- New Event button (superadmin only) -->
          <Link
            v-if="role === 'superadmin'"
            :href="route('events.create')"
            class="new-event-btn"
          >
            <PlusIcon class="w-4 h-4" stroke-width="3" />
            <span>Event Baru</span>
          </Link>
        </div>
      </header>

      <!-- SUPERADMIN VIEW -->
      <template v-if="role === 'superadmin'">
      <!-- Key Statistics -->
      <section class="space-y-4">
        <div class="flex items-center gap-3">
           <div class="h-6 w-1.5 bg-indigo-600 rounded-full"></div>
           <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Statistik Utama</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" v-if="stats">
          <StatCard label="Total Event" :value="stats?.total_events ?? 0" color="var(--primary)" icon="events" class="shadow-sm" />
          <StatCard label="Total Tamu" :value="stats?.total_guests ?? 0" color="var(--primary)" icon="guests" class="shadow-sm" />
          <StatCard label="Total Check-in" :value="stats?.total_checkins ?? 0" color="var(--success)" icon="checkin" class="shadow-sm" />
          <StatCard label="Total Check-out" :value="stats?.total_checkouts ?? 0" color="var(--warning)" icon="checkout" class="shadow-sm" />
        </div>
      </section>

      <!-- Main Analytics & Graphics -->
      <section class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Chart: Events Trend -->
          <div class="lg:col-span-2 card p-8 shadow-sm">
            <div class="flex items-center justify-between mb-8">
              <div class="flex items-center gap-3">
                <div class="h-6 w-1.5 bg-primary rounded-full"></div>
                <h3 class="text-xs font-extrabold text-muted uppercase tracking-widest">Tren Event Bulanan</h3>
              </div>
              <div class="flex gap-2">
                <span class="w-3 h-3 bg-primary rounded-full"></span>
                <span class="text-[10px] font-bold text-muted uppercase">Jumlah Event</span>
              </div>
            </div>
            <div class="h-[350px]">
               <apexchart type="area" height="100%" :options="eventsPerMonthOptions" :series="eventsPerMonthSeries" />
            </div>
          </div>

          <!-- Distribution Chart -->
          <div class="card p-8 shadow-sm border-slate-100/80 bg-white rounded-3xl relative overflow-hidden group">
            <div class="flex items-center justify-between mb-8 relative z-10">
              <div class="flex items-center gap-3">
                <div class="h-6 w-1.5 bg-success rounded-full"></div>
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Status Kehadiran</h3>
              </div>
            </div>
            
            <div class="h-[320px] flex flex-col justify-center relative z-10">
               <apexchart type="donut" height="280" :options="checkinOutOptions" :series="checkinOutSeries" />
                <div class="mt-8 grid grid-cols-2 gap-3">
                  <div class="flex flex-col px-4 py-3 bg-success-soft/50 rounded-2xl border border-success-soft/80 group-hover:bg-success-soft transition-colors">
                    <span class="text-[10px] font-bold text-success uppercase tracking-wider mb-1">Total Hadir</span>
                    <span class="text-xl font-black text-success tabular-nums">{{ charts?.checkinOut?.checkin ?? 0 }}</span>
                  </div>
                  <div class="flex flex-col px-4 py-3 bg-warning-soft/50 rounded-2xl border border-warning-soft/80 group-hover:bg-warning-soft transition-colors">
                    <span class="text-[10px] font-bold text-warning uppercase tracking-wider mb-1">Sudah Keluar</span>
                    <span class="text-xl font-black text-warning tabular-nums">{{ charts?.checkinOut?.checkout ?? 0 }}</span>
                  </div>
               </div>
            </div>
          </div>
        </div>

        <!-- Guest Attendance Bar Chart -->
        <div class="grid grid-cols-1 gap-6">
          <div class="card p-8 shadow-sm border-slate-100/80 bg-white rounded-3xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
              <div class="flex items-center gap-3">
                <div class="flex flex-col">
                  <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">
                    Kehadiran Tamu per Event ({{ stats?.total_events ?? 0 }} Event)
                  </h3>
                  <p class="text-[10px] font-bold text-slate-400 mt-0.5">Laporan partisipasi tamu pada seluruh event yang terdaftar</p>
                </div>
              </div>
              <div class="flex items-center gap-4 ml-9 sm:ml-0">
                <div class="flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-slate-200"></span>
                  <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Total Tamu</span>
                </div>
                <div class="flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                  <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Sudah Hadir</span>
                </div>
              </div>
            </div>
            <div class="h-[320px]">
              <apexchart type="bar" height="100%" :options="guestAttendanceOptions" :series="guestAttendanceSeries" />
            </div>
          </div>
        </div>
      </section>

      <!-- Recent Activity Feed -->
      <section class="space-y-4" v-if="charts?.recentCheckins?.length">
        <div class="flex items-center gap-3">
          <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Aktivitas Check-in Terbaru</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="c in charts.recentCheckins" :key="c.id"
            class="card px-5 py-4 flex items-center gap-4 transition-all hover:border-violet-200 group">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-violet-50 text-violet-600 group-hover:bg-violet-600 group-hover:text-white transition-colors">
              <ArrowLeftOnRectangleIcon v-if="c.status === 'checkout'" class="w-5 h-5" stroke-width="2.5" />
              <ArrowRightOnRectangleIcon v-else class="w-5 h-5" stroke-width="2.5" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-black text-slate-700 truncate group-hover:text-violet-700 transition-colors">{{ c.guest_name }}</p>
              <p class="text-[11px] text-slate-400 font-bold truncate uppercase tracking-tight">{{ c.event_name }}</p>
            </div>
            <div class="text-right shrink-0">
              <span class="text-[9px] font-black uppercase tracking-wider px-2 py-1 rounded-lg border transition-all"
                :class="c.status === 'checkout' ? 'bg-warning-soft text-warning border-warning-soft' : 'bg-success-soft text-success border-success-soft'">
                {{ c.status }}
              </span>
              <p class="text-[10px] text-slate-400 mt-1.5 font-bold">{{ c.time }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Full-Width Event Table -->
      <section class="space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">
            Ringkasan Event Terbaru
          </h3>
          <Link :href="route('events.index')" class="text-[10px] font-black text-violet-600 hover:text-white hover:bg-violet-600 border border-violet-100 bg-white px-4 py-2 rounded-xl transition-all uppercase tracking-widest">Lihat Semua &rarr;</Link>
        </div>
        
        <div class="card overflow-hidden shadow-sm border-slate-100/80 bg-white rounded-3xl" v-if="events">
          <div class="table-wrapper">
            <table class="data-table w-full">
              <thead>
                <tr>
                  <th class="pl-8 pr-4 py-4 text-left border-b border-slate-50">
                    <div class="flex items-center gap-2">
                      <CalendarIcon class="w-3.5 h-3.5 text-slate-400" stroke-width="2.5" />
                      <span class="text-[10px] uppercase tracking-widest">Event</span>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-left border-b border-slate-50">
                    <div class="flex items-center gap-2">
                      <MapPinIcon class="w-3.5 h-3.5 text-slate-400" stroke-width="2.5" />
                      <span class="text-[10px] uppercase tracking-widest">Lokasi</span>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-center border-b border-slate-50">
                    <div class="flex items-center justify-center gap-2">
                      <ShieldCheckIcon class="w-3.5 h-3.5 text-slate-400" stroke-width="2.5" />
                      <span class="text-[10px] uppercase tracking-widest">Status</span>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-center border-b border-slate-50">
                    <div class="flex items-center justify-center gap-2">
                      <UserGroupIcon class="w-3.5 h-3.5 text-slate-400" stroke-width="2.5" />
                      <span class="text-[10px] uppercase tracking-widest">Tamu</span>
                    </div>
                  </th>
                  <th class="pl-4 pr-8 py-4 text-right border-b border-slate-50">
                    <div class="flex items-center justify-end gap-2">
                      <CheckBadgeIcon class="w-3.5 h-3.5 text-slate-400" stroke-width="2.5" />
                      <span class="text-[10px] uppercase tracking-widest">Hadir</span>
                    </div>
                  </th>
                </tr>
              </thead>
              <tbody>
                <template v-if="events?.data && events.data.length > 0">
                  <tr v-for="e in events.data" :key="e?.id" class="transition-all hover:bg-slate-50/50 border-b border-slate-50 last:border-0 group">
                    <td class="pl-8 pr-4 py-6">
                      <div class="flex flex-col">
                        <span class="font-black text-slate-700 text-sm group-hover:text-violet-600 transition-colors">{{ e.name }}</span>
                        <span class="text-[11px] text-slate-400 mt-1 font-bold">{{ e.date }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-6">
                      <span class="text-xs text-secondary font-semibold">{{ e.location ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-6 text-center">
                      <span class="badge" :class="e.status === 'Aktif' ? 'badge-attending' : 'badge-regular'">
                        {{ e.status }}
                      </span>
                    </td>
                    <td class="px-6 py-6 text-center">
                      <span class="text-sm font-black text-primary">{{ e.total_guests ?? 0 }}</span>
                    </td>
                    <td class="pl-4 pr-8 py-6">
                      <div class="flex items-center justify-end gap-5">
                        <div class="flex flex-col items-end">
                          <span class="text-sm font-black text-slate-700">{{ e.total_checkins ?? 0 }}</span>
                          <span class="px-2 py-0.5 bg-violet-50 text-violet-600 rounded-lg text-[9px] font-black uppercase tracking-wider mt-1">{{ (e.total_guests > 0 ? Math.round(e.total_checkins / e.total_guests * 100) : 0) }}%</span>
                        </div>
                        <div class="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden hidden sm:block">
                          <div class="h-full bg-violet-500 rounded-full transition-all duration-1000 ease-out" 
                            :style="{ width: (e.total_guests > 0 ? (e.total_checkins / e.total_guests * 100) : 0) + '%' }">
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                </template>
                <tr v-else>
                  <td colspan="5" class="px-8 py-24 text-center">
                    <div class="flex flex-col items-center">
                      <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-200 mb-4">
                        <CalendarIcon class="w-8 h-8" />
                      </div>
                      <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Belum ada data event yang tersedia.</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </template>

      <!-- ADMIN EVENT VIEW -->
      <template v-else>
        <!-- Event Banner Header -->
        <section v-if="event">
          <div class="border border-slate-100 shadow-sm rounded-3xl p-8 flex flex-col md:flex-row items-center gap-8 bg-white overflow-hidden relative">
            <div class="absolute top-0 right-0 w-64 h-64 bg-slate-50 rounded-full -mr-32 -mt-32 opacity-50"></div>
            <div class="h-20 w-20 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 shadow-sm relative z-10">
              <CalendarIcon class="h-10 w-10" stroke-width="1.5" />
            </div>
            <div class="text-center md:text-left relative z-10 flex-1">
              <h2 class="text-2xl font-black text-slate-800 leading-tight">{{ event?.name }}</h2>
              <div class="flex flex-wrap justify-center md:justify-start items-center gap-4 mt-4">
                 <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <CalendarDaysIcon class="h-4 w-4 text-indigo-500" />
                    <span>{{ event?.date }}</span>
                 </div>
                 <div class="w-1.5 h-1.5 bg-slate-200 rounded-full hidden md:block"></div>
                 <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <MapPinIcon class="h-4 w-4 text-indigo-500" />
                    <span>{{ event?.location }}</span>
                 </div>
              </div>
            </div>
             <div class="shrink-0 flex items-center gap-3">
               <Link :href="route('scanner.index')" class="scan-qr-btn">
                 <QrCodeIcon class="w-5 h-5" stroke-width="2.5" />
                 <span>Scan QR</span>
                 <span class="scan-live-dot"></span>
               </Link>
             </div>
          </div>
        </section>

        <!-- Admin Statistics -->
        <section class="space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" v-if="event && stats">
            <StatCard label="Total Tamu" :value="stats?.total_guests ?? 0" color="#6366F1" icon="guests" class="shadow-sm border-slate-100/50" />
            <StatCard label="Sudah Hadir" :value="stats?.checked_in ?? 0" color="#10B981" icon="checkin" class="shadow-sm border-slate-100/50" />
            <StatCard label="Belum Hadir" :value="stats?.not_arrived ?? 0" color="#F59E0B" icon="pending" class="shadow-sm border-slate-100/50" />
            <StatCard label="Check-out" :value="stats?.checked_out ?? 0" v-if="event?.attendance_type === 'checkin_checkout'" color="#6366F1" icon="checkout" class="shadow-sm border-slate-100/50" />
            <StatCard label="Konfirmasi Hadir" :value="stats?.rsvp_attending ?? 0" color="#10B981" icon="checkin" v-else class="shadow-sm border-slate-100/50" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" v-if="event && stats">
            <StatCard label="Total PAX (Hadir)" :value="stats?.total_pax ?? 0" color="#6366F1" icon="guests" class="shadow-sm border-slate-100/50" />
            <StatCard label="Hadir (Konfirmasi)" :value="stats?.rsvp_attending ?? 0" color="#10B981" icon="checkin" class="shadow-sm border-slate-100/50" />
            <StatCard label="Absen" :value="stats?.rsvp_declined ?? 0" color="#EF4444" icon="close" class="shadow-sm border-slate-100/50" />
            <StatCard label="Belum Konfirmasi" :value="(stats?.total_guests || 0) - (stats?.rsvp_attending || 0) - (stats?.rsvp_declined || 0)" color="#94A3B8" icon="users" class="shadow-sm border-slate-100/50" />
          </div>
        </section>

        <!-- Admin Charts -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-8" v-if="event && charts">
          <div class="card p-8 shadow-sm border-slate-100/80 bg-white rounded-3xl">
            <div class="flex items-center gap-3 mb-8">
              <div class="h-6 w-1.5 bg-indigo-500 rounded-full"></div>
              <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-widest">Distribusi Tipe Tamu</h3>
            </div>
            <div class="h-[300px]">
              <apexchart type="donut" height="100%" :options="guestTypeOptions" :series="guestTypeSeries" />
            </div>
          </div>
          <div class="card p-8 shadow-sm border-slate-100/80 bg-white rounded-3xl">
            <div class="flex items-center gap-3 mb-8">
              <div class="h-6 w-1.5 bg-emerald-500 rounded-full"></div>
              <h3 class="text-xs font-extrabold text-slate-400 uppercase tracking-widest">Waktu Kedatangan</h3>
            </div>
            <div class="h-[300px]">
              <apexchart type="bar" height="100%" :options="arrivalOptions" :series="arrivalSeries" />
            </div>
          </div>
        </section>
      </template>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import Pagination from '@/Components/Pagination.vue';
import VueApexCharts from 'vue3-apexcharts';
import { 
  ArrowPathIcon, 
  PlusIcon, 
  ArrowLeftOnRectangleIcon, 
  ArrowRightOnRectangleIcon,
  CalendarIcon, 
  CalendarDaysIcon, 
  MapPinIcon, 
  QrCodeIcon,
  UserGroupIcon,
  ShieldCheckIcon,
  CheckBadgeIcon
} from '@heroicons/vue/24/outline';

const apexchart = VueApexCharts;
const isRefreshing = ref(false);

function doRefresh() {
  isRefreshing.value = true;
  router.reload({
    onFinish: () => { isRefreshing.value = false; },
  });
}

const props = defineProps({
  role: String,
  stats: Object,
  events: Object,
  event: Object,
  charts: Object,
});

/**
 * Superadmin Chart Data
 */
const eventsPerMonthSeries = computed(() => [{
  name: 'Jumlah Event',
  data: props.charts?.eventsPerMonth?.map(m => m.total) ?? [],
}]);
const eventsPerMonthOptions = computed(() => ({
  chart: { toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
  colors: ['#6366F1'],
  fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05 } },
  xaxis: { categories: props.charts?.eventsPerMonth?.map(m => m.month) ?? [], axisBorder: { show: false } },
  stroke: { curve: 'smooth', width: 3 },
  dataLabels: { enabled: false },
  grid: { borderColor: '#f8fafc', strokeDashArray: 4 },
}));

const guestAttendanceSeries = computed(() => [
  { name: 'Total Tamu', data: props.charts?.guestAttendance?.map(e => e.total) ?? [] },
  { name: 'Hadir', data: props.charts?.guestAttendance?.map(e => e.present) ?? [] }
]);
const guestAttendanceOptions = computed(() => ({
  chart: { toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
  colors: ['#F1F5F9', '#6366F1'],
  plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
  xaxis: { categories: props.charts?.guestAttendance?.map(e => e.name) ?? [], labels: { show: false }, axisBorder: { show: false } },
  dataLabels: { enabled: false },
  grid: { borderColor: '#f8fafc', strokeDashArray: 4 },
  legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px', fontWeight: 600 },
}));

const checkinOutSeries = computed(() => [props.charts?.checkinOut?.checkin ?? 0, props.charts?.checkinOut?.checkout ?? 0]);
const checkinOutOptions = computed(() => ({
  labels: ['Check-in', 'Check-out'],
  colors: ['#10B981', '#F59E0B'],
  chart: { fontFamily: 'Plus Jakarta Sans, sans-serif' },
  legend: { position: 'bottom' },
  plotOptions: { pie: { donut: { size: '70%' } } },
  dataLabels: { enabled: false },
}));

const guestTypeSeries = computed(() => props.charts?.guestTypes?.map(g => g.total) ?? []);
const guestTypeOptions = computed(() => ({
  labels: props.charts?.guestTypes?.map(g => g.type) ?? [],
  chart: { fontFamily: 'Plus Jakarta Sans, sans-serif' },
  colors: ['#6366F1', '#8B5CF6', '#EC4899', '#F59E0B', '#10B981'],
  legend: { position: 'bottom', fontSize: '11px' },
  plotOptions: { pie: { donut: { size: '70%' } } },
  dataLabels: { enabled: false },
}));

/**
 * Admin Event Chart Data
 */
const arrivalSeries = computed(() => [{
  name: 'Kedatangan',
  data: props.charts?.arrivalTrend?.map(t => t.total) ?? [],
}]);
const arrivalOptions = computed(() => ({
  chart: { toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
  colors: ['#10B981'],
  xaxis: { categories: props.charts?.arrivalTrend?.map(t => t.hour) ?? [] },
  plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
  dataLabels: { enabled: false },
  grid: { borderColor: '#f1f5f9' },
}));
</script>

<style scoped>
/* ===========================
   DASHBOARD HEADER
   =========================== */
.dashboard-header {
  display: flex;
  flex-direction: column;
  gap: 14px;
  border-bottom: 1px solid var(--border);
  padding-bottom: 24px;
}
@media (min-width: 640px) {
  .dashboard-header {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }
}

.dashboard-header__info { min-width: 0; flex: 1; }

.dashboard-header__title {
  font-size: 20px;
  font-weight: 900;
  color: var(--text-primary);
  letter-spacing: -0.02em;
  line-height: 1.3;
}
.dashboard-header__title span { color: var(--primary); }
@media (min-width: 768px) {
  .dashboard-header__title { font-size: 22px; }
}

.dashboard-header__sub {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.07em;
  margin-top: 5px;
  flex-wrap: wrap;
}
.live-dot {
  width: 7px; height: 7px;
  background: var(--success);
  border-radius: 50%;
  animation: livePulse 2s infinite;
  flex-shrink: 0;
}
@keyframes livePulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(0.8); }
}

.dashboard-header__actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
  flex-wrap: wrap;
}

/* Refresh button */
.refresh-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 10px;
  border: 1.5px solid var(--border);
  background: var(--card-bg);
  color: var(--text-muted);
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.18s;
  box-shadow: var(--shadow-sm);
  white-space: nowrap;
}
.refresh-btn:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
  background: var(--hover-bg);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}
.refresh-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.refresh-icon {
  width: 16px; height: 16px;
  flex-shrink: 0;
}
.refresh-btn.is-loading .refresh-icon {
  animation: spinIcon 0.8s linear infinite;
}
@keyframes spinIcon {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}

/* New Event button */
.new-event-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 10px;
  background: linear-gradient(135deg, #6366f1, #7c3aed);
  color: white;
  font-size: 13px;
  font-weight: 800;
  text-decoration: none;
  box-shadow: 0 3px 10px rgba(99,102,241,0.3);
  transition: all 0.18s;
  white-space: nowrap;
}
.new-event-btn:hover {
  background: linear-gradient(135deg, #4f46e5, #6d28d9);
  transform: translateY(-1px);
  box-shadow: 0 5px 16px rgba(99,102,241,0.4);
}
.new-event-btn svg { width: 14px; height: 14px; }

.progress-mini { width: 50px; height: 5px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
.progress-fill { height: 100%; border-radius: 999px; transition: width 0.5s ease; }
.card-header.border-b-0 { padding-bottom: 0; }

/* Scan QR Button */
.scan-qr-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 14px;
  font-weight: 800;
  font-size: 14px;
  color: white;
  background: linear-gradient(135deg, #6366f1 0%, #7c3aed 100%);
  box-shadow: 0 4px 16px rgba(99, 102, 241, 0.4);
  text-decoration: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  letter-spacing: 0.01em;
}
.scan-qr-btn:hover {
  background: linear-gradient(135deg, #4f46e5 0%, #6d28d9 100%);
  box-shadow: 0 6px 24px rgba(99, 102, 241, 0.55);
  transform: translateY(-2px);
}
.scan-qr-btn:active { transform: translateY(0); }

/* Live pulse dot */
.scan-live-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #a5f3fc;
  box-shadow: 0 0 0 0 rgba(165, 243, 252, 0.6);
  animation: scanPulse 1.6s infinite ease-out;
  flex-shrink: 0;
}
@keyframes scanPulse {
  0%   { box-shadow: 0 0 0 0 rgba(165, 243, 252, 0.7); }
  70%  { box-shadow: 0 0 0 6px rgba(165, 243, 252, 0); }
  100% { box-shadow: 0 0 0 0 rgba(165, 243, 252, 0); }
}

/* Data Table Styling */
.table-wrapper {
  overflow-x: auto;
}
.data-table th {
  background: var(--bg-soft);
  color: var(--text-muted);
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid var(--border);
  white-space: nowrap;
}
.data-table tr {
  border-bottom: 1px solid var(--border);
}
.data-table tr:last-child {
  border-bottom: none;
}
.data-table td {
  padding: 1.25rem 1.5rem;
}

/* Badge Styles */
.badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 12px;
  border-radius: 9999px;
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.badge-attending {
  background: var(--success-soft);
  color: var(--success);
}
.badge-regular {
  background: var(--bg-soft);
  color: var(--text-muted);
}
</style>
