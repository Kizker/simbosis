<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: {
    type: Array,
    required: true
  }
});
</script>

<template>
  <div v-if="links && links.length > 3" class="flex flex-wrap justify-center gap-1.5 mt-8">
    <template v-for="(link, key) in links" :key="key">
      <div
        v-if="link.url === null"
        class="px-4 py-2.5 text-sm text-on-surface-variant/40 bg-surface-container-low/40 rounded-xl border border-outline-variant/10 cursor-not-allowed select-none"
        v-html="link.label"
      />
      <Link
        v-else
        :href="link.url"
        class="px-4 py-2.5 text-sm rounded-xl transition-all duration-200 ease-in-out border font-medium"
        :class="link.active 
          ? 'bg-primary text-white border-primary shadow-md shadow-primary/10' 
          : 'bg-surface-container-low hover:bg-surface-container hover:text-primary text-on-surface-variant border-outline-variant/20 dark:bg-surface-slate-900'"
        v-html="link.label"
      />
    </template>
  </div>
</template>
