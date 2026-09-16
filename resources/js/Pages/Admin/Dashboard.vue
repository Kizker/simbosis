<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BadgeStatus from '@/Components/BadgeStatus.vue';

const props = defineProps({
  byStatus: {
    type: Object,
    default: () => ({})
  },
  views7d: {
    type: Array,
    default: () => []
  },
  topCategories: {
    type: Array,
    default: () => []
  },
  topTags: {
    type: Array,
    default: () => []
  },
  topAuthors: {
    type: Array,
    default: () => []
  },
  latestArticles: {
    type: Array,
    default: () => []
  },
  publishedToday: {
    type: Number,
    default: 0
  },
  featuredCount: {
    type: Number,
    default: 0
  },
  breakingCount: {
    type: Number,
    default: 0
  }
});

// Calculations matching Blade logic
const statusOrder = {
  draft: 'Draft',
  submitted: 'Submitted',
  review: 'Review',
  revision: 'Revision',
  published: 'Published',
  archived: 'Archived',
};

const statusTone = {
  draft: 'bg-slate-500',
  submitted: 'bg-primary',
  review: 'bg-error',
  revision: 'bg-error',
  published: 'bg-tertiary-container',
  archived: 'bg-outline-variant',
};

const statusCounts = computed(() => {
  const counts = {};
  Object.keys(statusOrder).forEach(key => {
    counts[key] = Number(props.byStatus[key] || 0);
  });
  return counts;
});

const articleTotal = computed(() => {
  return Object.values(statusCounts.value).reduce((sum, val) => sum + val, 0);
});

const publishedTotal = computed(() => {
  return statusCounts.value.published || 0;
});

const queueTotal = computed(() => {
  return (statusCounts.value.submitted || 0) + 
         (statusCounts.value.review || 0) + 
         (statusCounts.value.revision || 0);
});

const viewsTotal = computed(() => {
  return props.views7d.reduce((sum, row) => sum + Number(row.total || 0), 0);
});

const viewsMax = computed(() => {
  const max = Math.max(...props.views7d.map(row => Number(row.total || 0)), 0);
  return max > 0 ? max : 1;
});

const statusMax = computed(() => {
  const max = Math.max(...Object.values(statusCounts.value), 0);
  return max > 0 ? max : 1;
});

const authorLead = computed(() => {
  return props.topAuthors[0] || null;
});

const topCategory = computed(() => {
  return props.topCategories[0] || null;
});

const publishRate = computed(() => {
  if (articleTotal.value === 0) return 0;
  return Math.round((publishedTotal.value / articleTotal.value) * 100);
});

// Date formatter
const formatDate = (dateStr) => {
  const date = new Date(dateStr);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  return `${day}/${month}`;
};
</script>

