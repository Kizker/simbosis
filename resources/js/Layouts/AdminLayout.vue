<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Link, Head, usePage, router } from '@inertiajs/vue3';

import ToastNotification from '@/Components/ToastNotification.vue';

const props = defineProps({
  title: String
});

const page = usePage();

// Reactive shared props
const appName = computed(() => page.props.appName || 'Simbiosis CMS');
const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash || {});

// State
const sidebarOpen = ref(false);
const isDark = ref(false);

const roleLabel = computed(() => {
  if (!user.value) return 'Redaksi';
  return user.value.role || 'Administrator';
});

// Permissions check helper
const can = (permission) => {
  if (!user.value) return false;
  if (user.value.role === 'Superadmin') return true;
  
  if (permission === 'viewAnyArticle') {
    return user.value.permissions?.some(p => 
      ['articles.update_any', 'articles.update_own', 'articles.create', 'articles.review'].includes(p)
    );
  }
  
  return user.value.permissions?.includes(permission);
};

// Toggle theme method
const toggleTheme = () => {
  isDark.value = !isDark.value;
  document.documentElement.classList.toggle('dark', isDark.value);
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
};

// Check active state
const isRouteActive = (paths) => {
  const current = window.location.pathname;
  return paths.some(path => {
    if (path === '/harmony-access') {
      return current === '/harmony-access';
    }
    return current.startsWith(path);
  });
};

// Navigation layout
const navSections = computed(() => [
  {
    label: 'Konten',
    items: [
      { label: 'Dashboard', path: '/harmony-access', activePaths: ['/harmony-access'], can: true },
      { label: 'Artikel', path: '/harmony-access/articles', activePaths: ['/harmony-access/articles'], can: can('viewAnyArticle') },
      { label: 'Kategori', path: '/harmony-access/categories', activePaths: ['/harmony-access/categories'], can: can('categories.manage') },
      { label: 'Tag', path: '/harmony-access/tags', activePaths: ['/harmony-access/tags'], can: can('tags.manage') },
      { label: 'Media', path: '/harmony-access/media', activePaths: ['/harmony-access/media'], can: can('media.manage') },
    ].filter(item => item.can)
  },
  {
    label: 'Operasional',
    items: [
      { label: 'Iklan', path: '/harmony-access/ads', activePaths: ['/harmony-access/ads'], can: can('ads.manage') },
      { label: 'Komentar', path: '/harmony-access/comments', activePaths: ['/harmony-access/comments'], can: can('comments.moderate') },
      { label: 'Pesan', path: '/harmony-access/messages', activePaths: ['/harmony-access/messages'], can: can('messages.manage') },
      { 
        label: 'Settings', 
        path: '/harmony-access/settings', 
        activePaths: [
          '/harmony-access/settings', 
          '/harmony-access/seo', 
          '/harmony-access/sitemap', 
          '/harmony-access/pages', 
          '/harmony-access/about', 
          '/harmony-access/social'
        ], 
        can: can('site_settings.manage') 
      },
      { label: 'Users', path: '/harmony-access/users', activePaths: ['/harmony-access/users'], can: can('users.manage') },
      { label: 'Audit', path: '/harmony-access/audit-logs', activePaths: ['/harmony-access/audit-logs'], can: can('audit_logs.view') },
    ].filter(item => item.can)
  }
].filter(section => section.items.length > 0));

// Handle Sidebar Scroll
const navScroll = ref(null);
const persistScroll = () => {
  if (navScroll.value) {
    sessionStorage.setItem('admin-sidebar-scroll-top', String(navScroll.value.scrollTop || 0));
  }
};

const handleLogout = () => {
  if (confirm('Yakin ingin keluar?')) {
    router.post(route('logout'));
  }
};

onMounted(() => {
  const saved = localStorage.getItem('theme');
  const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)')?.matches;
  isDark.value = saved ? saved === 'dark' : !!prefersDark;
  document.documentElement.classList.toggle('dark', isDark.value);

  // Restore sidebar scroll
  const savedScroll = sessionStorage.getItem('admin-sidebar-scroll-top');
  if (savedScroll && navScroll.value) {
    navScroll.value.scrollTop = Number(savedScroll) || 0;
  }
});
</script>

