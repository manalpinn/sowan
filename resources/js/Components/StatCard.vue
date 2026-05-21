<template>
  <div class="stat-card">
    <div class="stat-card__icon" :style="{ background: color + '18' }">
      <component :is="iconComponent" :style="{ color }" class="h-5 w-5" stroke-width="2.5" />
    </div>
    <div class="stat-card__label">{{ label }}</div>
    <div class="stat-card__value" :style="{ color }">{{ formattedValue }}</div>
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

<style scoped>
.stat-card {
  padding: 1.5rem;
  background: white;
  border-radius: 1.5rem;
  border: 1px solid rgba(226, 232, 240, 0.8);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  height: 100%;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  border-color: rgba(99, 102, 241, 0.2);
}

.stat-card__icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
}

.stat-card__label {
  font-size: 0.65rem;
  font-weight: 800;
  color: #94A3B8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.25rem;
}

.stat-card__value {
  font-size: 1.75rem;
  font-weight: 900;
  line-height: 1;
  letter-spacing: -0.02em;
}

.stat-card__sub {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748B;
  margin-top: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}
</style>
