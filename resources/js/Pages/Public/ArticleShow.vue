<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import FormInput from '../../Components/FormInput.vue';
import { formatIndonesianDate } from '../../utils';
import BaseButton from '../../Components/BaseButton.vue';

const props = defineProps({
  article: {
    type: Object,
    required: true
  },
  renderedContentHtml: String,
  breadcrumbs: {
    type: Array,
    default: () => []
  },
  comments: {
    type: Array,
    default: () => []
  },
  related: {
    type: Array,
    default: () => []
  },
  shareLinks: {
    type: Object,
    default: () => ({})
  },
  previewMode: Boolean,
  metaTitle: String,
  metaDescription: String,
  commentsEnabled: {
    type: Boolean,
    default: true
  }
});

const page = usePage();

// Comment form helper
const commentForm = useForm({
  name: '',
  email: '',
  content: ''
});

const submitComment = () => {
  commentForm.post(`/artikel/${props.article.id}/komentar`, {
    preserveScroll: true,
    onSuccess: () => {
      commentForm.reset('content'); // Clear content but keep name/email
    }
  });
};

const imageUrl = computed(() => {
  const path = props.article.cover_image_path;
  if (!path) return '/brand/fallback-image.png';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  return `/storage/${path}`;
});

const authorAvatarUrl = computed(() => {
  return props.article.author?.avatar || null;
});

const authorName = computed(() => props.article.author?.name || 'Redaksi');
const categoryName = computed(() => props.article.category?.name || 'Berita');
const publishedAtFormatted = computed(() => formatIndonesianDate(props.article.published_at_formatted || props.article.published_at));
</script>

