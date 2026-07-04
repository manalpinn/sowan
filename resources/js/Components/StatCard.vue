<template>
  <div class="stat-card">
    <div class="stat-card__icon" :style="{ background: color + '18' }">
      <component :is="iconComponent" :style="{ color }" class="h-6 w-6" stroke-width="2" />
    </div>
    <div class="stat-card__value" :style="{ color }">{{ formattedValue }}</div>
    <div class="stat-card__label">{{ label }}</div>
    <div class="stat-card__sub" v-if="sub">{{ sub }}</div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  CalendarIcon, 
  UserGroupIcon, 
  CheckBadgeIcon, 
  ClockIcon, 
  ArrowRightOnRectangleIcon, 
  ArrowLeftOnRectangleIcon,
  ShieldCheckIcon,
  XMarkIcon,
  QuestionMarkCircleIcon,
  Square3Stack3DIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  label: String,
  value: { type: [Number, String], default: 0 },
  color: { type: String, default: '#7C3AED' },
  icon: { type: String, default: 'default' },
  sub: { type: String, default: '' },
});

const iconComponent = computed(() => {
  const map = {
    'events': CalendarIcon,
    'guests': UserGroupIcon,
    'checkin': ArrowRightOnRectangleIcon,
    'pending': ClockIcon,
    'checkout': ArrowLeftOnRectangleIcon,
    'rsvp': ShieldCheckIcon,
    'pax': Square3Stack3DIcon,
    'close': XMarkIcon,
    'users': UserGroupIcon,
  };
  return map[props.icon] || QuestionMarkCircleIcon;
});

const formattedValue = computed(() =>
  typeof props.value === 'number'
    ? props.value.toLocaleString('id-ID')
    : props.value
);
</script>


