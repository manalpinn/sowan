<template>
  <Head :title="pageTitle" />
  <div class="admin-layout" :class="{ dark: isDark }">

    <!-- Mobile Overlay -->
    <transition name="overlay">
      <div v-if="isMobileOpen" class="mobile-overlay" @click="isMobileOpen = false"></div>
    </transition>

    <!-- ========================
         SIDEBAR
    ========================= -->
    <aside class="sidebar" :class="{ 'sidebar--collapsed': isActualCollapsed, 'sidebar--open': isMobileOpen }">

      <!-- Logo + Close (mobile) -->
      <div class="sidebar__logo">
        <Link href="/" class="flex items-center gap-3 group">
          <div class="sidebar__logo-box flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 text-white transition-all duration-300 group-hover:rotate-6 group-hover:scale-110">
            <UsersIcon class="h-5 w-5" stroke-width="2.5" />
          </div>
          <span class="text-xl font-black tracking-tighter text-slate-900 uppercase sidebar__logo-text">SOWAN<span class="text-violet-600">.</span></span>
        </Link>
        <button class="sidebar__close-btn ml-auto" @click="isMobileOpen = false" aria-label="Close menu">
          <XMarkIcon class="h-5 w-5" stroke-width="2.5" />
        </button>
      </div>

      <!-- Navigation -->
      <nav class="sidebar__nav">
        <SidebarItem
          v-for="item in navItems"
          :key="item.name"
          :item="item"
          :collapsed="isActualCollapsed"
          @click="isMobileOpen = false"
        />
      </nav>

      <!-- Dark Mode Toggle (inside sidebar, always) -->
      <div
        v-if="!isActualCollapsed"
        class="sidebar__darkmode"
      >
        <div class="sidebar__darkmode-icon">
          <SunIcon v-if="isDark" class="h-4 w-4" />
          <MoonIcon v-else class="h-4 w-4" />
        </div>
        <span class="sidebar__darkmode-label">{{ isDark ? 'Dark Mode' : 'Light Mode' }}</span>
        <button
          class="sidebar__darkmode-switch"
          :class="{ 'is-on': isDark }"
          @click="toggleDark"
          :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        >
          <span class="sidebar__darkmode-thumb"></span>
        </button>
      </div>

      <!-- COLLAPSED: icon button only -->
      <div v-else class="sidebar__darkmode-collapsed">
        <button
          @click="toggleDark"
          class="sidebar__darkmode-icon-btn"
          :class="{ 'is-on': isDark }"
          :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
        >
          <SunIcon v-if="isDark" class="h-5 w-5" />
          <MoonIcon v-else class="h-5 w-5" />
          <span class="sidebar__darkmode-dot" :class="{ 'dot-on': isDark }"></span>
        </button>
      </div>

      <!-- User info + Logout (mobile footer) -->
      <div class="sidebar__user-footer">
        <div class="sidebar__user-info">
          <div class="sidebar__user-avatar">{{ userInitials }}</div>
          <div class="sidebar__user-text">
            <p class="sidebar__user-name">{{ $page.props.auth.user.name }}</p>
            <p class="sidebar__user-role">{{ userRole }}</p>
          </div>
        </div>
        <Link :href="route('logout')" method="post" as="button" class="sidebar__logout-btn" title="Logout">
          <ArrowRightOnRectangleIcon class="h-5 w-5" />
        </Link>
      </div>

      <!-- Collapse toggle (desktop footer) -->
      <div class="sidebar__footer">
        <button @click="toggleSidebar" class="sidebar__collapse-btn"
          :title="isActualCollapsed ? 'Expand' : 'Collapse'">
          <ChevronRightIcon v-if="isActualCollapsed" class="h-4 w-4" stroke-width="3" />
          <ChevronLeftIcon v-else class="h-4 w-4" stroke-width="3" />
          <span class="sidebar__collapse-label font-black uppercase tracking-widest text-[10px]">Tutup Menu</span>
        </button>
      </div>

    </aside>

    <!-- ========================
         MAIN AREA
    ========================= -->
    <div class="main-area">

      <!-- Topbar -->
      <header class="topbar">
        <!-- LEFT: Hamburger (mobile) + Title -->
        <div class="topbar__left">
          <button @click="isMobileOpen = true" class="topbar__hamburger" aria-label="Open menu">
            <Bars3Icon class="h-5 w-5" />
          </button>
          <div class="topbar__title-group">
            <h1 class="topbar__page-title">{{ pageTitle }}</h1>
            <nav class="breadcrumb" v-if="breadcrumbs.length">
              <template v-for="(crumb, i) in breadcrumbs" :key="i">
                <Link v-if="crumb.href" :href="crumb.href" class="breadcrumb__link">{{ crumb.label }}</Link>
                <span v-else class="breadcrumb__current">{{ crumb.label }}</span>
                <span v-if="i < breadcrumbs.length - 1" class="breadcrumb__sep">/</span>
              </template>
            </nav>
          </div>
        </div>

        <!-- RIGHT: User + Logout -->
        <div class="topbar__right">
          <!-- User group -->
          <div class="topbar__user-group">
            <div class="topbar__user">
              <div class="topbar__avatar">{{ userInitials }}</div>
              <div class="topbar__user-info">
                <span class="topbar__user-name">{{ $page.props.auth.user.name }}</span>
                <span class="topbar__user-role">{{ userRole }}</span>
              </div>
            </div>
            <Link :href="route('logout')" method="post" as="button" class="topbar__btn topbar__logout-btn" title="Logout">
              <ArrowRightOnRectangleIcon class="h-5 w-5" />
            </Link>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="main-content">
        <slot />
      </main>



    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, Head, usePage } from '@inertiajs/vue3';
