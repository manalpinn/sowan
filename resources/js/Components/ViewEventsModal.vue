<template>
  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0 scale-95"
    enter-to-class="opacity-100 scale-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100 scale-100"
    leave-to-class="opacity-0 scale-95"
  >
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" style="background-color: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px);">
      <!-- Click outside listener -->
      <div class="absolute inset-0" @click="close"></div>
      
      <!-- Modal Panel -->
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-[var(--border)] flex items-center justify-between bg-slate-50">
          <div>
            <h3 class="text-lg font-bold text-slate-800">Daftar Event User</h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Penugasan event untuk <span class="text-primary font-bold">{{ user?.name }}</span></p>
          </div>
          <button @click="close" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-md hover:bg-slate-200">
            <XMarkIcon class="w-5 h-5" stroke-width="2.5" />
          </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto">
          <div v-if="!user?.events || user.events.length === 0" class="text-center py-8">
             <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
               <CalendarDaysIcon class="w-6 h-6 text-slate-300" />
             </div>
             <p class="text-sm font-semibold text-slate-400">Belum ada event</p>
          </div>
          <ul v-else class="flex flex-col gap-3">
             <li v-for="event in user.events" :key="event.id" class="flex items-center justify-between p-3 border border-slate-100 rounded-xl bg-slate-50/50 hover:bg-slate-50 transition-colors">
               <div class="flex flex-col gap-1">
                 <span class="text-sm font-bold text-slate-700">{{ event.name }}</span>
                 <span v-if="event.date" class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                   <CalendarIcon class="w-3.5 h-3.5" />
                   {{ event.date }}
                 </span>
               </div>
               <EventStatusBadge :status="event.status" />
             </li>
          </ul>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-[var(--border)] bg-slate-50 flex items-center justify-end">
          <button 
            type="button" 
            class="btn btn-secondary text-sm px-6 py-2" 
            @click="close"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { XMarkIcon, CalendarDaysIcon, CalendarIcon } from '@heroicons/vue/24/outline';
import EventStatusBadge from '@/Components/EventStatusBadge.vue';

const props = defineProps({
  show: Boolean,
  user: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['close']);

const close = () => {
  emit('close');
};
</script>
