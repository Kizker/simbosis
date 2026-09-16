<script setup>
import { ref, computed, onUnmounted } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  roles: {
    type: Array,
    required: true
  },
  permissions: {
    type: Array,
    required: true
  }
});

const showPassword = ref(false);

const form = useForm({
  name: '',
  email: '',
  password: '',
  avatar: null,
  roles: [],
  permissions: []
});

const groupPermissionName = (name) => {
  const parts = name.split('.');
  const prefix = parts[0] || 'Lainnya';
  switch (prefix) {
    case 'articles': return 'Artikel';
    case 'comments': return 'Komentar';
    case 'media': return 'Media';
    case 'categories': return 'Kategori';
    case 'tags': return 'Tag';
    case 'ads': return 'Iklan';
    case 'audit_logs': return 'Audit Logs';
    case 'permissions':
    case 'roles':
    case 'site_settings':
      return 'Sistem & Akses';
    default: return prefix.charAt(0).toUpperCase() + prefix.slice(1);
  }
};

const displayPermissionLabel = (name) => {
  return name.replace(/^(articles\.|comments\.|media\.|categories\.|tags\.|ads\.|audit_logs\.|permissions\.|roles\.|site_settings\.)/, '');
};

const groupedPermissions = computed(() => {
  const groups = {};
  props.permissions.forEach(p => {
    const groupName = groupPermissionName(p.name);
    if (!groups[groupName]) {
      groups[groupName] = [];
    }
    groups[groupName].push(p);
  });
  return groups;
});

const avatarPreview = ref(null);

const handleAvatarChange = (e) => {
  const file = e.target.files[0];
  form.avatar = file;
  if (file) {
    if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value);
    avatarPreview.value = URL.createObjectURL(file);
  } else {
    avatarPreview.value = null;
  }
};

onUnmounted(() => {
  if (avatarPreview.value) {
    URL.revokeObjectURL(avatarPreview.value);
  }
});

const submit = () => {
  form.post('/harmony-access/users');
};
</script>

