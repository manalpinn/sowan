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
                
                <h1 class="text-3xl sm:text-4xl font-black tracking-tighter text-violet-600 mb-2 uppercase leading-none">Selamat Datang.</h1>
                <p class="text-slate-500 font-medium tracking-tight">Silakan masuk untuk melanjutkan akses Anda.</p>
            </div>

            <!-- Premium Light Card -->
            <div class="bg-white p-8 sm:p-12 md:p-14 rounded-[40px] border border-slate-200/60 shadow-[0_32px_64px_-16px_rgba(0,0,0,0.08)] relative overflow-hidden">
                <!-- Top Accent -->
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-violet-600 to-indigo-500"></div>
                
                <slot />
            </div>

            <!-- Enhanced Footer -->
            <div class="mt-12 flex flex-col items-center gap-4">
                <div class="flex items-center gap-2 px-3 py-1 bg-emerald-50 rounded-full border border-emerald-100/50">
                    <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-[9px] font-black text-emerald-700 uppercase tracking-widest">Sistem Aktif & Terenkripsi</span>
                </div>
                
                <p class="text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">
                    &copy; {{ new Date().getFullYear() }} SOWAN. OFFICIAL SYSTEM.
                </p>
                
                <div class="flex items-center gap-6">
                    <a href="#" class="text-[9px] font-black text-slate-400 hover:text-violet-600 transition-colors uppercase tracking-widest">Pusat Bantuan</a>
                    <a href="#" class="text-[9px] font-black text-slate-400 hover:text-violet-600 transition-colors uppercase tracking-widest">Kebijakan Privasi</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
}
</style>
