<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import NewsCard from '../../Components/NewsCard.vue';
import Pagination from '../../Components/Pagination.vue';
import FormInput from '../../Components/FormInput.vue';
import BaseButton from '../../Components/BaseButton.vue';

const props = defineProps({
  heading: String,
  items: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  breadcrumbs: {
    type: Array,
    default: () => []
  },
  metaTitle: String,
  metaDescription: String
});

const page = usePage();
const sortOption = ref(props.filters.sort || 'latest');
const dropdownOpen = ref(false);

const currentSortLabel = computed(() => {
  if (sortOption.value === 'latest') return 'Terbaru';
  if (sortOption.value === 'popular') return 'Terpopuler';
  if (sortOption.value.startsWith('cat:')) {
    const slug = sortOption.value.replace('cat:', '');
    const category = page.props.headerCategories.find(c => c.slug === slug);
    return category ? 'Kategori: ' + category.name : 'Kategori';
  }
  return 'Terbaru';
});

const selectOption = (val) => {
  sortOption.value = val;
  dropdownOpen.value = false;
  submitFilters();
};

const submitFilters = () => {
  if (sortOption.value.startsWith('cat:')) {
    const slug = sortOption.value.replace('cat:', '');
    router.get(`/kategori/${slug}`);
  } else {
    const params = {
      sort: sortOption.value
    };
    
    if (props.filters.category) params.category = props.filters.category;
    if (props.filters.tag) params.tag = props.filters.tag;

    router.get(window.location.pathname, params, {
      preserveState: true,
      replace: true
    });
  }
};
</script>

<template>
  <PublicLayout>
    <Head :title="metaTitle || heading">
      <meta name="description" :content="metaDescription" />
    </Head>

    <div class="mb-6 px-4 flex justify-center md:justify-start">
      <nav class="flex text-xs font-semibold uppercase tracking-wider text-on-surface-variant/75 text-center" aria-label="Breadcrumb">
        <ol class="inline-flex flex-wrap justify-center items-center space-x-1 md:space-x-2">
          <li v-for="(item, idx) in breadcrumbs" :key="idx" class="inline-flex items-center">
            <span v-if="idx > 0" class="mx-1 text-on-surface-variant/40">/</span>
            <Link v-if="item.href" :href="item.href" class="hover:text-primary transition-colors">{{ item.label }}</Link>
            <span v-else class="text-on-surface font-bold">{{ item.label }}</span>
          </li>
        </ol>
      </nav>
    </div>

    <!-- Heading & Filters Bar -->
    <div class="flex flex-col items-center md:flex-row justify-between gap-5 md:gap-6 mb-8 px-4 text-center md:text-left">
      <div>
        <h1 class="text-2xl md:text-4xl font-extrabold text-on-surface leading-tight tracking-tight">{{ heading }}</h1>
      </div>

      <!-- Filters Custom Dropdown -->
      <div class="relative z-40 w-full md:w-auto">
        <button 
          type="button" 
          @click="dropdownOpen = !dropdownOpen" 
          class="flex w-full md:w-auto items-center justify-between min-w-[160px] max-w-full gap-3 rounded-xl bg-surface-container-low dark:bg-surface-slate-900 border border-outline-variant/30 px-4 py-2.5 text-sm font-medium text-on-surface hover:border-primary/50 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all duration-200 outline-none"
        >
          <span>{{ currentSortLabel }}</span>
          <svg class="w-4 h-4 text-on-surface-variant transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': dropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- Full screen overlay to close dropdown -->
        <div v-if="dropdownOpen" @click="dropdownOpen = false" class="fixed inset-0 z-40 cursor-default"></div>

        <!-- Dropdown Panel -->
        <div v-if="dropdownOpen" class="absolute left-1/2 -translate-x-1/2 md:left-auto md:translate-x-0 md:right-0 mt-2 w-full md:w-56 bg-surface dark:bg-slate-900 border border-outline-variant/30 rounded-xl shadow-xl z-50 overflow-hidden py-2 transform origin-top md:origin-top-right transition-all duration-200">
          <!-- Sort Options -->
          <button 
            type="button" 
            @click="selectOption('latest')" 
            class="w-full flex items-center px-4 py-2 text-sm hover:bg-primary/10 hover:text-primary transition-colors duration-150"
            :class="sortOption === 'latest' ? 'text-primary font-bold bg-primary/5' : 'text-on-surface'"
          >
            <span class="flex-grow text-left">Terbaru</span>
            <svg v-if="sortOption === 'latest'" class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          </button>
          
          <button 
            type="button" 
            @click="selectOption('popular')" 
            class="w-full flex items-center px-4 py-2 text-sm hover:bg-primary/10 hover:text-primary transition-colors duration-150"
            :class="sortOption === 'popular' ? 'text-primary font-bold bg-primary/5' : 'text-on-surface'"
          >
            <span class="flex-grow text-left">Terpopuler</span>
            <svg v-if="sortOption === 'popular'" class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          </button>

          <div class="px-4 py-2 mt-2 mb-1 border-t border-outline-variant/20 flex items-center">
            <span class="text-[10px] font-bold tracking-wider text-on-surface-variant/70 uppercase">Kategori</span>
          </div>

          <div class="max-h-[240px] overflow-y-auto scrollbar-thin scrollbar-thumb-outline-variant/30 scrollbar-track-transparent">
            <button 
              type="button" 
              v-for="cat in $page.props.headerCategories" 
              :key="cat.id"
              @click="selectOption('cat:' + cat.slug)" 
              class="w-full flex items-center px-4 py-2 text-sm hover:bg-primary/10 hover:text-primary transition-colors duration-150"
              :class="sortOption === ('cat:' + cat.slug) ? 'text-primary font-bold bg-primary/5' : 'text-on-surface'"
            >
              <span class="flex-grow text-left">{{ cat.name }}</span>
              <svg v-if="sortOption === ('cat:' + cat.slug)" class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Grid List -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12 px-4">
      <NewsCard
        v-for="item in items.data"
        :key="item.id"
        :article="item"
        variant="grid"
      />

      <div v-if="!items.data || items.data.length === 0" class="col-span-1 sm:col-span-2 lg:col-span-3">
        <div class="liquid-glass rounded-xl p-8 text-center border-dashed border-2 border-outline-variant/30 text-on-surface-variant/70">
          Tidak ada berita ditemukan.
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div class="px-4">
      <Pagination :links="items.links" />
    </div>
  </PublicLayout>
</template>
