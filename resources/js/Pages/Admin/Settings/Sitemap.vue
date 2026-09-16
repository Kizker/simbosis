<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  sitemap_changefreq_default: {
    type: String,
    default: 'daily'
  },
  sitemap_priority_default: {
    type: [String, Number],
    default: '0.5'
  }
});

const form = useForm({
  sitemap_changefreq_default: props.sitemap_changefreq_default,
  sitemap_priority_default: props.sitemap_priority_default
});

const submit = () => {
  form.post('/harmony-access/sitemap');
};
</script>

<template>
  <AdminLayout title="Pengaturan Sitemap">
    <!-- Page Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Pengaturan Sitemap</h2>
        <p class="text-sm text-on-surface-variant mt-1">Atur frekuensi dan prioritas perayapan default untuk search engine.</p>
      </div>
      <Link href="/harmony-access/settings" class="btn-simbiosis-neutral rounded-full px-6 py-2.5 text-sm font-semibold flex items-center gap-2 shadow-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Kembali
      </Link>
    </header>

    <div class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm p-6 lg:p-8">
      <form @submit.prevent="submit" class="grid gap-6">
        <div>
          <label class="block text-sm font-bold text-on-surface mb-2">Changefreq Default <span class="text-error">*</span></label>
          <div class="relative">
            <select v-model="form.sitemap_changefreq_default" required class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all appearance-none cursor-pointer">
              <option value="hourly">Hourly</option>
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-on-surface-variant">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </div>
          <div v-if="form.errors.sitemap_changefreq_default" class="text-xs text-error mt-1">{{ form.errors.sitemap_changefreq_default }}</div>
        </div>
        
        <div>
          <label class="block text-sm font-bold text-on-surface mb-2">Priority Default <span class="text-error">*</span></label>
          <input type="number" step="0.1" min="0" max="1" v-model="form.sitemap_priority_default" required class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
          <p class="text-[10px] text-on-surface-variant mt-1.5">Format: 0.0 – 1.0 (1 angka desimal). Direkomendasikan: 0.8 untuk artikel.</p>
          <div v-if="form.errors.sitemap_priority_default" class="text-xs text-error mt-1">{{ form.errors.sitemap_priority_default }}</div>
        </div>

        <div class="pt-6 border-t border-outline-variant/20 flex gap-3 justify-end">
          <button type="submit" :disabled="form.processing" class="btn-simbiosis-save rounded-full px-8 py-2.5 text-sm font-bold shadow-sm disabled:opacity-50">
            Simpan Sitemap
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