import { notify } from '@/Utils/SweetAlert';
import SidebarItem from '@/Components/SidebarItem.vue';
import { 
  UsersIcon,
  UserGroupIcon, 
  XMarkIcon, 
  Bars3Icon, 
  SunIcon, 
  MoonIcon,
  ArrowRightOnRectangleIcon,
  ChevronRightIcon,
  ChevronLeftIcon,
  CheckIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  pageTitle: { type: String, default: 'Dashboard' },
  breadcrumbs: { type: Array, default: () => [] },
});

const page = usePage();
const isSidebarCollapsed = ref(false);
const isMobile = ref(false);

const isActualCollapsed = computed(() => {
  if (isMobile.value) return false;
  return isSidebarCollapsed.value;
});

const isDark = ref(false);
const isMobileOpen = ref(false);

const userInitials = computed(() => {
  const name = page.props.auth?.user?.name || 'User';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const userRole = computed(() => {
  const roles = page.props.auth?.user?.roles ?? [];
  if (roles.includes('superadmin')) return 'Super Admin';
  if (roles.includes('admin_event')) return 'Admin Event';
  return 'User';
});

const isSuperAdmin = computed(() => page.props.auth.user.roles?.includes('superadmin') ?? false);

const navItems = computed(() => {
  const items = [
    { name: 'Dashboard', href: route('dashboard'), icon: 'dashboard' },
    { name: 'Tamu', href: route('guests.index'), icon: 'guests' },
    { name: 'Log Kedatangan', href: route('checkins.index'), icon: 'log' },
    { name: 'Scan QR', href: route('scanner.index'), icon: 'scan' },
  ];
  if (isSuperAdmin.value) {
    items.splice(1, 0, { name: 'Event', href: route('events.index'), icon: 'events' });
    items.push({ name: 'User', href: route('users.index'), icon: 'users' });
  }
  return items;
});

function toggleSidebar() {
  isSidebarCollapsed.value = !isSidebarCollapsed.value;
  localStorage.setItem('sidebar_collapsed', isSidebarCollapsed.value);
}

function toggleDark() {
  isDark.value = !isDark.value;
  localStorage.setItem('dark_mode', isDark.value);
  if (isDark.value) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
}

function checkMobile() {
  isMobile.value = window.innerWidth <= 768;
}

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
  isDark.value = localStorage.getItem('dark_mode') === 'true';
  isSidebarCollapsed.value = localStorage.getItem('sidebar_collapsed') === 'true';

  if (isDark.value) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }

  // Check for initial flash messages
  if (page.props.flash.success) {
    notify.success(page.props.flash.success);
  }
  if (page.props.flash.error) {
    notify.error(page.props.flash.error);
  }
});

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
  if (flash.success) {
    notify.success(flash.success);
  }
  if (flash.error) {
    notify.error(flash.error);
  }
  if (flash.import_errors && flash.import_errors.length > 0) {
    let errorHtml = '<ul style="text-align: left; font-size: 13px; max-height: 200px; overflow-y: auto;">';
    flash.import_errors.forEach(err => {
      errorHtml += `<li style="margin-bottom: 5px;">• ${err}</li>`;
    });
    errorHtml += '</ul>';
    
    notify.alert({
      title: 'Beberapa baris dilewati',
      html: errorHtml,
      icon: 'warning',
      confirmButtonText: 'Tutup'
    });
  }
}, { deep: true });
</script>

<style scoped>
/* ==========================================
   LAYOUT ROOT
========================================== */
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: var(--bg-base);
  color: var(--text-primary);
  transition: background 0.3s, color 0.3s;
}

/* ==========================================
   OVERLAY (mobile)
========================================== */
.mobile-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(3px);
  -webkit-backdrop-filter: blur(3px);
  z-index: 99;
}
.overlay-enter-active, .overlay-leave-active { transition: opacity 0.25s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }

