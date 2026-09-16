<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  users: {
    type: Object,
    required: true
  },
  roles: {
    type: Array,
    required: true
  },
  permissions: {
    type: Array,
    required: true
  },
  q: {
    type: String,
    default: ''
  }
});

const searchQuery = ref(props.q);
const showDeleteModal = ref(false);
const userToDelete = ref(null);
const page = usePage();

let searchTimeout;
watch(searchQuery, (value) => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get('/harmony-access/users', { q: value }, {
      preserveState: true,
      replace: true,
      preserveScroll: true,
      only: ['users', 'q'],
      showProgress: false
    });
  }, 150);
});

const confirmDeleteUser = (user) => {
  userToDelete.value = user;
  showDeleteModal.value = true;
};

const deleteUser = () => {
  if (!userToDelete.value) return;
  router.delete(`/harmony-access/users/${userToDelete.value.id}`, {
    onSuccess: () => {
      showDeleteModal.value = false;
      userToDelete.value = null;
    }
  });
};
</script>

<template>
  <AdminLayout title="Manajemen Pengguna">
    <!-- Page Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Manajemen Pengguna</h2>
        <p class="text-sm text-on-surface-variant mt-1">Kelola daftar pengguna, peran (roles), dan hak akses (permissions) dalam sistem secara praktis.</p>
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
            placeholder="Cari user..."
            class="w-full pl-10 pr-4 py-2.5 rounded-full border border-outline-variant/50 bg-surface/50 dark:bg-slate-900/50 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all text-on-surface"
          />
        </div>

        <!-- Tambah User Button -->
        <Link href="/harmony-access/users/create" class="w-full sm:w-auto btn-simbiosis-create text-white px-6 py-2.5 rounded-full flex items-center justify-center gap-2 shadow-sm transition-all duration-180 whitespace-nowrap font-bold text-sm">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Tambah User
        </Link>
      </div>
    </header>

    <!-- Table Card -->
    <div class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-outline-variant/30 bg-surface-variant/20 dark:bg-slate-900/30">
              <th class="p-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Nama & Email</th>
              <th class="p-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Roles</th>
              <th class="p-4 text-xs font-semibold text-on-surface-variant uppercase tracking-wider text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/20 bg-surface/20 dark:bg-slate-900/20">
            <tr v-for="u in users.data" :key="u.id" class="hover:bg-primary/5 dark:hover:bg-primary/10 transition-colors group">
              <td class="p-4">
                <div class="text-sm font-bold text-on-surface">{{ u.name }}</div>
                <div class="text-xs text-on-surface-variant mt-0.5">{{ u.email }}</div>
              </td>
              <td class="p-4">
                <div class="flex flex-wrap gap-1.5">
                  <span v-for="r in u.roles" :key="r.id" class="inline-flex items-center px-2 py-0.5 rounded border border-secondary-container/30 bg-secondary-container/10 text-xs font-bold text-secondary">
                    {{ r.name }}
                  </span>
                  <span v-if="!u.roles || u.roles.length === 0" class="text-xs text-on-surface-variant">-</span>
                </div>
              </td>
              <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-2 transition-opacity">
                  <Link :href="`/harmony-access/users/${u.id}/edit`" class="inline-flex p-1.5 text-on-surface-variant hover:text-primary rounded hover:bg-primary/10 transition-colors" title="Edit User">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                  </Link>
                  <button v-if="page.props.auth.user.id !== u.id" type="button" @click="confirmDeleteUser(u)" class="inline-flex p-1.5 text-on-surface-variant hover:text-error rounded hover:bg-error/10 transition-colors" title="Hapus User">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!users.data || users.data.length === 0">
              <td colspan="4" class="p-8 text-center text-sm text-on-surface-variant">Tidak ada pengguna ditemukan.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination area at bottom -->
      <div v-if="users.links" class="border-t border-outline-variant/30 p-4 bg-surface-variant/10 dark:bg-slate-900/10">
        <Pagination :links="users.links" />
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4 text-center">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false"></div>

        <!-- Modal Content Card -->
        <div class="relative transform overflow-hidden rounded-2xl liquid-glass dark:liquid-glass-dark border border-outline-variant/50 p-6 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md">
          <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-error/30 bg-error/10 text-error">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
              </svg>
            </div>
            <div class="min-w-0 flex-1">
              <h3 class="text-lg font-bold text-on-surface">Hapus Pengguna</h3>
              <p class="mt-2 text-sm text-on-surface-variant leading-relaxed">
                Apakah Anda yakin ingin menghapus pengguna <span class="font-bold text-on-surface">{{ userToDelete?.name }}</span>? Tindakan ini tidak dapat dibatalkan dan semua data terkait akan dihapus secara permanen.
              </p>
            </div>
          </div>
          <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-outline-variant/20">
            <button type="button" @click="showDeleteModal = false" class="px-5 py-2.5 text-sm font-bold rounded-full btn-simbiosis-neutral border border-outline-variant transition-colors">
              Batal
            </button>
            <button type="button" @click="deleteUser" class="px-5 py-2.5 text-sm font-bold rounded-full btn-simbiosis-delete text-white shadow-sm transition-colors">
              Hapus Permanen
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