<template>
  <Head :title="title ? `${title} - CMS` : 'Admin - Simbiosis CMS'" />

  <div class="flex min-h-screen bg-background text-on-background transition-colors duration-200">
    <!-- Backdrop (Mobile) -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-30 bg-slate-950/45 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <!-- Sidebar navigation -->
    <aside
      class="bg-slate-900 dark:bg-black text-slate-300 fixed inset-y-0 left-0 z-40 flex w-[88vw] max-w-[19rem] flex-col transition-transform lg:sticky lg:top-0 lg:h-screen lg:w-[16.5rem] lg:max-w-none"
      :class="{
        '-translate-x-full': !sidebarOpen,
        'translate-x-0': sidebarOpen,
        'lg:translate-x-0': true
      }"
    >
      <!-- Logo brand bar -->
      <div class="flex items-center gap-3 px-4 border-b border-white/10 h-[72px]">
        <Link href="/harmony-access" class="flex min-w-0 items-center gap-3 self-stretch" @click="sidebarOpen = false">
          <img class="h-8" :src="'/brand/simbiosis-logo.png'" alt="" aria-hidden="true">
          <span class="flex min-w-0 flex-col justify-center leading-tight">
            <span class="block truncate text-[0.95rem] font-bold text-white">Simbiosis CMS</span>
            <span class="mt-1 block truncate text-xs text-slate-400 font-mono">{{ roleLabel }}</span>
          </span>
        </Link>

        <button
          type="button"
          class="ml-auto text-slate-400 hover:text-white lg:hidden p-1 rounded-lg border border-white/10"
          @click="sidebarOpen = false"
          aria-label="Tutup menu"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Navigation links -->
      <div
        ref="navScroll"
        class="flex-1 overflow-y-auto px-3 py-5"
        @scroll="persistScroll"
      >
        <div v-for="section in navSections" :key="section.label" class="mb-5">
          <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">
            {{ section.label }}
          </div>

          <div class="grid gap-1">
            <Link
              v-for="item in section.items"
              :key="item.path"
              :href="item.path"
              class="block px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150 ease-in-out"
              :class="isRouteActive(item.activePaths) 
                ? 'bg-primary/20 text-inverse-primary border border-primary/20' 
                : 'text-slate-300 hover:bg-white/5 hover:text-white border border-transparent'"
              @click="sidebarOpen = false"
            >
              <span class="block truncate">{{ item.label }}</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- Active account label -->
      <div class="border-t border-white/10 px-3 py-4">
        <div class="px-3 py-2">
          <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Akun Aktif</div>
          <div class="mt-1 truncate text-sm font-semibold text-white">{{ user?.name || 'Admin' }}</div>
          <div class="truncate text-xs text-slate-400 font-mono">{{ user?.email }}</div>
        </div>
      </div>
    </aside>

    <!-- Content shell -->
    <div class="flex min-w-0 flex-1 flex-col">
      <header class="sticky top-0 z-20 bg-surface/85 dark:bg-surface-slate-900/80 backdrop-blur-[20px] backdrop-saturate-150 border-b border-outline-variant/20 shadow-sm transition-all duration-200 ease-in-out">
        <div class="max-w-7xl mx-auto w-full flex items-center justify-between h-[72px] px-6">
          <div class="flex items-center gap-4">
            <button
              type="button"
              class="text-on-surface-variant hover:text-primary lg:hidden p-1.5 rounded-lg hover:bg-surface-variant/20 transition-all border border-outline-variant/20"
              @click="sidebarOpen = true"
              aria-label="Buka menu"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
              </svg>
            </button>

            <Link href="/harmony-access" class="lg:hidden shrink-0" aria-label="Simbiosis CMS">
              <img class="h-8" :src="'/brand/simbiosis-logo.png'" alt="" aria-hidden="true">
            </Link>

            <div class="admin-page-heading">
              <h1 class="text-xl font-bold text-on-surface tracking-tight">
                {{ title || 'Admin' }}
              </h1>
            </div>
          </div>

          <div class="flex items-center gap-4">
            <Link
              v-if="can('articles.create')"
              class="hidden lg:inline-flex bg-primary text-white hover:bg-primary/95 px-5 py-2 rounded-full font-semibold transition-all duration-200 ease-in-out text-sm shadow-sm hover:scale-[1.02]"
              href="/harmony-access/articles/create"
            >
              + Buat artikel
            </Link>

            <!-- Theme toggle -->
            <button
              type="button"
              @click="toggleTheme"
              aria-label="Toggle dark mode"
              class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant hover:text-primary hover:bg-primary/10 border border-outline-variant/20 bg-surface/50 transition-all duration-150"
            >
              <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-[1.15rem] h-[1.15rem]">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-[1.15rem] h-[1.15rem]">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21M4.93 4.93l1.59 1.59m10.96 10.96l1.59 1.59M3 12h2.25m13.5 0H21M6.52 17.48l-1.59 1.59m12.96-12.96l-1.59 1.59M12 7.5a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9Z" />
              </svg>
            </button>


            
            <button
              @click="handleLogout"
              class="text-on-surface-variant hover:text-error transition-all duration-150 flex items-center gap-1.5 font-semibold text-sm p-2 hover:bg-error/10 rounded-lg"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
              </svg>
              <span class="hidden lg:inline">Logout</span>
            </button>
          </div>
        </div>
      </header>

      <!-- Main Admin Content Area -->
      <main class="w-full max-w-7xl mx-auto my-5 px-6 flex-1 min-w-0 lg:my-6">
        <slot />
      </main>

      <footer class="mt-auto border-t border-outline-variant/15 py-4 text-xs text-on-surface-variant/60">
        <div class="max-w-7xl mx-auto px-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <span>Hak Cipta CV. Satu Harmony</span>
          <span class="hidden sm:inline">Simbiosis CMS | workspace redaksi</span>
        </div>
      </footer>
    </div>

    <!-- Global Toast Notification -->
    <ToastNotification />
  </div>
</template>