/* ==========================================
   SIDEBAR
========================================== */
.sidebar {
  width: 240px;
  min-height: 100vh;
  background: var(--sidebar-bg);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0; left: 0; bottom: 0;
  z-index: 100;
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}
.sidebar--collapsed { width: 68px; }

/* Mobile: hide sidebar by default */
@media (max-width: 768px) {
  .sidebar {
    width: 280px !important;
    transform: translateX(-110%);
    box-shadow: none;
  }
  .sidebar--open {
    transform: translateX(0);
    box-shadow: 6px 0 32px rgba(0,0,0,0.18);
  }
}

/* --- Logo row --- */
.sidebar__logo {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 16px;
  height: 64px;
  flex-shrink: 0;
  overflow: hidden;
}

.sidebar__logo-text {
  font-size: 18px;
  font-weight: 900;
  letter-spacing: -0.02em;
  transition: opacity 0.2s;
}

.dark .sidebar__logo-text {
  color: #f8fafc;
}

.sidebar--collapsed .sidebar__logo-text { opacity: 0; pointer-events: none; }

@media (max-width: 768px) {
  .sidebar--collapsed .sidebar__logo-text { opacity: 1; pointer-events: auto; }
}

/* Branding box */
.sidebar__logo-box {
  flex-shrink: 0;
  box-shadow: 0 8px 20px -4px rgba(124, 58, 237, 0.2);
}
.dark .sidebar__logo-box {
  box-shadow: none; /* Hilangkan shadow putih/terang di dark mode */
}

/* Close button — mobile only */
.sidebar__close-btn {
  display: none;
  width: 32px; height: 32px;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: var(--hover-bg);
  color: var(--text-muted);
  cursor: pointer;
  align-items: center; justify-content: center;
  flex-shrink: 0;
  transition: all 0.2s;
}
.sidebar__close-btn:hover { background: var(--danger-soft); color: var(--danger); }
@media (max-width: 768px) {
  .sidebar__close-btn { display: flex; }
}

/* --- Nav --- */
.sidebar__nav {
  flex: 1;
  padding: 16px 0;
  display: flex;
  flex-direction: column;
  gap: 4px;
  overflow-y: auto;
}

/* --- Dark mode row --- */
.sidebar__darkmode {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  margin: 8px 12px;
  border-radius: 12px;
  background: var(--hover-bg);
  border: 1px solid var(--border);
  flex-shrink: 0;
  overflow: hidden;
  transition: all 0.2s;
}
.sidebar__darkmode-icon {
  width: 20px; height: 20px;
  display: flex; align-items: center; justify-content: center;
  color: var(--text-muted);
  flex-shrink: 0;
}

.sidebar__darkmode-label {
  font-size: 11px; font-weight: 700;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  flex: 1;
  white-space: nowrap;
  opacity: 1;
  transition: opacity 0.2s;
}

/* Switch pill */
.sidebar__darkmode-switch {
  position: relative;
  width: 36px; height: 20px;
  border-radius: 999px;
  border: none;
  background: var(--border);
  cursor: pointer;
  flex-shrink: 0;
  transition: background 0.25s;
  padding: 0;
}
.sidebar__darkmode-switch.is-on { background: var(--primary); }
.sidebar__darkmode-thumb {
  position: absolute;
  top: 2px; left: 2px;
  width: 16px; height: 16px;
  border-radius: 50%;
  background: white;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
  transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
  display: block;
}
.sidebar__darkmode-switch.is-on .sidebar__darkmode-thumb { transform: translateX(16px); }

