<template>
  <div class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased py-8 px-4 flex items-center justify-center" :style="{ '--theme-primary': event.theme_color || '#0f172a', '--theme-contrast': getContrastColor(event.theme_color || '#0f172a') }">
    
    <div class="max-w-2xl w-full grid grid-cols-1 md:grid-cols-12 bg-white shadow-xl rounded-xl overflow-hidden">
      
      <!-- Left / Top Color Block -->
      <div class="md:col-span-5 relative p-6 sm:p-8 flex flex-col justify-between" :style="{ backgroundColor: 'var(--theme-primary)', color: 'var(--theme-contrast)' }">
        <div>
          <div class="inline-block px-3 py-1 bg-white/20 backdrop-blur-sm rounded-md text-[10px] uppercase tracking-widest font-bold mb-6 sm:mb-8">
            Event Pass
          </div>
          
          <h1 class="text-3xl sm:text-4xl font-bold leading-tight mb-2 tracking-tight break-words">{{ event.name }}</h1>
          <p class="text-sm opacity-80">{{ event.start_date }}</p>
        </div>
        
        <div class="mt-8">
          <p class="text-[10px] uppercase tracking-widest text-white/50 mb-1">Date & Time</p>
          <p class="font-semibold">{{ event.start_date }}</p>
          <p v-if="event.time_formatted" class="text-sm opacity-80">{{ event.time_formatted }}</p>
        </div>
      </div>

      <!-- Right / Bottom Content Block -->
      <div class="md:col-span-7 p-6 sm:p-8 md:p-10 flex flex-col">
        
        <div class="flex-1">
          <!-- Attendee info -->
          <div class="mb-6 sm:mb-8">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Attendee</p>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-800 break-words">{{ guest.name }}</h2>
            <div class="inline-block mt-2 px-3 py-1 bg-slate-100 text-slate-600 rounded text-xs font-bold uppercase tracking-wider">
              {{ guest.type }}
            </div>
          </div>

          <!-- Event Meta -->
          <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 mb-6 sm:mb-8 p-4 sm:p-5 bg-slate-50 rounded-xl border border-slate-100">
            <div class="flex-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Time</p>
              <p class="text-sm font-semibold text-slate-700">{{ event.time_formatted || event.start_date }}</p>
            </div>
            <div class="w-full sm:w-[1px] h-[1px] sm:h-auto bg-slate-200"></div>
            <div class="flex-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Venue</p>
              <p class="text-sm font-semibold text-slate-700">{{ event.location }}</p>
            </div>
          </div>

          <div v-if="event.google_maps_embed_url" class="mb-10">
            <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-2">Venue Map</p>
            <div class="h-32 w-full rounded-lg overflow-hidden border border-slate-200">
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

          <!-- RSVP Section -->
          <div class="border-t border-slate-100 pt-8">
            <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-4">RSVP Status</p>
            
            <form @submit.prevent="submitRsvp" class="space-y-6">
              <div class="space-y-4 sm:space-y-6">
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                  <button 
                    type="button"
                    v-for="opt in rsvpOptions" 
                    :key="opt.value"
                    @click="form.status = opt.value"
                    :class="[
                      'flex-1 py-3 px-4 rounded-lg border-2 text-sm font-bold uppercase tracking-wider transition-all',
                      form.status === opt.value 
                        ? 'border-transparent shadow-md' 
                        : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'
                    ]"
                    :style="form.status === opt.value ? { backgroundColor: 'var(--theme-primary)', color: 'var(--theme-contrast)' } : {}"
                  >
                    {{ opt.label }}
                  </button>
                </div>

                <div v-if="form.status === 'attending'" class="bg-slate-50 p-4 sm:p-5 rounded-xl border border-slate-100 animate-fade-in">
                  <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Guests Count</p>
                  <div class="flex items-center justify-between">
                    <button type="button" @click="form.plus_one = Math.max(0, form.plus_one - 1)" class="h-10 w-10 sm:h-12 sm:w-12 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-all active:scale-95">
                      <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </button>
                    
                    <div class="text-center">
                      <span class="text-2xl sm:text-3xl font-black text-slate-800">{{ form.plus_one + 1 }}</span>
                      <span class="text-xs font-bold text-slate-400 ml-1">Pax</span>
                    </div>

                    <button type="button" @click="form.plus_one = Math.min(3, form.plus_one + 1)" class="h-10 w-10 sm:h-12 sm:w-12 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-all active:scale-95">
                      <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </button>
                  </div>
                </div>
              </div>

              <button 
                type="submit" 
                class="w-full py-4 rounded-lg font-bold uppercase tracking-widest text-sm transition-all hover:opacity-90 disabled:opacity-50"
                :style="{ backgroundColor: 'var(--theme-primary)', color: 'var(--theme-contrast)' }"
                :disabled="processing || !form.status"
              >
                {{ processing ? 'Processing...' : 'Confirm' }}
              </button>
            </form>
          </div>
        </div>

      </div>

    </div>

    <!-- QR Code Block for Corporate (Below) -->
    <div class="max-w-2xl w-full mt-6 bg-white shadow-xl rounded-xl overflow-hidden p-8 relative">
      <div class="flex items-center justify-between md:flex-row flex-col gap-6 text-center md:text-left">
        <div>
          <h3 class="text-lg font-bold text-slate-900 mb-1">Access Token</h3>
          <p class="text-sm text-slate-500 mb-4">Please present this QR code at the reception desk.</p>
          <p class="font-mono text-xl tracking-widest font-bold" :style="{ color: 'var(--theme-primary)' }">{{ guest.qr_code }}</p>
        </div>
        
        <div class="mt-4 md:mt-0 relative z-10 flex shrink-0">
          <div class="bg-white p-3 sm:p-4 rounded-xl shadow-lg inline-block relative">
            <div class="absolute -inset-1 bg-white/30 rounded-xl blur"></div>
            <img :src="route('public.qr', guest.qr_code)" class="relative h-28 w-28 sm:h-32 sm:w-32 rounded-lg" />
          </div>
        </div>
        
        <div class="text-4xl sm:text-5xl md:text-6xl font-black font-mono tracking-widest opacity-20 absolute -bottom-4 -right-4 rotate-[-10deg]">
          {{ guest.qr_code }}
        </div>
      </div>
    </div>
    
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
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
  { value: 'attending', label: 'Accept' },
  { value: 'declined', label: 'Decline' },
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
    
    notify.alert('Success', response.data.message, 'success');
  } catch (err) {
    notify.error('Error', 'An error occurred while saving your RSVP.');
  } finally {
    processing.value = false;
  }
};
</script>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.2s ease-out;
}
</style>
