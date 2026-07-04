<template>
  <div class="relative w-full" ref="containerRef">
    <!-- Trigger & Selected Tags -->
    <div 
      class="min-h-[46px] p-2 border-[1.5px] border-[var(--border)] rounded-xl bg-[var(--input-bg)] flex flex-wrap gap-2 items-center cursor-text transition-all duration-200 shadow-sm"
      :class="{ 'border-primary ring-4 ring-primary-soft': isOpen }"
      @click="openDropdown"
    >
      <!-- Tags -->
      <div 
        v-for="event in visibleEvents" 
        :key="event.id"
        class="flex items-center gap-1.5 bg-primary-soft text-primary px-3 py-1.5 rounded-[8px] text-xs font-bold shadow-sm border border-primary/10 transition-all hover:bg-primary/20"
      >
        <span class="truncate max-w-[150px]">{{ event.name }}</span>
        <button type="button" @click.stop="removeEvent(event.id)" class="text-primary hover:text-primary-dark transition-colors ml-0.5">
          <XMarkIcon class="w-3.5 h-3.5" stroke-width="3" />
        </button>
      </div>

      <div 
        v-if="!isExpanded && hiddenEventsCount > 0"
        class="flex items-center gap-1.5 bg-slate-100 text-slate-600 px-3 py-1.5 rounded-[8px] text-xs font-bold shadow-sm border border-slate-200 cursor-pointer hover:bg-slate-200 transition-all"
        @click.stop="isExpanded = true"
      >
        + {{ hiddenEventsCount }} event lainnya...
      </div>

      <div 
        v-for="event in hiddenEvents" 
        :key="event.id"
        v-show="isExpanded"
        class="flex items-center gap-1.5 bg-primary-soft text-primary px-3 py-1.5 rounded-[8px] text-xs font-bold shadow-sm border border-primary/10 transition-all hover:bg-primary/20"
      >
        <span class="truncate max-w-[150px]">{{ event.name }}</span>
        <button type="button" @click.stop="removeEvent(event.id)" class="text-primary hover:text-primary-dark transition-colors ml-0.5">
          <XMarkIcon class="w-3.5 h-3.5" stroke-width="3" />
        </button>
      </div>

      <!-- Search Input -->
      <input 
        ref="searchInput"
        v-model="search"
        type="text"
        class="flex-1 min-w-[150px] bg-transparent border-none p-1 text-sm focus:ring-0 text-[var(--text-primary)] placeholder-[var(--text-muted)] font-medium outline-none"
        :placeholder="initialEvents.length === 0 ? 'Pilih event (bisa lebih dari satu)...' : (isOpen ? 'Cari event...' : '')"
        @focus="openDropdown"
        @keydown.delete="handleDelete"
        @keydown.down.prevent="highlightNext"
        @keydown.up.prevent="highlightPrev"
        @keydown.enter.prevent="selectHighlighted"
        @keydown.esc="closeDropdown"
      />

      <div class="absolute right-3 flex items-center gap-1.5 text-[var(--text-muted)] pointer-events-none transition-transform" :class="{'rotate-180': isOpen}">
         <ChevronDownIcon class="w-4 h-4" stroke-width="2.5" />
      </div>
    </div>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="transform scale-95 opacity-0 translate-y-[-10px]"
      enter-to-class="transform scale-100 opacity-100 translate-y-0"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="transform scale-100 opacity-100 translate-y-0"
      leave-to-class="transform scale-95 opacity-0 translate-y-[-10px]"
    >
      <div 
        v-show="isOpen" 
        class="absolute z-50 w-full mt-2 bg-white border border-[var(--border)] rounded-xl shadow-lg max-h-60 overflow-y-auto"
      >
        <div v-if="isLoading" class="p-4 text-center text-sm text-[var(--text-muted)] font-bold flex items-center justify-center gap-2">
           <ArrowPathIcon class="w-4 h-4 animate-spin text-primary" stroke-width="3" />
           Mencari event...
        </div>
        <div v-else-if="options.length === 0" class="p-4 text-center text-sm text-[var(--text-muted)] font-bold">
          Tidak ada event yang ditemukan.
        </div>
        <ul v-else class="py-1">
          <li 
            v-for="(event, index) in options" 
            :key="event.id"
            @click="toggleEvent(event)"
            @mouseenter="highlightedIndex = index"
            class="px-4 py-2.5 text-[13px] cursor-pointer transition-colors flex items-center gap-3"
            :class="{
              'bg-primary-soft': isSelected(event.id) && highlightedIndex !== index,
              'bg-[var(--hover-bg)]': highlightedIndex === index && !isSelected(event.id),
              'bg-primary text-white': highlightedIndex === index && isSelected(event.id)
            }"
          >
            <!-- Checkbox visual -->
            <div 
              class="w-4 h-4 rounded-md border flex items-center justify-center shrink-0 transition-colors"
              :class="{
                'bg-primary border-primary': isSelected(event.id) && highlightedIndex !== index,
                'bg-white border-white': isSelected(event.id) && highlightedIndex === index,
                'border-[var(--border-strong)] bg-white': !isSelected(event.id)
              }"
            >
              <CheckIcon v-if="isSelected(event.id)" class="w-3 h-3" :class="{'text-white': highlightedIndex !== index, 'text-primary': highlightedIndex === index}" stroke-width="4" />
            </div>
            
            <span class="font-semibold flex-1 truncate" :class="{
              'text-primary': isSelected(event.id) && highlightedIndex !== index,
              'text-white': isSelected(event.id) && highlightedIndex === index,
              'text-[var(--text-primary)]': !isSelected(event.id)
            }">{{ event.name }}</span>
          </li>
        </ul>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { XMarkIcon, ChevronDownIcon, CheckIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import { debounce } from 'lodash-es';
import axios from 'axios';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  // Pass the initial selected events so they display properly before any search
  initialSelected: {
    type: Array,
    default: () => [],
  }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const search = ref('');
const searchInput = ref(null);
const containerRef = ref(null);
const isLoading = ref(false);
const options = ref([]);
const highlightedIndex = ref(-1);

// We keep a reactive list of full event objects for the selected ones
// to show their names in the tags.
const initialEvents = ref([...props.initialSelected]);

const isExpanded = ref(false);
const maxVisible = 5;

const visibleEvents = computed(() => {
  return initialEvents.value.slice(0, maxVisible);
});

const hiddenEvents = computed(() => {
  return initialEvents.value.slice(maxVisible);
});

const hiddenEventsCount = computed(() => {
  return Math.max(0, initialEvents.value.length - maxVisible);
});

// Sync modelValue changes (e.g. from reset)
watch(() => props.modelValue, (newVal) => {
  // If modelValue is cleared, clear our internal representation
  if (newVal.length === 0) {
    initialEvents.value = [];
  }
});

const isSelected = (id) => props.modelValue.includes(id);

const toggleEvent = (event) => {
  const index = props.modelValue.indexOf(event.id);
  const newValue = [...props.modelValue];
  
  if (index === -1) {
    newValue.push(event.id);
    if (!initialEvents.value.find(e => e.id === event.id)) {
      initialEvents.value.push(event);
    }
  } else {
    newValue.splice(index, 1);
    initialEvents.value = initialEvents.value.filter(e => e.id !== event.id);
  }
  
  emit('update:modelValue', newValue);
  searchInput.value?.focus();
};

const removeEvent = (id) => {
  const newValue = props.modelValue.filter(val => val !== id);
  initialEvents.value = initialEvents.value.filter(e => e.id !== id);
  emit('update:modelValue', newValue);
};

const handleDelete = () => {
  if (search.value === '' && props.modelValue.length > 0) {
    const lastId = props.modelValue[props.modelValue.length - 1];
    removeEvent(lastId);
  }
};

const openDropdown = () => {
  isOpen.value = true;
  searchInput.value?.focus();
  if (options.value.length === 0) {
    fetchEvents('');
  }
};

const closeDropdown = () => {
  isOpen.value = false;
  search.value = '';
  highlightedIndex.value = -1;
};

const selectHighlighted = () => {
  if (isOpen.value && options.value[highlightedIndex.value]) {
    toggleEvent(options.value[highlightedIndex.value]);
  } else if (!isOpen.value) {
    openDropdown();
  }
};

const highlightNext = () => {
  if (!isOpen.value) {
    openDropdown();
    return;
  }
  if (highlightedIndex.value < options.value.length - 1) {
    highlightedIndex.value++;
  }
};

const highlightPrev = () => {
  if (highlightedIndex.value > 0) {
    highlightedIndex.value--;
  }
};

const fetchEvents = debounce(async (query) => {
  isLoading.value = true;
  try {
    const response = await axios.get(route('events.search'), { 
      params: { 
        q: query,
        exclude: props.modelValue
      }
    });
    options.value = response.data;
  } catch (error) {
    console.error('Failed to fetch events:', error);
  } finally {
    isLoading.value = false;
  }
}, 300);

watch(search, (newVal) => {
  fetchEvents(newVal);
  highlightedIndex.value = -1;
  if (!isOpen.value) isOpen.value = true;
});

// Click outside to close
const handleClickOutside = (event) => {
  if (containerRef.value && !containerRef.value.contains(event.target)) {
    closeDropdown();
  }
};

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<style scoped>
/* Scoped overrides if needed */
</style>
