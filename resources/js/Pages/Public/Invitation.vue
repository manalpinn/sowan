<template>
  <Head :title="'Undangan – ' + guest.name" />
  
  <component 
    :is="currentTemplateComponent" 
    :guest="guest" 
    :event="event" 
  />
</template>

<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';

import FormalTemplate from './Templates/FormalTemplate.vue';
import WeddingTemplate from './Templates/WeddingTemplate.vue';
import CorporateTemplate from './Templates/CorporateTemplate.vue';

const props = defineProps({
  guest: Object,
  event: Object,
});

const currentTemplateComponent = computed(() => {
  switch (props.event.invitation_template) {
    case 'wedding':
      return WeddingTemplate;
    case 'corporate':
      return CorporateTemplate;
    case 'formal':
    default:
      return FormalTemplate;
  }
});
</script>
