<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: [String, Object],
    required: true
  }
});

const statusValue = computed(() => {
  return typeof props.status === 'object' && props.status !== null
    ? props.status.value
    : String(props.status);
});

const statusConfig = computed(() => {
  const map = {
    draft: { label: 'Draft', cls: 'bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-slate-100' },
    submitted: { label: 'Submitted', cls: 'bg-blue-200 text-blue-900 dark:bg-blue-950/70 dark:text-blue-100' },
    review: { label: 'Review', cls: 'bg-amber-200 text-amber-900 dark:bg-amber-950/70 dark:text-amber-100' },
    revision: { label: 'Revision', cls: 'bg-orange-200 text-orange-900 dark:bg-orange-950/70 dark:text-orange-100' },
    published: { label: 'Published', cls: 'bg-emerald-200 text-emerald-900 dark:bg-emerald-950/70 dark:text-emerald-100' },
    archived: { label: 'Archived', cls: 'bg-slate-300 text-slate-900 dark:bg-slate-700 dark:text-slate-100' }
  };
  return map[statusValue.value] || { label: 'Unknown', cls: 'bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-slate-100' };
});
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold transition-all duration-200 ease-in-out"
    :class="statusConfig.cls"
  >
    {{ statusConfig.label }}
  </span>
</template>
