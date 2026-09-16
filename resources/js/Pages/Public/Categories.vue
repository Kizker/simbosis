<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';

const props = defineProps({
  categories: {
    type: Array,
    default: () => []
  },
  metaTitle: String,
  metaDescription: String
});

const colors = [
  'bg-primary-container text-on-primary-container',
  'bg-tertiary-container text-white',
  'bg-secondary-container text-on-secondary-container',
  'bg-surface-variant text-on-surface-variant',
  'bg-error-container text-on-error-container',
  'bg-primary-fixed text-on-primary-fixed'
];

const icons = [
  '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>',
  '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>',
  '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>',
  '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
  '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>',
  '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>'
];

const getColorClass = (index) => colors[index % colors.length];
const getSvgPath = (index) => icons[index % icons.length];
</script>

<template>
  <PublicLayout>
    <Head :title="metaTitle || 'Kategori'">
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
              <span class="text-on-surface font-bold">Kategori</span>
            </div>
          </li>
        </ol>
      </nav>
    </div>

    <div class="mb-12 text-center md:text-left px-4">
      <h1 class="text-4xl md:text-5xl font-extrabold text-on-surface mb-4 tracking-tight">Direktori Kategori</h1>
      <p class="text-lg text-on-surface-variant max-w-2xl">
        Jelajahi berbagai topik berita terkini dan mendalam dari seluruh penjuru dunia melalui kanal-kanal khusus kami.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12 px-4">
      <Link
        v-for="(c, index) in categories"
        :key="c.id"
        :href="`/kategori/${c.slug}`"
        class="group block h-full"
      >
        <div class="liquid-glass dark:liquid-glass-dark rounded-2xl p-6 h-full flex flex-col justify-between transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg hover:shadow-primary/10 hover:border-primary/50 bg-surface-container-low dark:bg-surface-slate-900">
          <div>
            <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 shadow-sm" :class="getColorClass(index)">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="getSvgPath(index)"></svg>
            </div>
            <h2 class="text-2xl font-bold text-primary dark:text-inverse-primary mb-2 line-clamp-1">{{ c.name }}</h2>
            <p class="text-sm md:text-base text-on-surface-variant mb-6 line-clamp-2">
              {{ c.description || `Temukan artikel terbaru, analisis, dan informasi mendalam seputar ${c.name}.` }}
            </p>
          </div>
          <div class="flex items-center justify-between border-t border-outline-variant/10 pt-4 mt-auto">
            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider font-label-sm">{{ Number(c.published_count || 0).toLocaleString() }} Artikel</span>
            <svg class="w-5 h-5 text-primary dark:text-inverse-primary group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
          </div>
        </div>
      </Link>

      <div v-if="categories.length === 0" class="col-span-1 md:col-span-2 lg:col-span-3">
        <div class="liquid-glass rounded-xl p-8 text-center border-dashed border-2 border-outline-variant/30 text-on-surface-variant">
          Belum ada kategori aktif.
        </div>
      </div>
    </div>
  </PublicLayout>
</template>
