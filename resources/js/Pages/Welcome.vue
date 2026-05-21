<template>
    <Head title="Sistem Manajemen Tamu Profesional" />

    <div class="min-h-screen bg-white font-sans text-slate-900 antialiased selection:bg-violet-100 selection:text-violet-900">
        
        <!-- Background Pattern (Global) -->
        <div class="fixed inset-0 z-0 pointer-events-none opacity-[0.03]" :style="{ backgroundImage: `url('${bgPattern}')` }"></div>

        <!-- Navbar -->
        <nav
            class="fixed top-0 lg:top-4 left-0 right-0 z-50 mx-auto max-w-7xl transition-all duration-500 px-4"
        >
            <div 
                class="flex items-center justify-between px-6 py-3 transition-all duration-500 rounded-2xl lg:rounded-3xl"
                :class="isScrolled ? 'bg-white/80 backdrop-blur-xl shadow-[0_8px_32px_rgba(124,58,237,0.12)] border border-white/20' : 'bg-transparent border-transparent'"
            >
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-3 group">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 text-white shadow-lg shadow-violet-200 transition-all duration-300 group-hover:rotate-6 group-hover:scale-110">
                        <UsersIcon class="h-5 w-5" stroke-width="2.5" />
                    </div>
                    <span class="text-xl font-black tracking-tighter text-slate-900 uppercase">SOWAN<span class="text-violet-600">.</span></span>
                </Link>

                <!-- Desktop Nav -->
                <div class="hidden md:flex items-center gap-10">
                    <a href="#fitur" class="text-sm font-bold text-slate-500 hover:text-violet-600 transition-colors uppercase tracking-widest">Fitur</a>
                    <a href="#manfaat" class="text-sm font-bold text-slate-500 hover:text-violet-600 transition-colors uppercase tracking-widest">Manfaat</a>
                    <div class="h-4 w-px bg-slate-200"></div>
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-sm font-bold text-violet-600 hover:text-violet-700 transition-colors">
                        Dashboard
                    </Link>
                    <Link v-else :href="route('login')" class="group relative inline-flex items-center justify-center overflow-hidden rounded-full bg-violet-600 px-6 py-2.5 text-sm font-bold text-white transition-all duration-300 hover:bg-violet-700 hover:shadow-xl hover:shadow-violet-200 active:scale-95 shadow-lg shadow-violet-100">
                        <span class="relative z-10">Masuk Sistem</span>
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                    </Link>
                </div>

                <!-- Mobile Toggle -->
                <button
                    @click="isMobileMenuOpen = !isMobileMenuOpen"
                    class="md:hidden flex items-center justify-center h-10 w-10 rounded-xl text-slate-600 hover:bg-slate-100 transition-colors"
                >
                    <svg v-if="!isMobileMenuOpen" viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/>
                    </svg>
                    <svg v-else viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-4"
            >
                <div v-if="isMobileMenuOpen" class="md:hidden absolute top-full left-4 right-4 mt-2 bg-white/95 backdrop-blur-xl border border-slate-100 rounded-3xl p-6 flex flex-col gap-5 shadow-2xl overflow-hidden">
                    <div class="absolute inset-0 z-0 bg-gradient-to-br from-violet-50/50 to-transparent"></div>
                    <div class="relative z-10 flex flex-col gap-5">
                        <a href="#fitur" @click="isMobileMenuOpen = false" class="text-lg font-black text-slate-700 hover:text-violet-600 transition-colors uppercase">Fitur Utama</a>
                        <a href="#manfaat" @click="isMobileMenuOpen = false" class="text-lg font-black text-slate-700 hover:text-violet-600 transition-colors uppercase">Manfaat Sistem</a>
                        <hr class="border-slate-100" />
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" @click="isMobileMenuOpen = false" class="text-lg font-black text-violet-600">Dashboard</Link>
                        <Link v-else :href="route('login')" @click="isMobileMenuOpen = false" class="flex justify-center rounded-2xl bg-violet-600 py-4 text-base font-black text-white shadow-xl shadow-violet-200">
                            Masuk ke Sistem
                        </Link>
                    </div>
                </div>
            </Transition>
        </nav>

        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-28 pb-16 md:pt-40 md:pb-24 lg:pt-56 lg:pb-40">
            <!-- Decorative Orbs -->
            <div class="absolute -top-40 -right-40 h-[600px] w-[600px] rounded-full bg-violet-100/40 blur-[120px] animate-pulse hidden lg:block"></div>
            <div class="absolute top-1/2 -left-40 h-[400px] w-[400px] rounded-full bg-indigo-100/30 blur-[100px] hidden lg:block"></div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 xl:gap-24 items-center">
                    <div class="relative z-10 text-center lg:text-left">
                        <!-- Badge -->
                        <div class="mb-6 lg:mb-10 inline-flex items-center gap-3 rounded-full border border-violet-100 bg-white/80 backdrop-blur-sm px-4 py-2 text-[10px] sm:text-[11px] font-black uppercase tracking-[0.2em] text-violet-600 shadow-sm transition-transform hover:scale-105">
                            <span class="flex h-2 w-2 rounded-full bg-violet-600 animate-pulse"></span>
                            Internal Guest Management
                        </div>

                        <!-- Headline -->
                        <h1 class="mb-6 md:mb-8 text-5xl sm:text-6xl md:text-7xl lg:text-[86px] xl:text-[96px] font-black leading-[0.9] tracking-tighter text-slate-900">
                            Efisien<span class="text-violet-600">.</span><br />
                            Aman<span class="text-violet-600">.</span><br />
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 via-indigo-500 to-violet-400">Digital.</span>
                        </h1>

                        <!-- Subheadline -->
                        <p class="mb-8 md:mb-12 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-500 max-w-lg mx-auto lg:mx-0 font-medium">
                            Tinggalkan cara lama. Kelola kedatangan tamu instansi Anda dengan sistem QR-Checkin yang terintegrasi dan profesional.
                        </p>

                        <!-- CTA Actions -->
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 sm:gap-6">
                            <Link :href="route('login')" class="w-full sm:w-auto group relative inline-flex items-center justify-center gap-3 rounded-2xl bg-violet-600 px-8 py-4 sm:px-10 sm:py-5 text-base sm:text-lg font-black text-white transition-all duration-300 hover:bg-violet-700 hover:-translate-y-1 hover:shadow-2xl hover:shadow-violet-200 active:scale-95 shadow-xl shadow-violet-100">
                                Mulai Sekarang
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20 transition-transform group-hover:rotate-45">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3">
                                        <path d="M7 17L17 7M17 7H7M17 7V17" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </Link>
                            <a href="#fitur" class="flex items-center gap-2 text-sm sm:text-base font-bold text-slate-600 hover:text-violet-600 transition-all border-b-2 border-transparent hover:border-violet-200 py-1 uppercase tracking-wider">
                                Lihat Fitur
                            </a>
                        </div>
                    </div>

                    <!-- Visual / Mockup -->
                    <div class="relative group mt-8 lg:mt-0">
                        <!-- Decorative Glow -->
                        <div class="absolute -inset-4 sm:-inset-10 md:-inset-20 bg-gradient-to-tr from-violet-500/10 to-indigo-500/10 blur-[60px] md:blur-[100px] rounded-[100px]"></div>
                        
                        <!-- Mockup Frame -->
                        <div class="relative p-2 sm:p-3 rounded-[32px] sm:rounded-[52px] bg-gradient-to-br from-slate-200 via-white to-slate-200 shadow-2xl transition-transform duration-700 lg:group-hover:scale-[1.03]">
                            <div class="relative overflow-hidden rounded-[24px] sm:rounded-[40px] bg-white aspect-[4/3.5] shadow-inner border border-slate-100/50">
                                <!-- Dashboard Interface Mockup -->
                                <div class="absolute inset-0 flex flex-col bg-[#F8FAFC] scale-[0.6] sm:scale-100 origin-top">
                                    <!-- Sidebar + Top Bar Mock -->
                                    <div class="flex h-full">
                                        <!-- Mini Sidebar -->
                                        <div class="w-10 sm:w-14 border-r border-slate-100 bg-white flex flex-col items-center py-6 gap-6">
                                            <div class="h-6 w-6 rounded bg-violet-600"></div>
                                            <div v-for="i in 4" :key="i" class="h-4 w-4 sm:h-5 sm:w-5 rounded bg-slate-50 border border-slate-100/50"></div>
                                        </div>
                                        
                                        <!-- Content Area -->
                                        <div class="flex-1 flex flex-col">
                                            <!-- Top Navbar Mock -->
                                            <div class="h-12 sm:h-16 border-b border-slate-100 bg-white px-4 sm:px-6 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-2 w-16 sm:w-24 bg-slate-200 rounded-full"></div>
                                                </div>
                                                <div class="flex gap-2">
                                                    <div class="h-6 w-6 sm:h-8 sm:w-8 rounded-full bg-slate-50 border border-slate-100"></div>
                                                    <div class="h-6 w-16 sm:h-8 sm:w-20 rounded-lg bg-slate-900"></div>
                                                </div>
                                            </div>

                                            <!-- Main Body Mock -->
                                            <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                                                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                                                    <div class="bg-white p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-slate-100 shadow-sm space-y-2">
                                                        <div class="h-2 w-1/2 bg-slate-100 rounded"></div>
                                                        <div class="h-1.5 w-full bg-violet-600/10 rounded-full"></div>
                                                    </div>
                                                    <div class="bg-white p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-slate-100 shadow-sm space-y-2">
                                                        <div class="h-2 w-1/2 bg-slate-100 rounded"></div>
                                                        <div class="flex items-end gap-1 h-4">
                                                            <div v-for="h in [40, 70, 50, 90]" :key="h" class="flex-1 bg-indigo-50 rounded-sm" :style="{ height: h + '%' }"></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-5 space-y-4">
                                                    <div v-for="i in 3" :key="i" class="flex items-center justify-between">
                                                        <div class="flex items-center gap-3">
                                                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg bg-slate-50"></div>
                                                            <div class="space-y-1.5">
                                                                <div class="h-2 w-20 sm:w-28 bg-slate-800 rounded"></div>
                                                                <div class="h-1 w-12 sm:w-20 bg-slate-200 rounded"></div>
                                                            </div>
                                                        </div>
                                                        <div class="h-4 w-12 sm:w-16 bg-emerald-50 rounded-full border border-emerald-100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Scan Line Effect -->
                                <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-violet-400 to-transparent z-20 animate-[scan_4s_ease-in-out_infinite]"></div>
                            </div>
                        </div>

                        <!-- Floating Badges (Hidden on mobile) -->
                        <div class="hidden sm:block absolute -top-6 -right-6 w-36 md:w-44 bg-white p-4 md:p-5 rounded-2xl md:rounded-3xl shadow-2xl border border-violet-50 animate-bounce-slow z-30">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="h-8 w-8 md:h-9 md:w-9 rounded-lg md:rounded-xl bg-violet-600 flex items-center justify-center text-white shadow-lg shadow-violet-200">
                                    <ShieldCheckIcon class="h-4 w-4 md:h-5 md:w-5" />
                                </div>
                                <div class="text-[10px] md:text-[11px] font-black uppercase text-slate-900 tracking-tight">Secured</div>
                            </div>
                        </div>

                        <div class="hidden md:block absolute bottom-12 -left-10 w-48 bg-slate-900 p-5 rounded-3xl shadow-2xl group-hover:translate-x-4 transition-transform duration-700 z-30">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-2xl bg-violet-600 flex items-center justify-center text-white shadow-lg shadow-violet-500/20">
                                    <QrCodeIcon class="h-6 w-6" />
                                </div>
                                <div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Status</div>
                                    <div class="text-sm font-black text-white uppercase leading-none">SCANNED</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="fitur" class="py-24 md:py-36 lg:py-48 bg-slate-50/30 relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] bg-[size:32px_32px] opacity-30"></div>
            
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-10 mb-20 md:mb-32">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-3 rounded-full border border-violet-100 bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.3em] text-violet-600 mb-8 shadow-sm">
                            <SparklesIcon class="h-3 w-3" />
                            Premium Features
                        </div>
                        <h2 class="text-4xl sm:text-5xl md:text-6xl font-black text-slate-900 tracking-tighter uppercase leading-[0.95]">
                            Solusi Cerdas<br />
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-indigo-500">Manajemen Tamu.</span>
                        </h2>
                    </div>
                    <p class="max-w-md text-lg text-slate-500 font-medium leading-relaxed pb-2">
                        Sistem kami dirancang untuk menghadirkan efisiensi maksimal tanpa mengorbankan keamanan data instansi Anda.
                    </p>
                </div>

                <!-- Features Grid -->
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
                    <div v-for="(f, i) in features" :key="i" class="group relative bg-white rounded-[32px] p-8 sm:p-10 border border-slate-100 transition-all duration-700 hover:border-violet-200 hover:shadow-[0_30px_70px_-15px_rgba(124,58,237,0.12)] hover:-translate-y-3 overflow-hidden">
                        <div class="absolute -top-10 -right-10 h-32 w-32 bg-violet-50 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        
                        <div class="relative z-10 mb-12 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-violet-600 group-hover:bg-violet-600 group-hover:text-white transition-all duration-500 group-hover:scale-110 group-hover:rotate-3 shadow-sm group-hover:shadow-xl group-hover:shadow-violet-200">
                            <component :is="f.icon" class="h-7 w-7" />
                        </div>
                        
                        <h4 class="relative z-10 mb-4 text-xl sm:text-2xl font-black text-slate-900 tracking-tight uppercase leading-tight">{{ f.title }}</h4>
                        <p class="relative z-10 text-sm leading-relaxed text-slate-500 font-medium group-hover:text-slate-600 transition-colors">{{ f.desc }}</p>
                        
                        <div class="mt-10 flex items-center gap-2 text-xs font-black text-violet-600 opacity-0 group-hover:opacity-100 transition-all translate-y-4 group-hover:translate-y-0 uppercase tracking-widest">
                            Pelajari Lebih Lanjut
                            <ArrowRightIcon class="h-3 w-3" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits & Stats Section -->
        <section id="manfaat" class="py-24 md:py-36 lg:py-48 relative overflow-hidden">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-20 lg:gap-32 items-center">
                    <!-- Left: Visual/Stats -->
                    <div class="relative order-2 lg:order-1">
                        <div class="relative grid grid-cols-2 gap-4 sm:gap-8">
                            <div v-for="(stat, i) in stats" :key="i" 
                                class="group relative bg-white p-8 sm:p-12 rounded-[40px] border border-slate-100 shadow-sm transition-all duration-500 hover:shadow-2xl hover:shadow-violet-100 hover:-translate-y-2 text-center"
                                :class="i % 2 !== 0 ? 'mt-8 sm:mt-12' : ''"
                            >
                                <div class="text-4xl sm:text-6xl font-black text-slate-900 mb-3 tracking-tighter tabular-nums group-hover:text-violet-600 transition-colors">
                                    {{ stat.value }}
                                </div>
                                <div class="text-[11px] sm:text-[13px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">{{ stat.label }}</div>
                                <div class="text-[9px] font-bold text-slate-300 uppercase tracking-widest">{{ stat.sub }}</div>
                                
                                <div class="absolute top-4 right-8 text-4xl font-black text-slate-50 select-none group-hover:text-violet-50/50 transition-colors">{{ i + 1 }}</div>
                            </div>
                        </div>
                        <!-- Decorative Blob -->
                        <div class="absolute -z-10 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[120%] h-[120%] bg-[radial-gradient(circle_at_center,rgba(124,58,237,0.03)_0%,transparent_70%)]"></div>
                    </div>

                    <!-- Right: Content -->
                    <div class="order-1 lg:order-2 text-center lg:text-left">
                        <div class="inline-flex items-center gap-3 mb-8 px-4 py-2 bg-violet-50 rounded-full border border-violet-100 text-[10px] font-black text-violet-600 uppercase tracking-widest">
                            <ShieldCheckIcon class="h-3 w-3" />
                            Security Protocol
                        </div>
                        <h3 class="text-4xl sm:text-5xl font-black text-slate-900 mb-10 tracking-tighter leading-[1.05] uppercase">
                            Keamanan &<br />
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-indigo-500">Infrastruktur</span> Teruji.
                        </h3>
                        <p class="text-lg text-slate-500 font-medium leading-relaxed mb-12 max-w-2xl mx-auto lg:mx-0">
                            Kami membangun Sowan dengan standar keamanan perbankan, memastikan setiap data kunjungan tersimpan dengan enkripsi tingkat tinggi dan hanya dapat diakses oleh pihak yang berwenang.
                        </p>
                        
                        <div class="space-y-6 text-left">
                            <div v-for="(adv, i) in advantages" :key="i" class="group flex items-center gap-6 p-5 sm:p-6 rounded-3xl border border-transparent hover:border-slate-100 hover:bg-white hover:shadow-xl hover:shadow-slate-100 transition-all duration-300">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-white text-xs font-black group-hover:bg-violet-600 group-hover:scale-110 transition-all">
                                    0{{ i + 1 }}
                                </div>
                                <div>
                                    <h5 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight uppercase group-hover:text-violet-600 transition-colors">{{ adv.title }}</h5>
                                    <p class="text-sm text-slate-500 font-medium leading-relaxed">{{ adv.desc }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative py-24 md:py-40 lg:py-56 bg-[#0B0A12] overflow-hidden">
            <!-- Sophisticated Background Elements -->
            <div class="absolute inset-0">
                <div class="absolute top-0 left-1/4 w-px h-full bg-gradient-to-b from-transparent via-violet-500/20 to-transparent"></div>
                <div class="absolute top-0 right-1/4 w-px h-full bg-gradient-to-b from-transparent via-violet-500/20 to-transparent"></div>
                <div class="absolute top-1/3 left-0 w-full h-px bg-gradient-to-r from-transparent via-violet-500/10 to-transparent"></div>
            </div>
            
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-violet-600/5 rounded-full blur-[120px]"></div>

            <div class="relative mx-auto max-w-5xl px-4 sm:px-6 text-center">
                <div class="mb-12 inline-flex items-center justify-center p-1 rounded-3xl bg-white/5 backdrop-blur-xl border border-white/10">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-violet-600 text-white shadow-2xl shadow-violet-500/20">
                        <UserGroupIcon class="h-8 w-8" />
                    </div>
                </div>
                
                <h2 class="text-4xl sm:text-6xl md:text-7xl font-black text-white mb-8 tracking-tighter leading-[0.9] uppercase">
                    Siap Memulai<br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-indigo-300 italic">Era Baru?</span>
                </h2>
                
                <p class="text-slate-400 text-lg sm:text-xl mb-16 max-w-2xl mx-auto font-medium leading-relaxed">
                    Jadikan setiap kunjungan sebagai pengalaman yang profesional, cepat, dan aman dengan sistem Sowan.
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                    <Link :href="route('login')" class="group w-full sm:w-auto inline-flex items-center justify-center gap-4 rounded-3xl bg-violet-600 px-10 py-6 text-xl font-black text-white transition-all hover:bg-violet-500 hover:scale-105 active:scale-95 shadow-[0_20px_50px_rgba(124,58,237,0.3)]">
                        Masuk Dashboard
                        <ArrowRightIcon class="h-6 w-6 transition-transform group-hover:translate-x-1" />
                    </Link>
                    
                    <a href="#fitur" class="w-full sm:w-auto inline-flex items-center justify-center px-10 py-6 text-xl font-black text-white border border-white/10 rounded-3xl hover:bg-white/5 transition-all">
                        Eksplorasi Fitur
                    </a>
                </div>
                
                <div class="mt-20 flex flex-col items-center gap-4">
                    <div class="flex -space-x-3">
                        <div v-for="i in 4" :key="i" class="h-10 w-10 rounded-full border-2 border-[#0B0A12] bg-slate-800 flex items-center justify-center text-[10px] font-bold text-white">
                            {{ String.fromCharCode(64 + i) }}
                        </div>
                    </div>
                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-[0.4em]">Dipercaya oleh instansi ternama di Indonesia</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-100 pt-24 pb-12">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid lg:grid-cols-12 gap-16 mb-20">
                    <div class="lg:col-span-5">
                        <Link href="/" class="flex items-center gap-3 mb-8 group">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-600 text-white transition-transform group-hover:rotate-6">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="9" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <span class="text-2xl font-black tracking-tighter text-slate-900 uppercase">SOWAN<span class="text-violet-600">.</span></span>
                        </Link>
                        <p class="text-lg text-slate-500 font-medium leading-relaxed max-w-sm mb-10">
                            Membangun standar baru dalam manajemen tamu digital untuk instansi pemerintah dan organisasi swasta.
                        </p>
                        <!-- Updated Footer Icons -->
                        <div class="flex gap-4">
                            <a v-for="(social, i) in socials" :key="i" :href="social.link" class="h-11 w-11 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-500 hover:text-white hover:bg-violet-600 transition-all duration-300 border border-slate-100 hover:border-violet-600 hover:-translate-y-1 shadow-sm">
                                <component :is="social.icon" class="h-5 w-5" />
                            </a>
                        </div>
                    </div>

                    <div class="lg:col-span-7 grid sm:grid-cols-3 gap-12">
                        <div>
                            <h6 class="text-[11px] font-black text-slate-900 uppercase tracking-[0.3em] mb-8">Eksplorasi</h6>
                            <ul class="space-y-4 text-base font-bold text-slate-500 uppercase tracking-tighter">
                                <li><a href="#" class="hover:text-violet-600 transition-colors">Beranda</a></li>
                                <li><a href="#fitur" class="hover:text-violet-600 transition-colors">Fitur Utama</a></li>
                                <li><a href="#manfaat" class="hover:text-violet-600 transition-colors">Manfaat</a></li>
                                <li><Link :href="route('login')" class="hover:text-violet-600 transition-colors">Masuk Sistem</Link></li>
                            </ul>
                        </div>
                        <div>
                            <h6 class="text-[11px] font-black text-slate-900 uppercase tracking-[0.3em] mb-8">Dukungan</h6>
                            <ul class="space-y-4 text-base font-bold text-slate-500 uppercase tracking-tighter">
                                <li><a href="#" class="hover:text-violet-600 transition-colors">Pusat Bantuan</a></li>
                                <li><a href="#" class="hover:text-violet-600 transition-colors">Panduan</a></li>
                                <li><a href="#" class="hover:text-violet-600 transition-colors">Kebijakan</a></li>
                            </ul>
                        </div>
                        <div>
                            <h6 class="text-[11px] font-black text-slate-900 uppercase tracking-[0.3em] mb-8">Kontak</h6>
                            <div class="text-base font-black text-slate-900 mb-2">support@sowan.id</div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Hubungi Admin Teknik.</div>
                        </div>
                    </div>
                </div>

                <div class="pt-12 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="flex items-center gap-4">
                        <div class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">
                            &copy; {{ currentYear }} SOWAN. SISTEM AKTIF.
                        </p>
                    </div>
                    <div class="flex items-center gap-10">
                        <a href="#" class="text-[10px] font-black text-slate-400 hover:text-slate-900 transition-colors uppercase tracking-[0.3em]">Privacy</a>
                        <a href="#" class="text-[10px] font-black text-slate-400 hover:text-slate-900 transition-colors uppercase tracking-[0.3em]">Terms</a>
                        <div class="text-[10px] font-black text-slate-300">v.1.0.4</div>
                    </div>
                </div>
            </div>
        </footer>

    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    UsersIcon, 
    ArrowRightIcon, 
    CheckCircleIcon, 
    DevicePhoneMobileIcon, 
    CursorArrowRaysIcon,
    ShieldCheckIcon,
    SparklesIcon,
    ArrowPathRoundedSquareIcon,
    ChartBarIcon,
    GlobeAltIcon,
    Bars3Icon,
    XMarkIcon,
    QrCodeIcon,
    ArrowPathIcon,
    DocumentChartBarIcon,
    UserGroupIcon
} from '@heroicons/vue/24/outline';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);
const currentYear = computed(() => new Date().getFullYear());

