<template>
    <GuestLayout>
        <Head title="Verifikasi OTP" />

        <div class="text-center mb-8">
            <h2 class="text-2xl font-black tracking-tight text-slate-900 uppercase">Verifikasi OTP</h2>
            <p class="mt-3 text-sm font-medium text-slate-500 leading-relaxed">
                Kami telah mengirimkan 6 digit kode OTP ke email:<br>
                <span class="font-black text-violet-600 tracking-wide">{{ email }}</span>
            </p>
        </div>

        <div v-if="status" class="mb-6 rounded-2xl bg-emerald-50 p-4 text-sm font-bold text-emerald-600 border border-emerald-100 flex items-center gap-3">
            <CheckCircleIcon class="h-5 w-5 shrink-0" />
            <span>{{ status }}</span>
        </div>

        <div v-if="errors.otp" class="mb-6 rounded-2xl bg-rose-50 p-4 text-sm font-bold text-rose-600 border border-rose-100 flex items-center gap-3">
            <ExclamationTriangleIcon class="h-5 w-5 shrink-0" />
            <span>{{ errors.otp }}</span>
        </div>

        <form @submit.prevent="submit">
            <div class="space-y-8">
                <!-- OTP Digits Container -->
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 text-center mb-4">
                        Masukkan 6 Digit Kode OTP
                    </label>
                    <div class="flex justify-center gap-2 md:gap-3" @paste="handlePaste">
                        <input
                            v-for="(digit, index) in digits"
                            :key="index"
                            :ref="el => { if (el) inputRefs[index] = el }"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="1"
                            class="w-12 h-14 md:w-14 md:h-16 text-center text-2xl font-black bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-violet-600 focus:ring-4 focus:ring-violet-600/5 focus:bg-white transition-all outline-none text-slate-800"
                            v-model="digits[index]"
                            @input="handleInput(index, $event)"
                            @keydown="handleKeyDown(index, $event)"
                            required
                        />
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="group relative w-full py-4 bg-violet-600 text-white rounded-2xl font-black text-lg shadow-xl shadow-violet-100 hover:bg-violet-700 hover:shadow-2xl hover:shadow-violet-200 hover:-translate-y-1 active:scale-95 transition-all disabled:opacity-50 disabled:translate-y-0 disabled:shadow-none overflow-hidden"
                    :disabled="processing || otpString.length !== 6"
                >
                    <div class="relative z-10 flex items-center justify-center gap-3">
                        <span v-if="processing" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            VERIFIKASI...
                        </span>
                        <span v-else class="uppercase tracking-widest">Verifikasi & Masuk</span>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                </button>
            </div>
        </form>

        <!-- Resend OTP Option -->
        <div class="mt-8 text-center border-t border-slate-100 pt-6">
            <p class="text-sm font-medium text-slate-500">
                Tidak menerima email OTP?
            </p>
            <div class="mt-2.5">
                <button
                    v-if="countdown > 0"
                    type="button"
                    disabled
                    class="text-xs font-bold uppercase tracking-widest text-slate-400 bg-slate-50 px-5 py-2.5 rounded-full border border-slate-100"
                >
                    Kirim Ulang OTP ({{ countdown }}s)
                </button>
                <button
                    v-else
                    type="button"
                    @click="resendOtp"
                    :disabled="resending"
                    class="text-xs font-black uppercase tracking-widest text-violet-600 hover:text-violet-700 bg-violet-50 hover:bg-violet-100/70 px-6 py-2.5 rounded-full transition-colors active:scale-95"
                >
                    {{ resending ? 'MENGIRIM...' : 'Kirim Ulang OTP' }}
                </button>
            </div>
        </div>

        <div class="mt-6 text-center">
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                type="button"
                class="text-xs font-bold uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors"
            >
                Kembali ke Login / Batal
            </Link>
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    email: String,
    status: String,
    errors: Object,
});

const digits = ref(['', '', '', '', '', '']);
const inputRefs = ref([]);
const processing = ref(false);
const resending = ref(false);
const countdown = ref(60);

const otpString = computed(() => digits.value.join(''));

onMounted(() => {
    // Focus first input automatically
    nextTick(() => {
        if (inputRefs.value[0]) {
            inputRefs.value[0].focus();
        }
    });

    // Start resend countdown
    startTimer();
});

const startTimer = () => {
    countdown.value = 60;
    const interval = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
            clearInterval(interval);
        }
    }, 1000);
};

const handleInput = (index, event) => {
    const val = event.target.value;
    // Keep only the last character and verify it's numeric
    if (val) {
        const numbersOnly = val.replace(/[^0-9]/g, '');
        digits.value[index] = numbersOnly.charAt(numbersOnly.length - 1);
        
        // Auto focus next input
        if (digits.value[index] && index < 5) {
            inputRefs.value[index + 1].focus();
        }
    }
};

const handleKeyDown = (index, event) => {
    // Backspace: clear current value or focus previous input
    if (event.key === 'Backspace') {
        if (!digits.value[index] && index > 0) {
            digits.value[index - 1] = '';
            inputRefs.value[index - 1].focus();
        } else {
            digits.value[index] = '';
        }
        event.preventDefault();
    }
    
    // Left arrow: focus previous
    if (event.key === 'ArrowLeft' && index > 0) {
        inputRefs.value[index - 1].focus();
    }

    // Right arrow: focus next
    if (event.key === 'ArrowRight' && index < 5) {
        inputRefs.value[index + 1].focus();
    }
};

const handlePaste = (event) => {
    event.preventDefault();
    const pastedData = event.clipboardData.getData('text').trim();
    if (/^\d{6}$/.test(pastedData)) {
        for (let i = 0; i < 6; i++) {
            digits.value[i] = pastedData.charAt(i);
        }
        // Focus last input or submit
        inputRefs.value[5].focus();
    }
};

const submit = () => {
    processing.value = true;
    router.post('/login/otp', { otp: otpString.value }, {
        onFinish: () => {
            processing.value = false;
        },
        onError: () => {
            // Clear inputs on error and focus first
            digits.value = ['', '', '', '', '', ''];
            if (inputRefs.value[0]) {
                inputRefs.value[0].focus();
            }
        }
    });
};

const resendOtp = () => {
    resending.value = true;
    router.post('/login/otp/resend', {}, {
        onFinish: () => {
            resending.value = false;
            startTimer();
        }
    });
};
</script>
