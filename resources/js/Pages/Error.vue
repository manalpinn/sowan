<template>
    <div class="min-h-screen flex items-center justify-center bg-[#F8FAFC] px-4 sm:px-6 font-sans antialiased selection:bg-violet-100 selection:text-violet-900 py-12 relative overflow-hidden">
        <!-- Sophisticated Light Background System -->
        <div class="fixed inset-0 overflow-hidden -z-10 pointer-events-none">
            <!-- Soft Ambient Glows -->
            <div class="absolute -top-[10%] -right-[10%] w-[70%] h-[70%] bg-violet-100/50 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[10%] -left-[10%] w-[50%] h-[50%] bg-indigo-50/40 rounded-full blur-[100px]"></div>
            
            <!-- Professional Grid Pattern -->
            <div class="absolute inset-0 opacity-[0.03]" 
                 style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%234F46E5\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2v-4h4v-2h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2v-4h4v-2H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
            </div>
        </div>

        <div class="w-full max-w-[500px] relative">
            <!-- Modern Branding -->
            <div class="text-center mb-10 md:mb-14">
                <Link href="/" class="inline-flex items-center gap-3.5 mb-8 group">
                    <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-violet-600 text-white shadow-xl shadow-violet-200 transition-all duration-500 group-hover:bg-violet-700 group-hover:rotate-6 group-hover:scale-110">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="9" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M23 21v-2a4 0 00-3-3.87M16 3.13a4 0 010 7.75" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="flex flex-col items-start">
                        <span class="text-2xl font-black tracking-tighter text-violet-600 uppercase leading-none">SOWAN<span class="text-indigo-600">.</span></span>
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-[0.4em] mt-1">Management Portal</span>
                    </div>
                </Link>
                
                <h1 class="text-3xl sm:text-4xl font-black tracking-tighter text-violet-600 mb-2 uppercase leading-none">{{ title }}</h1>
                <p class="text-slate-500 font-medium tracking-tight">{{ description }}</p>
            </div>

            <!-- Premium Light Card -->
            <div class="bg-white p-8 sm:p-12 md:p-10 rounded-[40px] border border-slate-200/60 shadow-[0_32px_64px_-16px_rgba(0,0,0,0.08)] relative overflow-hidden flex flex-col items-center text-center">
                <!-- Top Accent -->
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-violet-600 to-indigo-500"></div>
                
                <div class="mb-6 flex items-center justify-center h-24 w-24 rounded-full bg-violet-50 text-violet-500">
                    <svg v-if="status === 404" xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                
                <h2 class="text-xl font-bold text-slate-800 mb-2">Error {{ status }}</h2>
                <p class="text-slate-500 text-sm mb-8 leading-relaxed">
                    {{ message }}
                </p>

                <div class="flex flex-col sm:flex-row gap-3 w-full">
                    <button @click="refreshPage" class="flex-1 inline-flex justify-center items-center gap-2 rounded-2xl bg-white border-2 border-slate-200 px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all focus:outline-none focus:ring-2 focus:ring-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh Halaman
                    </button>
                    <Link href="/" class="flex-1 inline-flex justify-center items-center gap-2 rounded-2xl bg-violet-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-violet-200 hover:bg-violet-700 transition-all focus:outline-none focus:ring-2 focus:ring-violet-600 focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Kembali ke Beranda
                    </Link>
                </div>
                
                <div class="mt-6 pt-6 border-t border-slate-100 w-full text-center">
                    <a href="mailto:admin@example.com" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                        Butuh bantuan? Hubungi Admin
                    </a>
                </div>
            </div>

            <!-- Enhanced Footer -->
            <div class="mt-12 flex flex-col items-center gap-4">
                <div class="flex items-center gap-2 px-3 py-1 bg-rose-50 rounded-full border border-rose-100/50">
                    <div class="h-1.5 w-1.5 rounded-full bg-rose-500 animate-pulse"></div>
                    <span class="text-[9px] font-black text-rose-700 uppercase tracking-widest">Sistem Mengalami Kendala</span>
                </div>
                
                <p class="text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">
                    &copy; {{ new Date().getFullYear() }} SOWAN. OFFICIAL SYSTEM.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  status: {
    type: Number,
    required: true
  }
})

const title = computed(() => {
  return {
    503: 'Layanan Tidak Tersedia',
    502: 'Gateway Bermasalah',
    500: 'Kesalahan Server',
    404: 'Halaman Tidak Ditemukan',
    403: 'Akses Ditolak',
    401: 'Tidak Diizinkan',
  }[props.status] || 'Terjadi Kesalahan'
})

const description = computed(() => {
  return {
    503: 'Layanan sedang dalam perbaikan atau kelebihan beban.',
    502: 'Server menerima respons yang tidak valid.',
    500: 'Terjadi kesalahan pada sistem kami.',
    404: 'Halaman yang Anda cari tidak dapat ditemukan.',
    403: 'Anda tidak memiliki akses ke halaman ini.',
    401: 'Silakan masuk untuk mengakses halaman ini.',
  }[props.status] || 'Terdapat kendala pada permintaan Anda.'
})

const message = computed(() => {
  if (props.status === 404) {
    return 'Maaf, halaman yang Anda tuju mungkin telah dipindahkan, dihapus, atau Anda salah memasukkan alamat.'
  }
  if (props.status === 403 || props.status === 401) {
    return 'Maaf, Anda tidak memiliki izin yang diperlukan untuk melihat halaman ini.'
  }
  // For 5xx and other errors
  return 'Maaf, halaman sedang tidak dapat diakses. Kami sedang mengatasi masalah ini. Silakan coba beberapa saat lagi.'
})

const refreshPage = () => {
  window.location.reload()
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
}
</style>
