<template>
  <AdminLayout
    :page-title="user ? 'Edit User' : 'Tambah User'"
    :breadcrumbs="[{ label: 'Dashboard', href: route('dashboard') }, { label: 'User', href: route('users.index') }, { label: user ? 'Edit' : 'Tambah' }]"
  >
    <div class="card" style="max-width: 600px; margin: 0 auto;">
      <div class="card-header">
        <span class="card-title">{{ user ? 'Perbarui Akun Pengguna' : 'Buat Akun Baru' }}</span>
      </div>
      <div class="card-body">
        <form @submit.prevent="submit">
          <div class="form-group">
            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" v-model="form.name" class="form-input" placeholder="Nama admin" required>
            <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
          </div>

          <div class="form-group">
            <label class="form-label">Alamat Email <span class="text-danger">*</span></label>
            <input type="email" v-model="form.email" class="form-input" placeholder="admin@example.com" required>
            <div v-if="form.errors.email" class="form-error">{{ form.errors.email }}</div>
          </div>

          <div class="grid grid-2 gap-6">
            <div class="form-group">
              <label class="form-label">Role <span class="text-danger">*</span></label>
              <select v-model="form.role" class="form-select" required>
                <option value="admin_event">Admin Event</option>
                <option value="superadmin">Super Admin</option>
              </select>
              <div v-if="form.errors.role" class="form-error">{{ form.errors.role }}</div>
            </div>
            <div class="form-group" v-if="form.role === 'admin_event'">
              <label class="form-label">Penugasan Event <span class="text-danger">*</span></label>
              <select v-model="form.event_id" class="form-select" :required="form.role === 'admin_event'">
                <option value="">Pilih Event...</option>
                <option v-for="event in events" :key="event.id" :value="event.id">{{ event.name }}</option>
              </select>
              <div v-if="form.errors.event_id" class="form-error">{{ form.errors.event_id }}</div>
            </div>
          </div>

          <div class="divider mt-4 mb-6"></div>

          <div class="form-group">
            <label class="form-label">{{ user ? 'Ganti Password (Kosongkan jika tidak ingin mengubah)' : 'Password' }} <span v-if="!user" class="text-danger">*</span></label>
            <input type="password" v-model="form.password" class="form-input" :required="!user">
            <div v-if="form.errors.password" class="form-error">{{ form.errors.password }}</div>
          </div>

          <div class="form-group">
            <label class="form-label">Konfirmasi Password <span v-if="!user" class="text-danger">*</span></label>
            <input type="password" v-model="form.password_confirmation" class="form-input" :required="!user">
          </div>

          <div class="flex gap-4 mt-8">
            <button type="submit" class="btn btn-primary px-8" :disabled="form.processing">
              <CheckIcon v-if="!form.processing" class="h-4 w-4 mr-1" stroke-width="2.5" />
              {{ form.processing ? 'Menyimpan...' : (user ? 'Simpan Perubahan' : 'Buat User') }}
            </button>
            <Link :href="route('users.index')" class="btn btn-secondary">
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
  user: Object,
  events: Array,
});

const form = useForm({
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',
  role: props.user?.role ?? 'admin_event',
  event_id: props.user?.event_id ?? '',
  password: '',
  password_confirmation: '',
});

function submit() {
  if (props.user) {
    form.patch(route('users.update', props.user.id));
  } else {
    form.post(route('users.store'));
  }
}
</script>

<style scoped>
.text-danger { color: var(--danger); }
</style>
