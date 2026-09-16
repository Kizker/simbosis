<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, Head, usePage } from '@inertiajs/vue3';
import ToastNotification from '@/Components/ToastNotification.vue';

const page = usePage();

// Shared props from HandleInertiaRequests middleware
const appName = computed(() => page.props.appName || 'Simbiosis News');
const categories = computed(() => page.props.headerCategories || []);
const social = computed(() => page.props.social || {});
const flash = computed(() => page.props.flash || {});

// State
const isDark = ref(false);
const mobileMenuOpen = ref(false);
const searchQuery = ref('');
const searchOpen = ref(false);
const searchLoading = ref(false);
const searchItems = ref([]);
let searchAbortController = null;

// Methods
const toggleTheme = () => {
  isDark.value = !isDark.value;
  document.documentElement.classList.toggle('dark', isDark.value);
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
};

const handleLiveSearch = async () => {
  const q = searchQuery.value.trim();
  if (q.length < 2) {
    searchItems.value = [];
    searchOpen.value = false;
    return;
  }

  if (searchAbortController) {
    searchAbortController.abort();
  }
  searchAbortController = new AbortController();
  searchLoading.value = true;
  searchOpen.value = true;

  try {
    const url = new URL('/cari/live', window.location.origin);
    url.searchParams.set('q', q);
    const response = await fetch(url, {
      headers: { Accept: 'application/json' },
      signal: searchAbortController.signal,
    });
    const data = await response.json();
    searchItems.value = Array.isArray(data.items) ? data.items : [];
  } catch (error) {
    if (error.name !== 'AbortError') {
      searchItems.value = [];
    }
  } finally {
    searchLoading.value = false;
  }
};

const closeSearch = () => {
  setTimeout(() => {
    searchOpen.value = false;
  }, 200);
};

const preventImageDownload = (e) => {
  if (e.target && e.target.tagName && e.target.tagName.toLowerCase() === 'img') {
    e.preventDefault();
  }
};

onMounted(() => {
  const saved = localStorage.getItem('theme');
  const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)')?.matches;
  isDark.value = saved ? saved === 'dark' : !!prefersDark;
  document.documentElement.classList.toggle('dark', isDark.value);

  document.addEventListener('contextmenu', preventImageDownload);
  document.addEventListener('dragstart', preventImageDownload);
});

