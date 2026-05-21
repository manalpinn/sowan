<template>
  <AdminLayout
    page-title="Daftar Tamu"
    :breadcrumbs="[{ label: 'Dashboard', href: route('dashboard') }, { label: 'Tamu' }]"
  >
    <div class="card">
      <!-- Filter Bar -->
      <div class="filter-bar">

        <!-- Kiri: filter inputs -->
        <div class="filter-inputs">
          <!-- Baris 1: Cari + Tipe -->
          <div class="filter-row">
            <!-- Search -->
            <div class="filter-field filter-field--search">
              <MagnifyingGlassIcon class="filter-icon h-4 w-4" />
              <input
                type="text"
                v-model="search"
                class="filter-input"
                placeholder="Cari tamu..."
                @input="onFilterChange"
              >
            </div>

            <!-- Type filter -->
            <div class="filter-field">
              <UsersIcon class="filter-icon h-4 w-4" />
              <select v-model="type" class="filter-input filter-select" @change="onFilterChange">
                <option value="">Semua Tipe</option>
                <option value="VIP">VIP</option>
                <option value="VVIP">VVIP</option>
                <option value="Regular">Regular</option>
                <option value="Vendor">Vendor</option>
                <option value="Media">Media</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Kanan: action buttons, center vertikal -->
        <div class="filter-actions">
          <!-- Bulk WA -->
          <button
            v-if="selectedIds.length > 0"
            @click="bulkSendWA"
            class="btn btn-secondary btn-sm"
            :disabled="processingBulk"
          >
            <ArrowPathIcon v-if="processingBulk" class="animate-spin h-4 w-4" />
            <PaperAirplaneIcon v-else class="w-4 h-4" />
            WA ({{ selectedIds.length }})
          </button>

          <button
            v-if="selectedIds.length > 0"
            @click="bulkDelete"
            class="btn btn-danger btn-sm"
            :disabled="processingBulk"
          >
            <TrashIcon class="w-4 h-4" />
            Hapus ({{ selectedIds.length }})
          </button>

          <button @click="showImport = true" class="btn btn-secondary btn-sm">
            <ArrowUpTrayIcon class="w-4 h-4" />
            Import
          </button>

          <Link :href="route('guests.create')" class="btn btn-primary btn-sm">
            <PlusIcon class="w-4 h-4" stroke-width="2.5" />
            Tambah Tamu
          </Link>
        </div>

      </div>

      <div class="table-wrapper overflow-x-auto">
        <table class="data-table min-w-full table-auto border-collapse">
          <thead>
            <tr>
              <th class="w-10 px-4 py-3 text-left">
                <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected" class="form-checkbox">
              </th>
              <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase tracking-wider">Nama Tamu</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase tracking-wider">Tipe</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase tracking-wider">Kontak</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase tracking-wider">Event</th>
              <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase tracking-wider">Kehadiran</th>
              <th class="px-4 py-3 text-center text-xs font-bold text-muted uppercase tracking-wider">WhatsApp</th>
              <th class="px-4 py-3 text-center text-xs font-bold text-muted uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="guest in guests.data" :key="guest.id" class="transition-colors">
              <td class="px-4 py-4 vertical-align-middle">
                <input type="checkbox" v-model="selectedIds" :value="guest.id" class="form-checkbox">
              </td>
              <td class="px-4 py-4 vertical-align-middle">
                <div class="flex flex-col min-w-[150px]">
                  <span class="font-bold truncate">{{ guest.name }}</span>
                  <span class="text-xs text-muted" v-if="guest.table_number">Meja: {{ guest.table_number }}</span>
                </div>
              </td>
              <td class="px-4 py-4 vertical-align-middle">
                <span class="badge" :class="'badge-' + guest.type.toLowerCase()">{{ guest.type }}</span>
              </td>
              <td class="px-4 py-4 vertical-align-middle">
                <div class="flex flex-col min-w-[150px]">
                  <span class="text-sm truncate">{{ guest.phone || '-' }}</span>
                  <span class="text-xs text-muted truncate">{{ guest.email || '-' }}</span>
                </div>
              </td>
              <td class="px-4 py-4 vertical-align-middle">
                <span class="text-sm font-medium whitespace-nowrap">{{ guest.event?.name }}</span>
              </td>
              <td class="px-4 py-4 vertical-align-middle">
                <div v-if="guest.checkin_time" class="flex flex-col items-start whitespace-nowrap">
                  <span class="badge" :class="guest.checkin_status === 'checkout' ? 'badge-checked_out' : 'badge-checked_in'">
                    {{ guest.checkin_status === 'checkout' ? 'Sudah Pulang' : 'Sudah Hadir' }}
                  </span>
                  <span class="text-[10px] mt-1 text-slate-400 font-medium ml-1">{{ guest.time_range }}</span>
                </div>
                <div v-else class="flex flex-col items-start gap-1">
                  <span v-if="guest.rsvp_status === 'attending'" class="badge badge-attending">
                    Hadir (RSVP)
                  </span>
                  <span v-else-if="guest.rsvp_status === 'declined'" class="badge badge-declined">
                    Absen
                  </span>
                  <span v-else class="badge badge-not_arrived">
                    Belum Hadir
                  </span>
                </div>
              </td>
              <td class="px-4 py-4 vertical-align-middle text-center">
                <div class="flex flex-col items-center gap-1">
                  <button 
                    v-if="guest.whatsapp_status !== 'delivered' && guest.whatsapp_status !== 'pending'"
                    @click="sendWA(guest)" 
                    class="btn btn-sm btn-primary py-1 px-3 text-[10px] whitespace-nowrap" 
                    :disabled="!guest.phone || processingIds.includes(guest.id)"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3 mr-1"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                    Kirim WA
                  </button>
                  <div v-else-if="guest.whatsapp_status === 'pending'" class="badge badge-pending">
                    Memproses...
                  </div>
                  <div v-else-if="guest.whatsapp_status === 'delivered'" class="flex flex-col items-center">
                    <span class="badge badge-success text-[10px]">Sudah Terkirim</span>
                    <span class="text-[9px] text-muted mt-0.5">{{ guest.wa_sent_at_formatted }}</span>
                  </div>
                  <button 
                    v-else-if="guest.whatsapp_status === 'failed'"
                    @click="sendWA(guest)" 
                    class="btn btn-sm btn-danger py-1 px-3 text-[10px] whitespace-nowrap" 
                    :disabled="!guest.phone || processingIds.includes(guest.id)"
                  >
                    Gagal - Coba Lagi
                  </button>
                </div>
              </td>
              <td class="px-4 py-4 vertical-align-middle text-center">
                <div class="action-group">
                  <a :href="guest.invitation_link" target="_blank" class="btn-icon btn-info" title="Buka Undangan Web">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                  </a>
                  <button @click="copyLink(guest.invitation_link)" class="btn-icon btn-secondary" title="Copy Link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                  </button>
                  <a :href="route('guests.pdf', guest.id)" class="btn-icon btn-info" title="Download PDF">
                    <DocumentArrowDownIcon class="w-4 h-4" />
                  </a>
                  <div class="flex gap-2">
                    <Link :href="route('guests.edit', guest.id)" class="btn-icon btn-info !h-7 !w-7" title="Edit">
                      <PencilSquareIcon class="h-3.5 w-3.5" />
                    </Link>
                    <button @click="deleteGuest(guest)" class="btn-icon btn-danger !h-7 !w-7" title="Hapus">
                      <TrashIcon class="h-3.5 w-3.5" />
                    </button>
                    <button @click="showQr(guest)" class="btn-icon btn-secondary !h-7 !w-7" title="Lihat QR">
                      <QrCodeIcon class="h-3.5 w-3.5" />
                    </button>
                  </div>
                </div>
              </td>
            </tr>
            <tr v-if="guests.data.length === 0">
              <td colspan="8" class="empty-row">Tamu tidak ditemukan.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-sm text-gray-500 font-medium">
          Menampilkan <span class="font-bold text-gray-700">{{ guests.from || 0 }}</span> - <span class="font-bold text-gray-700">{{ guests.to || 0 }}</span> dari <span class="font-bold text-gray-700">{{ guests.total }}</span> tamu
        </div>
        <Pagination :links="guests.links" />
      </div>
    </div>

    <!-- Import Modal -->
    <Transition name="modal">
      <div v-if="showImport" class="import-modal-overlay" @click.self="showImport = false">
        <div class="import-modal">
          <div class="import-modal__header">
            <h2 class="import-modal__title">Import Data Tamu</h2>
            <button @click="showImport = false" class="import-modal__close">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <form @submit.prevent="doImport" enctype="multipart/form-data">
            <div class="import-modal__body">
              <div class="import-info">
                <p class="font-semibold text-sm mb-1">Format kolom yang didukung:</p>
                <code class="text-xs bg-gray-100 rounded px-2 py-1 block">nama, whatsapp, email, tipe, meja</code>
                <p class="text-xs text-muted mt-2">Format file: <strong>.xlsx</strong> (maks. 5MB)</p>
              </div>

              <div class="form-group mt-4">
                <label class="form-label" v-if="events && events.length > 0">Event</label>
                <select v-if="events && events.length > 0" v-model="importEventId" class="form-select w-full" required>
                  <option value="">-- Pilih Event --</option>
                  <option v-for="e in events" :key="e.id" :value="e.id">{{ e.name }}</option>
                </select>
              </div>

              <div class="form-group mt-4">
                <label class="form-label">File Import</label>
                <div
                  class="import-dropzone"
                  :class="{ 'import-dropzone--active': isDragging }"
                  @dragover.prevent="isDragging = true"
                  @dragleave="isDragging = false"
                  @drop.prevent="onFileDrop"
                  @click="$refs.fileInput.click()"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-10 h-10 text-muted mx-auto mb-2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
                  <p class="text-sm font-semibold">{{ importFile ? importFile.name : 'Klik atau seret file ke sini' }}</p>
                  <p class="text-xs text-muted mt-1">Hanya file XLSX</p>
                  <input ref="fileInput" type="file" accept=".xlsx" class="hidden" @change="onFileChange">
                </div>
              </div>
            </div>
            <div class="import-modal__footer">
              <button type="button" @click="showImport = false" class="btn btn-secondary">Batal</button>
              <button type="submit" class="btn btn-primary" :disabled="!importFile || importLoading">
                <svg v-if="importLoading" class="animate-spin w-4 h-4 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                {{ importLoading ? 'Mengimport...' : 'Import Sekarang' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { notify } from '@/Utils/SweetAlert';
