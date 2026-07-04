<template>
  <AdminLayout
    :page-title="`Detail Event: ${event.name}`"
    :breadcrumbs="[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Event', href: route('events.index') }, { label: 'Detail' }]"
  >
    <EventTabs current="overview" :event-id="event.id" />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
      <StatCard label="Total Tamu" :value="event.stats.total_guests" color="#7C3AED" icon="guests" />
       <StatCard label="Sudah Hadir" :value="event.stats.checked_in" color="#10B981" icon="checkin" />
      <StatCard label="Belum Hadir" :value="event.stats.not_arrived" color="#F59E0B" icon="pending" />
      <StatCard label="Total RSVP Masuk" :value="event.stats.total_rsvp" color="#3B82F6" icon="rsvp" />
      <StatCard label="Konfirmasi Hadir" :value="event.stats.rsvp_attending" :sub="`Total Pax: ${event.stats.rsvp_pax} Orang`" color="#EC4899" icon="pax" />
      <StatCard label="Check-out" :value="event.stats.checked_out" color="#64748B" icon="checkout" />
    </div>

    <div class="grid grid-2">
      <div class="card">
        <div class="card-header">
          <span class="card-title">Informasi Acara</span>
          <Link :href="route('events.edit', event.id)" class="btn btn-secondary flex items-center gap-2">
            <PencilSquareIcon class="w-4 h-4" />
            Edit Event
          </Link>
        </div>
        <div class="card-body">
          <div class="info-list">
            <div class="info-item">
              <span class="info-label">Nama Event</span>
              <span class="info-value font-bold">{{ event.name }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Tanggal</span>
              <span class="info-value">{{ event.start_date }} - {{ event.end_date }}</span>
            </div>
            <div class="info-item" v-if="event.start_time">
              <span class="info-label">Jam</span>
              <span class="info-value">{{ event.start_time }} - {{ event.end_time || 'Selesai' }} WIB</span>
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
              <EventStatusBadge :status="event.status" />
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
import { Link, router } from '@inertiajs/vue3';
import { PencilSquareIcon } from '@heroicons/vue/24/outline';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import EventStatusBadge from '@/Components/EventStatusBadge.vue';
import StatCard from '@/Components/StatCard.vue';
import EventTabs from '@/Components/EventTabs.vue';

defineProps({
  event: Object,
});
</script>

<style scoped>
.info-list { display: flex; flex-direction: column; gap: 16px; }
.info-item { display: flex; flex-direction: column; gap: 4px; }
.info-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; }
.info-value { font-size: 14px; color: var(--text-primary); }
</style>
