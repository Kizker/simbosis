<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

import { ref, watch } from 'vue';

const props = defineProps({
  tags: {
    type: Object,
    required: true
  },
  search: String
});

const searchQuery = ref(props.search || '');

const handleSearch = () => {
  const params = {};
  if (searchQuery.value.trim()) params.search = searchQuery.value.trim();
  
  router.get(route('admin.tags.index'), params, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    only: ['tags', 'search']
  });
};

let searchTimeout;
watch(searchQuery, (newVal) => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    handleSearch();
  }, 300);
});

watch(() => props.search, (newVal) => {
  if (newVal !== searchQuery.value) {
    searchQuery.value = newVal || '';
  }
});

const handleDelete = (id, name) => {
  if (confirm(`Yakin ingin menghapus tag "#${name}"?`)) {
    router.delete(route('admin.tags.destroy', id));
  }
};
</script>

<template>
  <AdminLayout title="Kelola Tag">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Kelola Tag</h2>
        <p class="text-sm text-on-surface-variant mt-1">Gunakan tag untuk topik atau label spesifik pada artikel.</p>
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
            placeholder="Cari tag..."
            class="w-full pl-10 pr-4 py-2.5 rounded-full border border-outline-variant/50 bg-surface/50 dark:bg-slate-900/50 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all text-on-surface"
          />
          <button v-if="searchQuery" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-on-surface-variant/60 hover:text-error transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>

        <Link
          href="/harmony-access/tags/create"
          class="w-full sm:w-auto btn-simbiosis-create text-white px-6 py-2.5 rounded-full flex items-center justify-center gap-2 shadow-sm transition-all duration-180 whitespace-nowrap font-bold text-sm"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          <span class="text-sm font-bold">Tambah Tag</span>
        </Link>
      </div>
    </div>

    <!-- Content Area -->
    <div class="liquid-glass dark:liquid-glass-dark rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/30 bg-surface-variant/20 dark:bg-surface-variant-dark/20">
              <th class="p-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Nama Tag</th>
              <th class="p-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Slug</th>
              <th class="p-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/20 bg-surface/20 dark:bg-surface-slate-950/20">
            <tr v-for="t in tags.data" :key="t.id" class="hover:bg-primary/5 dark:hover:bg-primary/10 transition-colors group">
              <td class="p-4">
                <span class="inline-block px-3 py-1 rounded-full border border-outline-variant/50 text-sm font-semibold text-on-surface-variant group-hover:bg-primary/20 group-hover:text-primary group-hover:border-primary/50 transition-all select-none">#{{ t.name }}</span>
              </td>
              <td class="p-4 text-sm text-on-surface-variant font-medium">{{ t.slug }}</td>
              <td class="p-4 text-right">
                <div class="flex justify-end gap-2">
                  <Link :href="`/harmony-access/tags/${t.id}/edit`" class="p-1.5 text-on-surface-variant hover:text-primary rounded hover:bg-primary/10 transition-colors" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                  </Link>
                  <button @click="handleDelete(t.id, t.name)" class="p-1.5 text-on-surface-variant hover:text-error rounded hover:bg-error/10 transition-colors" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="tags.data.length === 0">
              <td colspan="3" class="p-8 text-center text-sm text-on-surface-variant">Belum ada tag.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="border-t border-outline-variant/30 p-4 bg-surface-variant/10 dark:bg-surface-variant-dark/10">
        <Pagination :links="tags.links" />
      </div>
    </div>
  </AdminLayout>
</template>
