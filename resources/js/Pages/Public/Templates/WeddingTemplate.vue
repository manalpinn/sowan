<template>
  <div class="min-h-screen font-serif text-slate-800 antialiased py-12 px-4 flex items-center justify-center relative overflow-hidden" :style="{ '--theme-primary': event.theme_color || '#db2777', '--theme-contrast': getContrastColor(event.theme_color || '#db2777'), backgroundColor: '#fdfbfb' }">
    <!-- Subtle background decoration -->
    <div class="absolute top-0 left-0 w-full h-full opacity-5 pointer-events-none" :style="{ backgroundImage: 'radial-gradient(circle at center, var(--theme-primary) 1px, transparent 1px)', backgroundSize: '40px 40px' }"></div>

    <div class="max-w-lg w-full relative z-10">
      <!-- Invitation Card -->
      <div class="bg-white rounded-t-full shadow-2xl overflow-hidden border border-slate-100 p-8 lg:p-14 text-center relative" style="border-bottom-left-radius: 3rem; border-bottom-right-radius: 3rem;">
        
        <!-- Elegant Border Accent -->
        <div class="absolute top-2 left-2 right-2 bottom-2 border border-slate-100 rounded-t-full rounded-b-[2.5rem] pointer-events-none"></div>
        <div class="absolute top-4 left-4 right-4 bottom-4 border border-slate-50 rounded-t-full rounded-b-[2rem] pointer-events-none"></div>

        <!-- Top Accent / Logo area -->
        <div class="pt-6 sm:pt-10 pb-4 sm:pb-6 flex justify-center">
          <div class="h-12 sm:h-16 w-1px bg-slate-200" :style="{ backgroundColor: 'var(--theme-primary)' }"></div>
        </div>

        <p class="text-[10px] sm:text-xs tracking-[0.2em] sm:tracking-[0.3em] uppercase mb-3 sm:mb-4 opacity-60">The Wedding Of</p>
        
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-light mb-6 sm:mb-8" style="font-family: 'Playfair Display', serif;" :style="{ color: 'var(--theme-primary)' }">{{ event.name }}</h1>

        <div class="h-px w-16 sm:w-24 mx-auto bg-slate-100 mb-8 sm:mb-10"></div>

        <!-- Welcome Message -->
        <div class="mb-10">
          <p class="text-sm italic text-slate-500 mb-2">Kepada Yth. Bapak/Ibu/Saudara/i</p>
          <h2 class="text-2xl font-medium tracking-wide mb-3" :style="{ color: 'var(--theme-primary)' }">{{ guest.name }}</h2>
          <div class="inline-block px-4 py-1 border rounded-full text-xs uppercase tracking-widest text-slate-400" :style="{ borderColor: 'var(--theme-primary)', color: 'var(--theme-primary)' }">
            {{ guest.type }}
          </div>
        </div>

        <!-- Event Details -->
        <div class="space-y-8 mb-12 px-4">
          <div>
            <p class="text-[10px] tracking-widest uppercase text-slate-400 mb-2">Waktu Pelaksanaan</p>
            <p class="text-lg">{{ event.start_date }}</p>
            <p v-if="event.time_formatted" class="text-sm italic text-slate-500 mt-1">{{ event.time_formatted }}</p>
          </div>
          <div>
            <p class="text-[10px] tracking-widest uppercase text-slate-400 mb-2">Lokasi Acara</p>
            <p class="text-lg leading-relaxed">{{ event.location }}</p>
            
            <div v-if="event.google_maps_embed_url" class="mt-4 rounded-xl overflow-hidden border border-slate-100 shadow-sm h-40 w-full relative z-20">
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

        <!-- RSVP Section -->
        <div class="mt-10 sm:mt-16">
          <div class="text-center mb-6 sm:mb-8">
            <p class="text-[10px] uppercase tracking-[0.3em] text-slate-400 mb-2">RSVP</p>
            <h3 class="text-lg sm:text-xl font-bold" style="font-family: 'Playfair Display', serif;">Kehadiran Anda</h3>
          </div>
          
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
                    'py-4 px-2 rounded-xl border transition-all duration-500 text-sm tracking-widest uppercase',
                    form.status === opt.value 
                      ? 'shadow-md scale-[1.02]' 
                      : 'bg-transparent text-slate-400 hover:border-slate-300'
                  ]"
                  :style="form.status === opt.value ? { backgroundColor: 'var(--theme-primary)', borderColor: 'var(--theme-primary)', color: 'var(--theme-contrast)' } : { borderColor: '#f1f5f9' }"
                >
                  {{ opt.label }}
                </button>
              </div>

              <!-- Pax Selection -->
              <div v-if="form.status === 'attending'" class="mt-4 sm:mt-6 animate-fade-in">
                <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500 mb-3 text-center">Jumlah Tamu</p>
                <div class="flex items-center justify-center gap-4 sm:gap-6 p-3 sm:p-4 rounded-xl border border-slate-100 bg-slate-50 mb-4">
                  <button type="button" @click="form.plus_one = Math.max(0, form.plus_one - 1)" class="h-10 w-10 rounded-full border border-slate-200 flex items-center justify-center transition-all hover:bg-slate-50 text-slate-400 hover:text-slate-700">
                    <span class="text-xl leading-none -mt-1">-</span>
                  </button>
                  <div class="w-12 text-center">
                    <span class="text-3xl font-light">{{ form.plus_one + 1 }}</span>
                  </div>
                  <button type="button" @click="form.plus_one = Math.min(3, form.plus_one + 1)" class="h-10 w-10 rounded-full border border-slate-200 flex items-center justify-center transition-all hover:bg-slate-50 text-slate-400 hover:text-slate-700">
                    <span class="text-xl leading-none -mt-1">+</span>
                  </button>
                </div>
              </div>
            </div>

            <button 
              type="submit" 
              class="w-full py-4 mt-4 rounded-xl tracking-[0.2em] uppercase text-xs transition-all hover:opacity-90 active:scale-95 disabled:opacity-50"
              :style="{ backgroundColor: 'var(--theme-primary)', color: 'var(--theme-contrast)' }"
              :disabled="processing || !form.status"
            >
              {{ processing ? 'Menyimpan...' : 'Kirim Konfirmasi' }}
            </button>
          </form>
        </div>

      </div>

      <!-- QR Section -->
      <div class="mt-8 sm:mt-12 text-center p-6 sm:p-8 bg-slate-50 border-t border-slate-100">
        <p class="text-xs text-slate-500 tracking-widest uppercase mb-4 sm:mb-6">Scan This Ticket</p>
        <div class="inline-block bg-white p-3 sm:p-4 rounded-2xl shadow-sm border border-slate-100">
           <img :src="route('public.qr', guest.qr_code)" class="h-32 w-32 sm:h-40 sm:w-40" />
        </div>
        <div class="mt-4 sm:mt-6">
          <p class="text-[10px] uppercase tracking-[0.3em] text-slate-400">Token ID</p>
          <p class="text-2xl sm:text-3xl font-mono tracking-widest font-bold mt-1 sm:mt-2 text-slate-700 break-all">{{ guest.qr_code }}</p>
        </div>
      </div>
      
      <div class="mt-12 mb-8 text-center">
        <Link href="/" class="text-xs uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">
          Sowan Management
        </Link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
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
  { value: 'attending', label: 'Hadir' },
  { value: 'declined', label: 'Maaf, Absen' },
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
    
    notify.alert('Terima Kasih', response.data.message, 'success');
  } catch (err) {
    notify.error('Oops!', 'Terjadi kesalahan saat menyimpan konfirmasi Anda.');
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