/* === COLLAPSED STATE (desktop) === */
.sidebar__darkmode-collapsed {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px 0;
  margin: 4px 0;
  flex-shrink: 0;
}
.sidebar__darkmode-icon-btn {
  position: relative;
  width: 44px; height: 44px;
  border-radius: 12px;
  border: 1px solid var(--border);
  background: var(--hover-bg);
  color: var(--text-muted);
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.sidebar__darkmode-icon-btn:hover,
.sidebar__darkmode-icon-btn.is-on {
  background: var(--primary-soft);
  color: var(--primary);
  border-color: var(--primary-soft);
}
/* Status dot indicator */
.sidebar__darkmode-dot {
  position: absolute;
  top: 8px; right: 8px;
  width: 6px; height: 6px;
  border-radius: 50%;
  background: var(--text-muted);
  border: 1.5px solid var(--sidebar-bg);
  transition: background 0.2s;
}
.sidebar__darkmode-dot.dot-on { background: var(--primary); }

/* --- User footer (mobile only) --- */
.sidebar__user-footer {
  display: none;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 14px 14px;
  border-top: 1px solid var(--border);
  flex-shrink: 0;
}
@media (max-width: 768px) {
  .sidebar__user-footer { display: flex; }
}
.sidebar__user-info { display: flex; align-items: center; gap: 10px; min-width: 0; }
.sidebar__user-avatar {
  width: 36px; height: 36px;
  border-radius: 10px;
  background: var(--primary);
  color: white;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 700;
  flex-shrink: 0;
}
.sidebar__user-text { min-width: 0; }
.sidebar__user-name {
  font-size: 13px; font-weight: 700;
  color: var(--text-primary);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sidebar__user-role { font-size: 11px; color: var(--text-muted); }
.sidebar__logout-btn {
  width: 36px; height: 36px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 10px;
  border: 1px solid var(--danger-soft);
  background: transparent;
  color: var(--danger);
  cursor: pointer;
  flex-shrink: 0;
  transition: all 0.2s;
}
.sidebar__logout-btn:hover { background: var(--danger-soft); }

/* --- Collapse btn (desktop footer) --- */
.sidebar__footer {
  padding: 10px;
  border-top: 1px solid var(--border);
  flex-shrink: 0;
}
@media (max-width: 768px) {
  .sidebar__footer { display: none; }
}
.sidebar__collapse-btn {
  display: flex; align-items: center; gap: 10px;
  width: 100%; padding: 12px;
  border-radius: 12px; border: none;
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
}
.sidebar__collapse-btn:hover { background: var(--hover-bg); color: var(--text-primary); }

.sidebar__collapse-label {
  opacity: 1; transition: opacity 0.2s;
  font-size: 10px; font-weight: 900;
  text-transform: uppercase; letter-spacing: 0.1em;
}

.sidebar--collapsed .sidebar__collapse-btn {
  justify-content: center;
  padding: 12px 0;
  gap: 0;
}

.sidebar--collapsed .sidebar__collapse-label {
  display: none;
}

/* ==========================================
   MAIN AREA
========================================== */
.main-area {
  flex: 1;
  margin-left: 240px;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  min-width: 0;
}
.admin-layout:has(.sidebar--collapsed) .main-area { margin-left: 68px; }
@media (max-width: 768px) {
  .main-area { margin-left: 0 !important; }
}

/* ==========================================
   TOPBAR
========================================== */
.topbar {
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  background: var(--card-bg);
  border-bottom: 1px solid var(--border);
  position: sticky;
  top: 0;
  z-index: 50;
  gap: 10px;
}

.topbar__left {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
  min-width: 0;
}
.topbar__title-group { min-width: 0; }
.topbar__page-title {
  font-size: 16px; font-weight: 800;
  color: var(--text-primary);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  text-transform: uppercase;
  letter-spacing: -0.01em;
}

/* Hamburger — mobile only */
.topbar__hamburger {
  display: none;
  width: 40px; height: 40px;
  align-items: center; justify-content: center;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  flex-shrink: 0;
  transition: all 0.2s;
}
.topbar__hamburger:hover { background: var(--hover-bg); color: var(--primary); }
@media (max-width: 768px) {
  .topbar__hamburger { display: flex; }
}

/* RIGHT side */
.topbar__right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

/* Generic icon button */
.topbar__btn {
  width: 38px; height: 38px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
  text-decoration: none;
}
.topbar__btn:hover { background: var(--hover-bg); color: var(--text-primary); }


/* Logout button */
.topbar__logout-btn { color: var(--danger); border-color: var(--danger-soft); }
.topbar__logout-btn:hover { background: var(--danger-soft); color: var(--danger); }

/* User group */
.topbar__user-group {
  display: flex; align-items: center; gap: 8px;
  padding-left: 12px;
  border-left: 1px solid var(--border);
}
.topbar__user { display: flex; align-items: center; gap: 8px; }
.topbar__avatar {
  width: 36px; height: 36px;
  border-radius: 10px;
  background: var(--primary);
  color: white;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 700;
  flex-shrink: 0;
}
.topbar__user-info {
  display: none;
  flex-direction: column;
}
@media (min-width: 640px) {
  .topbar__user-info { display: flex; }
}
.topbar__user-name { font-size: 13px; font-weight: 700; color: var(--text-primary); }
.topbar__user-role { font-size: 11px; color: var(--text-muted); font-weight: 500; }

/* Breadcrumb */
.breadcrumb { display: flex; align-items: center; gap: 4px; }
.breadcrumb__link { font-size: 11px; color: var(--primary); text-decoration: none; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
.breadcrumb__link:hover { text-decoration: underline; }
.breadcrumb__current { font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
.breadcrumb__sep { font-size: 11px; color: var(--text-muted); }

/* ==========================================
   MAIN CONTENT
========================================== */
.main-content { flex: 1; padding: 16px; background: var(--bg-base); }
@media (min-width: 640px)  { .main-content { padding: 20px; } }
@media (min-width: 1024px) { .main-content { padding: 28px; } }


</style>
