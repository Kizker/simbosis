<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  about_title: String,
  about_content: String,
  about_meta_title: String,
  about_meta_desc: String,

  contact_title: String,
  contact_intro: String,
  contact_address: String,
  contact_phone: String,
  contact_email_public: String,
  contact_hours: String,
  contact_map_url: String,
  contact_recipient_email: String,
  contact_meta_title: String,
  contact_meta_desc: String
});

const form = useForm({
  about_title: props.about_title || '',
  about_content: props.about_content || '',
  about_meta_title: props.about_meta_title || '',
  about_meta_desc: props.about_meta_desc || '',

  contact_title: props.contact_title || '',
  contact_intro: props.contact_intro || '',
  contact_address: props.contact_address || '',
  contact_phone: props.contact_phone || '',
  contact_email_public: props.contact_email_public || '',
  contact_hours: props.contact_hours || '',
  contact_map_url: props.contact_map_url || '',
  contact_recipient_email: props.contact_recipient_email || '',
  contact_meta_title: props.contact_meta_title || '',
  contact_meta_desc: props.contact_meta_desc || ''
});

const submit = () => {
  form.post('/harmony-access/pages');
};
</script>

<template>
  <AdminLayout title="Konten Halaman Publik">
    <!-- Page Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Konten Halaman Publik</h2>
        <p class="text-sm text-on-surface-variant mt-1">Ubah teks dan informasi statis untuk halaman Tentang Kami dan Kontak.</p>
      </div>
      <Link href="/harmony-access/settings" class="px-6 py-2.5 rounded-full btn-simbiosis-neutral text-sm font-semibold flex items-center gap-2 shadow-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Kembali
      </Link>
    </header>

    <form @submit.prevent="submit" class="grid gap-8">
      <!-- Section: Tentang Kami -->
      <div class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-outline-variant/20 bg-surface/50 dark:bg-surface-dark/50 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h3 class="text-lg font-bold text-on-surface">Tentang Kami</h3>
        </div>
        <div class="p-6 grid gap-6">
          <div>
            <label class="block text-sm font-bold text-on-surface mb-2">Judul Halaman <span class="text-error">*</span></label>
            <input type="text" v-model="form.about_title" required class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
            <div v-if="form.errors.about_title" class="text-xs text-error mt-1">{{ form.errors.about_title }}</div>
          </div>
          
          <div>
            <label class="block text-sm font-bold text-on-surface mb-2">Isi Konten (Teks Biasa) <span class="text-error">*</span></label>
            <textarea v-model="form.about_content" required rows="8" maxlength="20000" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-3 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all leading-relaxed"></textarea>
            <p class="text-[10px] text-on-surface-variant mt-1.5">Tidak perlu format HTML. Baris baru (Enter) akan otomatis dipisahkan menjadi paragraf terpisah di halaman publik.</p>
            <div v-if="form.errors.about_content" class="text-xs text-error mt-1">{{ form.errors.about_content }}</div>
          </div>

          <div class="grid md:grid-cols-2 gap-6 bg-surface-variant/10 p-5 rounded-xl border border-outline-variant/30">
            <div class="md:col-span-2">
              <h4 class="text-sm font-bold text-on-surface">SEO Tentang Kami</h4>
            </div>
            <div>
              <label class="block text-sm font-bold text-on-surface mb-2">Meta Title</label>
              <input type="text" v-model="form.about_meta_title" required maxlength="190" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
              <div v-if="form.errors.about_meta_title" class="text-xs text-error mt-1">{{ form.errors.about_meta_title }}</div>
            </div>
            <div>
              <label class="block text-sm font-bold text-on-surface mb-2">Meta Description</label>
              <input type="text" v-model="form.about_meta_desc" required maxlength="300" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
              <div v-if="form.errors.about_meta_desc" class="text-xs text-error mt-1">{{ form.errors.about_meta_desc }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Section: Kontak -->
      <div class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-outline-variant/20 bg-surface/50 dark:bg-surface-dark/50 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
          </div>
          <h3 class="text-lg font-bold text-on-surface">Hubungi Kami (Kontak)</h3>
        </div>
        <div class="p-6 grid gap-6">
          <div class="grid md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-bold text-on-surface mb-2">Judul Halaman <span class="text-error">*</span></label>
              <input type="text" v-model="form.contact_title" required maxlength="120" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
              <div v-if="form.errors.contact_title" class="text-xs text-error mt-1">{{ form.errors.contact_title }}</div>
            </div>
            <div>
              <label class="block text-sm font-bold text-on-surface mb-2">Teks Pengantar Singkat</label>
              <input type="text" v-model="form.contact_intro" maxlength="500" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
              <div v-if="form.errors.contact_intro" class="text-xs text-error mt-1">{{ form.errors.contact_intro }}</div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold text-on-surface mb-2">Alamat Lengkap</label>
            <textarea v-model="form.contact_address" rows="3" maxlength="1000" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-3 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all resize-none"></textarea>
            <div v-if="form.errors.contact_address" class="text-xs text-error mt-1">{{ form.errors.contact_address }}</div>
          </div>

          <div class="grid md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-bold text-on-surface mb-2">Nomor Telepon</label>
              <input type="text" v-model="form.contact_phone" maxlength="120" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
              <div v-if="form.errors.contact_phone" class="text-xs text-error mt-1">{{ form.errors.contact_phone }}</div>
            </div>
            <div>
              <label class="block text-sm font-bold text-on-surface mb-2">Email Publik</label>
              <input type="email" v-model="form.contact_email_public" maxlength="190" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
              <div v-if="form.errors.contact_email_public" class="text-xs text-error mt-1">{{ form.errors.contact_email_public }}</div>
            </div>
            <div>
              <label class="block text-sm font-bold text-on-surface mb-2">Jam Operasional</label>
              <input type="text" v-model="form.contact_hours" maxlength="300" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all" placeholder="Senin-Jumat, 08:00 - 17:00">
              <div v-if="form.errors.contact_hours" class="text-xs text-error mt-1">{{ form.errors.contact_hours }}</div>
            </div>
          </div>

          <div class="grid md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-bold text-on-surface mb-2">Link Google Maps (URL) <span class="font-normal text-on-surface-variant">(Opsional)</span></label>
              <input type="url" v-model="form.contact_map_url" maxlength="500" placeholder="https://www.google.com/maps?q=..." class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
              <div v-if="form.errors.contact_map_url" class="text-xs text-error mt-1">{{ form.errors.contact_map_url }}</div>
            </div>
            <div>
              <label class="block text-sm font-bold text-on-surface mb-2">Email Tujuan Form Kontak <span class="text-error">*</span></label>
              <input type="email" v-model="form.contact_recipient_email" maxlength="190" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
              <p class="text-[10px] text-on-surface-variant mt-1.5">Alamat email yang akan menerima pesan dari form kontak publik. Kosongkan untuk menggunakan fallback ke `mail.from.address` dari sistem.</p>
              <div v-if="form.errors.contact_recipient_email" class="text-xs text-error mt-1">{{ form.errors.contact_recipient_email }}</div>
            </div>
          </div>

          <div class="grid md:grid-cols-2 gap-6 bg-surface-variant/10 p-5 rounded-xl border border-outline-variant/30">
            <div class="md:col-span-2">
              <h4 class="text-sm font-bold text-on-surface">SEO Kontak</h4>
            </div>
            <div>
              <label class="block text-sm font-bold text-on-surface mb-2">Meta Title</label>
              <input type="text" v-model="form.contact_meta_title" required maxlength="190" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
              <div v-if="form.errors.contact_meta_title" class="text-xs text-error mt-1">{{ form.errors.contact_meta_title }}</div>
            </div>
            <div>
              <label class="block text-sm font-bold text-on-surface mb-2">Meta Description</label>
              <input type="text" v-model="form.contact_meta_desc" required maxlength="300" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
              <div v-if="form.errors.contact_meta_desc" class="text-xs text-error mt-1">{{ form.errors.contact_meta_desc }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Submit Section -->
      <div class="flex justify-end pt-4 mb-8">
        <button type="submit" :disabled="form.processing" class="px-8 py-3 rounded-full btn-simbiosis-save text-white font-bold text-sm disabled:opacity-50 shadow-md">
          Simpan Semua Perubahan
        </button>
      </div>
    </form>
  </AdminLayout>
</template>