<template>
  <PublicLayout>
    <Head :title="metaTitle || article.title">
      <meta name="description" :content="metaDescription || article.excerpt" />
      <meta property="og:type" content="article" />
      <meta property="og:title" :content="metaTitle || article.title" />
      <meta property="og:description" :content="metaDescription || article.excerpt" />
      <meta property="og:image" :content="imageUrl" />
    </Head>

    <div v-if="previewMode" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-300/50 dark:border-amber-800/30 px-4 py-3 text-sm text-amber-900 dark:text-amber-200 rounded-2xl mb-4 mx-4">
      Mode preview admin. Halaman ini belum tentu dapat diakses publik sebelum artikel dipublish.
    </div>

    <!-- Breadcrumbs & Category Pill -->
    <div class="mb-6 flex flex-col gap-4 px-4">
      <nav class="flex text-xs font-semibold uppercase tracking-wider text-on-surface-variant/75" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
          <li v-for="(item, idx) in breadcrumbs" :key="idx" class="inline-flex items-center">
            <span v-if="idx > 0" class="mx-1 text-on-surface-variant/40">/</span>
            <Link v-if="item.href" :href="item.href" class="hover:text-primary transition-colors">{{ item.label }}</Link>
            <span v-else class="text-on-surface font-bold">{{ item.label }}</span>
          </li>
        </ol>
      </nav>
      
      <div class="flex items-center gap-3">
        <span class="bg-primary-container text-white text-xs px-3 py-1 rounded-full uppercase tracking-wider font-bold shadow-sm font-label-sm">
          {{ categoryName }}
        </span>
        <span class="text-on-surface-variant text-sm flex items-center gap-1">
          <svg class="w-4 h-4 text-on-surface-variant/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          {{ article.reading_time || 3 }} menit baca
        </span>
      </div>
    </div>

    <!-- Article Header -->
    <header class="mb-8 px-4">
      <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-on-surface mb-6 leading-tight tracking-tight">
        {{ article.title }}
      </h1>
      
      <div class="flex flex-wrap items-center justify-between gap-4 py-4 border-y border-outline-variant/20 transition-colors">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xl overflow-hidden shadow-sm shrink-0">
            <img v-if="authorAvatarUrl" :src="authorAvatarUrl" :alt="authorName" class="w-full h-full object-cover" />
            <span v-else>{{ authorName.charAt(0) }}</span>
          </div>
          <div>
            <Link class="text-base font-bold text-on-surface hover:text-primary transition-colors block" :href="`/penulis/${article.author_id}`">
              {{ authorName }}
            </Link>
            <span class="text-xs text-on-surface-variant">
              {{ publishedAtFormatted }} WIB
            </span>
          </div>
        </div>
        
        <div class="flex items-center gap-4 text-sm text-on-surface-variant font-label-sm">
          <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            {{ Number(article.view_count || 0).toLocaleString() }} tayangan
          </span>
          <span v-if="commentsEnabled" class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            {{ comments.length }} komentar
          </span>
        </div>
      </div>
    </header>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 px-4">
      
      <!-- Left Column: Article Body & Comments -->
      <article class="md:col-span-8 flex flex-col gap-8">
        
        <!-- Hero Image -->
        <div class="w-full aspect-video rounded-3xl overflow-hidden shadow-sm relative group">
          <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy" :src="imageUrl" :alt="article.title" />
          <div class="absolute bottom-0 left-0 w-full p-4 bg-gradient-to-t from-slate-950/80 to-transparent">
            <p class="text-xs text-white/80 font-label-sm">{{ article.title }}</p>
          </div>
        </div>

        <!-- In-Article Ad -->
        <div v-if="$page.props.ads?.in_article?.[0]" class="w-full my-2">
          <a :href="$page.props.ads.in_article[0].target_url" target="_blank" rel="noopener sponsored" class="block w-full rounded-2xl overflow-hidden shadow-sm hover:opacity-90 transition-opacity">
            <img :src="$page.props.ads.in_article[0].image_path.startsWith('http') ? $page.props.ads.in_article[0].image_path : `/storage/${$page.props.ads.in_article[0].image_path}`" :alt="$page.props.ads.in_article[0].title" class="w-full h-auto object-cover max-h-[150px]" />
          </a>
        </div>

        <!-- Prose Content -->
        <div class="prose dark:prose-invert max-w-none text-lg leading-relaxed text-on-surface font-sans" v-html="renderedContentHtml"></div>

        <!-- Tags -->
        <div v-if="article.tags && article.tags.length > 0" class="flex flex-wrap items-center gap-2 mt-4">
          <Link
            v-for="t in article.tags"
            :key="t.id"
            :href="`/tag/${t.slug}`"
            class="px-3 py-1 bg-surface-container dark:bg-slate-800 hover:bg-primary dark:hover:bg-inverse-primary hover:text-white dark:hover:text-slate-900 rounded-full text-xs font-semibold text-primary dark:text-inverse-primary transition-all duration-200"
          >
            #{{ t.name }}
          </Link>
        </div>

        <!-- Share Actions -->
        <div v-if="!previewMode && Object.keys(shareLinks).length > 0" class="mt-8 p-6 liquid-glass dark:liquid-glass-dark rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
          <span class="text-base font-bold text-on-surface">Bagikan artikel ini:</span>
          <div class="flex items-center gap-3">
            <a
              v-for="(link, key) in shareLinks"
              :key="key"
              :href="`/share/${article.slug}/${key}`"
              target="_blank"
              rel="noopener nofollow"
              :title="key === 'wa' ? 'WhatsApp' : key === 'fb' ? 'Facebook' : key === 'x' ? 'X' : 'Telegram'"
              class="w-10 h-10 flex items-center justify-center transition-transform duration-300 hover:-translate-y-1 hover:scale-110"
            >
              <svg v-if="key === 'wa'" viewBox="0 0 448 512" class="w-8 h-8 fill-[#25D366] drop-shadow-sm"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7 .9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
              <svg v-else-if="key === 'fb'" viewBox="0 0 512 512" class="w-8 h-8 fill-[#1877F2] drop-shadow-sm"><path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/></svg>
              <svg v-else-if="key === 'x'" viewBox="0 0 512 512" class="w-7 h-7 fill-black dark:fill-white drop-shadow-sm"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.6 318.1 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
              <svg v-else-if="key === 'telegram'" viewBox="0 0 496 512" class="w-8 h-8 fill-[#0088cc] drop-shadow-sm"><path d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm121.8 169.9l-40.7 191.8c-3 13.6-11.1 16.9-22.4 10.5l-62-45.7-29.9 28.8c-3.3 3.3-6.1 6.1-12.5 6.1l4.4-63.1 114.9-103.8c5-4.4-1.1-6.9-7.7-2.5l-142 89.4-61.2-19.1c-13.3-4.2-13.6-13.3 2.8-19.7l239.1-92.2c11.1-4 20.8 2.7 17.2 19.5z"/></svg>
            </a>
          </div>
        </div>

        <!-- Comments Section -->
        <section v-if="commentsEnabled" id="comments" class="mt-12">
          <h3 class="text-2xl font-bold text-on-surface mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-primary dark:text-inverse-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            Komentar ({{ comments.length }})
          </h3>

          <!-- Comment Form -->
          <div class="bg-surface/50 dark:bg-slate-900/50 rounded-2xl p-6 mb-8 border border-outline-variant/30 shadow-sm">
            <form @submit.prevent="submitComment" class="space-y-4">
              <div class="grid md:grid-cols-2 gap-4">
                <FormInput
                  placeholder="Nama Lengkap"
                  required
                  v-model="commentForm.name"
                  :error="commentForm.errors.name"
                />
                <FormInput
                  placeholder="Alamat Email"
                  type="email"
                  required
                  v-model="commentForm.email"
                  :error="commentForm.errors.email"
                />
              </div>
              
              <div class="flex flex-col gap-1 w-full">
                <textarea
                  v-model="commentForm.content"
                  placeholder="Tulis komentar Anda..."
                  required
                  maxlength="1000"
                  rows="3"
                  class="w-full bg-surface-container-low dark:bg-surface-slate-900 border border-outline-variant/30 rounded-2xl p-4 text-sm text-on-surface focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all duration-200 resize-none outline-none placeholder-on-surface-variant/40"
                  :class="{ 'border-error focus:border-error focus:ring-error/20': commentForm.errors.content }"
                ></textarea>
                <span v-if="commentForm.errors.content" class="text-xs text-error font-medium mt-0.5">{{ commentForm.errors.content }}</span>
              </div>
              
              <div class="flex items-center justify-end">
                <BaseButton
                  type="submit"
                  variant="mesh-primary"
                  :disabled="commentForm.processing"
                  class="px-6 py-2"
                >
                  {{ commentForm.processing ? 'Mengirim...' : 'Kirim Komentar' }}
                </BaseButton>
              </div>
            </form>
          </div>

          <!-- Comment List -->
          <div class="flex flex-col gap-6">
            <div
              v-for="c in comments"
              :key="c.id"
              class="flex gap-4 border-b border-outline-variant/15 pb-6 last:border-0"
            >
              <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary dark:text-inverse-primary font-bold text-lg shrink-0">
                {{ c.name.charAt(0) }}
              </div>
              <div>
                <div class="flex items-baseline gap-2 mb-1">
                  <span class="font-bold text-on-surface">{{ c.name }}</span>
                  <span class="text-xs text-on-surface-variant/60 font-label-sm">{{ c.created_at_diff || 'Baru saja' }}</span>
                </div>
                <p class="text-on-surface-variant/90 text-sm whitespace-pre-line">
                  {{ c.content }}
                </p>
              </div>
            </div>
            
            <div v-if="comments.length === 0" class="text-center py-8 text-on-surface-variant/70 border border-dashed border-outline-variant/30 rounded-2xl">
              Belum ada komentar. Jadilah yang pertama memberikan komentar!
            </div>
          </div>
        </section>
      </article>

      <!-- Sidebar: Related Articles -->
      <aside class="md:col-span-4 flex flex-col gap-8">
        <!-- Related Articles Card -->
        <div class="liquid-glass dark:liquid-glass-dark rounded-2xl p-6 shadow-sm">
          <h3 class="text-xl font-bold text-on-surface mb-4 border-b border-outline-variant/20 pb-2">
            Berita Terkait
          </h3>
          <div class="flex flex-col gap-4">
            <Link
              v-for="a in related"
              :key="a.id"
              :href="`/artikel/${a.slug}`"
              class="flex gap-4 group"
            >
              <div class="w-24 h-24 rounded-lg overflow-hidden shrink-0">
                <img
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" 
                  :src="a.cover_image_path ? (a.cover_image_path.startsWith('http') ? a.cover_image_path : `/storage/${a.cover_image_path}`) : '/brand/fallback-image.png'" 
                  :alt="a.title"
                />
              </div>
              <div class="flex flex-col justify-center min-w-0">
                <span v-if="a.category" class="text-[10px] font-semibold text-primary dark:text-inverse-primary mb-1 uppercase tracking-wider font-label-sm">{{ a.category.name }}</span>
                <h4 class="text-sm font-bold text-on-surface group-hover:text-primary dark:group-hover:text-inverse-primary transition-colors line-clamp-2 leading-snug">
                  {{ a.title }}
                </h4>
                <span class="text-[10px] text-on-surface-variant/65 mt-1 font-label-sm">{{ a.published_at_diff || a.published_at }}</span>
              </div>
            </Link>
          </div>
        </div>

        <!-- Sidebar Ads -->
        <!-- Sidebar Ads -->
        <div class="flex flex-col gap-6">
          <template v-for="i in 5" :key="'sidebar_ad_'+i">
            <a
              v-if="$page.props.ads?.[`article_sidebar_${i}`]?.[0]"
              :href="$page.props.ads[`article_sidebar_${i}`][0].target_url"
              target="_blank"
              rel="noopener sponsored"
              class="block w-full rounded-2xl overflow-hidden shadow-sm hover:opacity-90 transition-opacity"
            >
              <img :src="$page.props.ads[`article_sidebar_${i}`][0].image_path.startsWith('http') ? $page.props.ads[`article_sidebar_${i}`][0].image_path : `/storage/${$page.props.ads[`article_sidebar_${i}`][0].image_path}`" :alt="$page.props.ads[`article_sidebar_${i}`][0].title" class="w-full h-auto object-cover" />
            </a>
          </template>
        </div>
      </aside>
    </div>
  </PublicLayout>
</template>