onUnmounted(() => {
  document.removeEventListener('contextmenu', preventImageDownload);
  document.removeEventListener('dragstart', preventImageDownload);
});
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background text-on-background transition-colors duration-200">
    <header class="bg-[#0b1325]/90 backdrop-blur-[20px] sticky top-0 w-full h-[72px] border-b border-white/10 backdrop-saturate-150 shadow-sm z-50 transition-colors duration-200">
      <div class="flex items-center justify-between px-6 w-full max-w-7xl mx-auto h-full">
        <div class="flex items-center gap-8">
          <Link href="/" class="font-display-lg text-[#00f0ff] tracking-tight font-bold text-2xl" :aria-label="appName">
            <img class="h-8 md:hidden" :src="'/brand/simbiosis-putih.png'" alt="" aria-hidden="true" />
            <img class="h-8 hidden md:block" :src="'/brand/simbiosis-putih.png'" :alt="appName" />
          </Link>
          <nav class="hidden md:flex gap-6">
            <Link class="text-slate-300 hover:text-white transition-all duration-200 ease-in-out hover:bg-white/5 rounded-lg px-2.5 py-1 text-sm font-semibold uppercase tracking-wide" href="/">Home</Link>
            <Link class="text-slate-300 hover:text-white transition-all duration-200 ease-in-out hover:bg-white/5 rounded-lg px-2.5 py-1 text-sm font-semibold uppercase tracking-wide" href="/semua-berita">Semua Berita</Link>
            <Link class="text-slate-300 hover:text-white transition-all duration-200 ease-in-out hover:bg-white/5 rounded-lg px-2.5 py-1 text-sm font-semibold uppercase tracking-wide" href="/tentang-kami">Tentang</Link>
            <Link class="text-slate-300 hover:text-white transition-all duration-200 ease-in-out hover:bg-white/5 rounded-lg px-2.5 py-1 text-sm font-semibold uppercase tracking-wide" href="/kontak">Kontak</Link>
          </nav>
        </div>

        <div class="flex items-center gap-4">
          <!-- Live Search Bar -->
          <form
            action="/cari"
            method="get"
            class="hidden md:flex items-center bg-white/10 rounded-full px-4 py-1.5 border border-white/20 focus-within:border-[#00f0ff] focus-within:ring-1 focus-within:ring-[#00f0ff] transition-all duration-200 ease-in-out relative"
            @submit="searchQuery.trim().length < 2 ? $event.preventDefault() : null"
          >
            <input
              name="q"
              v-model="searchQuery"
              @input="handleLiveSearch"
              @focus="handleLiveSearch"
              @blur="closeSearch"
              autocomplete="off"
              class="bg-transparent border-none focus:ring-0 text-sm w-48 placeholder-white/50 outline-none text-white"
              placeholder="Cari berita..."
              type="text"
            />
            <button type="submit" class="text-white/70 hover:text-[#00f0ff] transition-all duration-200 ease-in-out">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </button>
            
            <!-- Live Search Dropdown -->
            <div v-if="searchOpen" class="absolute top-[50px] right-0 w-[320px] bg-surface dark:bg-slate-900 border border-outline-variant/30 rounded-2xl shadow-xl overflow-hidden z-50">
              <div v-if="searchLoading" class="p-4 text-sm text-center text-on-surface-variant">Mencari...</div>
              <div v-else-if="searchQuery.trim().length >= 2 && searchItems.length === 0" class="p-4 text-sm text-center text-on-surface-variant">Tidak ada hasil.</div>
              <div v-else class="max-h-[300px] overflow-y-auto">
                <Link
                  v-for="item in searchItems"
                  :key="item.url"
                  :href="item.url"
                  class="block p-3 hover:bg-surface-container dark:hover:bg-slate-800 transition-all duration-200 ease-in-out border-b border-outline-variant/10 last:border-0"
                >
                  <span class="block text-sm font-semibold text-on-surface line-clamp-1">{{ item.title }}</span>
                  <span class="block text-[10px] text-on-surface-variant/70 mt-1 font-label-sm">
                    <span>{{ item.category || 'Berita' }}</span>
                    <span v-if="item.published"> - </span>
                    <span>{{ item.published }}</span>
                  </span>
                </Link>
              </div>
            </div>
          </form>

          <!-- Theme Toggle Button -->
          <button
            type="button"
            class="text-white/80 hover:text-white transition-all duration-200 ease-in-out hover:scale-[1.05] p-2 hover:bg-white/10 rounded-full flex items-center justify-center"
            @click="toggleTheme"
            aria-label="Ubah mode warna"
            title="Ubah mode warna"
          >
            <svg v-if="isDark" class="w-5 h-5 stroke-current text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="5"></circle>
              <line x1="12" y1="1" x2="12" y2="3"></line>
              <line x1="12" y1="21" x2="12" y2="23"></line>
              <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
              <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
              <line x1="1" y1="12" x2="3" y2="12"></line>
              <line x1="21" y1="12" x2="23" y2="12"></line>
              <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
              <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <svg v-else class="w-5 h-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
          </button>

          <!-- Mobile Menu Toggle -->
          <button
            type="button"
            class="md:hidden text-white/80 hover:text-white transition-all duration-200 ease-in-out p-2 hover:bg-white/10 rounded-full flex items-center justify-center"
            @click="mobileMenuOpen = !mobileMenuOpen"
            aria-expanded="false"
            aria-label="Buka menu"
          >
            <svg class="w-5 h-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="3" y1="12" x2="21" y2="12"></line>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Nav Panel -->
      <div v-if="mobileMenuOpen" class="md:hidden absolute top-full left-0 w-full bg-surface dark:bg-slate-900 border-b border-outline-variant/20 shadow-lg z-50">
        <div class="px-6 py-4 grid gap-4 text-sm">
          <form
            action="/cari"
            method="get"
            class="flex items-center bg-surface-container rounded-full px-4 py-2 border border-outline-variant/30 focus-within:border-primary transition-all duration-200 ease-in-out"
          >
            <input name="q" class="bg-transparent border-none focus:ring-0 text-sm w-full placeholder-on-surface-variant/50 outline-none text-on-surface" placeholder="Cari berita..." type="text"/>
          </form>
          <div class="grid grid-cols-2 gap-2">
            <Link @click="mobileMenuOpen = false" class="bg-surface-container hover:bg-surface-container-high rounded-lg px-4 py-3 font-semibold text-center transition-all duration-200 ease-in-out" href="/">Home</Link>
            <Link @click="mobileMenuOpen = false" class="bg-surface-container hover:bg-surface-container-high rounded-lg px-4 py-3 font-semibold text-center transition-all duration-200 ease-in-out" href="/semua-berita">Semua Berita</Link>
            <Link @click="mobileMenuOpen = false" class="bg-surface-container hover:bg-surface-container-high rounded-lg px-4 py-3 font-semibold text-center transition-all duration-200 ease-in-out" href="/tentang-kami">Tentang</Link>
            <Link @click="mobileMenuOpen = false" class="bg-surface-container hover:bg-surface-container-high rounded-lg px-4 py-3 font-semibold text-center transition-all duration-200 ease-in-out" href="/kontak">Kontak</Link>
          </div>
        </div>
      </div>
    </header>

    <main class="w-full max-w-7xl mx-auto px-6 flex-grow my-4 pt-2">
      <!-- Header Ad Slot -->
      <div v-if="$page.props.ads?.header?.[0]" class="w-full mb-8">
        <a :href="$page.props.ads.header[0].target_url" target="_blank" rel="noopener sponsored" class="block w-full aspect-[6/1] rounded-2xl overflow-hidden shadow-sm hover:opacity-90 transition-opacity">
          <img :src="$page.props.ads.header[0].image_path.startsWith('http') ? $page.props.ads.header[0].image_path : `/storage/${$page.props.ads.header[0].image_path}`" :alt="$page.props.ads.header[0].title" class="w-full h-full object-cover object-center" />
        </a>
      </div>

      <!-- Main Slots Content -->
      <slot />
      
      <!-- Footer Ad Slot -->
      <div v-if="$page.props.ads?.footer?.[0]" class="w-full mt-12">
        <a :href="$page.props.ads.footer[0].target_url" target="_blank" rel="noopener sponsored" class="block w-full aspect-[6/1] rounded-2xl overflow-hidden shadow-sm hover:opacity-90 transition-opacity">
          <img :src="$page.props.ads.footer[0].image_path.startsWith('http') ? $page.props.ads.footer[0].image_path : `/storage/${$page.props.ads.footer[0].image_path}`" :alt="$page.props.ads.footer[0].title" class="w-full h-full object-cover object-center" />
        </a>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-[#0b1325] dark:bg-[#060a14] w-full mt-16 pt-10 pb-8 px-6 flex flex-col items-center justify-center transition-colors duration-200 border-t border-white/5 dark:border-white/10">
      <div class="max-w-7xl mx-auto w-full flex flex-col items-center justify-center">
        <!-- Centered Logo -->
        <Link href="/" class="flex-shrink-0 mb-8" :aria-label="appName">
          <img class="h-[42px] mx-auto object-contain" :src="'/brand/simbiosis-putih.png'" :alt="appName" />
        </Link>

        <!-- Centered Navigation -->
        <nav class="flex flex-wrap justify-center gap-x-8 gap-y-2 text-[12px] mb-5" aria-label="Navigasi footer">
          <Link class="text-slate-400 hover:text-white dark:!text-white/90 dark:hover:!text-white transition-colors duration-200 font-normal tracking-wide" href="/tentang-kami">Tentang Kami</Link>
          <Link class="text-slate-400 hover:text-white dark:!text-white/90 dark:hover:!text-white transition-colors duration-200 font-normal tracking-wide" href="#">Redaksi</Link>
          <Link class="text-slate-400 hover:text-white dark:!text-white/90 dark:hover:!text-white transition-colors duration-200 font-normal tracking-wide" href="#">Pedoman Siber</Link>
          <Link class="text-slate-400 hover:text-white dark:!text-white/90 dark:hover:!text-white transition-colors duration-200 font-normal tracking-wide" href="#">Karir</Link>
          <Link class="text-slate-400 hover:text-white dark:!text-white/90 dark:hover:!text-white transition-colors duration-200 font-normal tracking-wide" href="/kontak">Kontak</Link>
        </nav>

        <!-- Centered Copyright -->
        <p class="text-[11px] text-slate-500 dark:!text-white/60 tracking-wider text-center">
          © 2024 Hak Cipta CV. Satu Harmony
        </p>
      </div>
    </footer>
    <ToastNotification />
  </div>
</template>

<style>
/* Prevent image drag and selection globally within the public layout */
img {
  -webkit-user-drag: none;
  -khtml-user-drag: none;
  -moz-user-drag: none;
  -o-user-drag: none;
  user-drag: none;
  -webkit-touch-callout: none;
  user-select: none;
}
</style>