<template>
  <AdminLayout title="Dashboard">
    <!-- Header Section -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
      <div>
        <span class="text-xs font-semibold text-primary uppercase tracking-widest mb-2 block">Newsroom overview</span>
        <h1 class="text-3xl font-extrabold text-on-surface tracking-tight">Ringkasan redaksi hari ini</h1>
      </div>
      <div class="flex flex-wrap gap-3">
        <Link href="/harmony-access/articles" class="btn-simbiosis-neutral rounded-full px-6 py-2.5 text-sm font-semibold flex items-center gap-2">
          Kelola artikel
        </Link>
        <Link href="/harmony-access/articles/create" class="btn-simbiosis-create rounded-full px-6 py-2.5 text-sm font-semibold flex items-center gap-2 shadow-sm">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          Buat artikel
        </Link>
      </div>
    </header>

    <!-- KPI Grid -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Total Artikel -->
      <Link href="/harmony-access/articles" class="liquid-glass dark:liquid-glass-dark rounded-xl p-6 flex flex-col justify-between hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group relative overflow-hidden">
        <svg class="absolute -right-4 -bottom-4 w-24 h-24 text-primary/5 group-hover:text-primary/10 transition-colors duration-500 transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
        <div class="flex items-center gap-3 mb-2 relative z-10">
          <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          </div>
          <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Total Artikel</span>
        </div>
        <div class="flex items-baseline gap-2 mt-1 relative z-10">
          <span class="text-4xl font-black text-on-surface group-hover:text-primary transition-colors">{{ articleTotal.toLocaleString() }}</span>
          <span class="text-xs text-on-surface-variant font-medium">Seluruh status</span>
        </div>
      </Link>
      
      <!-- Published -->
      <Link href="/harmony-access/articles?status=published" class="liquid-glass dark:liquid-glass-dark rounded-xl p-6 flex flex-col justify-between hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group relative overflow-hidden">
        <svg class="absolute -right-4 -bottom-4 w-24 h-24 text-secondary/5 group-hover:text-secondary/10 transition-colors duration-500 transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <div class="flex items-center gap-3 mb-2 relative z-10">
          <div class="w-8 h-8 rounded-lg bg-secondary/10 flex items-center justify-center text-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Published</span>
        </div>
        <div class="flex items-baseline gap-2 mt-1 relative z-10">
          <span class="text-4xl font-black text-on-surface group-hover:text-secondary transition-colors">{{ publishedTotal.toLocaleString() }}</span>
          <span class="text-xs text-on-surface-variant font-medium">{{ publishedToday.toLocaleString() }} hari ini</span>
        </div>
      </Link>
      
      <!-- Dalam Review -->
      <Link href="/harmony-access/articles?status=review" class="liquid-glass dark:liquid-glass-dark rounded-xl p-6 flex flex-col justify-between hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group relative overflow-hidden">
        <svg class="absolute -right-4 -bottom-4 w-24 h-24 text-error/5 group-hover:text-error/10 transition-colors duration-500 transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="flex items-center gap-3 mb-2 relative z-10">
          <div class="w-8 h-8 rounded-lg bg-error/10 flex items-center justify-center text-error">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Dalam Review</span>
        </div>
        <div class="flex items-baseline gap-2 mt-1 relative z-10">
          <span class="text-4xl font-black text-on-surface group-hover:text-error transition-colors">{{ queueTotal.toLocaleString() }}</span>
          <span class="text-xs font-semibold text-error bg-error/10 px-2.5 py-0.5 rounded-full border border-error/20">Perlu aksi</span>
        </div>
      </Link>
      
      <!-- Total Views -->
      <div class="liquid-glass dark:liquid-glass-dark rounded-xl p-6 flex flex-col justify-between hover:-translate-y-1 hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
        <svg class="absolute -right-4 -bottom-4 w-24 h-24 text-tertiary-container/10 group-hover:text-tertiary-container/20 transition-colors duration-500 transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
        <div class="flex items-center gap-3 mb-2 relative z-10">
          <div class="w-8 h-8 rounded-lg bg-tertiary-container/20 flex items-center justify-center text-tertiary-container">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
          </div>
          <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Total Views (7H)</span>
        </div>
        <div class="flex items-baseline gap-2 mt-1 relative z-10">
          <span class="text-4xl font-black text-on-surface group-hover:text-tertiary-container transition-colors">{{ viewsTotal.toLocaleString() }}</span>
          <span class="text-xs text-on-surface-variant font-medium line-clamp-1 truncate" :title="`Top Author: ${authorLead?.name || 'Belum ada'}`">🏆 {{ authorLead?.name || '-' }}</span>
        </div>
      </div>
    </section>

    <!-- Main Dashboard Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column: Chart & Status & Rank -->
      <div class="lg:col-span-2 flex flex-col gap-6">
        
        <!-- Views Chart 7 Days -->
        <div class="liquid-glass dark:liquid-glass-dark rounded-xl p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-on-surface">Views 7 Hari</h3>
            <p class="text-xs text-on-surface-variant">Agregasi performa baca harian</p>
          </div>
          
          <div v-if="views7d.length > 0" class="flex items-end gap-2 md:gap-4 h-52 mt-4 pt-4 border-b border-outline-variant/30 px-2">
            <div v-for="row in views7d" :key="row.viewed_date" class="flex flex-col items-center flex-1 group h-full justify-end">
              <span class="text-[11px] font-black text-primary opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:-translate-y-1 transition-all duration-300 mb-1">{{ Number(row.total).toLocaleString() }}</span>
              <div class="w-full max-w-[40px] bg-surface-variant/20 rounded-t-xl relative flex justify-center h-4/5 items-end overflow-hidden">
                <div class="w-full bg-gradient-to-t from-primary/80 to-primary group-hover:from-primary group-hover:to-primary-container transition-all duration-500 rounded-t-xl shadow-[0_-2px_10px_rgba(0,97,148,0.2)]" :style="{ height: `${Math.max(5, Math.round((Number(row.total) / viewsMax) * 100))}%` }"></div>
              </div>
              <span class="text-[10px] text-on-surface-variant mt-3 font-semibold truncate w-full text-center group-hover:text-primary transition-colors">{{ formatDate(row.viewed_date) }}</span>
            </div>
          </div>
          <div v-else class="w-full h-52 flex items-center justify-center text-sm text-on-surface-variant border-2 border-dashed border-outline-variant/30 rounded-xl">
            Belum ada data views.
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Pipeline Status -->
          <div class="liquid-glass dark:liquid-glass-dark rounded-xl p-6">
            <div class="flex justify-between items-center mb-4 border-b border-outline-variant/20 pb-4">
              <h3 class="text-lg font-bold text-on-surface">Pipeline Artikel</h3>
              <Link href="/harmony-access/articles" class="text-xs font-bold text-primary hover:underline">Buka Semua</Link>
            </div>
            <div class="flex flex-col gap-3.5">
              <Link
                v-for="(label, key) in statusOrder"
                :key="key"
                :href="`/harmony-access/articles?status=${key}`"
                class="flex items-center gap-3 group"
              >
                <span class="w-20 text-xs font-bold text-on-surface-variant group-hover:text-primary transition-colors">{{ label }}</span>
                <div class="flex-1 bg-surface-variant/30 h-3 rounded-full overflow-hidden shadow-inner">
                  <div class="h-full rounded-full transition-all duration-700 ease-out group-hover:brightness-125 relative overflow-hidden" :class="statusTone[key]" :style="{ width: `${Math.max(2, Math.round((statusCounts[key] / statusMax) * 100))}%` }">
                    <div class="absolute inset-0 bg-white/20 w-full h-full transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                  </div>
                </div>
                <span class="w-8 text-right text-xs font-bold text-on-surface group-hover:text-primary transition-colors">{{ statusCounts[key].toLocaleString() }}</span>
              </Link>
            </div>
          </div>

          <!-- Top Category -->
          <div class="liquid-glass dark:liquid-glass-dark rounded-xl p-6">
            <div class="mb-4 border-b border-outline-variant/20 pb-4">
              <h3 class="text-lg font-bold text-on-surface">Top Kategori</h3>
              <p class="text-xs text-on-surface-variant mt-1">{{ topCategory ? `${topCategory.name} memimpin.` : 'Belum ada kategori' }}</p>
            </div>
            <div class="flex flex-col gap-2.5">
              <div v-for="(item, idx) in topCategories.slice(0, 5)" :key="item.id" class="flex items-center justify-between p-3 rounded-lg hover:bg-surface-variant/30 transition-colors border border-transparent hover:border-outline-variant/20 group">
                <div class="flex items-center gap-3">
                  <span class="w-6 h-6 rounded bg-primary/10 text-primary flex items-center justify-center text-xs font-black">{{ idx + 1 }}</span>
                  <span class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors">{{ item.name }}</span>
                </div>
                <span class="text-xs font-black bg-surface-variant/60 px-3 py-1 rounded-full text-on-surface shadow-sm">{{ Number(item.total).toLocaleString() }}</span>
              </div>
              <div v-if="topCategories.length === 0" class="text-xs text-on-surface-variant text-center py-4">Belum ada kategori berita.</div>
            </div>
          </div>
        </div>

      </div>
      
      <!-- Right Column: Progress, Activity, Spotlight -->
      <div class="flex flex-col gap-6">
        <!-- Publish Rate Target -->
        <div class="liquid-glass dark:liquid-glass-dark rounded-xl p-6 relative overflow-hidden group">
          <div class="absolute -right-6 -top-6 w-32 h-32 bg-primary/5 rounded-full group-hover:scale-150 transition-transform duration-700 ease-out pointer-events-none"></div>
          <h3 class="text-lg font-bold text-on-surface mb-5 relative z-10">Target Publikasi</h3>
          <div class="w-full bg-surface-variant/40 rounded-full h-4 mb-4 overflow-hidden border border-outline-variant/20 shadow-inner relative z-10">
            <div class="bg-gradient-to-r from-primary via-primary to-secondary-container h-full rounded-full transition-all duration-1000 ease-out relative overflow-hidden" :style="{ width: `${Math.min(100, publishRate)}%` }">
              <div class="absolute inset-0 bg-white/20 w-full h-full transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
            </div>
          </div>
          <div class="flex justify-between text-xs relative z-10">
            <span class="text-on-surface-variant font-semibold">{{ publishedTotal.toLocaleString() }} dari {{ articleTotal.toLocaleString() }} Artikel</span>
            <span class="font-black text-primary text-sm">{{ publishRate }}%</span>
          </div>
        </div>
        
        <!-- Activity Feed -->
        <div class="liquid-glass dark:liquid-glass-dark rounded-xl p-6 flex-1 flex flex-col">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-on-surface">Aktivitas Terbaru</h3>
            <Link href="/harmony-access/articles" class="text-xs font-bold text-primary hover:underline">Detail</Link>
          </div>
          
          <div v-if="latestArticles.length > 0" class="flex flex-col pt-1">
            <div v-for="(article, idx) in latestArticles" :key="article.id" class="flex gap-4 group items-stretch">
              <!-- Timeline Icon & Line Column -->
              <div class="flex flex-col items-center shrink-0 w-5">
                <div class="w-5 h-5 rounded-full bg-surface dark:bg-surface-slate-900 border-2 border-primary/50 group-hover:border-primary flex items-center justify-center shrink-0 shadow-sm transition-all duration-300 group-hover:scale-125 group-hover:shadow-[0_0_8px_rgba(0,97,148,0.4)] relative z-20">
                  <span class="w-1.5 h-1.5 rounded-full bg-primary/70 group-hover:bg-primary transition-colors duration-300"></span>
                </div>
                <div v-if="idx !== latestArticles.length - 1" class="w-[2px] flex-1 bg-gradient-to-b from-primary/40 to-outline-variant/20 group-hover:from-primary/60 transition-colors duration-300 -my-1 relative z-10"></div>
              </div>
              
              <!-- Content -->
              <div class="flex flex-col w-full min-w-0 bg-surface/40 dark:bg-slate-800/40 p-3 -mt-2 mb-5 rounded-xl border border-transparent group-hover:border-outline-variant/30 group-hover:bg-surface dark:group-hover:bg-slate-800 transition-all duration-300 shadow-sm group-hover:shadow-md">
                <Link :href="`/harmony-access/articles/${article.id}/edit`" class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors line-clamp-2 leading-snug">
                  {{ article.title }}
                </Link>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                  <span class="text-[9px] font-black text-primary uppercase tracking-widest bg-primary/10 px-2 py-0.5 rounded-full">{{ article.category?.name || 'Uncategorized' }}</span>
                  <span class="w-1 h-1 rounded-full bg-outline-variant"></span>
                  <span class="text-[11px] font-medium text-on-surface-variant truncate max-w-[120px]">{{ article.author?.name || 'No Author' }}</span>
                </div>
                <div class="mt-2.5">
                  <BadgeStatus :status="article.status" />
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-xs text-on-surface-variant text-center py-8 flex-1 flex items-center justify-center">
            Belum ada aktivitas artikel terbaru.
          </div>
        </div>
        
        <!-- Spotlight -->
        <div class="grid grid-cols-2 gap-4">
          <div class="liquid-glass dark:liquid-glass-dark rounded-xl p-5 text-center">
            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider block mb-1">Featured</span>
            <span class="text-3xl font-extrabold text-secondary">{{ featuredCount }}</span>
          </div>
          <div class="liquid-glass dark:liquid-glass-dark rounded-xl p-5 text-center">
            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider block mb-1">Breaking</span>
            <span class="text-3xl font-extrabold text-error">{{ breakingCount }}</span>
          </div>
        </div>

      </div>
    </div>
  </AdminLayout>
</template>
