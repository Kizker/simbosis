<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BadgeStatus from '@/Components/BadgeStatus.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  articles: {
    type: Object,
    required: true
  },
  status: String,
  search: String
});

const searchQuery = ref(props.search || '');
const currentStatus = ref(props.status || '');

const tabs = [
  { key: '', label: 'Semua' },
  { key: 'draft', label: 'Draft' },
  { key: 'submitted', label: 'Pending Review' },
  { key: 'review', label: 'Review' },
  { key: 'published', label: 'Dipublikasi' },
  { key: 'archived', label: 'Diarsipkan' },
];

const handleSearch = () => {
  const params = {};
  if (searchQuery.value.trim()) params.search = searchQuery.value.trim();
  if (currentStatus.value) params.status = currentStatus.value;
  
  router.get('/harmony-access/articles', params, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
    only: ['articles', 'search', 'status'],
    showProgress: false
  });
};

let searchTimeout;
watch(searchQuery, (newVal) => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    handleSearch();
  }, 150);
});

const handleTabClick = (key) => {
  currentStatus.value = key;
  handleSearch();
};

watch(() => props.search, (newVal) => {
  if (newVal !== searchQuery.value) {
    searchQuery.value = newVal || '';
  }
});
</script>

<template>
  <AdminLayout title="Manajemen Berita">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Manajemen Berita</h2>
        <p class="text-sm text-on-surface-variant mt-1">Kelola, edit, dan publikasikan artikel portal berita Anda.</p>
      </div>
      <Link
        href="/harmony-access/articles/create"
        class="btn-simbiosis-create rounded-full px-6 py-2.5 flex items-center justify-center gap-2 self-start md:self-auto shadow-sm"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        <span class="text-sm font-bold">Buat Artikel</span>
      </Link>
    </div>

    <!-- Content Area: Glassmorphism Container -->
    <div class="liquid-glass dark:liquid-glass-dark rounded-xl shadow-sm overflow-hidden">
      <!-- Toolbar & Tabs -->
      <div class="border-b border-outline-variant/30 px-6 pt-4 flex flex-col xl:flex-row xl:items-end justify-between gap-4">
        <!-- Tabs -->
        <div class="flex space-x-6 overflow-x-auto scrollbar-hide">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            type="button"
            @click="handleTabClick(tab.key)"
            class="pb-3 text-sm whitespace-nowrap px-1 transition-all border-b-2 font-semibold"
            :class="currentStatus === tab.key 
              ? 'border-primary text-primary font-bold' 
              : 'border-transparent text-on-surface-variant hover:text-primary'"
          >
            {{ tab.label }}
          </button>
        </div>

        <!-- Search/Filter Actions -->
        <div class="pb-3 flex items-center gap-3 w-full xl:w-auto">
          <div class="relative flex-1 xl:flex-none">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-outline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input
              v-model="searchQuery"
              class="pl-10 pr-4 py-1.5 rounded-full border border-outline-variant/50 bg-surface/50 dark:bg-slate-900 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary w-full xl:w-64 transition-all text-on-surface"
              placeholder="Cari judul..."
              type="text"
            />
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/30 bg-surface-variant/20 dark:bg-surface-variant-dark/20">
              <th class="p-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Judul & Kategori</th>
              <th class="p-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Status</th>
              <th class="p-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Penulis</th>
              <th class="p-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Tgl. Update</th>
              <th class="p-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/20 bg-surface/20 dark:bg-surface-slate-950/20">
            <tr v-for="a in articles.data" :key="a.id" class="hover:bg-primary/5 dark:hover:bg-primary/10 transition-colors group">
              <td class="p-4">
                <div class="text-sm font-semibold text-on-surface max-w-xs md:max-w-md truncate">
                  <Link class="hover:text-primary transition-colors" :href="`/harmony-access/articles/${a.id}/edit`">{{ a.title }}</Link>
                </div>
                <div class="text-xs text-primary mt-1 font-medium">{{ a.category?.name || 'Tanpa Kategori' }}</div>
              </td>
              <td class="p-4">
                <BadgeStatus :status="a.status" />
              </td>
              <td class="p-4">
                <div class="flex items-center gap-2">
                  <img v-if="a.author?.avatar" :src="a.author.avatar" class="w-6 h-6 rounded-full object-cover border border-outline-variant/30">
                  <div v-else class="w-6 h-6 rounded-full bg-surface-variant flex items-center justify-center text-[10px] font-bold text-on-surface-variant uppercase">
                    {{ (a.author?.name || 'U').substring(0, 2) }}
                  </div>
                  <span class="text-xs text-on-surface font-medium">{{ a.author?.name || 'Tanpa Penulis' }}</span>
                </div>
              </td>
              <td class="p-4 text-xs text-on-surface-variant font-medium">
                {{ new Date(a.updated_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
              </td>
              <td class="p-4 text-right">
                <div class="flex justify-end gap-2">
                  <Link :href="`/harmony-access/articles/${a.id}/edit`" class="p-1.5 text-on-surface-variant hover:text-primary rounded hover:bg-primary/10 transition-colors" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                  </Link>
                  <a v-if="a.status === 'published'" :href="`/artikel/${a.slug}`" target="_blank" class="p-1.5 text-on-surface-variant hover:text-secondary rounded hover:bg-secondary/10 transition-colors" title="Lihat Publikasi">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                  </a>
                </div>
              </td>
            </tr>
            <tr v-if="articles.data.length === 0">
              <td colspan="5" class="p-8 text-center text-on-surface-variant">
                Belum ada artikel yang ditemukan.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div class="border-t border-outline-variant/30 p-4 bg-surface-variant/10 dark:bg-surface-variant-dark/10">
        <Pagination :links="articles.links" />
      </div>
    </div>
  </AdminLayout>
</template>
