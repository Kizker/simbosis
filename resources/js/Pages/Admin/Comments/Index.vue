<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
  comments: {
    type: Object,
    required: true
  }
});

const handleApprove = (id) => {
  router.post(route('admin.comments.approve', id));
};

const handleHide = (id) => {
  router.post(route('admin.comments.hide', id));
};

const handleDelete = (id) => {
  if (confirm('Yakin ingin menghapus komentar ini secara permanen?')) {
    router.delete(route('admin.comments.destroy', id));
  }
};
</script>

<template>
  <AdminLayout title="Moderasi Komentar">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Moderasi Komentar</h2>
        <p class="text-sm text-on-surface-variant mt-1">Kelola dan tinjau komentar dari pengunjung sebelum ditampilkan.</p>
      </div>
    </div>

    <!-- Content Area -->
    <div class="grid gap-4">
      <div v-for="c in comments.data" :key="c.id" class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm p-5 sm:p-6 transition-all hover:border-primary/50 group flex flex-col gap-4">
        
        <!-- Header: Info and Actions -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
          <!-- Left: Meta Info -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-3 mb-2">
              <span class="font-bold text-on-surface">{{ c.name }}</span>
              <span class="text-xs font-mono text-on-surface-variant bg-surface-variant/20 dark:bg-slate-800 px-2 py-0.5 rounded">{{ c.email }}</span>
              
              <span v-if="c.status === 'approved'" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-900 uppercase tracking-wider">Approved</span>
              <span v-else-if="c.status === 'hidden'" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800 dark:bg-slate-900 dark:text-slate-400 border border-slate-200 dark:border-slate-800 uppercase tracking-wider">Hidden</span>
              <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-850 dark:bg-blue-950/60 dark:text-blue-200 border border-blue-250 dark:border-blue-900 uppercase tracking-wider">{{ c.status }}</span>
            </div>
            
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-on-surface-variant font-semibold">
              <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5L18.5 7H20"></path></svg>
                <span>Artikel: <Link class="font-bold text-primary hover:underline" :href="`/harmony-access/articles/${c.article_id}/edit`">#{{ c.article_id }} ({{ c.article?.title }})</Link></span>
              </div>
              <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ new Date(c.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span>
              </div>
            </div>
          </div>
          
          <!-- Right: Actions -->
          <div class="flex flex-row gap-2 shrink-0 w-full sm:w-auto">
            <button
              v-if="c.status !== 'approved'"
              @click="handleApprove(c.id)"
              class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 text-xs font-bold rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-800 hover:bg-emerald-600 hover:text-white transition-all select-none"
            >
              Setujui
            </button>
            
            <button
              v-if="c.status !== 'hidden'"
              @click="handleHide(c.id)"
              class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 text-xs font-bold rounded-xl bg-surface-variant/30 text-on-surface border border-outline-variant/30 hover:bg-slate-600 hover:text-white transition-all select-none"
            >
              Sembunyikan
            </button>
            
            <button
              @click="handleDelete(c.id)"
              class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 text-xs font-bold rounded-xl bg-error/10 text-error border border-error/20 hover:bg-error hover:text-white transition-all select-none"
            >
              Hapus
            </button>
          </div>
        </div>

        <!-- Content Box -->
        <div class="text-sm text-on-surface leading-relaxed p-4 bg-surface/50 dark:bg-slate-900/50 rounded-xl border border-outline-variant/30 relative w-full">
          <svg class="w-6 h-6 text-outline-variant/20 absolute top-4 left-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
          <div class="pl-10 relative z-10">{{ c.content }}</div>
        </div>
      </div>

      <div v-if="comments.data.length === 0" class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl p-12 text-center">
        <p class="text-sm font-semibold text-on-surface-variant">Belum ada komentar untuk dimoderasi.</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="comments.links && comments.links.length > 3" class="mt-6">
      <Pagination :links="comments.links" />
    </div>
  </AdminLayout>
</template>
