<template>
  <AdminLayout
    :page-title="`Detail Event: ${event.name}`"
    :breadcrumbs="[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Event', href: route('events.index') }, { label: 'Detail' }]"
  >
    <div class="grid grid-4 mb-6">
      <StatCard label="Total Tamu" :value="event.stats.total_guests" color="#7C3AED" icon="guests" />
      <StatCard label="Sudah Hadir" :value="event.stats.checked_in" color="#10B981" icon="checkin" />
      <StatCard label="Belum Hadir" :value="event.stats.not_arrived" color="#F59E0B" icon="pending" />
      <StatCard label="Check-out" :value="event.stats.checked_out" color="#3B82F6" icon="checkout" />
    </div>

    <div class="grid grid-2">
      <div class="card">
        <div class="card-header">
          <span class="card-title">Informasi Acara</span>
          <Link :href="route('events.edit', event.id)" class="btn btn-secondary btn-sm">Edit Event</Link>
        </div>
        <div class="card-body">
          <div class="info-list">
            <div class="info-item">
              <span class="info-label">Nama Event</span>
              <span class="info-value font-bold">{{ event.name }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Waktu</span>
              <span class="info-value">{{ event.start_date }} - {{ event.end_date }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Lokasi</span>
              <span class="info-value">{{ event.location }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Deskripsi</span>
              <span class="info-value text-muted">{{ event.description || 'Tidak ada deskripsi.' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Status</span>
              <span class="badge" :class="event.is_active ? 'badge-checked_in' : 'badge-failed'">
                {{ event.is_active ? 'Aktif' : 'Non-aktif' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <span class="card-title">Konfigurasi & Tema</span>
        </div>
        <div class="card-body">
          <div class="info-list">
            <div class="info-item">
              <span class="info-label">Warna Tema</span>
              <div class="flex items-center gap-3">
                <div class="h-6 w-6 rounded-full border border-gray-200" :style="{ backgroundColor: event.theme_color }"></div>
                <span class="info-value">{{ event.theme_color }}</span>
              </div>
            </div>
            <div class="info-item">
              <span class="info-label">Pesan Sambutan WA</span>
              <div class="p-3 bg-gray-50 rounded-lg text-sm italic border border-gray-100">
                "{{ event.welcome_message || 'Belum diatur.' }}"
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/StatCard.vue';

defineProps({
  event: Object,
});
</script>

<style scoped>
.info-list { display: flex; flex-direction: column; gap: 16px; }
.info-item { display: flex; flex-direction: column; gap: 4px; }
.info-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; }
.info-value { font-size: 14px; color: var(--text-primary); }
.btn-sm { padding: 4px 12px; font-size: 12px; }
</style>
