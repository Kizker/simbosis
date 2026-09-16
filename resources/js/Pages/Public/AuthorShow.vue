<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import NewsCard from '../../Components/NewsCard.vue';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
  author: {
    type: Object,
    required: true
  },
  profile: {
    type: Object,
    default: null
  },
  items: {
    type: Object,
    required: true
  },
  metaTitle: String,
  metaDescription: String
});

const getInitials = (name) => {
  if (!name) return 'RD';
  const words = name.replace(/[^A-Za-z0-9 ]/g, '').split(' ');
  let initials = '';
  for (const w of words) {
    if (w) {
      initials += w[0].toUpperCase();
    }
  }
  return initials ? initials.substring(0, 2) : 'RD';
};

const joinedDate = computed(() => {
  if (!props.author.created_at) return '';
  const date = new Date(props.author.created_at);
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  return `${months[date.getMonth()]} ${date.getFullYear()}`;
});
</script>

<template>
  <PublicLayout>
    <Head :title="metaTitle || author.name">
      <meta name="description" :content="metaDescription" />
    </Head>

    <div class="mb-6 px-4">
      <nav class="flex text-xs font-semibold uppercase tracking-wider text-on-surface-variant/75" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
          <li class="inline-flex items-center">
            <Link href="/" class="hover:text-primary transition-colors">Beranda</Link>
          </li>
          <li>
            <div class="flex items-center">
              <span class="mx-1 text-on-surface-variant/40">/</span>
              <span class="text-on-surface-variant hover:text-primary">Penulis</span>
            </div>
          </li>
          <li>
            <div class="flex items-center">
              <span class="mx-1 text-on-surface-variant/40">/</span>
              <span class="text-on-surface font-bold line-clamp-1">{{ author.name }}</span>
            </div>
          </li>
        </ol>
      </nav>
    </div>

    <!-- Author Profile Header -->
    <section class="liquid-glass dark:liquid-glass-dark rounded-3xl p-8 mb-12 flex flex-col md:flex-row items-center md:items-start gap-8 relative overflow-hidden shadow-sm mx-4 bg-surface-container-low dark:bg-surface-slate-900 border border-outline-variant/20 transition-all duration-300">
      <!-- Decorative blur element -->
      <div class="absolute -top-10 -left-10 w-40 h-40 bg-secondary-container rounded-full blur-[40px] opacity-35 dark:opacity-20 pointer-events-none"></div>
      
      <div class="relative shrink-0">
        <img v-if="author.avatar" class="w-32 h-32 md:w-40 md:h-40 rounded-full object-cover border-4 border-surface shadow-md" :src="author.avatar" :alt="author.name" />
        <div v-else class="w-32 h-32 md:w-40 md:h-40 rounded-full bg-primary/10 flex items-center justify-center text-primary text-5xl font-extrabold border-4 border-surface shadow-md">
          {{ getInitials(author.name) }}
        </div>
        <!-- Verification Badge -->
        <div class="absolute bottom-2 right-2 bg-primary text-white rounded-full p-1 shadow-sm border-2 border-surface flex items-center justify-center" title="Verified Author">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm-1 14.5l-4-4 1.5-1.5L11 13.5l6-6 1.5 1.5-7.5 7.5z"/>
          </svg>
        </div>
      </div>
      
      <div class="flex flex-col flex-grow text-center md:text-left z-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
          <div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-on-surface mb-1">{{ author.name }}</h1>
            <p class="text-sm text-primary dark:text-inverse-primary font-bold tracking-wide uppercase font-label-sm">Kontributor Simbiosis</p>
          </div>
          
          <!-- Social Links -->
          <div v-if="profile" class="flex gap-3 justify-center md:justify-start">
            <a v-if="profile.twitter" :href="profile.twitter" target="_blank" rel="noopener nofollow" class="w-10 h-10 rounded-full bg-surface-container dark:bg-slate-800 flex items-center justify-center text-on-surface hover:bg-primary dark:hover:bg-inverse-primary hover:text-white dark:hover:text-slate-900 transition-all duration-200" title="Twitter">
              <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                <path d="M18.9 2H22l-6.77 7.74L23.2 22h-6.25l-4.9-6.42L6.44 22H3.33l7.24-8.28L.8 2h6.4l4.43 5.86L18.9 2Z"/>
              </svg>
            </a>
            <a v-if="profile.instagram" :href="profile.instagram" target="_blank" rel="noopener nofollow" class="w-10 h-10 rounded-full bg-surface-container dark:bg-slate-800 flex items-center justify-center text-on-surface hover:bg-primary dark:hover:bg-inverse-primary hover:text-white dark:hover:text-slate-900 transition-all duration-200" title="Instagram">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect width="16" height="16" x="4" y="4" rx="4" stroke-width="2"/>
                <circle cx="12" cy="12" r="3" stroke-width="2"/>
                <path stroke-linecap="round" stroke-width="2" d="M16.5 7.5v.001"/>
              </svg>
            </a>
            <a v-if="profile.website" :href="profile.website" target="_blank" rel="noopener nofollow" class="w-10 h-10 rounded-full bg-surface-container dark:bg-slate-800 flex items-center justify-center text-on-surface hover:bg-primary dark:hover:bg-inverse-primary hover:text-white dark:hover:text-slate-900 transition-all duration-200" title="Website">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
              </svg>
            </a>
          </div>
        </div>
        
        <p v-if="profile && profile.bio" class="text-base text-on-surface-variant/90 max-w-3xl mb-6 leading-relaxed">
          {{ profile.bio }}
        </p>
        <p v-else class="text-base text-on-surface-variant/60 max-w-3xl mb-6 leading-relaxed italic">
          Belum ada bio yang ditambahkan.
        </p>
        
        <div class="flex flex-wrap items-center justify-center md:justify-start gap-6 text-sm text-on-surface-variant/85 border-t border-outline-variant/15 pt-4">
          <div class="flex items-center gap-1.5 font-label-sm">
            <svg class="w-4 h-4 text-on-surface-variant/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5L18.5 7H20"></path>
            </svg>
            <span><strong>{{ items.total }}</strong> Artikel</span>
          </div>
          <div class="flex items-center gap-1.5 font-label-sm">
            <svg class="w-4 h-4 text-on-surface-variant/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span>Bergabung {{ joinedDate }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Articles Section -->
    <section class="px-4">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-on-surface">Karya Terbaru</h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <NewsCard
          v-for="art in items.data"
          :key="art.id"
          :article="art"
          variant="grid"
        />

        <div v-if="!items.data || items.data.length === 0" class="col-span-1 sm:col-span-2 lg:col-span-3">
          <div class="liquid-glass rounded-xl p-8 text-center border-dashed border-2 border-outline-variant/30 text-on-surface-variant">
            Belum ada karya yang dipublikasikan.
          </div>
        </div>
      </div>

      <Pagination :links="items.links" />
    </section>
  </PublicLayout>
</template>
