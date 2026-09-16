<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  ads: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

const searchQuery = ref(props.filters?.q || '');
let searchTimeout;

watch(searchQuery, (value) => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get('/harmony-access/ads', { q: value }, {
      preserveState: true,
      replace: true,
      preserveScroll: true,
      only: ['ads', 'filters'],
      showProgress: false
    });
  }, 150);
});

const handleDelete = (id, title) => {
  if (confirm(`Yakin ingin menghapus iklan "${title}"?`)) {
    router.delete(route('admin.ads.destroy', id));
  }
};
</script>

<template>
  <AdminLayout title="Iklan Banner">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Iklan Banner</h2>
        <p class="text-sm text-on-surface-variant mt-1">Kelola slot iklan dan promosi di berbagai area website.</p>
      </div>
      
      <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
        <!-- Search Bar -->
        <div class="relative w-full sm:w-64">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-outline-variant">
            <svg class="h-4 w-4 text-on-surface-variant/70" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
            </svg>
          </span>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Cari iklan..."
            class="w-full pl-10 pr-4 py-2.5 rounded-full border border-outline-variant/50 bg-surface/50 dark:bg-slate-900/50 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all text-on-surface"
          />
        </div>

        <Link
          href="/harmony-access/ads/create"
          class="btn-simbiosis-create rounded-full px-6 py-2.5 flex items-center justify-center gap-2 w-full sm:w-auto shadow-sm"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          <span class="text-sm font-bold">Tambah Iklan</span>
        </Link>
      </div>
    </div>

    <!-- Content Area -->
    <div class="grid gap-4">
      <div v-for="a in ads.data" :key="a.id" class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all hover:border-primary/50 group">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-3 mb-2">
            <h3 class="text-lg font-bold text-on-surface truncate">{{ a.title }}</h3>
            <span v-if="a.is_active" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-900">Aktif</span>
            <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 dark:bg-slate-900 dark:text-slate-400 border border-slate-200 dark:border-slate-800">Nonaktif</span>
          </div>
          <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-on-surface-variant font-medium">
            <div class="flex items-center gap-1.5">
              <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
              <span>Slot:</span> <span class="capitalize text-on-surface font-bold">{{ a.slot }}</span>
            </div>
            <div class="flex items-center gap-1.5">
              <svg class="w-4 h-4 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
              <span>{{ a.starts_at ? new Date(a.starts_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : 'Tanpa batas' }} &rarr; {{ a.ends_at ? new Date(a.ends_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : 'Tanpa batas' }}</span>
            </div>
          </div>
        </div>
        <div class="flex gap-2 w-full sm:w-auto shrink-0 justify-end mt-4 sm:mt-0">
          <Link :href="`/harmony-access/ads/${a.id}/edit`" class="btn-simbiosis-edit rounded-full inline-flex items-center justify-center px-4 py-2 text-xs font-bold shadow-sm">
            Edit Iklan
          </Link>
          <button @click="handleDelete(a.id, a.title)" class="btn-simbiosis-delete rounded-full p-2 shrink-0 inline-flex items-center justify-center w-8 h-8 transition-colors" title="Hapus">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
          </button>
        </div>
      </div>
      
      <div v-if="ads.data.length === 0" class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl p-12 text-center">
        <p class="text-sm font-semibold text-on-surface-variant">Belum ada iklan banner.</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="ads.links && ads.links.length > 3" class="mt-6">
      <Pagination :links="ads.links" />
    </div>
  </AdminLayout>
</template>
