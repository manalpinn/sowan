<template>
  <AdminLayout
    :page-title="guest ? 'Edit Tamu' : 'Tambah Tamu'"
    :breadcrumbs="[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Tamu', href: route('guests.index') }, { label: guest ? 'Edit' : 'Tambah' }]"
  >
    <div class="card" style="max-width: 600px; margin: 0 auto;">
      <div class="card-header">
        <span class="card-title">{{ guest ? 'Perbarui Data Tamu' : 'Tambah Tamu Baru' }}</span>
      </div>
      <div class="card-body">
        <form @submit.prevent="submit">
          <div class="form-group" v-if="events.length > 1">
            <label class="form-label">Event <span class="text-danger">*</span></label>
            <select v-model="form.event_id" class="form-select" required>
              <option value="" disabled>Pilih Event</option>
              <option v-for="e in events" :key="e.id" :value="e.id">{{ e.name }}</option>
            </select>
            <div v-if="form.errors.event_id" class="form-error">{{ form.errors.event_id }}</div>
          </div>

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
                <option value="Media">Media</option>
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
            <Link :href="route('guests.index')" class="btn btn-secondary">
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
