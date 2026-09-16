<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import NewsCard from '../../Components/NewsCard.vue';
import { formatIndonesianDate } from '../../utils';

const props = defineProps({
  breaking: {
    type: Array,
    default: () => []
  },
  latest: {
    type: Array,
    default: () => []
  },
  featured: {
    type: Array,
    default: () => []
  },
  heroSlides: {
    type: Array,
    default: () => []
  },
  homeCategoryColumns: {
    type: Array,
    default: () => []
  },
  sidebarAdHtml: {
    type: String,
    default: ''
  }
});

// Build slides
const slides = computed(() => {
  const lead = props.breaking?.[0] || props.latest?.[0];
  const list = [];
  if (props.heroSlides && props.heroSlides.length > 0) list.push(...props.heroSlides);
  if (lead) list.push(lead);
  if (props.featured && props.featured.length > 0) list.push(...props.featured);

  const uniqueList = [];
  const ids = new Set();
  for (const item of list) {
    if (item && !ids.has(item.id)) {
      ids.add(item.id);
      uniqueList.push(item);
    }
  }

  return uniqueList.slice(0, 8).map(a => ({
    id: a.id,
    title: a.title,
    excerpt: a.excerpt || a.body_excerpt || '',
    url: `/artikel/${a.slug}`,
    category: a.category?.name || 'Berita',
    published_at: formatIndonesianDate(a.published_at_diff || a.published_at),
    image: a.cover_image_path ? (a.cover_image_path.startsWith('http') ? a.cover_image_path : `/storage/${a.cover_image_path}`) : '/brand/fallback-image.png'
  }));
});

const currentSlide = ref(0);
const sliderInterval = ref(null);

const startSlider = () => {
  stopSlider();
  if (slides.value.length > 1) {
    sliderInterval.value = setInterval(() => {
      currentSlide.value = (currentSlide.value + 1) % slides.value.length;
    }, 5000);
  }
};

const stopSlider = () => {
  if (sliderInterval.value) {
    clearInterval(sliderInterval.value);
    sliderInterval.value = null;
  }
};

const prevSlide = () => {
  if (slides.value.length === 0) return;
  currentSlide.value = (currentSlide.value - 1 + slides.value.length) % slides.value.length;
};

const nextSlide = () => {
  if (slides.value.length === 0) return;
  currentSlide.value = (currentSlide.value + 1) % slides.value.length;
};

// Daily News calculation
const leadArticle = computed(() => props.breaking?.[0] || props.latest?.[0]);
const dailyRest = computed(() => props.latest ? props.latest.slice(1, 5) : []);

// Sidebar Articles: Combine featured and fallback to latest unique articles to fill space
const sidebarArticles = computed(() => {
  const list = [...props.featured];
  if (list.length < 6 && props.latest) {
    const excludeIds = new Set([
      ...list.map(a => a.id),
      leadArticle.value?.id,
      ...dailyRest.value.map(a => a.id)
    ].filter(Boolean));
    for (const art of props.latest) {
      if (!excludeIds.has(art.id)) {
        list.push(art);
        excludeIds.add(art.id);
      }
      if (list.length >= 6) break;
    }
  }
  return list.slice(0, 6);
});

onMounted(() => {
  startSlider();
});

onUnmounted(() => {
  stopSlider();
});
</script>

