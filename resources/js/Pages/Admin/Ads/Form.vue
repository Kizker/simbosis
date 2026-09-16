<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import BaseButton from '@/Components/BaseButton.vue';

const props = defineProps({
  ad: {
    type: Object,
    required: true
  }
});

// Format date for datetime-local
const formatDatetime = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const pad = (num) => String(num).padStart(2, '0');
  
  const yyyy = date.getFullYear();
  const MM = pad(date.getMonth() + 1);
  const dd = pad(date.getDate());
  const hh = pad(date.getHours());
  const mm = pad(date.getMinutes());
  
  return `${yyyy}-${MM}-${dd}T${hh}:${mm}`;
};

const form = useForm({
  _method: props.ad.id ? 'PUT' : 'POST',
  slot: props.ad.slot || 'header',
  title: props.ad.title || '',
  target_url: props.ad.target_url || '',
  image: null,
  starts_at: formatDatetime(props.ad.starts_at),
  ends_at: formatDatetime(props.ad.ends_at),
  is_active: props.ad.id ? !!props.ad.is_active : true
});

const submit = () => {
  if (props.ad.id) {
    form.post(route('admin.ads.update', props.ad.id));
  } else {
    form.post(route('admin.ads.store'));
  }
};
</script>

<template>
  <AdminLayout :title="ad.id ? 'Edit Iklan' : 'Tambah Iklan'">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">{{ ad.id ? 'Edit Iklan' : 'Tambah Iklan' }}</h2>
        <p class="text-sm text-on-surface-variant mt-1">Atur properti dan posisi iklan banner.</p>
      </div>
      <Link href="/harmony-access/ads" class="px-6 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface-variant hover:bg-surface-variant/30 transition-colors text-sm font-semibold flex items-center gap-2 shadow-sm bg-surface/50">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Kembali
      </Link>
    </div>

    <div class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm p-6 lg:p-8">
      <form @submit.prevent="submit" class="grid gap-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label for="slot" class="text-xs font-bold tracking-wider uppercase text-on-surface-variant/80">Slot Posisi <span class="text-error">*</span></label>
            <select
              id="slot"
              v-model="form.slot"
              required
              class="w-full rounded-xl border border-outline-variant/30 px-4 py-2.5 text-sm bg-surface-container-low dark:bg-slate-900 text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none mt-1 capitalize font-semibold"
            >
              <option value="header">Header Atas (1200x200 px)</option>
              <option value="sidebar_top">Sidebar Atas (300x250 px)</option>
              <option value="sidebar_bottom">Sidebar Bawah (300x250 px)</option>
              <option value="article_sidebar_1">Sidebar Artikel 1 (300x250 px)</option>
              <option value="article_sidebar_2">Sidebar Artikel 2 (300x250 px)</option>
              <option value="article_sidebar_3">Sidebar Artikel 3 (300x250 px)</option>
              <option value="article_sidebar_4">Sidebar Artikel 4 (300x250 px)</option>
              <option value="article_sidebar_5">Sidebar Artikel 5 (300x250 px)</option>
              <option value="in_article">Dalam Artikel (728x90 px)</option>
              <option value="home_banner">Beranda Tengah (728x90 px)</option>
              <option value="footer">Footer Bawah (1200x200 px)</option>
            </select>
            <span v-if="form.errors.slot" class="text-xs text-error font-medium mt-1 block">{{ form.errors.slot }}</span>
          </div>

          <FormInput
            id="title"
            label="Judul Internal"
            v-model="form.title"
            :error="form.errors.title"
            required
            placeholder="Contoh: Promo Ramadhan Sidebar"
          />
        </div>

        <FormInput
          id="target_url"
          label="Target URL"
          type="url"
          v-model="form.target_url"
          :error="form.errors.target_url"
          required
          placeholder="https://..."
        />

        <!-- Image Preview & Upload -->
        <div class="grid gap-4">
          <div v-if="ad.image_path">
            <label class="text-xs font-bold tracking-wider uppercase text-on-surface-variant/80 block mb-2">Gambar Saat Ini</label>
            <img :src="ad.image_path.startsWith('http') ? ad.image_path : `/storage/${ad.image_path}`" alt="Ad Preview" class="h-32 object-contain rounded-lg border border-outline-variant/30 bg-surface-container-lowest p-2" />
          </div>

          <div>
            <label for="image" class="text-xs font-bold tracking-wider uppercase text-on-surface-variant/80 block mb-1">Upload Gambar Baru <span v-if="!ad.id" class="text-error">*</span></label>
            <input 
              type="file" 
              id="image" 
              @input="form.image = $event.target.files[0]" 
              accept="image/*"
              class="w-full text-sm text-on-surface file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all outline-none" 
            />
            <span v-if="form.errors.image" class="text-xs text-error font-medium mt-1 block">{{ form.errors.image }}</span>
            <p class="text-xs text-on-surface-variant mt-1">Format: JPG, PNG, GIF (Maks 2MB).</p>
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
          <FormInput
            id="starts_at"
            label="Tanggal Mulai (Opsional)"
            type="datetime-local"
            v-model="form.starts_at"
            :error="form.errors.starts_at"
          />
          <FormInput
            id="ends_at"
            label="Tanggal Selesai (Opsional)"
            type="datetime-local"
            v-model="form.ends_at"
            :error="form.errors.ends_at"
          />
        </div>

        <label class="flex items-center gap-3 p-4 rounded-xl border border-outline-variant/30 bg-surface-variant/5 cursor-pointer hover:bg-surface-variant/10 transition-colors w-max select-none">
          <input type="checkbox" v-model="form.is_active" class="rounded border-outline-variant text-primary focus:ring-primary h-5 w-5" />
          <span class="text-sm font-bold text-on-surface">Aktifkan Iklan Ini</span>
        </label>

        <div class="pt-6 border-t border-outline-variant/20 flex gap-3 justify-end">
          <Link href="/harmony-access/ads" class="px-6 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface-variant hover:bg-surface-variant/30 transition-colors text-sm font-semibold shadow-sm bg-surface/50">
            Batal
          </Link>
          <BaseButton
            type="submit"
            variant="mesh-primary"
            class="px-8"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Menyimpan...' : 'Simpan Iklan' }}
          </BaseButton>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
