<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';

const props = defineProps({
  title: String,
  metaTitle: String,
  metaDescription: String,
  heroHeadline: String,
  heroText: String,
  visi: String,
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
  publisher: String,
  address: String,
  email: String,
  phone: String
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

const gradients = [
  'from-sky-400 to-blue-600',
  'from-teal-400 to-emerald-600',
  'from-indigo-400 to-violet-600',
  'from-rose-400 to-pink-600'
];

const getGrad = (index) => gradients[index % gradients.length];

const cleanPhone = computed(() => {
  return props.phone ? props.phone.replace(/[^0-9]/g, '') : '';
});

// Format headline to highlight "Simbiosis" in colors matching blade output
const formattedHeadline = computed(() => {
  if (!props.heroHeadline) return '';
  return props.heroHeadline.replace(
    /Simbiosis/g,
    '<span class="text-[#006194] dark:text-[#39b8fd]">Simbiosis</span>'
  );
});
</script>

<template>
  <PublicLayout>
    <Head :title="metaTitle || title">
      <meta name="description" :content="metaDescription" />
    </Head>

    <!-- Breadcrumb Centered -->
    <div class="flex justify-center mt-4 mb-2">
      <nav class="flex text-xs font-semibold uppercase tracking-wider text-on-surface-variant/75" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
          <li class="inline-flex items-center">
            <Link href="/" class="hover:text-primary transition-colors">Beranda</Link>
          </li>
          <li>
            <div class="flex items-center">
              <span class="mx-1 text-on-surface-variant/40">/</span>
              <span class="text-on-surface font-bold">{{ title }}</span>
            </div>
          </li>
        </ol>
      </nav>
    </div>

    <!-- Hero Section -->
    <div class="relative z-10 max-w-4xl mx-auto text-center mb-12 mt-6 px-4">
      <span class="bg-[#eff4ff] dark:bg-[#13283f] text-[#006194] dark:text-[#93ccff] border border-[#d3e4fe] dark:border-slate-800 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider inline-flex items-center gap-2 mb-6">
        ● JURNALISME TERPERCAYA
      </span>
      <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50 mb-6 leading-tight max-w-3xl mx-auto" v-html="formattedHeadline"></h1>
      <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-base md:text-lg max-w-2xl mx-auto whitespace-pre-line">
        {{ heroText }}
      </p>
    </div>

    <!-- Visi & Misi Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16 px-4">
      <!-- Visi Card -->
      <div class="surface p-6 md:p-8 flex flex-col justify-between hover:shadow-md transition-shadow duration-300 relative group overflow-hidden bg-surface-container-low dark:bg-surface-slate-900">
        <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-sky-500/5 group-hover:scale-125 transition-transform duration-500"></div>
        <div>
          <div class="w-12 h-12 rounded-2xl bg-[#e5eeff] dark:bg-[#1e293b] flex items-center justify-center text-[#006194] dark:text-[#93ccff] mb-6 font-bold shadow-inner">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
            </svg>
          </div>
          <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-50 mb-4 tracking-tight">Visi Kami</h2>
          <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
            {{ visi }}
          </p>
        </div>
      </div>

      <!-- Misi Card -->
      <div class="surface p-6 md:p-8 flex flex-col justify-between hover:shadow-md transition-shadow duration-300 relative group overflow-hidden bg-surface-container-low dark:bg-surface-slate-900">
        <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-teal-500/5 group-hover:scale-125 transition-transform duration-500"></div>
        <div>
          <div class="w-12 h-12 rounded-2xl bg-[#e5eeff] dark:bg-[#1e293b] flex items-center justify-center text-[#006194] dark:text-[#93ccff] mb-6 font-bold shadow-inner">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
          </div>
          <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-50 mb-4 tracking-tight">Misi Kami</h2>
          <ul class="space-y-4">
            <li v-for="misi in misiList.filter(m => m)" :key="misi" class="flex items-start gap-3">
              <span class="mt-1 flex-shrink-0 w-5 h-5 rounded-full bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </span>
              <span class="text-slate-600 dark:text-slate-400 leading-relaxed">{{ misi }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Core Values Section -->
    <div class="mb-16 px-4">
      <div class="text-center mb-8">
        <h2 class="text-3xl font-extrabold text-slate-900 dark:text-slate-50 tracking-tight">3 Pilar Core Values</h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
          v-for="(val, index) in valuesList"
          :key="index"
          class="surface p-6 flex flex-col items-center text-center gap-4 hover:-translate-y-1 transition-all duration-300 shadow-sm hover:shadow-md border-[#dce9ff]/60 bg-white/50 dark:bg-slate-900/50"
        >
          <div class="flex-shrink-0 w-14 h-14 rounded-full bg-[#eff4ff] dark:bg-slate-800 flex items-center justify-center text-[#006194] dark:text-[#93ccff] shadow-sm">
            <!-- Dynamic Icon (Shield, Scale, Lightbulb) -->
            <svg v-if="index % 3 === 0" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <svg v-else-if="index % 3 === 1" class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-slate-50 mb-2">{{ val.title }}</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ val.desc }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tim Redaksi Section -->
    <div class="mb-16 px-4">
      <div class="mb-8 text-center">
        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-slate-50 tracking-tight whitespace-nowrap">Tim Redaksi</h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 justify-center">
        <div
          v-for="(member, index) in teamList"
          :key="index"
          class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 text-center flex flex-col items-center group"
        >
          <img v-if="member.photo && member.photo !== 'null'" :src="member.photo" :alt="member.name" class="w-20 h-20 rounded-full object-cover mb-4 border border-slate-100 dark:border-slate-800 shadow-sm group-hover:scale-105 transition-transform duration-300" />
          <div v-else class="w-20 h-20 rounded-full bg-gradient-to-br text-white font-extrabold text-2xl flex items-center justify-center mb-4 shadow-sm group-hover:scale-105 transition-transform duration-300" :class="getGrad(index)">
            {{ getInitials(member.name) }}
          </div>
          <h3 class="font-bold text-slate-900 dark:text-slate-50 text-base leading-tight group-hover:text-[#006194] dark:group-hover:text-[#93ccff] transition-colors duration-200">{{ member.name }}</h3>
          <span class="mt-2 px-3 py-1 rounded-full text-xs font-semibold bg-[#eff4ff] dark:bg-slate-800 text-[#006194] dark:text-[#93ccff] border border-[#d3e4fe] dark:border-slate-700/60">
            {{ member.role }}
          </span>
        </div>
      </div>
    </div>

    <!-- Informasi Perusahaan & Kontak -->
    <div class="bg-[#0b1c30] text-white p-8 md:p-12 rounded-3xl shadow-lg mb-6 mt-10 mx-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
        <!-- Left Column: Publisher Info -->
        <div>
          <span class="text-xs text-slate-400 block uppercase tracking-wider font-bold mb-1">Penerbit</span>
          <strong class="text-2xl text-[#39b8fd] font-black block mb-4">{{ publisher }}</strong>
          <p class="text-sm text-slate-300 leading-relaxed mb-6">
            Simbiosis News diterbitkan secara profesional dan independen oleh {{ publisher }}, berkomitmen pada standar jurnalisme berkualitas tinggi di era digital.
          </p>
          <div class="mt-6 flex items-center gap-3 text-sm text-slate-300 bg-[#13283f]/60 border border-slate-700/40 p-4 rounded-xl">
            <svg class="w-5 h-5 text-[#39b8fd] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>SK Menkumham: AHU-001234.AH.01.01.Tahun 2024</span>
          </div>
        </div>

        <!-- Right Column: Contact Details -->
        <div class="bg-[#13283f] p-6 rounded-2xl border border-slate-800/40">
          <h2 class="text-lg font-extrabold text-white mb-6 tracking-tight">Kontak Kami</h2>
          <div class="space-y-6 text-sm">
            <!-- Address -->
            <div class="flex gap-3">
              <svg class="w-5 h-5 text-[#39b8fd] mt-1 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <div>
                <span class="text-xs text-slate-400 block font-semibold mb-0.5">Kantor Redaksi</span>
                <p class="text-slate-200 leading-relaxed">
                  {{ address }}
                </p>
              </div>
            </div>

            <!-- Email -->
            <div class="flex gap-3">
              <svg class="w-5 h-5 text-[#39b8fd] mt-1 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <div>
                <span class="text-xs text-slate-400 block font-semibold mb-0.5">Email</span>
                <a :href="`mailto:${email}`" class="text-slate-200 hover:text-[#39b8fd] transition-colors font-medium break-all">
                  {{ email }}
                </a>
              </div>
            </div>

            <!-- Phone -->
            <div class="flex gap-3">
              <svg class="w-5 h-5 text-[#39b8fd] mt-1 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              <div>
                <span class="text-xs text-slate-400 block font-semibold mb-0.5">Telepon</span>
                <a :href="`https://wa.me/${cleanPhone}`" target="_blank" rel="noopener nofollow" class="text-slate-200 hover:text-[#39b8fd] transition-colors font-medium">
                  {{ phone }}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>