import { debounce } from 'lodash-es';

import { 
  MagnifyingGlassIcon, 
  UsersIcon, 
  ArrowPathIcon, 
  PaperAirplaneIcon, 
  ArrowUpTrayIcon, 
  PlusIcon,
  PencilSquareIcon,
  TrashIcon,
  QrCodeIcon,
  DocumentArrowDownIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  guests: Object,
  events: Array,
  filters: Object,
});

const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');
const selectedIds = ref([]);
const processingIds = ref([]);
const processingBulk = ref(false);

const isAllSelected = computed(() => {
  return props.guests.data.length > 0 && selectedIds.value.length === props.guests.data.length;
});

function toggleSelectAll(e) {
  if (e.target.checked) {
    selectedIds.value = props.guests.data.map(g => g.id);
  } else {
    selectedIds.value = [];
  }
}

function copyLink(link) {
  navigator.clipboard.writeText(link).then(() => {
    notify.success('Link undangan berhasil disalin ke clipboard.');
  }).catch(err => {
    notify.error('Tidak dapat menyalin link.');
    console.error('Gagal menyalin link: ', err);
  });
}

const onFilterChange = debounce(() => {
  router.get(route('guests.index'), {
    search: search.value,
    type: type.value,
  }, {
    preserveState: true,
    replace: true,
  });
}, 300);

