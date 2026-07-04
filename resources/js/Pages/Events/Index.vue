<template>
  <AdminLayout
    page-title="Manajemen Event"
    :breadcrumbs="[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Event' }]"
  >
    <div class="py-8 space-y-6 max-w-7xl mx-auto">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Daftar Event</h2>
          <p class="text-sm font-bold text-slate-400 dark:text-slate-400 mt-1">Kelola data event dengan mudah</p>
        </div>
        <Link v-if="role === 'superadmin'" :href="route('events.create')" class="new-event-btn">
          <PlusIcon stroke-width="3" />
          <span>Buat Event Baru</span>
        </Link>
      </div>

      <!-- Filters & Actions -->
      <div class="card p-4 sm:p-5 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 lg:w-2/3">
          <!-- Search -->
          <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
              <MagnifyingGlassIcon class="h-5 w-5 text-slate-400 dark:text-slate-400" />
            </div>
            <input 
              v-model="searchQuery" 
              @keyup.enter="applyFilters"
              type="text" 
              placeholder="Cari nama event..." 
              class="block w-full pl-10 pr-3 py-2.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-600 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-400/20 focus:border-indigo-500 transition-colors"
            >
          </div>
          <!-- Filter Status -->
          <select 
            v-model="statusFilter" 
            @change="applyFilters"
            class="block w-full sm:w-48 py-2.5 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-400/20 focus:border-indigo-500 transition-colors"
          >
            <option value="">Semua Status</option>
            <option value="Aktif">Aktif</option>
            <option value="Akan Datang">Akan Datang</option>
            <option value="Selesai">Selesai</option>
          </select>
          
          <!-- Sort Column -->
          <select 
            v-model="sortColumn" 
            @change="applyFilters"
            class="block w-full sm:w-48 py-2.5 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-400/20 focus:border-indigo-500 transition-colors"
          >
            <option value="start_date">Urutkan: Tanggal</option>
            <option value="name">Urutkan: Nama</option>
          </select>

          <!-- Sort Direction -->
          <button 
            @click="toggleSortDirection" 
            class="flex items-center justify-center p-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors shrink-0"
            title="Ubah arah urutan"
          >
            <BarsArrowDownIcon v-if="sortDirection === 'desc'" class="w-5 h-5" />
            <BarsArrowUpIcon v-else class="w-5 h-5" />
          </button>
        </div>
        
        <div class="flex items-center gap-2 lg:w-auto">
           <button 
             @click="resetFilters" 
             class="px-4 py-2.5 text-sm font-bold text-slate-500 dark:text-slate-400 dark:text-slate-400 hover:text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:bg-slate-800 rounded-xl transition-colors w-full sm:w-auto"
           >
             Reset
           </button>
        </div>
      </div>

      <!-- Table Content -->
      <div class="card overflow-hidden bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700/80 rounded-2xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700 uppercase tracking-wider text-[11px] font-black text-slate-400 dark:text-slate-400">
              <tr>
                <th class="px-6 py-4">Event Info</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-center">Tamu & Kehadiran</th>
                <th class="px-6 py-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
              <tr v-for="e in events.data" :key="e.id" class="hover:bg-slate-50 dark:bg-slate-900/50 transition-colors group">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0 shadow-sm" :style="e.theme_color ? { backgroundColor: e.theme_color + '15', color: e.theme_color, borderColor: e.theme_color + '30' } : {}">
                      <CalendarDaysIcon class="h-5 w-5" stroke-width="2" />
                    </div>
                    <div class="flex flex-col min-w-0">
                      <span class="font-bold text-slate-800 dark:text-slate-100 text-sm truncate group-hover:text-indigo-600 dark:text-indigo-400 transition-colors">{{ e.name }}</span>
                      <div class="flex items-center gap-1.5 mt-0.5 text-xs font-semibold text-slate-500 dark:text-slate-400 dark:text-slate-400">
                        <span>{{ e.date }}</span>
                        <span v-if="e.location">&bull; <span class="truncate max-w-[150px] inline-block align-bottom">{{ e.location }}</span></span>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-col gap-1 items-start">
                    <EventStatusBadge :status="e.status" />
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-col items-center justify-center gap-1.5">
                    <div class="flex items-center gap-4 text-xs font-bold text-slate-600 dark:text-slate-300 dark:text-slate-500">
                      <span title="Total Tamu"><UserGroupIcon class="w-4 h-4 inline-block text-slate-400 dark:text-slate-400 mr-1" />{{ e.total_guests }}</span>
                      <span title="Hadir" class="text-emerald-600 dark:text-emerald-400"><CheckBadgeIcon class="w-4 h-4 inline-block text-emerald-400 mr-1" />{{ e.total_checkins }}</span>
                    </div>
                    <div class="w-24 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                      <div class="h-full bg-emerald-500 rounded-full transition-all duration-1000 ease-out" 
                        :style="{ width: (e.total_guests > 0 ? (e.total_checkins / e.total_guests * 100) : 0) + '%' }">
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="route('events.show', e.id)" class="btn btn-primary" title="Kelola Event">
                      <EyeIcon class="w-4 h-4" />
                      <span>Kelola</span>
                    </Link>
                    <Link :href="route('events.edit', e.id)" class="btn btn-secondary" title="Edit Event">
                      <PencilSquareIcon class="w-4 h-4" />
                      <span>Edit</span>
                    </Link>
                    <button v-if="role === 'superadmin'" @click="deleteEvent(e)" class="btn btn-danger" title="Hapus Event">
                      <TrashIcon class="w-4 h-4" />
                      <span>Hapus</span>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="events.data.length === 0">
                <td colspan="4" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-slate-50 dark:bg-slate-900 rounded-full flex items-center justify-center text-slate-300 dark:text-slate-500 mb-4">
                      <CalendarDaysIcon class="w-8 h-8" />
                    </div>
                    <p class="text-sm font-bold text-slate-400 dark:text-slate-400 uppercase tracking-widest">Tidak ada event ditemukan.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30 flex items-center justify-between" v-if="events.total > 0">
          <div class="text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">
            Menampilkan <span class="text-slate-700 dark:text-slate-200">{{ events.from }}</span> - <span class="text-slate-700 dark:text-slate-200">{{ events.to }}</span> dari <span class="text-slate-700 dark:text-slate-200">{{ events.total }}</span>
          </div>
          <Pagination :links="events.links" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import EventStatusBadge from '@/Components/EventStatusBadge.vue';
