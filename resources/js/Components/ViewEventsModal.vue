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
      <div class="relative bg-white dark:bg-[#131121] rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-[var(--border)] flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
          <div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Daftar Event User</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">Penugasan event untuk <span class="text-primary font-bold">{{ user?.name }}</span></p>
          </div>
          <button @click="close" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors p-1 rounded-md hover:bg-slate-200 dark:hover:bg-slate-800">
            <XMarkIcon class="w-5 h-5" stroke-width="2.5" />
          </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto">
          <div v-if="!user?.events || user.events.length === 0" class="text-center py-8">
             <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-3">
               <CalendarDaysIcon class="w-6 h-6 text-slate-300 dark:text-slate-600" />
             </div>
             <p class="text-sm font-semibold text-slate-400 dark:text-slate-500">Belum ada event</p>
          </div>
          <template v-else>
            <ul class="flex flex-col gap-3">
               <li v-for="event in displayedEvents" :key="event.id" class="flex items-center justify-between p-3 border border-slate-100 dark:border-slate-800/80 rounded-xl bg-slate-50/50 dark:bg-slate-900/30 hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                 <div class="flex flex-col gap-1">
                   <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ event.name }}</span>
                   <span v-if="event.date" class="text-xs text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                     <CalendarIcon class="w-3.5 h-3.5" />
                     {{ event.date }}
                   </span>
                 </div>
                 <EventStatusBadge :status="event.status" />
               </li>
            </ul>

            <!-- Show more / less toggle -->
            <button 
              v-if="user.events.length > maxVisible"
              @click="isExpanded = !isExpanded"
              class="mt-3 w-full py-2 text-xs font-bold text-primary hover:text-primary-dark transition-colors flex items-center justify-center gap-1.5 rounded-lg hover:bg-primary-soft"
            >
              <ChevronDownIcon class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': isExpanded }" stroke-width="3" />
              {{ isExpanded ? 'Tampilkan lebih sedikit' : `Tampilkan ${user.events.length - maxVisible} event lainnya` }}
            </button>
          </template>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-[var(--border)] bg-slate-50 dark:bg-slate-900/50 flex items-center justify-end">
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
import { ref, computed, watch } from 'vue';
import { XMarkIcon, CalendarDaysIcon, CalendarIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';
import EventStatusBadge from '@/Components/EventStatusBadge.vue';

const props = defineProps({
  show: Boolean,
  user: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['close']);

const maxVisible = 5;
const isExpanded = ref(false);

const displayedEvents = computed(() => {
  if (!props.user?.events) return [];
  return isExpanded.value ? props.user.events : props.user.events.slice(0, maxVisible);
});

// Reset expanded state when modal closes
watch(() => props.show, (val) => {
  if (!val) isExpanded.value = false;
});

const close = () => {
  emit('close');
};
</script>