async function deleteGuest(guest) {
  const result = await notify.confirm(
    'Hapus Tamu?',
    `Apakah Anda yakin ingin menghapus tamu "${guest.name}"? Data yang dihapus tidak dapat dikembalikan.`,
    'Ya, Hapus'
  );
  
  if (result.isConfirmed) {
    router.delete(route('guests.destroy', guest.id));
  }
}

function showQr(guest) {
  notify.alert({
    title: guest.name,
    html: `
      <div class="flex flex-col items-center justify-center py-4">
        <div class="bg-white p-4 rounded-3xl shadow-sm border-4 border-slate-100">
          <img src="${route('guests.qr', guest.id)}" class="w-64 h-64 mx-auto" alt="QR Code" />
        </div>
        <p class="mt-6 text-sm text-slate-500 font-medium">Scan QR Code ini untuk check-in di lokasi acara.</p>
      </div>
    `,
    icon: 'info',
    confirmButtonText: 'Tutup'
  });
}

function sendWA(guest) {
  processingIds.value.push(guest.id);
  router.post(route('guests.send-whatsapp', guest.id), {}, {
    onFinish: () => {
      processingIds.value = processingIds.value.filter(id => id !== guest.id);
    }
  });
}

async function bulkSendWA() {
  const result = await notify.confirm(
    'Kirim Bulk WhatsApp?',
    `Kirim undangan WhatsApp ke ${selectedIds.value.length} tamu terpilih?`,
    'Ya, Kirim Sekarang',
    'info'
  );
  
  if (result.isConfirmed) {
    processingBulk.value = true;
    router.post(route('guests.bulk-whatsapp'), {
      ids: selectedIds.value
    }, {
      onFinish: () => {
        processingBulk.value = false;
        selectedIds.value = [];
      }
    });
  }
}

