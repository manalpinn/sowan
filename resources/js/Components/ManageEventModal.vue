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
            <h3 class="text-lg font-bold text-slate-800">Kelola Event User</h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Penugasan event untuk <span class="text-primary font-bold">{{ user.name }}</span></p>
          </div>
          <button @click="close" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-md hover:bg-slate-200">
            <XMarkIcon class="w-5 h-5" stroke-width="2.5" />
          </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto">
          <form @submit.prevent="submit">
            <div class="form-group mb-0">
              <label class="form-label text-sm">Pilih Event <span class="text-danger">*</span></label>
              
              <!-- Reuse our MultiSelectEvent component -->
              <MultiSelectEvent 
                v-model="form.event_ids" 
                :initial-selected="initialSelectedEvents" 
              />
              
              <p class="text-[11px] text-slate-500 mt-2 font-semibold flex items-center gap-1.5">
                <InformationCircleIcon class="w-3.5 h-3.5" />
                Hanya menampilkan event aktif. Event lama bisa dihapus dengan menekan tanda (X).
              </p>
              
              <div v-if="form.errors.event_ids" class="form-error mt-1">{{ form.errors.event_ids }}</div>
            </div>
          </form>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-[var(--border)] bg-slate-50 flex items-center justify-end gap-3">
          <button 
            type="button" 
            class="btn btn-secondary text-sm px-4 py-2" 
            @click="close"
            :disabled="form.processing"
          >
            Batal
          </button>
          <button 
            type="button" 
            class="btn btn-primary text-sm px-6 py-2 flex items-center gap-2"
            @click="submit"
            :disabled="form.processing"
          >
            <ArrowPathIcon v-if="form.processing" class="w-4 h-4 animate-spin" />
            <CheckIcon v-else class="w-4 h-4" stroke-width="2.5" />
            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { XMarkIcon, CheckIcon, ArrowPathIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';
import MultiSelectEvent from '@/Components/MultiSelectEvent.vue';
import { notify } from '@/Utils/SweetAlert';

const props = defineProps({
  show: Boolean,
  user: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['close']);

const initialSelectedEvents = ref([]);

const form = useForm({
  event_ids: []
});

// Reset and populate form when modal opens
watch(() => props.show, (isShowing) => {
  if (isShowing && props.user) {
    // Populate initial selections from the user object
    form.clearErrors();
    const userEvents = props.user.events || [];
    initialSelectedEvents.value = [...userEvents];
    form.event_ids = userEvents.map(e => e.id);
  }
});

const close = () => {
  if (!form.processing) {
    emit('close');
  }
};

const submit = () => {
  form.post(route('users.events.sync', props.user.id), {
    preserveScroll: true,
    onSuccess: () => {
      notify.toast('Penugasan event berhasil diperbarui', 'success');
      close();
    }
  });
};
</script>

<style scoped>
.text-danger { color: var(--danger); }
</style>
