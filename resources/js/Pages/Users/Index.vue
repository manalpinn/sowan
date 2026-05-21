<template>
  <AdminLayout
    page-title="Manajemen User"
    :breadcrumbs="[{ label: 'Dashboard', href: route('dashboard') }, { label: 'User' }]"
  >
    <div class="card overflow-hidden">
      <div class="card-header flex-col lg:flex-row items-start lg:items-center gap-4 bg-card-bg">
        <div>
          <span class="card-title block">Daftar Pengguna</span>
          <span class="text-[11px] text-muted font-medium uppercase tracking-wider">Kelola akses dan penugasan admin event</span>
        </div>
        
        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto lg:ml-auto">
          <div class="search-wrapper">
            <MagnifyingGlassIcon class="search-icon h-4 w-4" stroke-width="2.5" />
            <input
              type="text"
              v-model="search"
              class="form-input search-input"
              placeholder="Cari nama atau email..."
              @input="onSearchChange"
            >
          </div>

          <Link :href="route('users.create')" class="btn btn-primary w-full sm:w-auto">
            <PlusIcon class="h-4 w-4" stroke-width="2.5" />
            Tambah User
          </Link>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table min-w-full">
          <thead>
            <tr>
              <th class="px-6 py-4">Nama & Email</th>
              <th class="px-6 py-4">Peran</th>
              <th class="px-6 py-4">Penugasan Event</th>
              <th class="px-6 py-4">Terdaftar</th>
              <th class="px-6 py-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users.data" :key="user.id" class="group transition-all duration-200">
              <td class="px-6 py-4">
                <div class="flex flex-col">
                  <span class="font-bold text-primary group-hover:text-primary transition-colors">{{ user.name }}</span>
                  <span class="text-xs text-muted font-medium">{{ user.email }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <span class="badge" :class="user.role === 'superadmin' ? 'badge-vvip' : 'badge-regular'">
                  {{ user.role === 'superadmin' ? 'Super Admin' : 'Admin Event' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div v-if="user.event" class="flex items-center gap-2.5">
                  <div class="h-2 w-2 rounded-full shadow-sm" :style="{ backgroundColor: user.event.theme_color || 'var(--primary)' }"></div>
                  <span class="text-sm font-semibold text-secondary capitalize">{{ user.event.name }}</span>
                </div>
                <span v-else class="text-xs text-muted italic font-medium">— Belum ditugaskan —</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-xs text-secondary font-medium">
                {{ user.created_at }}
              </td>
              <td class="px-6 py-4 text-center">
                <div class="action-group">
                  <Link :href="route('users.edit', user.id)" class="btn-icon btn-info" title="Edit">
                    <PencilSquareIcon class="h-4 w-4" />
                  </Link>
                  <button 
                    @click="deleteUser(user)" 
                    class="btn-icon btn-danger" 
                    :disabled="user.id === $page.props.auth.user.id" 
                    title="Hapus"
                  >
                    <TrashIcon class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="users.data.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-muted italic font-medium bg-bg-base">User tidak ditemukan.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 bg-card-bg border-t border-border flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-sm text-secondary">
          Menampilkan <span class="font-bold text-primary">{{ users.from || 0 }}</span> – <span class="font-bold text-primary">{{ users.to || 0 }}</span> dari <span class="font-bold text-primary">{{ users.total }}</span> user
        </div>
        <Pagination :links="users.links" />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { debounce } from 'lodash-es';
import { notify } from '@/Utils/SweetAlert';
import { 
  MagnifyingGlassIcon, 
  PlusIcon, 
  PencilSquareIcon, 
  TrashIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  users: Object,
  filters: Object,
});

const search = ref(props.filters?.search || '');

const onSearchChange = debounce(() => {
  router.get(route('users.index'), { search: search.value }, {
    preserveState: true,
    replace: true,
  });
}, 300);

async function deleteUser(user) {
  const result = await notify.confirm(
    'Hapus User?',
    `Apakah Anda yakin ingin menghapus user "${user.name}"? Akses user ini akan dicabut sepenuhnya.`,
    'Ya, Hapus'
  );
  
  if (result.isConfirmed) {
    router.delete(route('users.destroy', user.id));
  }
}
</script>

<style scoped>
.action-group { display: flex; gap: 8px; justify-content: center; }
</style>