async function bulkDelete() {
  const result = await notify.confirm(
    'Hapus Banyak Tamu?',
    `Apakah Anda yakin ingin menghapus ${selectedIds.value.length} tamu terpilih? Data yang dihapus tidak dapat dikembalikan.`,
    'Ya, Hapus Semua',
    'warning'
  );
  
  if (result.isConfirmed) {
    processingBulk.value = true;
    router.post(route('guests.bulk-delete'), {
      ids: selectedIds.value
    }, {
      onFinish: () => {
        processingBulk.value = false;
        selectedIds.value = [];
      }
    });
  }
}

// Import logic
const showImport = ref(false);
const importFile = ref(null);
const importEventId = ref('');
const importLoading = ref(false);
const isDragging = ref(false);

function onFileChange(e) {
  const file = e.target.files[0];
  if (file) importFile.value = file;
}

function onFileDrop(e) {
  isDragging.value = false;
  const file = e.dataTransfer.files[0];
  if (file) importFile.value = file;
}

function doImport() {
  if (!importFile.value) return;
  
  importLoading.value = true;
  const formData = new FormData();
  formData.append('file', importFile.value);
  formData.append('event_id', importEventId.value);
  
  router.post(route('guests.import'), formData, {
    onSuccess: () => {
      showImport.value = false;
      importFile.value = null;
      importEventId.value = '';
    },
    onError: (errors) => {
      let errorMsg = Object.values(errors).join('\n');
      notify.error(errorMsg || 'Terjadi kesalahan saat mengimport data.');
    },
    onFinish: () => {
      importLoading.value = false;
    }
  });
}
</script>

<style scoped>
.action-group { display: flex; gap: 6px; justify-content: center; }
.table-footer { padding: 16px 24px; border-top: 1px solid var(--border); }
.empty-row { padding: 48px; text-align: center; color: var(--text-muted); font-style: italic; }

/* ================================
   FILTER BAR
================================ */
.filter-bar {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 14px 20px;
  border-bottom: 1px solid var(--border);
}
@media (min-width: 560px) {
  .filter-bar {
    flex-direction: row;
    align-items: center;
    gap: 12px;
  }
}
.filter-inputs { display: flex; flex-direction: column; gap: 8px; flex: 1; min-width: 0; }
.filter-row { display: flex; flex-direction: column; gap: 8px; }
@media (min-width: 560px) {
  .filter-row { flex-direction: row; align-items: center; gap: 8px; }
}
.filter-field {
  position: relative; display: flex; align-items: center;
  width: 100%;
}
@media (min-width: 560px) {
  .filter-field           { width: 155px; flex-shrink: 0; }
  .filter-field--search   { width: 200px; }
}
@media (min-width: 1024px) {
  .filter-field           { width: 165px; }
  .filter-field--search   { width: 220px; }
}
.filter-icon {
  position: absolute; left: 10px;
  width: 14px; height: 14px;
  color: var(--text-muted); pointer-events: none; z-index: 1;
}
.filter-input {
  width: 100%; height: 38px;
  padding: 0 10px 0 32px;
  border-radius: 9px;
  border: 1.5px solid var(--border);
  background: var(--card-bg);
  color: var(--text-primary);
  font-size: 13px; font-family: inherit;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.filter-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
.filter-input::placeholder { color: var(--text-muted); }
.filter-select {
  appearance: none; -webkit-appearance: none;
  padding-right: 28px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 8px center; background-size: 14px; cursor: pointer;
}
.filter-actions { display: flex; gap: 8px; flex-shrink: 0; flex-wrap: wrap; align-items: center; }

.form-checkbox {
  width: 16px;
  height: 16px;
  color: var(--primary);
  border-color: var(--border);
  border-radius: 4px;
  cursor: pointer;
}

.vertical-align-middle {
  vertical-align: middle;
}

/* Zebra striping for table */
.data-table tbody tr:nth-child(even) td {
  background-color: rgba(var(--primary-rgb), 0.015);
}

/* Import Modal Styles */
.import-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.import-modal {
  background: var(--card-bg);
  width: 100%;
  max-width: 500px;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 20px 50px rgba(0,0,0,0.2);
}

.import-modal__header {
  padding: 20px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.import-modal__title {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-primary);
}

.import-modal__close {
  background: transparent;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 4px;
}

.import-modal__body {
  padding: 24px;
}

.import-info {
  background: var(--bg-base);
  border: 1px solid var(--border);
  padding: 16px;
  border-radius: 12px;
}

.import-dropzone {
  border: 2px dashed var(--border);
  border-radius: 12px;
  padding: 30px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}

.import-dropzone:hover, .import-dropzone--active {
  border-color: var(--primary);
  background: var(--primary-soft);
}

.import-modal__footer {
  padding: 20px;
  border-top: 1px solid var(--border);
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

/* Modal Transition */
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
}
</style>
