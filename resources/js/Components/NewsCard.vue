<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { formatIndonesianDate } from '../utils';

const props = defineProps({
  article: {
    type: Object,
    required: true
  },
  variant: {
    type: String,
    default: 'grid' // 'featured', 'grid', 'list'
  }
});

const imageUrl = computed(() => {
  const path = props.article.cover_image_path || props.article.image_path;
  if (!path) return '/brand/fallback-image.png';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  return `/storage/${path}`;
});

const categoryName = computed(() => props.article.category?.name || (props.article.video_url ? 'Video' : (props.article.image_path ? 'Foto' : 'Berita')));
const authorName = computed(() => props.article.author?.name || 'Redaksi');
const articleUrl = computed(() => {
  if (props.article.video_url) return `/video/${props.article.slug}`;
  if (props.article.image_path) return `/foto/${props.article.slug}`;
  return `/artikel/${props.article.slug}`;
});

const formattedDate = computed(() => {
  return formatIndonesianDate(props.article.published_at_formatted || props.article.published_at);
});
</script>

<template>
  <!-- Featured Variation -->
  <div v-if="variant === 'featured'" class="relative overflow-hidden rounded-3xl group shadow-md transition-all duration-300 hover:scale-[1.01] hover:shadow-xl aspect-[16/9] w-full transform-gpu">
    <img :src="imageUrl" :alt="article.title" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent"></div>
    
    <!-- Liquid Glass Info Panel -->
    <div class="absolute bottom-4 left-4 right-4 liquid-glass-image rounded-2xl p-4 md:p-6 backdrop-blur-md border border-white/20">
      <div class="flex items-center gap-3 mb-2">
        <span class="px-2.5 py-0.5 bg-primary text-white rounded-full text-xs font-bold font-label-sm tracking-wider uppercase">{{ categoryName }}</span>
        <span class="text-xs text-white/80 font-label-sm">{{ formattedDate }}</span>
      </div>
      <Link :href="articleUrl" prefetch>
        <h3 class="font-display-lg text-lg md:text-2xl text-white font-bold leading-tight hover:text-secondary-container transition-colors line-clamp-2">
          {{ article.title }}
        </h3>
      </Link>
      <p class="text-xs md:text-sm text-white/80 mt-2 line-clamp-2 hidden md:block">
        {{ article.excerpt || article.body_excerpt || '' }}
      </p>
    </div>
  </div>

  <!-- Featured Compact Variation (for narrow grids/columns) -->
  <div v-else-if="variant === 'featured-compact'" class="relative overflow-hidden rounded-3xl group shadow-md transition-all duration-300 hover:scale-[1.01] hover:shadow-xl aspect-[4/3] w-full transform-gpu">
    <img :src="imageUrl" :alt="article.title" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/30 to-transparent"></div>
    
    <!-- Liquid Glass Info Panel (Compact) -->
    <div class="absolute bottom-3 left-3 right-3 liquid-glass-image rounded-2xl p-3 md:p-4 backdrop-blur-md border border-white/10">
      <div class="flex items-center gap-2 mb-1.5">
        <span class="px-2.5 py-0.5 bg-primary text-white rounded-full text-[10px] font-bold font-label-sm tracking-wider uppercase">{{ categoryName }}</span>
        <span class="text-[10px] text-white/80 font-label-sm">{{ formattedDate }}</span>
      </div>
      <Link :href="articleUrl" prefetch>
        <h3 class="text-sm md:text-base text-white font-bold leading-snug hover:text-secondary-container transition-colors line-clamp-2">
          {{ article.title }}
        </h3>
      </Link>
    </div>
  </div>

  <!-- List Variation -->
  <div v-else-if="variant === 'list'" class="flex flex-col gap-2 py-3 border-b border-outline-variant/15 last:border-b-0 w-full group">
    <div class="w-full aspect-[16/9] overflow-hidden rounded-xl bg-surface-container-low dark:bg-slate-800 transform-gpu">
      <img :src="imageUrl" :alt="article.title" loading="lazy" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
    </div>
    <div class="flex-grow min-w-0 flex flex-col justify-start">
      <div class="flex items-center gap-1.5 mb-1 text-[10px] font-label-sm flex-wrap">
        <span class="font-bold tracking-wider uppercase text-primary dark:text-inverse-primary">{{ categoryName }}</span>
        <span class="text-on-surface-variant/40">•</span>
        <span class="text-on-surface-variant/60 font-semibold">{{ formattedDate }}</span>
      </div>
      <Link :href="articleUrl" prefetch>
        <h4 class="font-headline-xl text-sm md:text-base text-on-surface font-bold hover:text-primary dark:hover:text-inverse-primary transition-colors line-clamp-2 leading-snug mb-1">
          {{ article.title }}
        </h4>
      </Link>
      <p class="text-[11px] text-on-surface-variant/75 mt-0.5 line-clamp-2 leading-normal mb-1">
        {{ article.excerpt || article.body_excerpt || '' }}
      </p>
      <span class="text-[10px] text-on-surface-variant/50 block mt-auto">Oleh: {{ authorName }}</span>
    </div>
  </div>

  <!-- Grid Variation -->
  <div v-else class="surface overflow-hidden hover:shadow-lg transition-all duration-300 hover:scale-[1.01] flex flex-col h-full group p-4 border border-outline-variant/20 dark:border-slate-800 rounded-2xl bg-surface-container-lowest/80 dark:bg-surface-slate-900/60 backdrop-blur-md transform-gpu">
    <div class="w-full aspect-[16/10] overflow-hidden rounded-xl mb-4 relative transform-gpu">
      <img :src="imageUrl" :alt="article.title" loading="lazy" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
      <span class="absolute top-2 left-2 px-2.5 py-0.5 bg-primary/90 text-white rounded-full text-[10px] font-bold font-label-sm tracking-wider uppercase backdrop-blur-sm">{{ categoryName }}</span>
    </div>
    <div class="flex flex-col flex-grow">
      <div class="flex items-center gap-2 text-[10px] text-on-surface-variant/60 mb-2 font-label-sm">
        <span>{{ formattedDate }}</span>
        <span>•</span>
        <span>{{ authorName }}</span>
      </div>
      <Link :href="articleUrl" class="flex-grow" prefetch>
        <h4 class="font-headline-xl text-base text-on-surface font-bold hover:text-primary dark:hover:text-inverse-primary transition-colors line-clamp-2 leading-snug mb-2">
          {{ article.title }}
        </h4>
      </Link>
      <p class="text-xs text-on-surface-variant/75 line-clamp-2 mb-4">
        {{ article.excerpt || article.body_excerpt || '' }}
      </p>
      <div class="mt-auto pt-2 border-t border-outline-variant/10 flex justify-between items-center">
        <Link :href="articleUrl" class="text-xs font-bold text-primary dark:text-inverse-primary hover:underline flex items-center gap-1 group/btn" prefetch>
          Selengkapnya
          <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover/btn:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
        </Link>
      </div>
    </div>
  </div>
</template>
