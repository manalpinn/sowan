<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-6 rounded-2xl bg-emerald-50 p-4 text-sm font-bold text-emerald-600 border border-emerald-100 flex items-center gap-3">
            <CheckCircleIcon class="h-5 w-5" />
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="space-y-7">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2.5 ml-1">Alamat Email</label>
                    <div class="relative group">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-violet-600 transition-colors">
                            <EnvelopeIcon class="h-5 w-5" />
                        </span>
                        <input
                            id="email"
                            type="email"
                            class="w-full pl-14 pr-5 py-4 bg-slate-50 border-slate-100 rounded-2xl text-slate-900 placeholder:text-slate-400 focus:ring-4 focus:ring-violet-600/5 focus:border-violet-600 focus:bg-white transition-all outline-none font-medium"
                            v-model="form.email"
                            required
                            autofocus
                            placeholder="nama@sowan.id"
                            autocomplete="username"
                        />
                    </div>
                    <div v-if="form.errors.email" class="mt-2 text-[11px] font-bold text-red-500 ml-1 uppercase tracking-tighter">{{ form.errors.email }}</div>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-2.5 ml-1">
                        <label for="password" class="block text-xs font-black uppercase tracking-widest text-slate-500">Password</label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-[11px] font-black uppercase tracking-tighter text-violet-600 hover:text-violet-700 transition-colors"
                        >
                            Lupa Password?
                        </Link>
                    </div>
                    <div class="relative group">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-violet-600 transition-colors">
                            <LockClosedIcon class="h-5 w-5" />
                        </span>
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="w-full pl-14 pr-12 py-4 bg-slate-50 border-slate-100 rounded-2xl text-slate-900 placeholder:text-slate-400 focus:ring-4 focus:ring-violet-600/5 focus:border-violet-600 focus:bg-white transition-all outline-none font-medium"
                            v-model="form.password"
                            required
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                        />
                        <button 
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-violet-600 transition-colors p-1"
                        >
                            <EyeIcon v-if="!showPassword" class="h-5 w-5" />
                            <EyeSlashIcon v-else class="h-5 w-5" />
                        </button>
                    </div>
                    <div v-if="form.errors.password" class="mt-2 text-[11px] font-bold text-red-500 ml-1 uppercase tracking-tighter">{{ form.errors.password }}</div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center ml-1">
                    <label class="flex items-center cursor-pointer group">
                        <div class="relative">
                            <input
                                type="checkbox"
                                name="remember"
                                v-model="form.remember"
                                class="sr-only peer"
                            />
                            <div class="w-5 h-5 bg-slate-50 border-2 border-slate-100 rounded-lg peer-checked:bg-violet-600 peer-checked:border-violet-600 transition-all"></div>
                            <svg viewBox="0 0 24 24" class="absolute inset-0 h-5 w-5 text-white scale-0 peer-checked:scale-100 transition-transform" fill="none" stroke="currentColor" stroke-width="4">
                                <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="ms-3 text-sm font-bold text-slate-500 group-hover:text-slate-900 transition-colors">Ingat sesi saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button
                    class="group relative w-full py-4 bg-violet-600 text-white rounded-2xl font-black text-lg shadow-xl shadow-violet-100 hover:bg-violet-700 hover:shadow-2xl hover:shadow-violet-200 hover:-translate-y-1 active:scale-95 transition-all disabled:opacity-50 disabled:translate-y-0 disabled:shadow-none overflow-hidden"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    <div class="relative z-10 flex items-center justify-center gap-3">
                        <span v-if="form.processing" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            MEMPROSES...
                        </span>
                        <span v-else class="uppercase tracking-widest">Masuk ke Sistem</span>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                </button>
            </div>
        </form>
    </GuestLayout>
</template>

<script setup>
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    EnvelopeIcon, 
    LockClosedIcon, 
    CheckCircleIcon,
    EyeIcon,
    EyeSlashIcon
} from '@heroicons/vue/24/outline';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const showPassword = ref(false);
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>
