<template>
  <div class="nav-item-wrapper" :class="{ 'collapsed': collapsed }">
    <Link
      :href="item.href"
      class="nav-item"
      :class="{ 'nav-item--active': isActive }"
      :title="collapsed ? item.name : ''"
    >
      <div class="nav-item__icon-box">
        <component :is="iconComponent" class="nav-item__icon" />
      </div>
      <span class="nav-item__label">{{ item.name }}</span>
    </Link>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
  Squares2X2Icon,
  UserGroupIcon,
  CalendarIcon,
  QueueListIcon,
  QrCodeIcon,
  UsersIcon,
  QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  item: { type: Object, required: true },
  collapsed: { type: Boolean, default: false },
});

const page = usePage();
const isActive = computed(() => {
  const currentPath = page.url.split('?')[0];
  const itemPath = new URL(props.item.href, window.location.origin).pathname;
  return currentPath === itemPath || (itemPath !== '/' && currentPath.startsWith(itemPath));
});

const iconComponent = computed(() => {
  const map = {
    'dashboard': Squares2X2Icon,
    'guests': UserGroupIcon,
    'events': CalendarIcon,
    'users': UsersIcon,
    'log': QueueListIcon,
    'scan': QrCodeIcon,
  };
  return map[props.item.icon] || QuestionMarkCircleIcon;
});
</script>

<style scoped>
.nav-item-wrapper {
  position: relative;
  padding: 0 10px;
}

.nav-item {
  display: flex;
  align-items: center;
  height: 44px;
  padding: 0 12px;
  border-radius: 12px;
  text-decoration: none;
  color: var(--text-secondary);
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  white-space: nowrap;
  position: relative;
  gap: 12px;
}

/* Icon Box: The center of gravity */
.nav-item__icon-box {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform 0.2s;
}

.nav-item__icon {
  width: 20px;
  height: 20px;
  stroke-width: 2;
}

.nav-item__label {
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 0.01em;
  opacity: 1;
  transition: opacity 0.2s, transform 0.2s;
}

/* --- States --- */
.nav-item:hover {
  background: var(--hover-bg);
  color: var(--text-primary);
}

.nav-item:hover .nav-item__icon-box {
  transform: scale(1.05);
}

.nav-item--active {
  background: var(--primary-soft);
  color: var(--primary);
}

.nav-item--active .nav-item__label {
  color: var(--primary);
  font-weight: 700;
}

/* Active Highlight: Precise & Compact */
.nav-item--active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 10px;
  bottom: 10px;
  width: 3px;
  background: var(--primary);
  border-radius: 0 4px 4px 0;
}

/* --- Collapsed State --- */
.collapsed {
  padding: 0;
  display: flex;
  justify-content: center;
}

.collapsed .nav-item {
  width: 44px;
  padding: 0;
  justify-content: center;
  gap: 0;
}

.collapsed .nav-item__label {
  display: none;
}

/* Force label visibility on mobile even if parent has .collapsed */
@media (max-width: 768px) {
  .nav-item-wrapper.collapsed {
    padding: 0 10px;
    display: block;
  }
  .nav-item-wrapper.collapsed .nav-item {
    width: 100%;
    padding: 0 12px;
    justify-content: flex-start;
    gap: 12px;
  }
  .nav-item-wrapper.collapsed .nav-item__label {
    display: block;
  }
  .nav-item-wrapper.collapsed .nav-item--active::before {
    left: 0;
  }
}

.collapsed .nav-item--active::before {
  left: 2px;
}

/* Tooltip */
.collapsed .nav-item:hover::after {
  content: attr(title);
  position: absolute;
  left: calc(100% + 10px);
  top: 50%;
  transform: translateY(-50%);
  background: #1e293b;
  color: #ffffff;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
  pointer-events: none;
  z-index: 1000;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  animation: tooltip-fade-in 0.15s ease-out;
}

/* Hide tooltip on mobile */
@media (max-width: 768px) {
  .collapsed .nav-item:hover::after {
    display: none;
  }
}

@keyframes tooltip-fade-in {
  from { opacity: 0; transform: translateY(-50%) translateX(-5px); }
  to { opacity: 1; transform: translateY(-50%) translateX(0); }
}
</style>
