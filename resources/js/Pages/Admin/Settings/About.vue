<script setup>
import { ref } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  about_title: String,
  about_meta_title: String,
  about_meta_desc: String,
  about_hero_headline: String,
  about_hero_subheadline: String,
  about_hero_text: String,
  about_visi: String,
  misiList: {
    type: Array,
    default: () => []
  },
  valuesList: {
    type: Array,
    default: () => []
  },
  teamList: {
    type: Array,
    default: () => []
  },
  about_publisher: String,
  about_address: String,
  about_email: String,
  about_phone: String
});

const activeTab = ref('general');

// Initialize reactive local state lists
const localMisi = ref([...props.misiList]);
const localValues = ref(props.valuesList.map(v => ({ ...v })));
const localTeam = ref(props.teamList.map(t => ({ ...t, photoFile: null })));

const addMisi = () => {
  localMisi.value.push('');
};

const removeMisi = (idx) => {
  localMisi.value.splice(idx, 1);
};

const addValue = () => {
  localValues.value.push({ title: '', desc: '' });
};

const removeValue = (idx) => {
  localValues.value.splice(idx, 1);
};

const addTeam = () => {
  localTeam.value.push({ name: '', role: '', photo: null, photoFile: null });
};

const removeTeam = (idx) => {
  localTeam.value.splice(idx, 1);
};

const handleTeamPhotoChange = (event, idx) => {
  const file = event.target.files[0];
  if (file) {
    localTeam.value[idx].photoFile = file;
    localTeam.value[idx].photo = URL.createObjectURL(file); // preview
  }
};

const submit = () => {
  // Construct dynamic data payload manually or using Inertia Form
  // To handle file uploads, we construct FormData and send it.
  const formData = new FormData();
  
  formData.append('about_title', props.about_title);
  formData.append('about_meta_title', props.about_meta_title);
  formData.append('about_meta_desc', props.about_meta_desc);
  formData.append('about_hero_headline', props.about_hero_headline);
  formData.append('about_hero_subheadline', props.about_hero_subheadline);
  formData.append('about_hero_text', props.about_hero_text);
  formData.append('about_visi', props.about_visi);
  formData.append('about_publisher', props.about_publisher);
  formData.append('about_address', props.about_address);
  formData.append('about_email', props.about_email);
  formData.append('about_phone', props.about_phone);

  // Misi
  localMisi.value.forEach((m, idx) => {
    formData.append(`misi[${idx}]`, m);
  });

  // Values
  localValues.value.forEach((val, idx) => {
    formData.append(`value_titles[${idx}]`, val.title);
    formData.append(`value_descs[${idx}]`, val.desc);
  });

  // Team
  localTeam.value.forEach((member, idx) => {
    formData.append(`team_names[${idx}]`, member.name);
    formData.append(`team_roles[${idx}]`, member.role);
    if (member.photo && !member.photoFile) {
      formData.append(`team_photos[${idx}]`, member.photo);
    }
    if (member.photoFile) {
      formData.append(`team_photo_${idx}`, member.photoFile);
    }
  });

  // Inertia post using FormData
  useForm({}).submit('post', '/harmony-access/about', {
    forceFormData: true,
    data: formData,
    onSuccess: () => {
      // Success will refresh page props automatically
    }
  });
};

const form = useForm({
  about_title: props.about_title || '',
  about_meta_title: props.about_meta_title || '',
  about_meta_desc: props.about_meta_desc || '',
  about_hero_headline: props.about_hero_headline || '',
  about_hero_subheadline: props.about_hero_subheadline || '',
  about_hero_text: props.about_hero_text || '',
  about_visi: props.about_visi || '',
  about_publisher: props.about_publisher || '',
  about_address: props.about_address || '',
  about_email: props.about_email || '',
  about_phone: props.about_phone || ''
});

const submitDirect = () => {
  form.transform((data) => {
    const payload = { ...data };
    
    payload.misi = localMisi.value;
    
    payload.value_titles = localValues.value.map(v => v.title);
    payload.value_descs = localValues.value.map(v => v.desc);
    
    payload.team_names = localTeam.value.map(t => t.name);
    payload.team_roles = localTeam.value.map(t => t.role);
    payload.team_photos = localTeam.value.map(t => t.photo);

    localTeam.value.forEach((member, idx) => {
      if (member.photoFile) {
        payload[`team_photo_${idx}`] = member.photoFile;
      }
    });

    return payload;
  }).post('/harmony-access/about', {
    forceFormData: true
  });
};
</script>

