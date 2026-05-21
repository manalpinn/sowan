<template>
  <AdminLayout
    :page-title="event ? 'Edit Event' : 'Tambah Event'"
    :breadcrumbs="[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Event', href: route('events.index') }, { label: event ? 'Edit' : 'Tambah' }]"
  >
    <div class="card" style="max-width: 800px; margin: 0 auto;">
      <div class="card-header">
        <span class="card-title">{{ event ? 'Perbarui Informasi Event' : 'Buat Event Baru' }}</span>
      </div>
      <div class="card-body">
        <form @submit.prevent="submit">
          <div class="form-group">
            <label class="form-label">Nama Event <span class="text-danger">*</span></label>
            <input type="text" v-model="form.name" class="form-input" placeholder="Contoh: Wedding of John & Jane" required>
            <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
          </div>

          <div class="grid grid-2 gap-6">
            <div class="form-group">
              <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
              <input type="date" v-model="form.start_date" class="form-input" required>
              <div v-if="form.errors.start_date" class="form-error">{{ form.errors.start_date }}</div>
            </div>
            <div class="form-group">
              <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
              <input type="date" v-model="form.end_date" class="form-input" required>
              <div v-if="form.errors.end_date" class="form-error">{{ form.errors.end_date }}</div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Lokasi <span class="text-danger">*</span></label>
            <input type="text" v-model="form.location" class="form-input" placeholder="Contoh: Hotel Mulia, Jakarta" required>
            <div v-if="form.errors.location" class="form-error">{{ form.errors.location }}</div>
          </div>

          <div class="form-group">
            <label class="form-label">Deskripsi</label>
            <textarea v-model="form.description" class="form-textarea" rows="3" placeholder="Deskripsi singkat acara..."></textarea>
          </div>

          <div class="grid grid-2 gap-6">
            <div class="form-group">
              <label class="form-label">Warna Tema (HEX)</label>
              <div class="flex gap-2">
                <input type="color" v-model="form.theme_color" class="h-10 w-12 rounded-lg border border-gray-300 p-1">
                <input type="text" v-model="form.theme_color" class="form-input" placeholder="#7C3AED">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Status</label>
              <select v-model="form.is_active" class="form-select">
                <option :value="true">Aktif</option>
                <option :value="false">Non-aktif</option>
              </select>
            </div>
          </div>

          <div class="grid grid-2 gap-6">
            <div class="form-group">
              <label class="form-label">Tipe Kehadiran</label>
              <select v-model="form.attendance_type" class="form-select">
                <option value="checkin_only">Hanya Check-in</option>
                <option value="checkin_checkout">Check-in & Check-out</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Template Undangan</label>
              <select v-model="form.invitation_template" class="form-select">
                <option value="formal">Formal / Default</option>
                <option value="wedding">Pernikahan (Wedding)</option>
                <option value="corporate">Perusahaan (Corporate)</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Pesan Sambutan (WhatsApp)</label>
            <textarea v-model="form.welcome_message" class="form-textarea" rows="2" placeholder="Pesan yang akan muncul di undangan WA..."></textarea>
          </div>

          <div class="flex gap-4 mt-6">
            <button type="submit" class="btn btn-primary px-8" :disabled="form.processing">
              <CheckIcon v-if="!form.processing" class="h-4 w-4 mr-1" stroke-width="2.5" />
              {{ form.processing ? 'Menyimpan...' : (event ? 'Simpan Perubahan' : 'Buat Event') }}
            </button>
            <Link :href="route('events.index')" class="btn btn-secondary">
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
  event: Object,
});

const form = useForm({
  name: props.event?.name ?? '',
  start_date: props.event?.start_date ? new Date(props.event.start_date).toISOString().split('T')[0] : '',
  end_date: props.event?.end_date ? new Date(props.event.end_date).toISOString().split('T')[0] : '',
  location: props.event?.location ?? '',
  description: props.event?.description ?? '',
  theme_color: props.event?.theme_color ?? '#7C3AED',
  welcome_message: props.event?.welcome_message ?? '',
  attendance_type: props.event?.attendance_type ?? 'checkin_only',
  invitation_template: props.event?.invitation_template ?? 'formal',
  is_active: props.event?.is_active ?? true,
});

function submit() {
  if (props.event) {
    form.patch(route('events.update', props.event.id));
  } else {
    form.post(route('events.store'));
  }
}
</script>

<style scoped>
.text-danger { color: var(--danger); }
</style>
