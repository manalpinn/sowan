<template>
  <div class="event-context-tabs">
    <Link 
      :href="route('events.show', eventId)" 
      class="event-context-tab"
      :class="{ 'active': current === 'overview' }"
    >
      Overview
    </Link>
    <Link 
      :href="route('guests.index', { event_id: eventId })" 
      class="event-context-tab"
      :class="{ 'active': current === 'guests' }"
    >
      Daftar Tamu
    </Link>
    <Link 
      :href="route('checkins.index', { event_id: eventId })" 
      class="event-context-tab"
      :class="{ 'active': current === 'checkins' }"
    >
      Log Kedatangan
    </Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  eventId: { type: [Number, String], required: true },
  current: { type: String, required: true }
});
</script>

<style scoped>
.event-context-tabs {
  display: flex;
  gap: 32px;
  border-bottom: 1px solid var(--border);
  margin-bottom: 24px;
  padding: 0 4px;
  overflow-x: auto;
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.event-context-tabs::-webkit-scrollbar {
  display: none;
}
.event-context-tab {
  padding: 12px 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-muted);
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
  text-decoration: none;
  white-space: nowrap;
}
.event-context-tab:hover {
  color: var(--text-primary);
}
.event-context-tab.active {
  color: var(--primary);
  border-bottom-color: var(--primary);
}
</style>