<template>
  <AdminLayout title="Kelola Tentang Kami">
    <!-- Page Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Kelola Tentang Kami</h2>
        <p class="text-sm text-on-surface-variant mt-1">Atur profil, visi, misi, nilai inti, dan redaksi yang ditampilkan publik.</p>
      </div>
      <Link href="/harmony-access/settings" class="btn-simbiosis-neutral rounded-full px-6 py-2.5 text-sm font-semibold flex items-center gap-2 shadow-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Kembali
      </Link>
    </header>

    <!-- Tabbed Form Container -->
    <div class="grid gap-6">
      <!-- Tab Navigation -->
      <nav class="flex flex-wrap gap-2 p-2 rounded-xl bg-surface-variant/20 dark:bg-surface-variant-dark/20 border border-outline-variant/30 backdrop-blur-sm">
        <button 
          type="button" 
          @click="activeTab = 'general'" 
          :class="activeTab === 'general' ? 'bg-white dark:bg-slate-900 text-primary shadow-sm border border-outline-variant/50' : 'text-on-surface-variant hover:bg-white/50 dark:hover:bg-slate-900/50 border border-transparent'"
          class="px-4 py-2.5 rounded-lg text-sm font-bold transition-all duration-300 flex items-center gap-2 outline-none flex-1 min-w-[140px] justify-center"
        >
          <svg class="w-4 h-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
          Utama & SEO
        </button>
        
        <button 
          type="button" 
          @click="activeTab = 'visimisi'" 
          :class="activeTab === 'visimisi' ? 'bg-white dark:bg-slate-900 text-primary shadow-sm border border-outline-variant/50' : 'text-on-surface-variant hover:bg-white/50 dark:hover:bg-slate-900/50 border border-transparent'"
          class="px-4 py-2.5 rounded-lg text-sm font-bold transition-all duration-300 flex items-center gap-2 outline-none flex-1 min-w-[140px] justify-center"
        >
          <svg class="w-4 h-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
          Visi & Misi
        </button>
        
        <button 
          type="button" 
          @click="activeTab = 'values'" 
          :class="activeTab === 'values' ? 'bg-white dark:bg-slate-900 text-primary shadow-sm border border-outline-variant/50' : 'text-on-surface-variant hover:bg-white/50 dark:hover:bg-slate-900/50 border border-transparent'"
          class="px-4 py-2.5 rounded-lg text-sm font-bold transition-all duration-300 flex items-center gap-2 outline-none flex-1 min-w-[140px] justify-center"
        >
          <svg class="w-4 h-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
          Nilai Inti
        </button>
        
        <button 
          type="button" 
          @click="activeTab = 'team'" 
          :class="activeTab === 'team' ? 'bg-white dark:bg-slate-900 text-primary shadow-sm border border-outline-variant/50' : 'text-on-surface-variant hover:bg-white/50 dark:hover:bg-slate-900/50 border border-transparent'"
          class="px-4 py-2.5 rounded-lg text-sm font-bold transition-all duration-300 flex items-center gap-2 outline-none flex-1 min-w-[140px] justify-center"
        >
          <svg class="w-4 h-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          Redaksi
        </button>
        
        <button 
          type="button" 
          @click="activeTab = 'contact'" 
          :class="activeTab === 'contact' ? 'bg-white dark:bg-slate-900 text-primary shadow-sm border border-outline-variant/50' : 'text-on-surface-variant hover:bg-white/50 dark:hover:bg-slate-900/50 border border-transparent'"
          class="px-4 py-2.5 rounded-lg text-sm font-bold transition-all duration-300 flex items-center gap-2 outline-none flex-1 min-w-[140px] justify-center"
        >
          <svg class="w-4 h-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
          Penerbit & Kontak
        </button>
      </nav>

      <!-- Main Form -->
      <form @submit.prevent="submitDirect" class="grid gap-6">
        <!-- TAB 1: Utama & SEO -->
        <div v-show="activeTab === 'general'" class="grid gap-6">
          <div class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant/20 bg-surface/50 dark:bg-surface-dark/50">
              <h3 class="text-lg font-bold text-on-surface">Pengaturan Dasar & SEO Halaman</h3>
            </div>
            <div class="p-6 grid gap-6">
              <div>
                <label class="block text-sm font-bold text-on-surface mb-2">Judul Halaman (Breadcrumb & Tab) <span class="text-error">*</span></label>
                <input type="text" v-model="form.about_title" required class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                <div v-if="form.errors.about_title" class="text-xs text-error mt-1">{{ form.errors.about_title }}</div>
              </div>
              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-bold text-on-surface mb-2">Meta Title (Google Search Title) <span class="text-error">*</span></label>
                  <input type="text" v-model="form.about_meta_title" required maxlength="190" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                  <div v-if="form.errors.about_meta_title" class="text-xs text-error mt-1">{{ form.errors.about_meta_title }}</div>
                </div>
                <div>
                  <label class="block text-sm font-bold text-on-surface mb-2">Meta Description (Ringkasan Google) <span class="text-error">*</span></label>
                  <input type="text" v-model="form.about_meta_desc" required maxlength="300" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                  <div v-if="form.errors.about_meta_desc" class="text-xs text-error mt-1">{{ form.errors.about_meta_desc }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant/20 bg-surface/50 dark:bg-surface-dark/50">
              <h3 class="text-lg font-bold text-on-surface">Hero Section (Header)</h3>
              <p class="text-[10px] text-on-surface-variant mt-1">Banner visual teratas pada halaman Tentang Kami dengan aksen gradasi.</p>
            </div>
            <div class="p-6 grid gap-6">
              <div>
                <label class="block text-sm font-bold text-on-surface mb-2">Headline Utama Hero <span class="text-error">*</span></label>
                <input type="text" v-model="form.about_hero_headline" required maxlength="190" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                <div v-if="form.errors.about_hero_headline" class="text-xs text-error mt-1">{{ form.errors.about_hero_headline }}</div>
              </div>
              <div>
                <label class="block text-sm font-bold text-on-surface mb-2">Sub-Headline Editorial <span class="text-error">*</span></label>
                <input type="text" v-model="form.about_hero_subheadline" required maxlength="255" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                <div v-if="form.errors.about_hero_subheadline" class="text-xs text-error mt-1">{{ form.errors.about_hero_subheadline }}</div>
              </div>
              <div>
                <label class="block text-sm font-bold text-on-surface mb-2">Paragraf Deskripsi Profil <span class="text-error">*</span></label>
                <textarea v-model="form.about_hero_text" rows="6" required maxlength="2000" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-3 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all leading-relaxed resize-none"></textarea>
                <p class="text-[10px] text-on-surface-variant mt-1.5">Paragraf penjelasan badan pers. Tekan enter untuk baris baru.</p>
                <div v-if="form.errors.about_hero_text" class="text-xs text-error mt-1">{{ form.errors.about_hero_text }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: Visi & Misi -->
        <div v-show="activeTab === 'visimisi'" class="grid gap-6">
          <div class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant/20 bg-surface/50 dark:bg-surface-dark/50">
              <h3 class="text-lg font-bold text-on-surface">Pernyataan Visi Perusahaan</h3>
            </div>
            <div class="p-6">
              <label class="block text-sm font-bold text-on-surface mb-2">Teks Visi Utama <span class="text-error">*</span></label>
              <textarea v-model="form.about_visi" rows="4" required maxlength="1000" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-3 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all leading-relaxed resize-none"></textarea>
              <div v-if="form.errors.about_visi" class="text-xs text-error mt-1">{{ form.errors.about_visi }}</div>
            </div>
          </div>

          <div class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant/20 bg-surface/50 dark:bg-surface-dark/50 flex justify-between items-center">
              <h3 class="text-lg font-bold text-on-surface">Poin-Poin Misi Perusahaan</h3>
              <button type="button" @click="addMisi" class="text-xs font-bold px-3 py-1.5 rounded-full bg-primary/10 text-primary hover:bg-primary/20 transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Tambah Misi
              </button>
            </div>
            <div class="p-6 grid gap-4">
              <div v-for="(item, index) in localMisi" :key="index" class="flex gap-3 items-center">
                <input type="text" v-model="localMisi[index]" required maxlength="300" class="flex-grow rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all" />
                <button type="button" @click="removeMisi(index)" class="w-10 h-10 rounded-full bg-error/10 text-error hover:bg-error/20 flex items-center justify-center transition-colors shrink-0" title="Hapus Misi">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
              </div>
              
              <div v-if="localMisi.length === 0" class="text-sm text-on-surface-variant p-6 border border-dashed border-outline-variant/30 rounded-xl text-center bg-surface-variant/10">
                Belum ada poin misi yang ditambahkan.
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 3: Nilai Inti -->
        <div v-show="activeTab === 'values'" class="grid gap-6">
          <div class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant/20 bg-surface/50 dark:bg-surface-dark/50 flex justify-between items-center">
              <h3 class="text-lg font-bold text-on-surface">Pilar Nilai Inti Jurnalisme</h3>
              <button type="button" @click="addValue" class="text-xs font-bold px-3 py-1.5 rounded-full bg-secondary-container text-on-secondary-container hover:opacity-90 transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Tambah Pilar
              </button>
            </div>
            <div class="p-6 grid gap-6">
              <div v-for="(item, index) in localValues" :key="index" class="p-5 rounded-xl bg-surface-variant/10 border border-outline-variant/30 flex flex-col md:grid md:grid-cols-[100px_1fr_2fr_auto] gap-4 items-start relative group">
                <div class="w-full md:w-auto flex items-center justify-between md:block md:pt-2.5">
                  <div class="font-bold text-primary text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Pilar {{ index + 1 }}
                  </div>
                  <!-- Mobile Delete Button -->
                  <button type="button" @click="removeValue(index)" class="md:hidden w-8 h-8 rounded-full bg-error/10 text-error hover:bg-error/20 flex items-center justify-center transition-colors shrink-0" title="Hapus Pilar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </div>
                <div class="w-full">
                  <label class="block text-xs font-bold text-on-surface mb-1">Judul Nilai</label>
                  <input type="text" v-model="item.title" required maxlength="100" class="w-full rounded-lg border border-outline-variant bg-surface dark:bg-slate-900 px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all" />
                </div>
                <div class="w-full">
                  <label class="block text-xs font-bold text-on-surface mb-1">Deskripsi Singkat</label>
                  <input type="text" v-model="item.desc" required maxlength="300" class="w-full rounded-lg border border-outline-variant bg-surface dark:bg-slate-900 px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all" />
                </div>
                <div class="hidden md:block pt-6">
                  <button type="button" @click="removeValue(index)" class="w-9 h-9 rounded-full bg-error/10 text-error hover:bg-error/20 flex items-center justify-center transition-colors" title="Hapus Pilar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </div>
              </div>

              <div v-if="localValues.length === 0" class="text-sm text-on-surface-variant p-6 border border-dashed border-outline-variant/30 rounded-xl text-center bg-surface-variant/10">
                Belum ada pilar nilai inti yang ditambahkan.
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 4: Redaksi -->
        <div v-show="activeTab === 'team'" class="grid gap-6">
          <div class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant/20 bg-surface/50 dark:bg-surface-dark/50 flex justify-between items-center">
              <h3 class="text-lg font-bold text-on-surface">Struktur Kru Redaksi</h3>
              <button type="button" @click="addTeam" class="text-xs font-bold px-3 py-1.5 rounded-full bg-tertiary-container text-on-tertiary-container hover:opacity-90 transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Tambah Anggota
              </button>
            </div>
            <div class="p-6 grid gap-4">
              <div v-for="(item, index) in localTeam" :key="index" class="p-4 rounded-xl border border-outline-variant/30 bg-surface-variant/5 flex flex-col md:grid md:grid-cols-[auto_1fr_1fr_auto] gap-4 items-start md:items-center relative group">
                <!-- Mobile header row (Avatar + Delete button) -->
                <div class="flex w-full items-center justify-between md:w-auto md:block">
                  <!-- Avatar -->
                  <div class="w-14 h-14 rounded-full overflow-hidden bg-surface-variant/30 border border-outline-variant/30 flex items-center justify-center shrink-0">
                    <img v-if="item.photo" :src="item.photo" class="w-full h-full object-cover" />
                    <span v-else class="text-sm font-bold text-on-surface-variant">
                      {{ item.name ? item.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase() : 'RD' }}
                    </span>
                  </div>
                  <!-- Mobile Delete -->
                  <button type="button" @click="removeTeam(index)" class="md:hidden w-8 h-8 rounded-full bg-error/10 text-error hover:bg-error/20 flex items-center justify-center transition-colors shrink-0" title="Hapus Anggota">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </div>
                
                <!-- Name & Upload -->
                <div class="w-full">
                  <label class="block text-xs font-bold text-on-surface mb-1">Nama Lengkap</label>
                  <input type="text" v-model="item.name" required maxlength="120" placeholder="Nama Lengkap" class="w-full rounded-lg border border-outline-variant bg-surface dark:bg-slate-900 px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all mb-2" />
                  
                  <input type="file" @change="handleTeamPhotoChange($event, index)" class="w-full text-[10px] text-on-surface-variant file:mr-2 file:py-1.5 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all cursor-pointer bg-surface dark:bg-slate-900 rounded-md border border-outline-variant/30 p-0.5" />
                </div>

                <!-- Role -->
                <div class="w-full">
                  <label class="block text-xs font-bold text-on-surface mb-1">Jabatan / Peran</label>
                  <input type="text" v-model="item.role" required maxlength="120" placeholder="Contoh: Pemimpin Redaksi" class="w-full rounded-lg border border-outline-variant bg-surface dark:bg-slate-900 px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all" />
                </div>

                <!-- Delete (Desktop) -->
                <div class="hidden md:block pt-5">
                  <button type="button" @click="removeTeam(index)" class="w-10 h-10 rounded-full bg-error/10 text-error hover:bg-error/20 flex items-center justify-center transition-colors" title="Hapus Anggota">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </div>
              </div>

              <div v-if="localTeam.length === 0" class="text-sm text-on-surface-variant p-6 border border-dashed border-outline-variant/30 rounded-xl text-center bg-surface-variant/10">
                Belum ada anggota redaksi yang ditambahkan.
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 5: Penerbit & Kontak -->
        <div v-show="activeTab === 'contact'" class="grid gap-6">
          <div class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant/20 bg-surface/50 dark:bg-surface-dark/50">
              <h3 class="text-lg font-bold text-on-surface">Penerbit & Legalitas Redaksi</h3>
              <p class="text-[10px] text-on-surface-variant mt-1">Info legal penerbit dan profil kontak fisik pers di footer Tentang Kami.</p>
            </div>
            <div class="p-6 grid gap-6">
              <div>
                <label class="block text-sm font-bold text-on-surface mb-2">Badan Hukum Penerbit (Diterbitkan Oleh) <span class="text-error">*</span></label>
                <input type="text" v-model="form.about_publisher" required maxlength="190" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                <div v-if="form.errors.about_publisher" class="text-xs text-error mt-1">{{ form.errors.about_publisher }}</div>
              </div>
              <div>
                <label class="block text-sm font-bold text-on-surface mb-2">Alamat Fisik Kantor Redaksi <span class="text-error">*</span></label>
                <textarea v-model="form.about_address" rows="3" required maxlength="500" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-3 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all leading-relaxed resize-none"></textarea>
                <div v-if="form.errors.about_address" class="text-xs text-error mt-1">{{ form.errors.about_address }}</div>
              </div>
              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-bold text-on-surface mb-2">Email Korespondensi Redaksi <span class="text-error">*</span></label>
                  <input type="email" v-model="form.about_email" required maxlength="190" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                  <div v-if="form.errors.about_email" class="text-xs text-error mt-1">{{ form.errors.about_email }}</div>
                </div>
                <div>
                  <label class="block text-sm font-bold text-on-surface mb-2">No. Telepon / WhatsApp Redaksi <span class="text-error">*</span></label>
                  <input type="text" v-model="form.about_phone" required maxlength="120" class="w-full rounded-lg border border-outline-variant bg-surface/50 dark:bg-surface-dark/50 px-4 py-2.5 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                  <div v-if="form.errors.about_phone" class="text-xs text-error mt-1">{{ form.errors.about_phone }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Global Save Action Bar -->
        <div class="sticky bottom-4 z-10 liquid-glass dark:liquid-glass-dark border border-outline-variant/30 p-4 flex flex-col md:flex-row items-center justify-between gap-4 rounded-xl shadow-lg mt-4">
          <div class="text-[11px] text-on-surface-variant font-medium flex items-center gap-2">
            <svg class="w-4 h-4 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Pastikan menyimpan perubahan sebelum beralih ke menu utama lain.
          </div>
          <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
            <Link href="/harmony-access/settings" class="btn-simbiosis-neutral rounded-full w-full md:w-auto px-6 py-2.5 text-center text-sm font-bold shadow-sm">
              Batal
            </Link>
            <button type="submit" :disabled="form.processing" class="btn-simbiosis-save rounded-full w-full md:w-auto px-8 py-2.5 text-sm font-bold shadow-md disabled:opacity-50">
              Simpan Semua Pengaturan
            </button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
