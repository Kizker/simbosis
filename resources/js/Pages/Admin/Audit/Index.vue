<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  logs: {
    type: Object,
    required: true
  },
  q: {
    type: String,
    default: ''
  }
});

const searchQuery = ref(props.q);

let searchTimeout;
watch(searchQuery, (value) => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get('/harmony-access/audit-logs', { q: value }, {
      preserveState: true,
      replace: true,
      preserveScroll: true,
      only: ['logs', 'q'],
      showProgress: false
    });
  }, 150);
});
</script>

<template>
  <AdminLayout title="Audit Logs">
    <!-- Page Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Audit Logs</h2>
        <p class="text-sm text-on-surface-variant mt-1">Pantau aktivitas dan riwayat perubahan dalam sistem.</p>
      </div>
      
      <div class="w-full md:w-auto">
        <div class="relative w-full sm:w-64">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-outline-variant">
            <svg class="h-4 w-4 text-on-surface-variant/70" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
            </svg>
          </span>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Cari action/entity..."
            class="w-full pl-10 pr-4 py-2.5 rounded-full border border-outline-variant/50 bg-surface/50 dark:bg-slate-900/50 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all text-on-surface"
          />
        </div>
      </div>
    </header>

    <!-- Content Area -->
    <div class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/30 bg-surface-variant/20 dark:bg-slate-900/30">
              <th class="p-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Waktu</th>
              <th class="p-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Aktor</th>
              <th class="p-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Aksi</th>
              <th class="p-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Entitas</th>
              <th class="p-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Meta</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/20 bg-surface/20 dark:bg-slate-900/20">
            <tr v-for="l in logs.data" :key="l.id" class="hover:bg-primary/5 dark:hover:bg-primary/10 transition-colors">
              <td class="p-4 whitespace-nowrap">
                <span class="text-xs font-medium text-on-surface-variant">{{ l.created_at }}</span>
              </td>
              <td class="p-4 whitespace-nowrap">
                <span class="text-sm font-semibold text-on-surface">{{ l.actor_name }}</span>
              </td>
              <td class="p-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold border border-primary-container/30 bg-primary-container/10 text-primary-container">
                  {{ l.action_label }}
                </span>
              </td>
              <td class="p-4 whitespace-nowrap">
                <span class="text-sm text-on-surface-variant">{{ l.entity_label }}</span>
              </td>
              <td class="p-4 text-xs text-on-surface-variant break-all max-w-xs font-mono">
                {{ l.meta_label }}
              </td>
            </tr>
            <tr v-if="!logs.data || logs.data.length === 0">
              <td colspan="5" class="p-8 text-center text-sm text-on-surface-variant">Belum ada riwayat aktivitas.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination area at bottom -->
      <div v-if="logs.links" class="border-t border-outline-variant/30 p-4 bg-surface-variant/10 dark:bg-slate-900/10">
        <Pagination :links="logs.links" />
      </div>
    </div>
  </AdminLayout>
</template>