<template>
  <AdminLayout title="Tambah User">
    <div class="w-full">
      <!-- Page Header -->
      <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
          <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Tambah User Baru</h2>
          <p class="text-sm text-on-surface-variant mt-1">Isi formulir di bawah ini untuk menambahkan pengguna ke dalam sistem.</p>
        </div>
        <Link href="/harmony-access/users" class="px-6 py-2.5 rounded-full btn-simbiosis-neutral text-sm font-semibold flex items-center gap-2 shadow-sm">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Kembali
        </Link>
      </header>

      <!-- Form Card -->
      <div class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm p-6 sm:p-8">
        <form @submit.prevent="submit" class="grid gap-6 lg:grid-cols-2">
          <!-- Avatar Upload -->
          <div class="lg:col-span-2">
            <label class="block text-sm font-bold text-on-surface mb-2">Foto Profil (Avatar)</label>
            <div class="flex items-center gap-6">
              <div class="shrink-0 w-24 h-24 rounded-full overflow-hidden bg-surface-variant/30 border border-outline-variant/30 flex items-center justify-center">
                <img v-if="avatarPreview" 
                     :src="avatarPreview" 
                     class="w-full h-full object-cover" 
                     alt="Avatar Preview" />
                <svg v-else class="w-10 h-10 text-on-surface-variant/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div class="flex-grow">
                <input 
                  type="file" 
                  accept="image/*"
                  @change="handleAvatarChange"
                  class="block w-full text-sm text-on-surface-variant file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all cursor-pointer"
                />
                <p class="text-xs text-on-surface-variant mt-2">Format: JPG, PNG, WEBP. Maks 5MB.</p>
                <div v-if="form.errors.avatar" class="text-xs text-error mt-1.5 font-medium">{{ form.errors.avatar }}</div>
              </div>
            </div>
          </div>

          <!-- Name field -->
          <div>
            <label class="block text-sm font-bold text-on-surface mb-2">Nama Lengkap <span class="text-error">*</span></label>
            <input
              type="text"
              v-model="form.name"
              required
              maxlength="255"
              class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-slate-900/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all"
              placeholder="Masukkan nama lengkap..."
            />
            <div v-if="form.errors.name" class="text-xs text-error mt-1.5 font-medium">{{ form.errors.name }}</div>
          </div>

          <!-- Email field -->
          <div>
            <label class="block text-sm font-bold text-on-surface mb-2">Alamat Email <span class="text-error">*</span></label>
            <input
              type="email"
              v-model="form.email"
              required
              maxlength="255"
              class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-slate-900/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all"
              placeholder="nama@domain.com"
            />
            <div v-if="form.errors.email" class="text-xs text-error mt-1.5 font-medium">{{ form.errors.email }}</div>
          </div>

          <!-- Password field -->
          <div class="lg:col-span-2">
            <label class="block text-sm font-bold text-on-surface mb-2">Password <span class="text-error">*</span></label>
            <div class="relative w-full lg:w-1/2">
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                required
                minlength="8"
                class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-slate-900/50 px-4 py-2.5 pr-10 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                placeholder="Minimal 8 karakter..."
              />
              <button 
                type="button" 
                @click="showPassword = !showPassword" 
                class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors focus:outline-none"
              >
                <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
              </button>
            </div>
            <div v-if="form.errors.password" class="text-xs text-error mt-1.5 font-medium">{{ form.errors.password }}</div>
          </div>

          <hr class="border-outline-variant/20 my-2 lg:col-span-2 hidden lg:block">

          <!-- Roles Selection -->
          <div class="lg:col-span-2">
            <label class="block text-sm font-bold text-on-surface mb-3">Pilih Role <span class="text-error">*</span></label>
            <div class="flex flex-wrap gap-3">
              <label 
                v-for="r in roles" 
                :key="r.id"
                class="relative flex items-center justify-center px-4 py-2.5 rounded-xl border cursor-pointer select-none transition-all duration-200"
                :class="form.roles.includes(r.name) 
                  ? 'bg-primary/10 border-primary text-primary shadow-sm' 
                  : 'bg-surface/50 dark:bg-slate-900/50 border-outline-variant/50 text-on-surface-variant hover:border-primary/50 hover:bg-primary/5'"
              >
                <input 
                  type="checkbox" 
                  v-model="form.roles" 
                  :value="r.name" 
                  class="sr-only" 
                />
                <div class="flex items-center gap-2">
                  <svg v-if="form.roles.includes(r.name)" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                  </svg>
                  <span class="text-sm font-bold">{{ r.name }}</span>
                </div>
              </label>
            </div>
            <div v-if="form.errors.roles" class="text-xs text-error mt-2 font-medium">{{ form.errors.roles }}</div>
          </div>

          <!-- Direct Permissions -->
          <div class="lg:col-span-2 hidden">
            <label class="block text-sm font-bold text-on-surface mb-3">Direct Permissions (Opsional)</label>
            <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 border border-outline-variant/30 rounded-xl p-4 bg-surface-variant/10 scrollbar-thin">
              <div v-for="(items, module) in groupedPermissions" :key="module" class="space-y-2">
                <div class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mt-1">{{ module }}</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                  <label v-for="p in items" :key="p.id" class="flex items-center justify-between p-2.5 rounded-xl bg-surface/80 dark:bg-slate-900/80 border border-outline-variant/30 hover:border-primary/50 transition-colors cursor-pointer select-none">
                    <span class="text-xs font-semibold text-on-surface truncate mr-3" :title="p.name">
                      {{ displayPermissionLabel(p.name) }}
                    </span>
                    <div class="relative flex items-center shrink-0">
                      <input
                        type="checkbox"
                        v-model="form.permissions"
                        :value="p.name"
                        class="sr-only peer"
                      />
                      <div class="w-8 h-4.5 bg-outline-variant/50 rounded-full peer peer-checked:bg-primary after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:after:translate-x-3.5"></div>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Form Action Footer -->
          <div class="flex items-center justify-end gap-3 pt-6 mt-2 border-t border-outline-variant/20 lg:col-span-2">
            <button
              type="submit"
              :disabled="form.processing"
              class="px-8 py-3 text-sm font-bold rounded-full btn-simbiosis-create text-white shadow-sm disabled:opacity-50"
            >
              Simpan User
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
