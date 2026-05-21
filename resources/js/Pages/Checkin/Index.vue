<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center p-6">
    <div v-if="!guest" class="card max-w-md w-full text-center py-12">
       <div class="h-16 w-16 bg-danger-soft text-danger rounded-full flex items-center justify-center mx-auto mb-4">
         <svg viewBox="0 0 24 24" class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
       </div>
       <h1 class="text-xl font-bold text-gray-900 mb-2">Undangan Tidak Valid</h1>
       <p class="text-muted mb-6">Maaf, token atau QR Code tidak ditemukan di sistem kami.</p>
       <a href="/" class="btn btn-primary">Kembali ke Beranda</a>
    </div>

    <div v-else class="max-w-md w-full">
      <div class="card overflow-hidden">
        <!-- Header -->
        <div class="bg-primary p-8 text-center text-white">
          <div class="h-20 w-20 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
            <svg viewBox="0 0 24 24" class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M8 2v3M16 2v3M3.5 9.09h17M21 8.5V17c0 3-1.5 5-5 5H8c-3.5 0-5-2-5-5V8.5c0-3 1.5-5 5-5h8c3.5 0 5 2 5 5z"/><path d="M15.695 13.7h.009M15.695 16.7h.009M11.995 13.7h.009M11.995 16.7h.009M8.294 13.7h.01M8.294 16.7h.01" stroke-width="3"/></svg>
          </div>
          <h1 class="text-2xl font-bold tracking-tight">{{ guest.event_name }}</h1>
          <p class="text-white/80 mt-1 font-medium">Digital Invitation</p>
        </div>

        <div class="p-8">
          <div class="text-center mb-8">
            <p class="text-xs font-bold text-primary uppercase tracking-[0.2em] mb-2">Selamat Datang</p>
            <h2 class="text-2xl font-extrabold text-gray-900">{{ guest.name }}</h2>
            <div class="inline-flex items-center gap-2 mt-3 px-3 py-1 bg-gray-100 rounded-full text-xs font-bold text-gray-600">
              <span class="w-2 h-2 bg-primary rounded-full animate-pulse"></span>
              {{ guest.type }} Guest
            </div>
          </div>

          <!-- QR Code Section -->
          <div class="qr-container bg-white border-2 border-dashed border-gray-200 rounded-3xl p-6 mb-8 text-center">
            <div class="qr-box bg-gray-50 rounded-2xl p-4 inline-block mb-4">
              <img :src="route('public.qr', { qr_code: qrCode })" alt="QR Code" class="w-48 h-48 mx-auto" />
            </div>
            <div class="space-y-1">
              <p class="text-xs text-muted font-medium">Token Kehadiran:</p>
              <p class="text-xl font-black tracking-[0.2em] text-primary uppercase">{{ qrCode }}</p>
            </div>
          </div>

          <!-- Self Check-in Button -->
          <button 
            v-if="guest.checkin_status === 'not_arrived'"
            @click="processSelfCheckin"
            class="btn btn-primary w-full py-4 rounded-2xl text-lg font-bold shadow-lg shadow-primary/30"
            :disabled="processing"
          >
            {{ processing ? 'Memproses...' : 'Konfirmasi Kehadiran' }}
          </button>

          <div v-else class="text-center p-4 bg-success-soft rounded-2xl">
            <div class="flex items-center justify-center gap-2 text-success font-bold">
              <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
              <span>{{ guest.checkin_status === 'checkin' ? 'Anda Sudah Hadir' : 'Anda Sudah Pulang' }}</span>
            </div>
          </div>

          <p class="text-center text-[10px] text-muted mt-8 leading-relaxed">
            Tunjukkan QR Code ini kepada petugas di meja registrasi <br> atau klik tombol di atas untuk check-in mandiri.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  guest: Object,
  qrCode: String,
  guest_id: [Number, String],
});

const processing = ref(false);

const processSelfCheckin = async () => {
  processing.value = true;
  try {
    const response = await axios.post(route('checkin.self'), {
      qr_code: props.qrCode
    });
    
    if (response.data.success) {
      alert(response.data.message);
      router.reload();
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal memproses check-in mandiri.');
  } finally {
    processing.value = false;
  }
};
</script>

<style scoped>
.qr-container { transition: border-color 0.3s; }
.qr-container:hover { border-color: var(--primary); }
</style>
