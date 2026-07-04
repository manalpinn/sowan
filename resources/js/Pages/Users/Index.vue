<template>
  <AdminLayout
    page-title="Manajemen User"
    :breadcrumbs="[{ label: 'Dashboard', href: route('dashboard') }, { label: 'User' }]"
  >
    <div class="card overflow-hidden">
      <div class="card-header flex-col lg:flex-row items-start lg:items-center gap-4 bg-card-bg" style="border-bottom: 1px solid var(--border); padding: 1.25rem 1.5rem;">
        <div>
          <span class="card-title block text-lg font-bold">Daftar Pengguna</span>
          <span class="text-[11px] text-muted font-medium uppercase tracking-wider">Kelola akses dan penugasan admin event</span>
        </div>
        
        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto lg:ml-auto">
          <div class="search-wrapper relative">
            <MagnifyingGlassIcon class="search-icon h-4 w-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-muted" stroke-width="2.5" />
            <input
              type="text"
              v-model="search"
              class="form-input search-input pl-9 h-[38px] text-sm"
              placeholder="Cari nama atau email..."
              @input="onSearchChange"
            >
          </div>

          <button
            v-if="selectedIds.length > 0"
            @click="bulkDelete"
            class="btn btn-danger btn-sm"
            :disabled="processingBulk"
          >
            <ArrowPathIcon v-if="processingBulk" class="animate-spin h-4 w-4" />
            <TrashIcon v-else class="w-4 h-4" />
            Hapus ({{ selectedIds.length }})
          </button>

          <Link :href="route('users.create')" class="btn btn-primary btn-sm">
            <PlusIcon class="h-4 w-4" stroke-width="2.5" />
            Tambah User
          </Link>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table min-w-full">
          <thead>
            <tr>
              <th class="w-10 px-6 py-4 text-left">
                <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected" class="form-checkbox">
              </th>
              <th class="px-6 py-4">Nama & Email</th>
              <th class="px-6 py-4">Penugasan Event</th>
              <th class="px-6 py-4">Terdaftar</th>
              <th class="px-6 py-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users.data" :key="user.id" class="group transition-all duration-200">
              <td class="px-6 py-4 vertical-align-middle">
                <input 
                  v-if="user.id !== $page.props.auth.user.id"
                  type="checkbox" 
                  v-model="selectedIds" 
                  :value="user.id" 
                  class="form-checkbox"
                >
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-col">
                  <span class="font-bold text-primary group-hover:text-primary transition-colors">{{ user.name }}</span>
                  <span class="text-xs text-muted font-medium">{{ user.email }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div v-if="user.events && user.events.length > 0" class="flex items-center gap-2">
                  <span class="inline-flex items-center justify-center px-2.5 py-1.5 text-[11px] font-bold text-primary bg-primary-soft border border-primary-soft/50 rounded-md shadow-sm leading-none h-[28px]">
                    {{ user.events.length }} Event
                  </span>
                  <button 
                    @click="openViewEvents(user)"
                    class="inline-flex items-center justify-center px-2.5 py-1.5 text-[11px] font-bold text-slate-600 bg-white border border-slate-200 hover:border-primary/30 hover:bg-primary-soft hover:text-primary transition-all rounded-md shadow-sm gap-1.5 leading-none h-[28px] cursor-pointer"
                  >
                    <EyeIcon class="w-3.5 h-3.5" />
                    Lihat Event
                  </button>
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
                    v-if="user.id !== $page.props.auth.user.id"
                    @click="deleteUser(user)" 
                    class="btn-icon btn-danger" 
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

    <!-- Modal Lihat Event -->
    <ViewEventsModal 
      :show="isViewModalOpen" 
      :user="activeUser" 
      @close="isViewModalOpen = false" 
    />
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { debounce } from 'lodash-es';
import { notify } from '@/Utils/SweetAlert';
import ViewEventsModal from '@/Components/ViewEventsModal.vue';
import { 
  MagnifyingGlassIcon, 
  PlusIcon, 
  PencilSquareIcon, 
  TrashIcon,
  ArrowPathIcon,
  EyeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  users: Object,
  filters: Object,
});

const search = ref(props.filters?.search || '');

const isViewModalOpen = ref(false);
const activeUser = ref(null);

function openViewEvents(user) {
  activeUser.value = user;
  isViewModalOpen.value = true;
}

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

const selectedIds = ref([]);
const processingBulk = ref(false);

const page = usePage();

const validUsers = computed(() => {
  return props.users.data.filter(u => u.id !== page.props.auth.user.id);
});

const isAllSelected = computed(() => {
  return validUsers.value.length > 0 && selectedIds.value.length === validUsers.value.length;
});

function toggleSelectAll(event) {
  if (event.target.checked) {
    selectedIds.value = validUsers.value.map(u => u.id);
  } else {
    selectedIds.value = [];
  }
}

async function bulkDelete() {
  const result = await notify.confirm(
    'Hapus Banyak User?',
    `Apakah Anda yakin ingin menghapus ${selectedIds.value.length} user terpilih?`,
    'Ya, Hapus Semua',
    'warning'
  );
  
  if (result.isConfirmed) {
    processingBulk.value = true;
    router.post(route('users.bulk-delete'), {
      ids: selectedIds.value
    }, {
      onFinish: () => {
        processingBulk.value = false;
        selectedIds.value = [];
      }
    });
  }
}
</script>

<style scoped>
.action-group { display: flex; gap: 8px; justify-content: center; }
</style>
