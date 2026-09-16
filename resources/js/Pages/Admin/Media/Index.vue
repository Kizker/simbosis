<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  media: {
    type: Object,
    required: true
  },
  search: String
});

const searchQuery = ref(props.search || '');

const handleSearch = () => {
  const params = {};
  if (searchQuery.value.trim()) params.search = searchQuery.value.trim();
  
  router.get(route('admin.media.index'), params, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    only: ['media', 'search']
  });
};

let searchTimeout;
watch(searchQuery, (newVal) => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    handleSearch();
  }, 300);
});

watch(() => props.search, (newVal) => {
  if (newVal !== searchQuery.value) {
    searchQuery.value = newVal || '';
  }
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const hasPermission = (permission) => {
  if (!user.value) return false;
  if (user.value.role === 'Superadmin') return true;
  return user.value.permissions?.includes(permission);
};

// Form upload state
const fileInput = ref(null);
const uploadLoading = ref(false);
const uploadError = ref('');

const handleUpload = async (e) => {
  const file = e.target.files[0];
  if (!file) return;

  uploadLoading.value = true;
  uploadError.value = '';

  const formData = new FormData();
  formData.append('file', file);

  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const response = await fetch(route('admin.media.store'), {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token || '',
        'Accept': 'application/json'
      },
      body: formData
    });

    if (!response.ok) {
      throw new Error(`Upload gagal (${response.status})`);
    }

    // Refresh media gallery lists
    router.reload({
      only: ['media'],
      onSuccess: () => {
        if (fileInput.value) fileInput.value.value = '';
        alert('File media berhasil diunggah.');
      }
    });
  } catch (err) {
    uploadError.value = err.message || 'Terjadi kesalahan saat mengunggah.';
  } finally {
    uploadLoading.value = false;
  }
};

const handleDelete = (id, path) => {
  if (confirm(`Yakin ingin menghapus gambar ini?\n${path}`)) {
    router.delete(route('admin.media.destroy', id), {
      preserveScroll: true
    });
  }
};
</script>

<template>
  <AdminLayout title="Media Library">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Media Library</h2>
        <p class="text-sm text-on-surface-variant mt-1">Kelola aset gambar dan file yang diunggah untuk artikel atau halaman.</p>
      </div>

      <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
        <!-- Search Bar -->
        <div class="relative w-full sm:w-64">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-outline-variant">
            <svg class="h-4 w-4 text-on-surface-variant/70" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
            </svg>
          </span>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Cari media..."
            class="w-full pl-10 pr-4 py-2.5 rounded-full border border-outline-variant/50 bg-surface/50 dark:bg-slate-900/50 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all text-on-surface"
          />
          <button v-if="searchQuery" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-on-surface-variant/60 hover:text-error transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Upload Section -->
    <div v-if="hasPermission('media.upload')" class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm p-6 mb-8">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
        <div class="flex-1">
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            @change="handleUpload"
            :disabled="uploadLoading"
            class="block w-full text-sm text-on-surface-variant file:mr-4 file:py-2.5 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all cursor-pointer border border-outline-variant/30 rounded-xl p-1 bg-surface-container-low dark:bg-slate-900 outline-none"
          />
        </div>
        <button
          type="button"
          @click="fileInput.click()"
          :disabled="uploadLoading"
          class="w-full sm:w-auto px-8 py-3 text-sm font-bold rounded-full btn-simbiosis-create text-white shadow-sm hover:scale-[1.02] transition-transform duration-180 flex items-center justify-center gap-2 shrink-0 select-none disabled:opacity-50"
        >
          <svg class="w-5 h-5 animate-bounce" v-if="uploadLoading" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4-4m0 0L8 8m4-4v12" />
          </svg>
          <svg class="w-5 h-5" v-else fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
          </svg>
          {{ uploadLoading ? 'Mengunggah...' : 'Upload File' }}
        </button>
      </div>
      <div v-if="uploadError" class="text-xs text-error mt-2 font-medium">{{ uploadError }}</div>
      <div class="mt-3 flex items-center gap-2 text-xs text-on-surface-variant font-medium">
        <svg class="w-4 h-4 text-secondary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Hanya file gambar (JPEG, PNG, GIF, WEBP). Validasi MIME dan ukuran maksimal diproses otomatis oleh sistem.
      </div>
    </div>

    <!-- Gallery Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div v-for="m in media.data" :key="m.id" class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl overflow-hidden group hover:border-primary/50 transition-colors shadow-sm flex flex-col">
        <div class="relative pt-[75%] bg-surface-variant/10 dark:bg-slate-950/20 overflow-hidden border-b border-outline-variant/15">
          <img 
            class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            loading="lazy"
            :src="`/storage/${m.path}`"
            :alt="m.original_name"
          >
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        </div>
        <div class="p-3 bg-surface/50 dark:bg-slate-900/50 flex-1 flex flex-col justify-between">
          <div>
            <div class="text-xs font-mono text-on-surface break-all line-clamp-2" :title="m.path">{{ m.path }}</div>
          </div>
          <div class="mt-3 flex items-center justify-between border-t border-outline-variant/25 pt-2">
            <div class="flex items-center gap-1.5 text-[10px] text-on-surface-variant font-semibold">
              <svg class="w-3 h-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
              <span class="truncate max-w-[80px]">{{ m.uploader?.name || 'Sistem' }}</span>
            </div>
            <div class="flex items-center gap-1">
              <a
                :href="`/storage/${m.path}`"
                :download="m.original_name"
                class="p-1.5 rounded-lg text-primary hover:text-primary-dark hover:bg-primary/10 transition-colors"
                title="Unduh Gambar"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
              </a>
              <button
                type="button"
                v-if="hasPermission('media.manage')"
                @click="handleDelete(m.id, m.path)"
                class="p-1.5 rounded-lg text-error/70 hover:text-error hover:bg-error/10 transition-colors"
                title="Hapus Gambar"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="media.data.length === 0" class="col-span-full liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl p-12 text-center">
        <svg class="w-12 h-12 text-outline-variant/50 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        <p class="text-sm font-semibold text-on-surface-variant">Belum ada file media yang diunggah.</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="media.links && media.links.length > 3" class="mt-8 border-t border-outline-variant/30 pt-6">
      <Pagination :links="media.links" />
    </div>
  </AdminLayout>
</template>
