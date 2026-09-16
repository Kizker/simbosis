<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import BaseButton from '@/Components/BaseButton.vue';
import BadgeStatus from '@/Components/BadgeStatus.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import TagInput from '@/Components/TagInput.vue';

const props = defineProps({
  article: {
    type: Object,
    required: true
  },
  categories: {
    type: Array,
    required: true
  }
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Permission checking helper
const hasPermission = (permission) => {
  if (!user.value) return false;
  if (user.value.role === 'Superadmin') return true;
  return user.value.permissions?.includes(permission);
};

const canUpdateArticle = computed(() => {
  if (!props.article.id) {
    return hasPermission('articles.create');
  }
  if (hasPermission('articles.update_any')) return true;
  return hasPermission('articles.update_own') && props.article.author_id === user.value.id;
});

const reviewOnly = computed(() => {
  return props.article.id && !canUpdateArticle.value && hasPermission('articles.review');
});

// Form states
const form = useForm({
  _method: props.article.id ? 'PUT' : 'POST',
  title: props.article.title || '',
  excerpt: props.article.excerpt || '',
  category_id: props.article.category_id || '',
  cover_image_path: props.article.cover_image_path || '',
  cover_image_file: null,
  content_html: props.article.content_html || '',
  tag_ids: props.article.tags ? props.article.tags.map(t => t.id) : [],
  new_tags: [],
  is_breaking: props.article.is_breaking || false,
  is_featured: props.article.is_featured || false,
  seo_title: props.article.seo_title || '',
  seo_desc: props.article.seo_desc || '',
  canonical_url: props.article.canonical_url || '',
  regenerate_slug: false,
  publish_now: false,
});

// Image upload preview helper
const imagePreview = ref(null);
const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.cover_image_file = file;
    const reader = new FileReader();
    reader.onload = (event) => {
      imagePreview.value = event.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const submitForm = () => {
  form.publish_now = false;
  if (props.article.id) {
    form.post(route('admin.articles.update', props.article.id));
  } else {
    form.post(route('admin.articles.store'));
  }
};

const publishNow = () => {
  if (confirm('Simpan perubahan lalu publish artikel sekarang?')) {
    form.publish_now = true;
    if (props.article.id) {
      form.post(route('admin.articles.update', props.article.id));
    } else {
      form.post(route('admin.articles.store'));
    }
  }
};

// Workflow actions forms
const submitWorkflowForm = useForm({});
const handleSubmitForReview = () => {
  submitWorkflowForm.post(route('admin.articles.submit', props.article.id));
};

const handleStartReview = () => {
  submitWorkflowForm.post(route('admin.articles.startReview', props.article.id));
};

const revisionNote = ref('');
const handleRequestRevision = () => {
  if (!revisionNote.value.trim()) {
    alert('Catatan revisi wajib diisi.');
    return;
  }
  const revisionForm = useForm({ note: revisionNote.value });
  revisionForm.post(route('admin.articles.requestRevision', props.article.id), {
    onSuccess: () => {
      revisionNote.value = '';
    }
  });
};

const handlePublishOnly = () => {
  submitWorkflowForm.post(route('admin.articles.publish', props.article.id));
};

const handleArchive = () => {
  if (confirm('Yakin ingin mengarsipkan artikel ini?')) {
    submitWorkflowForm.post(route('admin.articles.archive', props.article.id));
  }
};
</script>

<template>
  <AdminLayout :title="article.id ? (reviewOnly ? 'Review Artikel' : 'Edit Artikel') : 'Buat Artikel Baru'">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">
          {{ article.id ? (reviewOnly ? 'Review Artikel' : 'Edit Artikel') : 'Buat Artikel Baru' }}
        </h2>
        <p class="text-sm text-on-surface-variant mt-1">Lengkapi informasi artikel sebelum dipublikasikan ke portal.</p>
      </div>
      <div v-if="article.id" class="flex gap-3">
        <a :href="`/harmony-access/articles/${article.id}/preview`" target="_blank" rel="noopener" class="btn-simbiosis-neutral rounded-full px-5 py-2.5 text-sm font-semibold flex items-center gap-2 shadow-sm">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
          Preview Publik
        </a>
      </div>
    </div>

    <div class="mt-4 grid gap-6 xl:grid-cols-3">
      <!-- Main Form Column -->
      <div class="xl:col-span-2 liquid-glass dark:liquid-glass-dark rounded-xl shadow-sm p-6 lg:p-8">
        <form @submit.prevent="submitForm" class="grid gap-6">
          <fieldset class="grid gap-6" :disabled="reviewOnly">
            <!-- Title -->
            <FormInput
              id="title"
              label="Judul Artikel"
              v-model="form.title"
              :error="form.errors.title"
              required
              placeholder="Masukkan judul artikel"
            />

            <!-- Excerpt -->
            <div class="flex flex-col gap-1 w-full">
              <label for="excerpt" class="text-xs font-bold tracking-wider uppercase text-on-surface-variant/80">Ringkasan (Excerpt)</label>
              <textarea
                id="excerpt"
                rows="3"
                v-model="form.excerpt"
                placeholder="Tulis ringkasan singkat artikel..."
                class="w-full rounded-xl border border-outline-variant/30 px-4 py-2.5 text-sm bg-surface-container-low dark:bg-slate-900 text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none outline-none"
              ></textarea>
              <span v-if="form.errors.excerpt" class="text-xs text-error font-medium mt-0.5">{{ form.errors.excerpt }}</span>
            </div>

            <!-- Meta attributes -->
            <div class="grid md:grid-cols-2 gap-6">
              <div>
                <label for="category_id" class="text-xs font-bold tracking-wider uppercase text-on-surface-variant/80">Kategori <span class="text-error">*</span></label>
                <select
                  id="category_id"
                  v-model="form.category_id"
                  required
                  class="w-full rounded-xl border border-outline-variant/30 px-4 py-2.5 text-sm bg-surface-container-low dark:bg-slate-900 text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none mt-1"
                >
                  <option value="" disabled>Pilih Kategori...</option>
                  <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <span v-if="form.errors.category_id" class="text-xs text-error font-medium mt-1 block">{{ form.errors.category_id }}</span>
              </div>
              
              <!-- Cover Image Upload Area -->
              <div class="md:col-span-2">
                <label class="text-xs font-bold tracking-wider uppercase text-on-surface-variant/80">Upload Cover Image (opsional)</label>
                <div class="relative border-2 border-dashed border-outline-variant/30 rounded-xl p-6 bg-surface-variant/5 text-center hover:bg-surface-variant/15 transition-colors mt-1">
                  <input type="file" accept="image/*" @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                  <div class="flex flex-col items-center gap-2 pointer-events-none">
                    <svg class="w-8 h-8 text-on-surface-variant" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="text-sm font-medium text-on-surface">Klik atau seret gambar ke sini</span>
                    <span class="text-xs text-on-surface-variant">PNG, JPG, WEBP maks 2MB</span>
                  </div>
                </div>
                <div v-if="imagePreview || form.cover_image_path" class="mt-4">
                  <span class="text-xs text-on-surface-variant font-bold block mb-2">Preview Gambar Cover:</span>
                  <img :src="imagePreview || (form.cover_image_path.startsWith('http') ? form.cover_image_path : `/storage/${form.cover_image_path}`)" class="h-48 object-cover rounded-xl border border-outline-variant/30 shadow-sm" />
                </div>
                <span v-if="form.errors.cover_image_file" class="text-xs text-error font-medium mt-1 block">{{ form.errors.cover_image_file }}</span>
              </div>
            </div>

            <hr class="border-outline-variant/20">

            <!-- Body editor -->
            <div class="flex flex-col gap-1 w-full">
              <label class="text-xs font-bold tracking-wider uppercase text-on-surface-variant/80 mb-1">Isi Berita</label>
              <RichTextEditor
                v-model="form.content_html"
                :disabled="reviewOnly"
                :error="form.errors.content_html"
              />
            </div>

            <hr class="border-outline-variant/20">

            <!-- Tags -->
            <div>
              <label class="text-xs font-bold tracking-wider uppercase text-on-surface-variant/80 block mb-3">Tag Relevan</label>
              <TagInput 
                v-model="form.tag_ids" 
                v-model:newTags="form.new_tags" 
                :initial-tags="article.tags || []"
              />
            </div>

            <!-- Features -->
            <div class="grid md:grid-cols-2 gap-4">
              <label class="flex items-center gap-3 p-4 rounded-xl border border-outline-variant/30 bg-surface-variant/5 cursor-pointer hover:bg-surface-variant/10 transition-colors">
                <input type="checkbox" v-model="form.is_breaking" class="w-5 h-5 rounded border-outline-variant text-error focus:ring-error" />
                <div class="flex flex-col select-none">
                  <span class="text-sm font-bold text-error uppercase tracking-wider">Breaking News</span>
                  <span class="text-xs text-on-surface-variant mt-0.5">Tandai sebagai berita penting/darurat.</span>
                </div>
              </label>
              <label class="flex items-center gap-3 p-4 rounded-xl border border-outline-variant/30 bg-surface-variant/5 cursor-pointer hover:bg-surface-variant/10 transition-colors">
                <input type="checkbox" v-model="form.is_featured" class="w-5 h-5 rounded border-outline-variant text-secondary focus:ring-secondary" />
                <div class="flex flex-col select-none">
                  <span class="text-sm font-bold text-secondary uppercase tracking-wider">Featured</span>
                  <span class="text-xs text-on-surface-variant mt-0.5">Tampilkan di slider/sorotan utama.</span>
                </div>
              </label>
            </div>

            <hr class="border-outline-variant/20">

            <!-- SEO Optimizations -->
            <div class="bg-surface-variant/5 p-6 rounded-xl border border-outline-variant/20">
              <h3 class="text-sm font-bold text-on-surface mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Optimasi SEO
              </h3>
              <div class="grid gap-4">
                <FormInput
                  id="seo_title"
                  label="SEO Title"
                  v-model="form.seo_title"
                  :error="form.errors.seo_title"
                />
                <div class="flex flex-col gap-1 w-full">
                  <label for="seo_desc" class="text-xs font-bold tracking-wider uppercase text-on-surface-variant/80">SEO Description</label>
                  <textarea
                    id="seo_desc"
                    rows="2"
                    v-model="form.seo_desc"
                    placeholder="Tulis deskripsi meta SEO..."
                    class="w-full rounded-xl border border-outline-variant/30 px-4 py-2 text-sm bg-surface-container-low dark:bg-slate-900 text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none outline-none"
                  ></textarea>
                  <span v-if="form.errors.seo_desc" class="text-xs text-error font-medium mt-0.5">{{ form.errors.seo_desc }}</span>
                </div>

              </div>
            </div>
          </fieldset>

          <div class="mt-4 pt-4 border-t border-outline-variant/20 flex flex-col sm:flex-row items-center justify-between gap-4">
            <label v-if="article.id && canUpdateArticle" class="flex items-center gap-2 text-sm font-medium text-on-surface-variant cursor-pointer">
              <input type="checkbox" v-model="form.regenerate_slug" class="rounded border-outline-variant text-primary focus:ring-primary">
              Regenerate slug
            </label>
            <div v-else></div>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
              <Link href="/harmony-access/articles" class="btn-simbiosis-neutral rounded-full w-full sm:w-auto px-6 py-2.5 text-center text-sm font-bold shadow-sm flex items-center justify-center">
                Kembali
              </Link>
              <BaseButton
                v-if="canUpdateArticle"
                type="submit"
                variant="save"
                class="w-full sm:w-auto px-8"
                :disabled="form.processing"
              >
                {{ form.processing ? 'Menyimpan...' : (article.id ? 'Simpan Perubahan' : 'Buat Artikel') }}
              </BaseButton>
            </div>
          </div>
        </form>
      </div>

      <!-- Workflow Sidebar Column -->
      <div class="xl:col-span-1">
        <aside class="liquid-glass dark:liquid-glass-dark rounded-xl shadow-sm p-6 sticky top-24">
          <h3 class="text-lg font-extrabold text-on-surface tracking-tight mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Sistem Workflow
          </h3>

          <div v-if="article.id">
            <!-- Current Status badge -->
            <div class="mb-6 bg-surface/50 dark:bg-slate-900 rounded-xl p-4 border border-outline-variant/25">
              <span class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Status Saat Ini</span>
              <BadgeStatus :status="article.status" />
            </div>

            <!-- Workflow control buttons -->
            <div class="flex flex-col gap-3 mb-6">
              <!-- Submit to review button -->
              <button
                v-if="(article.status === 'draft' || article.status === 'revision') && canUpdateArticle"
                @click="handleSubmitForReview"
                :disabled="submitWorkflowForm.processing"
                class="btn-simbiosis-edit rounded-full w-full py-2.5 shadow-sm text-sm font-bold"
              >
                Kirim ke Reviewer
              </button>

              <!-- Start review button -->
              <button
                v-if="hasPermission('articles.review') && (article.status === 'submitted' || article.status === 'revision')"
                @click="handleStartReview"
                :disabled="submitWorkflowForm.processing"
                class="btn-simbiosis-edit rounded-full w-full py-2.5 shadow-sm text-sm font-bold"
              >
                Mulai Review
              </button>

              <!-- Request revision block -->
              <div v-if="hasPermission('articles.review') && article.status === 'review'" class="flex flex-col gap-2">
                <textarea
                  v-model="revisionNote"
                  required
                  rows="3"
                  class="w-full rounded-xl border border-outline-variant/30 px-3 py-2 text-sm bg-surface/50 dark:bg-slate-900 text-on-surface focus:ring-1 focus:ring-error focus:border-error transition-all resize-none outline-none"
                  placeholder="Tulis catatan revisi..."
                ></textarea>
                <button
                  @click="handleRequestRevision"
                  class="btn-simbiosis-delete rounded-full w-full py-2 shadow-sm text-sm font-bold"
                >
                  Minta Revisi
                </button>
              </div>

              <!-- Publish Now (Editors/Admins shortcut or explicit) -->
              <div v-if="hasPermission('articles.publish')" class="mt-2 pt-4 border-t border-outline-variant/20">
                <button
                  type="button"
                  @click="publishNow"
                  :disabled="form.processing"
                  class="btn-simbiosis-save rounded-full w-full py-3 shadow-md flex items-center justify-center gap-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                  Publikasi Sekarang
                </button>
                <p class="text-[10px] text-center text-on-surface-variant mt-2 font-medium">Menyimpan dan langsung menayangkan artikel.</p>
              </div>

              <!-- Archive article button -->
              <div v-if="hasPermission('articles.archive') && article.status !== 'archived'" class="mt-2 pt-4 border-t border-outline-variant/20">
                <button
                  @click="handleArchive"
                  :disabled="submitWorkflowForm.processing"
                  class="btn-simbiosis-delete rounded-full w-full py-2.5 shadow-sm text-sm flex items-center justify-center gap-2"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                  Arsipkan Artikel
                </button>
              </div>
            </div>

            <!-- Workflow history timeline -->
            <div class="bg-surface-variant/5 rounded-xl p-4 border border-outline-variant/20">
              <span class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-3">Riwayat Status</span>
              <div v-if="article.status_history && article.status_history.length > 0" class="flex flex-col gap-4 relative before:absolute before:inset-y-0 before:left-[7px] before:w-px before:bg-outline-variant/25 ml-1">
                <div v-for="h in article.status_history" :key="h.id" class="relative z-10 pl-6">
                  <span class="absolute left-1 top-1.5 w-2 h-2 rounded-full bg-outline-variant border border-surface"></span>
                  <div class="text-[9px] text-on-surface-variant font-medium mb-0.5">
                    {{ new Date(h.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                  </div>
                  <div class="flex items-center gap-1.5 text-xs font-bold text-on-surface">
                    <span class="capitalize">{{ h.from_status }}</span>
                    <svg class="w-3 h-3 text-outline-variant" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    <span class="capitalize">{{ h.to_status }}</span>
                  </div>
                  <div v-if="h.note" class="mt-1 text-[11px] text-on-surface-variant bg-surface-container/60 px-2 py-1 rounded border border-outline-variant/15 inline-block font-medium">
                    {{ h.note }}
                  </div>
                </div>
              </div>
              <div v-else class="text-xs text-on-surface-variant text-center py-2 font-medium">Belum ada riwayat status.</div>
            </div>
          </div>
          
          <div v-else class="text-center p-6 bg-surface-variant/5 rounded-xl border border-outline-variant/30 border-dashed">
            <svg class="w-8 h-8 text-outline-variant mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
            <p class="text-xs font-semibold text-on-surface-variant leading-relaxed">Simpan draf pertama untuk mengakses fitur workflow redaksi.</p>
          </div>
        </aside>
      </div>
    </div>
  </AdminLayout>
</template>
