<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import BaseButton from '@/Components/BaseButton.vue';

const props = defineProps({
  tag: {
    type: Object,
    required: true
  }
});

const form = useForm({
  _method: props.tag.id ? 'PUT' : 'POST',
  name: props.tag.name || '',
  regenerate_slug: false
});

const submit = () => {
  if (props.tag.id) {
    form.post(route('admin.tags.update', props.tag.id));
  } else {
    form.post(route('admin.tags.store'));
  }
};
</script>

<template>
  <AdminLayout :title="tag.id ? 'Edit Tag' : 'Tambah Tag'">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">{{ tag.id ? 'Edit Tag' : 'Tambah Tag' }}</h2>
        <p class="text-sm text-on-surface-variant mt-1">Kelola detail tag berita.</p>
      </div>
      <Link href="/harmony-access/tags" class="px-6 py-2.5 rounded-xl border border-outline-variant/30 text-on-surface-variant hover:bg-surface-variant/30 transition-colors text-sm font-semibold flex items-center gap-2 shadow-sm bg-surface/50">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Kembali
      </Link>
    </div>

    <div class="liquid-glass dark:liquid-glass-dark rounded-xl shadow-sm p-6 lg:p-8">
      <form @submit.prevent="submit" class="grid gap-6">
        <!-- Name -->
        <FormInput
          id="name"
          label="Nama Tag"
          v-model="form.name"
          :error="form.errors.name"
          required
          placeholder="Masukkan nama tag"
        />

        <!-- Regenerate slug (edit only) -->
        <div v-if="tag.id" class="flex items-center">
          <label class="flex items-center gap-3 p-4 rounded-xl border border-outline-variant/30 bg-surface-variant/5 cursor-pointer hover:bg-surface-variant/10 transition-colors w-max select-none">
            <input type="checkbox" v-model="form.regenerate_slug" class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary">
            <span class="text-sm font-bold text-on-surface">Regenerate slug</span>
          </label>
        </div>

        <div class="pt-4 border-t border-outline-variant/20 flex gap-3">
          <BaseButton
            type="submit"
            variant="mesh-primary"
            class="px-8"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Menyimpan...' : 'Simpan Tag' }}
          </BaseButton>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
