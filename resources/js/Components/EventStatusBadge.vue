<template>
  <span :class="['inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold rounded-full leading-none whitespace-nowrap', styleClasses]">
    {{ status }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true,
  }
});

const styleClasses = computed(() => {
  const s = props.status?.toLowerCase() || '';
  if (s === 'selesai' || s === 'berhasil') {
    return 'bg-emerald-100 text-emerald-700'; // Hijau / Success
  }
  if (s === 'aktif') {
    return 'bg-blue-100 text-blue-700'; // Biru / Primary
  }
  if (s === 'akan datang' || s === 'upcoming') {
    return 'bg-amber-100 text-amber-800'; // Kuning / Warning
  }
  return 'bg-slate-100 text-slate-600'; // Default
});
</script>
