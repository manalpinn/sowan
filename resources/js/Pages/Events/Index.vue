<template>
  <AdminLayout
    page-title="Manajemen Event"
    :breadcrumbs="[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Event' }]"
  >
    <div class="card">
      <div class="card-header">
        <span class="card-title">Daftar Event</span>
        <Link :href="route('events.create')" class="btn btn-primary">
          <PlusIcon class="h-4 w-4" stroke-width="2.5" />
          Tambah Event
        </Link>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Event</th>
              <th>Waktu & Lokasi</th>
              <th>Tamu</th>
              <th>Kehadiran</th>
              <th>Status</th>
              <th style="text-align: center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="event in events.data" :key="event.id">
              <td>
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-xl flex items-center justify-center text-white shadow-sm" :style="{ backgroundColor: event.theme_color || '#7C3AED' }">
                    <CalendarDaysIcon class="h-5 w-5" stroke-width="2" />
                  </div>
                  <span class="font-bold">{{ event.name }}</span>
                </div>
              </td>
              <td>
                <div class="flex flex-col">
                  <span class="text-sm font-medium">{{ event.start_date }} - {{ event.end_date }}</span>
                  <span class="text-xs text-muted">{{ event.location }}</span>
                </div>
              </td>
              <td>{{ event.total_guests }} Tamu</td>
              <td>
                <div class="flex items-center gap-2">
                  <div class="progress-mini" style="width: 40px;">
                    <div class="progress-fill" :style="{ width: (event.total_guests ? (event.total_checkins / event.total_guests * 100) : 0) + '%' }"></div>
                  </div>
                  <span class="text-xs font-medium">{{ event.total_checkins }} Hadir</span>
                </div>
              </td>
              <td>
                <span class="badge" :class="event.is_active ? 'badge-checked_in' : 'badge-failed'">
                  {{ event.is_active ? 'Aktif' : 'Non-aktif' }}
                </span>
              </td>
              <td>
                <div class="action-group">
                  <Link :href="route('events.show', event.id)" class="btn-icon btn-secondary" title="Detail">
                    <EyeIcon class="h-4 w-4" />
                  </Link>
                  <Link :href="route('events.edit', event.id)" class="btn-icon btn-info" title="Edit">
                    <PencilSquareIcon class="h-4 w-4" />
                  </Link>
                  <button @click="deleteEvent(event)" class="btn-icon btn-danger" title="Hapus">
                    <TrashIcon class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-sm text-gray-500 font-medium">
          Menampilkan <span class="font-bold text-gray-700">{{ events.from || 0 }}</span> - <span class="font-bold text-gray-700">{{ events.to || 0 }}</span> dari <span class="font-bold text-gray-700">{{ events.total }}</span> event
        </div>
        <Pagination :links="events.links" />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { notify } from '@/Utils/SweetAlert';
import { 
  PlusIcon, 
  CalendarDaysIcon, 
  EyeIcon, 
  PencilSquareIcon, 
  TrashIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  events: Object,
});

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

<style scoped>
.action-group { display: flex; gap: 8px; justify-content: center; }
.progress-mini { height: 6px; background: var(--border); border-radius: 999px; overflow: hidden; }
.progress-fill { height: 100%; background: var(--primary); border-radius: 999px; }
.table-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
</style>
