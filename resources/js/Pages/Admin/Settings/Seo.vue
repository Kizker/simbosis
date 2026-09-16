<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  site_name: String,
  default_meta_title: String,
  default_meta_desc: String,
  default_og_image_path: String,
  google_analytics_id: String
});

const form = useForm({
  site_name: props.site_name || '',
  default_meta_title: props.default_meta_title || '',
  default_meta_desc: props.default_meta_desc || '',
  default_og_image_path: props.default_og_image_path || '',
  google_analytics_id: props.google_analytics_id || ''
});

import { ref, onUnmounted } from 'vue';

const ogPreview = ref(null);

const handleOgImageChange = (e) => {
  const file = e.target.files[0];
  form.default_og_image_path = file;
  if (file) {
    if (ogPreview.value) URL.revokeObjectURL(ogPreview.value);
    ogPreview.value = URL.createObjectURL(file);
  } else {
    ogPreview.value = null;
  }
};

onUnmounted(() => {
  if (ogPreview.value) {
    URL.revokeObjectURL(ogPreview.value);
  }
});

const submit = () => {
  form.post('/harmony-access/seo');
};
</script>

<template>
  <AdminLayout title="SEO Global">
    <!-- Page Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">SEO Global</h2>
        <p class="text-sm text-on-surface-variant mt-1">Konfigurasi pengaturan SEO bawaan untuk seluruh halaman situs.</p>
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
          <label class="block text-sm font-bold text-on-surface mb-2">
            Nama Situs (Site Name) <span class="text-error">*</span>
          </label>
          <input type="text" v-model="form.site_name" required class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
          <div v-if="form.errors.site_name" class="text-xs text-error mt-1">{{ form.errors.site_name }}</div>
        </div>
        
        <div>
          <label class="block text-sm font-bold text-on-surface mb-2">
            Default Meta Title <span class="text-error">*</span>
          </label>
          <input type="text" v-model="form.default_meta_title" required maxlength="190" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
          <div v-if="form.errors.default_meta_title" class="text-xs text-error mt-1">{{ form.errors.default_meta_title }}</div>
        </div>
        
        <div>
          <label class="block text-sm font-bold text-on-surface mb-2">
            Default Meta Description <span class="text-error">*</span>
          </label>
          <textarea v-model="form.default_meta_desc" required maxlength="300" rows="3" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all resize-none"></textarea>
          <div v-if="form.errors.default_meta_desc" class="text-xs text-error mt-1">{{ form.errors.default_meta_desc }}</div>
        </div>
        
        <div>
          <label class="block text-sm font-bold text-on-surface mb-2">Default OG Image (Opsional)</label>
          <div class="flex items-center gap-6">
            <div class="shrink-0 w-24 h-24 rounded-lg overflow-hidden bg-surface-variant/30 border border-outline-variant/30 flex items-center justify-center">
              <img v-if="ogPreview || (form.default_og_image_path && typeof form.default_og_image_path === 'string')" 
                   :src="ogPreview || (form.default_og_image_path.startsWith('http') ? form.default_og_image_path : '/storage/' + form.default_og_image_path)" 
                   class="w-full h-full object-cover" 
                   alt="OG Image Preview" />
              <svg v-else class="w-8 h-8 text-on-surface-variant/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div class="flex-grow">
              <input 
                type="file" 
                accept="image/*"
                @change="handleOgImageChange"
                class="block w-full text-sm text-on-surface-variant file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all cursor-pointer"
              />
              <p class="text-[10px] text-on-surface-variant mt-2">Digunakan saat membagikan halaman yang tidak memiliki gambar spesifik ke media sosial. Format: JPG, PNG, WEBP.</p>
              <div v-if="form.errors.default_og_image_path" class="text-xs text-error mt-1.5 font-medium">{{ form.errors.default_og_image_path }}</div>
            </div>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-bold text-on-surface mb-2 flex items-center gap-2">
            Google Analytics ID (Opsional)
            <svg class="w-4 h-4 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </label>
          <input type="text" v-model="form.google_analytics_id" maxlength="50" placeholder="G-XXXX..." class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
          <div v-if="form.errors.google_analytics_id" class="text-xs text-error mt-1">{{ form.errors.google_analytics_id }}</div>
        </div>

        <div class="pt-6 border-t border-outline-variant/20 flex gap-3 justify-end">
          <button type="submit" :disabled="form.processing" class="btn-simbiosis-save rounded-full px-8 py-2.5 text-sm font-bold shadow-sm disabled:opacity-50">
            Simpan SEO
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