import { notify } from '@/Utils/SweetAlert';
import { 
  PlusIcon, 
  CalendarDaysIcon,
  MapPinIcon,
  PencilSquareIcon,
  TrashIcon,
  EyeIcon,
  MagnifyingGlassIcon,
  UserGroupIcon,
  CheckBadgeIcon,
  BarsArrowDownIcon,
  BarsArrowUpIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  events: Object,
  filters: Object,
  role: String
});

const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const sortColumn = ref(props.filters?.sort || 'start_date');
const sortDirection = ref(props.filters?.direction || 'desc');

function applyFilters() {
  router.get(route('events.index'), {
    search: searchQuery.value,
    status: statusFilter.value,
    sort: sortColumn.value,
    direction: sortDirection.value
  }, {
    preserveState: true,
    preserveScroll: true
  });
}

function resetFilters() {
  searchQuery.value = '';
  statusFilter.value = '';
  sortColumn.value = 'start_date';
  sortDirection.value = 'desc';
  applyFilters();
}

function toggleSortDirection() {
  sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  applyFilters();
}

async function deleteEvent(event) {
  const result = await notify.confirm(
    'Hapus Event?',
    `Apakah Anda yakin ingin menghapus event "${event.name}"? Semua data tamu dan kehadiran terkait akan ikut terhapus.`,
    'Ya, Hapus'
  );
  
  if (result.isConfirmed) {
    router.delete(route('events.destroy', event.id));
  }
}
</script>