<template>
  <PublicLayout>
    <Head title="Berita Terbaru" />

    <div class="flex-grow w-full pt-2 pb-8 space-y-8">
      
      <section
        v-if="slides.length > 0"
        class="w-full relative aspect-[4/5] sm:aspect-[4/3] md:aspect-video lg:h-[614px] lg:aspect-auto rounded-3xl overflow-hidden group shadow-lg"
        @mouseenter="stopSlider"
        @mouseleave="startSlider"
      >
        <div class="absolute inset-0 overflow-hidden">
          <div
            class="flex h-full w-full transition-transform duration-700 ease-in-out"
            :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
          >
            <div
              v-for="slide in slides"
              :key="slide.id"
              class="relative h-full w-full shrink-0"
            >
              <img class="absolute inset-0 h-full w-full object-cover" :src="slide.image" :alt="slide.title" />
              <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/40 to-transparent"></div>
              <div class="absolute bottom-0 left-0 w-full p-6 md:p-12 z-10 flex flex-col justify-end">
                <div class="flex items-center gap-3 mb-4">
                  <span class="bg-secondary-container text-on-secondary-container text-xs px-3 py-1 rounded-full uppercase tracking-wider font-bold shadow-sm">{{ slide.category }}</span>
                  <span class="text-white/80 text-sm flex items-center gap-1 font-label-sm">{{ slide.published_at }}</span>
                </div>
                <Link class="text-2xl md:text-5xl font-bold text-white mb-4 max-w-3xl leading-tight hover:underline" :href="slide.url">
                  {{ slide.title }}
                </Link>
                <p v-if="slide.excerpt" class="hidden md:block text-base text-white/90 max-w-2xl line-clamp-2">
                  {{ slide.excerpt }}
                </p>
              </div>
            </div>
          </div>
          
          <!-- Slider Navigation Arrows -->
          <div class="absolute top-1/2 -translate-y-1/2 left-4 md:left-8 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <button @click="prevSlide" aria-label="Sebelumnya" class="liquid-glass-dark text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-white/20 transition-colors backdrop-blur-md">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
          </div>
          <div class="absolute top-1/2 -translate-y-1/2 right-4 md:right-8 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <button @click="nextSlide" aria-label="Berikutnya" class="liquid-glass-dark text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-white/20 transition-colors backdrop-blur-md">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
          </div>
        </div>
      </section>

      <!-- Bento Grid Layout -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-stretch">
        
        <!-- Left Column: Socials, Ads & Sorotan (4 cols) -->
        <div class="md:col-span-4 flex flex-col gap-6">
          
          <!-- Ad Slot (Moved to Top of Sidebar) -->
          <a v-if="$page.props.ads?.sidebar_top?.[0]" :href="$page.props.ads.sidebar_top[0].target_url" target="_blank" rel="noopener sponsored" class="block rounded-2xl overflow-hidden shadow-sm hover:opacity-90 transition-opacity">
            <img :src="$page.props.ads.sidebar_top[0].image_path.startsWith('http') ? $page.props.ads.sidebar_top[0].image_path : `/storage/${$page.props.ads.sidebar_top[0].image_path}`" :alt="$page.props.ads.sidebar_top[0].title" class="w-full h-auto object-cover" />
          </a>

          <!-- Sorotan Hari Ini -->
          <div class="liquid-glass dark:liquid-glass-dark rounded-2xl p-6 shadow-sm flex-grow flex flex-col">
            <div class="flex items-center justify-between mb-4 border-b border-outline-variant/20 pb-2">
              <h2 class="text-lg font-bold text-on-surface">Sorotan Hari Ini</h2>
            </div>
            <div class="flex flex-col gap-4 flex-grow justify-between">
              <NewsCard
                v-for="art in sidebarArticles"
                :key="art.id"
                :article="art"
                variant="list"
              />
            </div>
          </div>

          <!-- Ad Slot (Bottom of Sidebar) -->
          <a v-if="$page.props.ads?.sidebar_bottom?.[0]" :href="$page.props.ads.sidebar_bottom[0].target_url" target="_blank" rel="noopener sponsored" class="block rounded-2xl overflow-hidden shadow-sm hover:opacity-90 transition-opacity">
            <img :src="$page.props.ads.sidebar_bottom[0].image_path.startsWith('http') ? $page.props.ads.sidebar_bottom[0].image_path : `/storage/${$page.props.ads.sidebar_bottom[0].image_path}`" :alt="$page.props.ads.sidebar_bottom[0].title" class="w-full h-auto object-cover" />
          </a>
        </div>

        <!-- Right Column: Latest News & Category Columns (8 cols) -->
        <div class="md:col-span-8 flex flex-col gap-6">
          <div class="flex items-center justify-between border-b border-outline-variant/20 pb-2">
            <h2 class="text-2xl font-bold text-on-surface">Berita Terkini</h2>
          </div>

          <!-- Lead Large Card -->
          <div v-if="leadArticle" class="w-full">
            <NewsCard :article="leadArticle" variant="featured" />
          </div>

          <!-- In-Article Ad (Sejajar dengan Sorotan) -->
          <div v-if="$page.props.ads?.home_banner?.[0]" class="w-full">
            <a :href="$page.props.ads.home_banner[0].target_url" target="_blank" rel="noopener sponsored" class="block w-full rounded-2xl overflow-hidden shadow-sm hover:opacity-90 transition-opacity">
              <img :src="$page.props.ads.home_banner[0].image_path.startsWith('http') ? $page.props.ads.home_banner[0].image_path : `/storage/${$page.props.ads.home_banner[0].image_path}`" :alt="$page.props.ads.home_banner[0].title" class="w-full h-auto object-cover max-h-[150px]" />
            </a>
          </div>

          <!-- Latest Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <NewsCard
              v-for="art in dailyRest"
              :key="art.id"
              :article="art"
              variant="grid"
            />
          </div>

          <!-- Category Columns -->
          <section v-if="homeCategoryColumns && homeCategoryColumns.length > 0" class="mt-8 pt-8 border-t border-outline-variant/10 flex-grow">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 h-full">
              <div
                v-for="category in homeCategoryColumns"
                :key="category.id"
                class="flex flex-col gap-6"
              >
                <div class="flex items-center justify-between border-b border-outline-variant/20 pb-2">
                  <Link :href="`/kategori/${category.slug}`" class="text-xl font-bold text-on-surface hover:text-primary dark:hover:text-inverse-primary transition-colors flex items-center gap-1">
                    {{ category.name }}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                  </Link>
                </div>
                
                <!-- Category Main Article -->
                <div v-if="category.home_articles && category.home_articles[0]" class="w-full">
                  <NewsCard :article="category.home_articles[0]" variant="featured-compact" />
                </div>
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>
