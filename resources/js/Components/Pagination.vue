<template>
  <div v-if="links && links.length > 3" class="flex items-center justify-center sm:justify-end">
    <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
      <template v-for="(link, i) in links" :key="i">
        <!-- Ellipsis -->
        <span
          v-if="link.url === null && link.label === '...'"
          class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 bg-white"
        >
          ...
        </span>

        <!-- Clickable Links -->
        <Link
          v-else-if="link.url"
          :href="link.url"
          class="relative inline-flex items-center px-4 py-2 text-sm font-semibold transition-all duration-200 focus:z-20"
          :class="[
            link.active
              ? 'z-10 bg-purple-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600'
              : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 bg-white',
            i === 0 ? 'rounded-l-md' : '',
            i === links.length - 1 ? 'rounded-r-md' : '',
          ]"
        >
          <span v-html="formatLabel(link.label)"></span>
        </Link>

        <!-- Disabled Links -->
        <span
          v-else
          class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-400 ring-1 ring-inset ring-gray-300 bg-gray-50 cursor-not-allowed"
          :class="[
            i === 0 ? 'rounded-l-md' : '',
            i === links.length - 1 ? 'rounded-r-md' : '',
          ]"
        >
          <span v-html="formatLabel(link.label)"></span>
        </span>
      </template>
    </nav>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: Array,
});

function formatLabel(label) {
  const strLabel = String(label);
  if (strLabel.includes('Previous')) {
    return '<svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>';
  }
  if (strLabel.includes('Next')) {
    return '<svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>';
  }
  return strLabel;
}
</script>
