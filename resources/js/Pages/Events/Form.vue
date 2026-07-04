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
        <div v-if="isPast" class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex gap-3 items-start">
          <ExclamationTriangleIcon class="w-5 h-5 shrink-0 text-amber-500" />
          <span>Event ini sudah selesai. Anda hanya dapat mengubah <strong>Nama Event</strong>.</span>
        </div>
        <form @submit.prevent="submit">
          <div class="form-group">
            <label class="form-label">Nama Event <span class="text-danger">*</span></label>
            <input type="text" v-model="form.name" class="form-input" placeholder="Contoh: Wedding of John & Jane" required>
            <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
          </div>

          <div class="grid grid-2 gap-6">
            <div class="form-group">
              <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
              <VueDatePicker 
                v-model="form.start_date" 
                model-type="yyyy-MM-dd"
                :format="formatDate"
                :enable-time-picker="false"
                :min-date="!event ? new Date(new Date().setHours(0,0,0,0)) : null"
                auto-apply
                :clearable="false"
                :disabled="isPast"
                required
              >
                <template #dp-input="{ value }">
                  <div class="relative w-full">
                    <input type="text" :value="formatDate(form.start_date)" class="form-input sowan-datepicker-input w-full" :class="{ 'bg-slate-50 dark:bg-slate-900 text-slate-400 dark:text-slate-400': isPast }" placeholder="dd/mm/yyyy" readonly :disabled="isPast" />
                    <svg class="dp__input_icon h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  </div>
                </template>
              </VueDatePicker>
              <div v-if="isPast" class="mt-1.5 text-xs text-slate-500 dark:text-slate-400 dark:text-slate-400 flex items-start gap-1">
                <svg class="w-4 h-4 shrink-0 text-slate-400 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span>Tanggal tidak dapat diubah karena event sudah berlalu</span>
              </div>
              <div v-else-if="form.errors.start_date" class="form-error">{{ form.errors.start_date }}</div>
            </div>
            <div class="form-group">
              <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
              <VueDatePicker 
                v-model="form.end_date" 
                model-type="yyyy-MM-dd"
                :format="formatDate"
                :enable-time-picker="false"
                :min-date="form.start_date ? new Date(form.start_date + 'T00:00:00') : (!event ? new Date(new Date().setHours(0,0,0,0)) : null)"
                auto-apply
                :clearable="false"
                :disabled="isPast"
                required
              >
                <template #dp-input="{ value }">
                  <div class="relative w-full">
                    <input type="text" :value="formatDate(form.end_date)" class="form-input sowan-datepicker-input w-full" :class="{ 'bg-slate-50 dark:bg-slate-900 text-slate-400 dark:text-slate-400': isPast }" placeholder="dd/mm/yyyy" readonly :disabled="isPast" />
                    <svg class="dp__input_icon h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  </div>
                </template>
              </VueDatePicker>
              <div v-if="isPast" class="mt-1.5 text-xs text-slate-500 dark:text-slate-400 dark:text-slate-400 flex items-start gap-1">
                <svg class="w-4 h-4 shrink-0 text-slate-400 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span>Tanggal tidak dapat diubah karena event sudah berlalu</span>
              </div>
              <div v-else-if="form.errors.end_date" class="form-error">{{ form.errors.end_date }}</div>
            </div>
          </div>

          <div class="grid grid-2 gap-6">
            <div class="form-group">
              <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
              <input type="time" v-model="form.start_time" class="form-input" :disabled="isPast" required>
              <div v-if="form.errors.start_time" class="form-error">{{ form.errors.start_time }}</div>
            </div>
            <div class="form-group">
              <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
              <input type="time" v-model="form.end_time" class="form-input" :disabled="isPast" required>
              <div v-if="form.errors.end_time" class="form-error">{{ form.errors.end_time }}</div>
            </div>
          </div>

          <div class="form-group relative">
            <label class="form-label">Lokasi <span class="text-danger">*</span></label>
            <div class="text-xs text-slate-500 dark:text-slate-400 dark:text-slate-400 mb-2">Ketik untuk mencari dari peta. Jika tidak ditemukan, ketik manual nama tempat yang sesuai dengan di Google Maps.</div>
            <div class="relative">
              <input 
                type="text" 
                v-model="form.location" 
                @input="searchLocation"
                class="form-input w-full" 
                :disabled="isPast" 
                placeholder="Cari lokasi acara..." 
                required
              >
              <!-- Loading Indicator -->
              <div v-if="isSearchingLocation" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="animate-spin h-5 w-5 text-violet-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </div>
            </div>
            
            <!-- Search Results Dropdown -->
            <ul v-if="locationResults.length > 0 && !isPast" class="absolute z-10 mt-1 w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-lg max-h-60 overflow-y-auto">
              <li 
                v-for="(res, i) in locationResults" 
                :key="i"
                @click="selectLocation(res)"
                class="px-4 py-3 hover:bg-slate-50 dark:bg-slate-900 cursor-pointer text-sm border-b border-slate-100 dark:border-slate-700 last:border-0"
              >
                <div class="font-bold text-slate-800 dark:text-slate-100">{{ res.name || res.display_name.split(',')[0] }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 dark:text-slate-400 mt-0.5 truncate">{{ res.display_name }}</div>
              </li>
            </ul>
            <div v-if="form.errors.location" class="form-error">{{ form.errors.location }}</div>
            <div v-if="form.errors.latitude" class="form-error">{{ form.errors.latitude }}</div>
            
            <!-- Map Preview -->
            <div v-if="form.latitude && form.longitude" class="mt-4 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-600 shadow-sm relative" style="height: 250px;">
              <iframe 
                width="100%" 
                height="100%" 
                frameborder="0" 
                style="border:0"
                :src="`https://maps.google.com/maps?q=${form.latitude},${form.longitude}&t=&z=15&ie=UTF8&iwloc=&output=embed`" 
                allowfullscreen>
              </iframe>
              <div class="absolute bottom-2 right-2 flex gap-2">
                 <a :href="form.google_maps_link" target="_blank" class="bg-white dark:bg-slate-800/90 backdrop-blur text-slate-800 dark:text-slate-100 text-xs px-3 py-1.5 rounded-lg shadow-sm hover:bg-white dark:bg-slate-800 font-medium flex items-center gap-1.5 transition-colors">
                   <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-rose-500"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                   Buka di Maps
                 </a>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Link Google Maps <span class="text-xs text-slate-500 dark:text-slate-400 dark:text-slate-400 font-normal ml-1">(Opsional)</span></label>
            <input type="text" v-model="form.google_maps_link" @input="clearCoordinates" class="form-input" :disabled="isPast" placeholder="Paste link dari Google Maps jika lokasi di atas tidak akurat">
            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400 dark:text-slate-400">Akan terisi otomatis jika Anda memilih lokasi dari daftar pencarian. Jika hasil pencarian tidak sesuai, Anda bisa menempelkan link Google Maps secara manual di sini.</div>
            <div v-if="form.errors.google_maps_link" class="form-error">{{ form.errors.google_maps_link }}</div>
          </div>

          <div class="form-group">
            <label class="form-label">Deskripsi</label>
            <textarea v-model="form.description" class="form-textarea" rows="3" :disabled="isPast" placeholder="Deskripsi singkat acara..."></textarea>
          </div>

          <div class="grid grid-2 gap-6">
            <div class="form-group">
              <label class="form-label">Warna Tema (HEX)</label>
              <div class="flex gap-2">
                <input type="color" v-model="form.theme_color" class="h-10 w-12 rounded-lg border border-gray-300 p-1" :disabled="isPast">
                <input type="text" v-model="form.theme_color" class="form-input" :disabled="isPast" placeholder="#7C3AED">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Status</label>
              <select v-model="form.is_active" class="form-select" :disabled="isPast">
                <option :value="true">Aktif</option>
                <option :value="false">Non-aktif</option>
              </select>
            </div>
          </div>

          <div class="grid grid-2 gap-6">
            <div class="form-group">
              <label class="form-label">Tipe Kehadiran</label>
              <select v-model="form.attendance_type" class="form-select" :disabled="isPast">
                <option value="checkin_only">Hanya Check-in</option>
                <option value="checkin_checkout">Check-in & Check-out</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Template Undangan</label>
              <select v-model="form.invitation_template" class="form-select" :disabled="isPast">
                <option value="formal">Formal / Default</option>
                <option value="wedding">Pernikahan (Wedding)</option>
                <option value="corporate">Perusahaan (Corporate)</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Pesan Sambutan (WhatsApp)</label>
            <textarea v-model="form.welcome_message" class="form-textarea" rows="2" :disabled="isPast" placeholder="Pesan yang akan muncul di undangan WA..."></textarea>
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
import { computed, ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CheckIcon, XMarkIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import axios from 'axios';

const props = defineProps({
  event: Object,
});

const todayObj = new Date();
const today = `${todayObj.getFullYear()}-${String(todayObj.getMonth() + 1).padStart(2, '0')}-${String(todayObj.getDate()).padStart(2, '0')}`;

const isPast = computed(() => {
  if (!props.event) return false;
  const endDateStr = props.event.end_date || props.event.start_date;
  if (!endDateStr) return false;
  
  const safeDateStr = typeof endDateStr === 'string' ? endDateStr.replace(' ', 'T') : endDateStr;
  const end = new Date(safeDateStr);
  end.setHours(0, 0, 0, 0);
  
  const now = new Date();
  now.setHours(0, 0, 0, 0);
  
  return end < now;
});

const formatDateForInput = (dateStr) => {
  if (!dateStr) return '';
  // Safari compatibility: replace space with T
  const safeDateStr = typeof dateStr === 'string' ? dateStr.replace(' ', 'T') : dateStr;
  const d = new Date(safeDateStr);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

// Location Search Logic
const locationResults = ref([]);
const isSearchingLocation = ref(false);
let debounceTimeout = null;
const map = ref(null);
const marker = ref(null);

const clearCoordinates = () => {
  form.latitude = null;
  form.longitude = null;
};

const searchLocation = () => {
  form.latitude = null;
  form.longitude = null;
  form.location_name = '';
  form.address = '';
  form.google_maps_link = '';

  if (debounceTimeout) clearTimeout(debounceTimeout);
  if (!form.location || form.location.length < 3) {
    locationResults.value = [];
    return;
  }
  
  debounceTimeout = setTimeout(async () => {
    isSearchingLocation.value = true;
    try {
      const response = await axios.get(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(form.location)}&limit=5&countrycodes=id&addressdetails=1`, {
        headers: {
            'Accept-Language': 'id-ID',
            'User-Agent': 'SowanGuestBook/1.0'
        }
      });
      locationResults.value = response.data;
    } catch (e) {
      console.error('Error searching location', e);
    } finally {
      isSearchingLocation.value = false;
    }
  }, 500);
};

const selectLocation = (result) => {
  const name = result.name || result.display_name.split(',')[0];
  form.location_name = name;
  form.address = result.display_name;
  form.latitude = result.lat;
  form.longitude = result.lon;

  form.location = name + ' - ' + result.display_name.split(',').slice(1, 3).join(',').trim();
  form.google_maps_link = `https://www.google.com/maps?q=${result.lat},${result.lon}`;
  locationResults.value = [];
};

const form = useForm({
  name: props.event?.name ?? '',
  start_date: formatDateForInput(props.event?.start_date),
  end_date: formatDateForInput(props.event?.end_date),
  start_time: props.event?.start_time ? props.event.start_time.substring(0, 5) : '08:00',
  end_time: props.event?.end_time ? props.event.end_time.substring(0, 5) : '15:00',
  location: props.event?.location ?? '',
  location_name: props.event?.location_name ?? '',
  address: props.event?.address ?? '',
  latitude: props.event?.latitude ?? null,
  longitude: props.event?.longitude ?? null,
  google_maps_link: props.event?.google_maps_link ?? '',
  description: props.event?.description ?? '',
  theme_color: props.event?.theme_color ?? '#7C3AED',
  welcome_message: props.event?.welcome_message ?? '',
  attendance_type: props.event?.attendance_type ?? 'checkin_only',
  invitation_template: props.event?.invitation_template ?? 'formal',
  is_active: props.event?.is_active ?? true,
});


const formatDate = (date) => {
  if (!date) return '';
  // Safari compatibility: replace space with T
  const safeDate = typeof date === 'string' ? date.replace(' ', 'T') : date;
  const d = new Date(safeDate);
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  return `${day}/${month}/${year}`;
};

function submit() {
  if (!props.event && form.start_date < today) {
    form.setError('start_date', 'Tanggal event tidak boleh kurang dari hari ini.');
    return;
  }
  
  if (form.end_time <= form.start_time) {
    form.setError('end_time', 'Waktu selesai harus lebih besar dari waktu mulai.');
    return;
  }

  if (props.event) {
    form.patch(route('events.update', props.event.id));
  } else {
    form.post(route('events.store'));
  }
}
</script>

<style>
/* Vue Datepicker Sowan Theme Customization (Global so it affects teleported popup) */
:root {
   --dp-background-color: var(--input-bg);
   --dp-text-color: var(--text-primary);
   --dp-hover-color: var(--hover-bg);
   --dp-hover-text-color: var(--text-primary);
   --dp-hover-icon-color: var(--text-secondary);
   --dp-primary-color: #7C3AED !important;
   --dp-primary-disabled-color: var(--primary-soft);
   --dp-primary-text-color: #ffffff !important;
   --dp-secondary-color: var(--border);
   --dp-border-color: var(--border);
   --dp-menu-border-color: var(--border);
   --dp-border-color-hover: #7C3AED !important;
   --dp-border-color-focus: #7C3AED !important;
   --dp-disabled-color: var(--bg-base);
   --dp-icon-color: var(--text-muted);
   --dp-danger-color: var(--danger);
   --dp-tooltip-color: var(--card-bg);
   --dp-border-radius: 10px;
   --dp-font-family: inherit;
}

.sowan-datepicker-input {
  padding: 10px 14px;
  padding-left: 36px;
  height: auto;
  font-size: 14px;
  font-weight: 500;
  border: 1.5px solid var(--border);
  box-shadow: none;
  transition: border-color var(--transition), box-shadow var(--transition);
}

.sowan-datepicker-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.12);
}

.dp__input_icon {
  left: 12px;
  color: var(--primary);
}
</style>

<style scoped>
.text-danger { color: var(--danger); }


</style>