const bgPattern = `data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%237C3AED' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2v-4h4v-2h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2v-4h4v-2H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E`;

const features = [
    {
        title: 'Check-in QR Instan',
        desc: 'Proses kehadiran tamu secepat kilat dengan pemindaian QR Code yang responsif dan akurat.',
        icon: QrCodeIcon,
    },
    {
        title: 'Log Real-time',
        desc: 'Pantau arus kedatangan tamu secara langsung melalui dashboard monitoring yang mutakhir.',
        icon: ArrowPathIcon,
    },
    {
        title: 'Keamanan Data',
        desc: 'Enkripsi data tamu dan kontrol akses berlapis untuk menjamin privasi informasi organisasi.',
        icon: ShieldCheckIcon,
    },
    {
        title: 'Laporan Otomatis',
        desc: 'Hasilkan laporan kehadiran harian atau per acara dalam format profesional hanya dengan satu klik.',
        icon: DocumentChartBarIcon,
    },
];

const advantages = [
    {
        title: 'Tanpa Registrasi Publik',
        desc: 'Menghindari spam dan akses tidak sah. Akun hanya diberikan kepada personil yang berwenang.',
    },
    {
        title: 'Efisiensi Operasional',
        desc: 'Mengurangi waktu tunggu tamu di meja resepsionis hingga 80% dibandingkan buku tamu manual.',
    },
    {
        title: 'Infrastruktur Cloud',
        desc: 'Akses dari mana saja dan kapan saja melalui perangkat mobile maupun desktop tanpa instalasi.',
    },
];

const stats = [
    { value: '< 2 dtk', label: 'Kecepatan', sub: 'Rata-rata check-in' },
    { value: '100%', label: 'Akurasi', sub: 'Data terverifikasi' },
    { value: 'Cloud', label: 'Hosting', sub: 'Akses 24/7 aman' },
    { value: 'Digital', label: 'Ekosistem', sub: 'Tanpa limbah fisik' },
];

const socials = [
    { icon: UserGroupIcon, link: '#' },
    { icon: ShieldCheckIcon, link: '#' },
    { icon: QrCodeIcon, link: '#' },
    { icon: DocumentChartBarIcon, link: '#' },
];

const handleScroll = () => {
    isScrolled.value = window.scrollY > 40;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,800&display=swap');

html {
    scroll-behavior: smooth;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

@keyframes scan {
    0%, 100% { transform: translateY(0); opacity: 0; }
    5%, 95% { opacity: 1; }
    50% { transform: translateY(400px); }
}

@keyframes bounce-slow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.animate-bounce-slow {
    animation: bounce-slow 3s ease-in-out infinite;
}
</style>
