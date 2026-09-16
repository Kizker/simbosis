<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import BaseButton from '@/Components/BaseButton.vue';

const props = defineProps({
  category: {
    type: Object,
    required: true
  }
});

const form = useForm({
  _method: props.category.id ? 'PUT' : 'POST',
  name: props.category.name || '',
  slug: props.category.slug || '',
  description: props.category.description || '',
  is_active: props.category.id ? !!props.category.is_active : true,
  regenerate_slug: false
});

const submit = () => {
  if (props.category.id) {
    form.post(route('admin.categories.update', props.category.id));
  } else {
    form.post(route('admin.categories.store'));
  }
};
</script>

<template>
  <AdminLayout :title="category.id ? 'Edit Kategori' : 'Tambah Kategori'">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">{{ category.id ? 'Edit Kategori' : 'Tambah Kategori' }}</h2>
        <p class="text-sm text-on-surface-variant mt-1">Kelola detail kategori berita.</p>
      </div>
      <Link href="/harmony-access/categories" class="px-6 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface-variant hover:bg-surface-variant/30 transition-colors text-sm font-semibold flex items-center gap-2 shadow-sm bg-surface/50">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Kembali
      </Link>
    </div>

    <div class="liquid-glass dark:liquid-glass-dark rounded-xl shadow-sm p-6 lg:p-8">
      <form @submit.prevent="submit" class="grid gap-6 lg:grid-cols-2">
        <!-- Name -->
        <FormInput
          id="name"
          label="Nama Kategori"
          v-model="form.name"
          :error="form.errors.name"
          required
          placeholder="Masukkan nama kategori"
        />

        <!-- Slug -->
        <FormInput
          id="slug"
          label="Slug (opsional)"
          v-model="form.slug"
          :error="form.errors.slug"
          placeholder="auto jika kosong"
        />

        <!-- Description -->
        <div class="flex flex-col gap-1 w-full lg:col-span-2">
          <label for="description" class="text-xs font-bold tracking-wider uppercase text-on-surface-variant/80">Deskripsi</label>
          <textarea
            id="description"
            rows="4"
            v-model="form.description"
            placeholder="Tulis deskripsi kategori..."
            class="w-full rounded-xl border border-outline-variant/30 px-4 py-2.5 text-sm bg-surface-container-low dark:bg-slate-900 text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none outline-none"
          ></textarea>
          <span v-if="form.errors.description" class="text-xs text-error font-medium mt-0.5">{{ form.errors.description }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-6 lg:col-span-2">
          <!-- Active switch -->
          <label class="flex items-center gap-3 p-4 rounded-xl border border-outline-variant/30 bg-surface-variant/5 cursor-pointer hover:bg-surface-variant/10 transition-colors w-max select-none">
            <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary" />
            <span class="text-sm font-bold text-on-surface">Aktif</span>
          </label>

          <!-- Regenerate slug (edit only) -->
          <label v-if="category.id" class="flex items-center gap-3 p-4 rounded-xl border border-outline-variant/30 bg-surface-variant/5 cursor-pointer hover:bg-surface-variant/10 transition-colors w-max select-none">
            <input type="checkbox" v-model="form.regenerate_slug" class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary">
            <span class="text-sm font-bold text-on-surface">Regenerate slug</span>
          </label>
        </div>

        <div class="pt-4 border-t border-outline-variant/20 flex gap-3 lg:col-span-2">
          <BaseButton
            type="submit"
            variant="mesh-primary"
            class="px-8"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Menyimpan...' : 'Simpan Kategori' }}
          </BaseButton>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
