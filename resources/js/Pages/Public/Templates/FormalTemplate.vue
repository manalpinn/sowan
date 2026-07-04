<template>
  <div class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased py-10 px-4" :style="{ '--theme-primary': event.theme_color || '#7C3AED', '--theme-contrast': getContrastColor(event.theme_color || '#7C3AED') }">
    <div class="max-w-xl mx-auto">
      <!-- Logo -->
      <div class="flex justify-center mb-8">
        <Link href="/" class="flex items-center gap-3 group no-underline">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl text-white shadow-lg transition-all duration-300 group-hover:rotate-6 group-hover:scale-110" style="background-color: var(--theme-primary)">
            <UsersIcon class="h-5 w-5" stroke-width="2.5" />
          </div>
          <span class="text-2xl font-black tracking-tighter text-slate-900 uppercase">SOWAN<span style="color: var(--theme-primary)">.</span></span>
        </Link>
      </div>

      <!-- Invitation Card -->
      <div class="bg-white sm:rounded-[2rem] shadow-xl overflow-hidden border border-slate-100">
        <!-- Banner/Header -->
        <div class="h-40 sm:h-48 relative flex items-center justify-center px-4 sm:px-6 text-center" :style="{ backgroundColor: 'var(--theme-primary)', color: 'var(--theme-contrast)' }">
          <div class="absolute inset-0 bg-black/10"></div>
          <div class="relative z-10 w-full">
            <p class="text-[10px] sm:text-xs font-bold uppercase tracking-[0.2em] mb-1 sm:mb-2 opacity-80">Official Invitation</p>
            <h1 class="text-2xl sm:text-3xl font-black leading-tight break-words px-2">{{ event.name }}</h1>
          </div>
        </div>

        <div class="p-6 sm:p-8 lg:p-12">
          <!-- Welcome Message -->
          <div class="text-center mb-8 sm:mb-10">
            <p class="text-slate-500 text-sm sm:text-base font-medium mb-1 sm:mb-2">Kepada Yth.</p>
            <h2 class="text-2xl sm:text-3xl font-black mb-3 sm:mb-4 break-words" :style="{ color: 'var(--theme-primary)' }">{{ guest.name }}</h2>
            <div class="inline-block px-4 py-1.5 rounded-full bg-slate-100 text-slate-600 text-xs font-bold uppercase">
              {{ guest.type }}
            </div>
          </div>

          <!-- Event Details -->
          <div class="space-y-6 bg-slate-50 rounded-3xl p-6 mb-10">
            <div class="flex gap-4">
              <div class="h-10 w-10 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0" style="color: var(--theme-primary)">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              </div>
              <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase">Waktu</p>
                <p class="font-bold text-slate-700">{{ event.start_date }}</p>
                <p v-if="event.time_formatted" class="text-sm font-semibold text-slate-500 mt-1">{{ event.time_formatted }}</p>
              </div>
            </div>
            <div class="flex gap-4">
              <div class="h-10 w-10 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0" style="color: var(--theme-primary)">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              </div>
              <div class="flex-1">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Lokasi</p>
                <p class="font-bold text-slate-700" :class="{ 'mb-3': event.google_maps_embed_url }">{{ event.location }}</p>
                <!-- Google Maps Embed -->
                <div v-if="event.google_maps_embed_url" class="rounded-2xl overflow-hidden border border-slate-200 shadow-sm h-48 w-full">
                  <iframe
                    width="100%"
                    height="100%"
                    style="border:0;"
                    loading="lazy"
                    allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade"
                    :src="event.google_maps_embed_url"
                  ></iframe>
                </div>
              </div>
            </div>
          </div>

          <!-- RSVP Section -->
          <div class="border-t border-slate-100 pt-10">
            <h3 class="text-xl font-black text-center mb-8">Konfirmasi Kehadiran (RSVP)</h3>
            
            <form @submit.prevent="submitRsvp" class="space-y-6">
              <div class="space-y-6">
                <!-- Status Selection -->
                <div class="grid grid-cols-2 gap-4">
                  <button 
                    type="button"
                    v-for="opt in rsvpOptions" 
                    :key="opt.value"
                    @click="form.status = opt.value"
                    :class="[
                      'flex flex-col items-center gap-2 sm:gap-3 p-4 sm:p-6 rounded-2xl sm:rounded-[2rem] border-2 transition-all duration-300',
                      form.status === opt.value 
                        ? (opt.value === 'attending' 
                            ? 'border-emerald-500 bg-emerald-50 text-emerald-600 ring-2 sm:ring-4 ring-emerald-50 shadow-lg shadow-emerald-100 scale-[1.02]' 
                            : 'border-rose-500 bg-rose-50 text-rose-600 ring-2 sm:ring-4 ring-rose-50 shadow-lg shadow-rose-100 scale-[1.02]')
                        : 'border-slate-100 bg-white text-slate-400 hover:border-slate-200'
                    ]"
                  >
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl sm:rounded-2xl flex items-center justify-center transition-colors"
                      :class="form.status === opt.value 
                        ? (opt.value === 'attending' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white') 
                        : 'bg-slate-50 text-slate-300'"
                    >
                      <component :is="opt.icon" class="h-6 w-6" stroke-width="3" />
                    </div>
                    <span class="text-sm font-black uppercase tracking-[0.2em]">{{ opt.label }}</span>
                  </button>
                </div>

                <!-- Pax Selection (Visible when attending) -->
                <div v-if="form.status === 'attending'" class="bg-violet-50/50 p-6 sm:p-8 rounded-2xl sm:rounded-[2rem] border border-violet-100 animate-fade-in shadow-inner">
                  <div class="text-center mb-6">
                    <p class="text-[10px] font-black text-violet-400 uppercase tracking-[0.2em] mb-1">Konfirmasi Pax</p>
                    <h4 class="text-base sm:text-lg font-black text-violet-900 uppercase">Jumlah Tamu Hadir</h4>
                  </div>
                  
                  <div class="flex items-center justify-center gap-4 sm:gap-8 mb-6">
                    <button type="button" @click="form.plus_one = Math.max(0, form.plus_one - 1)" class="h-14 w-14 rounded-2xl bg-white border-2 flex items-center justify-center transition-all shadow-sm active:scale-90" :style="{ borderColor: 'var(--theme-primary)', color: 'var(--theme-primary)' }" onmouseover="this.style.backgroundColor='var(--theme-primary)'; this.style.color='white'" onmouseout="this.style.backgroundColor='white'; this.style.color='var(--theme-primary)'">
                      <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </button>
                    
                    <div class="flex flex-col items-center">
                      <span class="text-5xl font-black text-slate-800 leading-none">{{ form.plus_one + 1 }}</span>
                      <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Orang</span>
                    </div>

                    <button type="button" @click="form.plus_one = Math.min(3, form.plus_one + 1)" class="h-14 w-14 rounded-2xl bg-white border-2 flex items-center justify-center transition-all shadow-sm active:scale-90" :style="{ borderColor: 'var(--theme-primary)', color: 'var(--theme-primary)' }" onmouseover="this.style.backgroundColor='var(--theme-primary)'; this.style.color='white'" onmouseout="this.style.backgroundColor='white'; this.style.color='var(--theme-primary)'">
                      <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </button>
                  </div>
                  
                  <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-3 border border-slate-200 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-widest opacity-80" style="color: var(--theme-primary)">Satu undangan berlaku untuk {{ form.plus_one + 1 }} Pax</p>
                  </div>
                </div>
              </div>

              <button 
                type="submit" 
                class="group relative w-full overflow-hidden py-5 rounded-[2rem] font-black text-lg shadow-xl transition-all active:scale-95 disabled:opacity-50"
                :style="{ backgroundColor: 'var(--theme-primary)', color: 'var(--theme-contrast)' }"
                :disabled="processing || !form.status"
              >
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                <span class="relative z-10 uppercase tracking-widest">{{ processing ? 'Menyimpan...' : 'Kirim Konfirmasi' }}</span>
              </button>
            </form>
          </div>
        </div>

        <div class="bg-slate-900 p-8 sm:p-10 text-center text-white relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-violet-600/10 to-transparent"></div>
          <p class="text-[10px] sm:text-xs font-black opacity-60 uppercase tracking-[0.2em] sm:tracking-[0.3em] mb-4 sm:mb-6 text-violet-300 relative z-10">Scan QR saat kedatangan</p>
          <div class="relative z-10 inline-block bg-white p-4 sm:p-6 rounded-3xl sm:rounded-[2.5rem] shadow-2xl shadow-violet-500/20 mb-4 sm:mb-6">
             <img :src="route('public.qr', guest.qr_code)" class="h-32 w-32 sm:h-44 sm:w-44" />
          </div>
          <div class="relative z-10">
            <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest mb-1 sm:mb-2">Token Manual</p>
            <p class="text-2xl sm:text-4xl font-black tracking-[0.2em] sm:tracking-[0.3em] text-violet-400 font-mono break-all">{{ guest.qr_code }}</p>
          </div>
        </div>
      </div>

      <footer class="mt-10 text-center text-slate-400 text-sm font-medium">
        &copy; 2026 Sowan Management System.
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { 
  CheckIcon, 
  XMarkIcon, 
  UsersIcon
} from '@heroicons/vue/24/outline';
import { notify } from '@/Utils/SweetAlert';

const props = defineProps({
  guest: Object,
  event: Object,
});

let initialStatus = props.guest.rsvp_status !== 'pending' ? props.guest.rsvp_status : null;
let initialPlusOne = props.guest.plus_one_count || 0;

const form = reactive({
  status: initialStatus,
  plus_one: initialPlusOne,
});

const processing = ref(false);

const rsvpOptions = [
  { value: 'attending', label: 'Hadir', icon: CheckIcon },
  { value: 'declined', label: 'Absen', icon: XMarkIcon },
];

const getContrastColor = (hexColor) => {
  if (!hexColor) return '#ffffff';
  const hex = hexColor.replace('#', '');
  if (hex.length !== 6) return '#ffffff';
  const r = parseInt(hex.substr(0, 2), 16);
  const g = parseInt(hex.substr(2, 2), 16);
  const b = parseInt(hex.substr(4, 2), 16);
  const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
  return (yiq >= 128) ? '#1e293b' : '#ffffff';
};

const submitRsvp = async () => {
  processing.value = true;
  try {
    const response = await axios.post(route('public.rsvp', props.guest.qr_code), {
      status: form.status,
      plus_one: form.plus_one
    });
    
    notify.alert('Terima Kasih!', response.data.message, 'success');
  } catch (err) {
    notify.error('Oops!', 'Maaf, terjadi kesalahan saat menyimpan konfirmasi Anda.');
  } finally {
    processing.value = false;
  }
};
</script>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}
</style>
