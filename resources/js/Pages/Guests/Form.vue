<template>
  <AdminLayout
    :page-title="guest ? 'Edit Tamu' : 'Tambah Tamu'"
    :breadcrumbs="[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Tamu', href: route('guests.index', { event_id: guest?.event_id ?? default_event_id }) }, { label: guest ? 'Edit' : 'Tambah' }]"
  >
    <div class="card" style="max-width: 600px; margin: 0 auto;">
      <div class="card-header">
        <span class="card-title">{{ guest ? 'Perbarui Data Tamu' : 'Tambah Tamu Baru' }}</span>
      </div>
      <div class="card-body">
        <form @submit.prevent="submit">
          <div class="mb-5 p-4 bg-primary/10 border border-primary/20 rounded-lg flex items-center gap-3 text-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider opacity-80 mb-0.5">Event Saat Ini</p>
              <p class="text-sm font-bold m-0">{{ selectedEventName }}</p>
            </div>
          </div>
          <input type="hidden" v-model="form.event_id">
          <div v-if="form.errors.event_id" class="form-error mb-4">{{ form.errors.event_id }}</div>

          <div class="form-group">
            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" v-model="form.name" class="form-input" placeholder="Contoh: Bpk. Ahmad Fauzi" required>
            <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
          </div>

          <div class="grid grid-2 gap-6">
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" v-model="form.email" class="form-input" placeholder="ahmad@example.com">
              <div v-if="form.errors.email" class="form-error">{{ form.errors.email }}</div>
            </div>
            <div class="form-group">
              <label class="form-label">Nomor WhatsApp</label>
              <input type="text" v-model="form.phone" class="form-input" placeholder="628123456789">
              <div v-if="form.errors.phone" class="form-error">{{ form.errors.phone }}</div>
            </div>
          </div>

          <div class="grid grid-2 gap-6">
            <div class="form-group">
              <label class="form-label">Tipe Tamu <span class="text-danger">*</span></label>
              <select v-model="form.type" class="form-select" required>
                <option value="Regular">Regular</option>
                <option value="VIP">VIP</option>
                <option value="VVIP">VVIP</option>
                <option value="Vendor">Vendor</option>
              </select>
              <div v-if="form.errors.type" class="form-error">{{ form.errors.type }}</div>
            </div>
            <div class="form-group">
              <label class="form-label">Nomor Meja</label>
              <input type="text" v-model="form.table_number" class="form-input" placeholder="Contoh: 12">
              <div v-if="form.errors.table_number" class="form-error">{{ form.errors.table_number }}</div>
            </div>
          </div>

          <div class="flex gap-4 mt-6">
            <button type="submit" class="btn btn-primary px-8" :disabled="form.processing">
              <CheckIcon v-if="!form.processing" class="h-4 w-4 mr-1" stroke-width="2.5" />
              {{ form.processing ? 'Menyimpan...' : (guest ? 'Simpan Perubahan' : 'Tambah Tamu') }}
            </button>
            <Link :href="route('guests.index', { event_id: form.event_id })" class="btn btn-secondary">
              <XMarkIcon class="h-4 w-4 mr-1" />
              Batal
            </Link>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  guest: Object,
  events: Array,
  default_event_id: [String, Number],
});

const form = useForm({
  event_id: props.guest?.event_id ?? props.guest?.event?.id ?? (props.events.length === 1 ? props.events[0].id : (props.default_event_id || '')),
  name: props.guest?.name ?? '',
  email: props.guest?.email ?? '',
  phone: props.guest?.phone ?? '',
  type: props.guest?.type ?? 'Regular',
  table_number: props.guest?.table_number ?? '',
});

const selectedEventName = computed(() => {
  const event = props.events.find(e => e.id === form.event_id);
  return event ? event.name : 'Pilih Event';
});

function submit() {
  if (props.guest) {
    form.patch(route('guests.update', props.guest.id));
  } else {
    form.post(route('guests.store'));
  }
}
</script>

<style scoped>
.text-danger { color: var(--danger); }
</style>
