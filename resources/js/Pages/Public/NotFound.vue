<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-10 text-center border border-slate-100">
      <div class="w-24 h-24 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-8">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-12 h-12">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>
      <h1 class="text-3xl font-black text-slate-900 mb-4 tracking-tight">Undangan Tidak Ditemukan</h1>
      <p class="text-slate-500 leading-relaxed mb-10">
        Maaf, link undangan yang Anda akses tidak valid atau data tamu telah dihapus dari sistem kami.
      </p>
      <div class="space-y-4">
        <p class="text-xs text-slate-400 font-medium uppercase tracking-widest">Hubungi Panitia</p>
        <div class="h-px bg-slate-100 w-full"></div>
        <p class="text-sm text-slate-500">
          Silakan hubungi pihak penyelenggara acara untuk mendapatkan link undangan yang baru.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
// Simple error page
</script>

<style scoped>
/* Gradient and premium touches */
body {
  background-image: radial-gradient(circle at top right, #f8fafc, #f1f5f9);
}
</style>
