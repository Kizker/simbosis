<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import FormInput from '../../Components/FormInput.vue';
import BaseButton from '../../Components/BaseButton.vue';

const props = defineProps({
  title: String,
  metaTitle: String,
  metaDescription: String,
  address: String,
  phone: String,
  emailPublic: String,
  hours: String,
  mapUrl: String
});

// Inertia Form helper
const form = useForm({
  name: '',
  email: '',
  message: ''
});

const submitMessage = () => {
  form.post('/kontak', {
    onSuccess: () => {
      form.reset();
    }
  });
};

const isEmbedMap = computed(() => {
  return props.mapUrl && props.mapUrl.includes('/maps/embed');
});

import { computed } from 'vue';
</script>

<template>
  <PublicLayout>
    <Head :title="metaTitle || title">
      <meta name="description" :content="metaDescription" />
    </Head>

    <div class="mt-4 mb-8 px-4 flex justify-center">
      <nav class="flex justify-center text-xs font-semibold uppercase tracking-wider text-on-surface-variant/75" aria-label="Breadcrumb">
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

    <!-- Page Header -->
    <header class="w-full mb-10 text-center flex flex-col items-center px-4">
      <h1 class="text-4xl md:text-5xl font-extrabold text-primary dark:text-inverse-primary tracking-tight mb-4">{{ title }}</h1>
      <p class="text-lg text-on-surface-variant max-w-2xl mx-auto">
        Kami siap mendengarkan. Hubungi tim redaksi, layanan pelanggan, atau kunjungi kantor pusat kami untuk informasi lebih lanjut.
      </p>
    </header>

    <!-- Content Grid -->
    <div class="w-full grid grid-cols-1 md:grid-cols-12 gap-8 mb-12 px-4">
      
      <!-- Left Column: Contact Form (7 cols) -->
      <section class="md:col-span-7">
        <div class="liquid-glass dark:liquid-glass-dark rounded-2xl p-6 md:p-8 w-full h-full shadow-sm">
          <h2 class="text-2xl font-bold text-on-surface mb-6 flex items-center gap-3">
            <svg class="w-8 h-8 text-secondary-container" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            Kirim Pesan
          </h2>
          
          <form @submit.prevent="submitMessage" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Name -->
              <FormInput
                label="Nama Lengkap"
                v-model="form.name"
                placeholder="Masukkan nama lengkap Anda"
                required
                :error="form.errors.name"
              />
              <!-- Email -->
              <FormInput
                label="Alamat Email"
                type="email"
                v-model="form.email"
                placeholder="Masukkan alamat email Anda"
                required
                :error="form.errors.email"
              />
            </div>
            
            <!-- Message -->
            <div class="flex flex-col gap-1 w-full">
              <label class="text-xs font-bold tracking-wider uppercase text-on-surface-variant/80 dark:text-surface-variant/80" for="message">
                Pesan Anda <span class="text-error">*</span>
              </label>
              <textarea
                id="message"
                v-model="form.message"
                required
                maxlength="2000"
                rows="5"
                placeholder="Tuliskan pesan atau pertanyaan Anda di sini..."
                class="w-full rounded-2xl bg-surface-container-low dark:bg-surface-slate-900 border border-outline-variant/30 px-4 py-3 text-sm text-on-surface placeholder-on-surface-variant/40 resize-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all duration-200 outline-none"
                :class="{ 'border-error focus:border-error focus:ring-error/20': form.errors.message }"
              ></textarea>
              <span v-if="form.errors.message" class="text-xs text-error font-medium mt-0.5">{{ form.errors.message }}</span>
            </div>
            
            <!-- Submit -->
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full md:w-auto px-8 py-3 rounded-full font-semibold text-sm gap-2 flex items-center justify-center bg-primary text-white hover:brightness-110 hover:-translate-y-0.5 shadow-sm hover:shadow-md transition-all duration-200"
              :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
            >
              <span>{{ form.processing ? 'Mengirim...' : 'Kirim Pesan' }}</span>
              <svg v-if="!form.processing" class="w-5 h-5 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
              </svg>
            </button>
          </form>
        </div>
      </section>

      <!-- Right Column: Contact Info & Map (5 cols) -->
      <section class="md:col-span-5 flex flex-col gap-8">
        <!-- Contact Info Card -->
        <div class="liquid-glass dark:liquid-glass-dark rounded-2xl p-6 w-full shadow-sm">
          <h3 class="text-2xl font-bold text-on-surface mb-6 border-b border-outline-variant/20 pb-4">
            Informasi Kontak
          </h3>
          <ul class="space-y-6">
            <!-- Address -->
            <li v-if="address" class="flex items-start gap-4">
              <div class="p-2 bg-surface-container dark:bg-slate-800 rounded-lg text-primary dark:text-inverse-primary mt-1 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </div>
              <div>
                <h4 class="text-xs font-semibold text-on-surface-variant uppercase mb-1">Kantor Pusat</h4>
                <p class="text-base text-on-surface leading-relaxed whitespace-pre-line">
                  {{ address }}
                </p>
              </div>
            </li>
            
            <!-- Contact Details -->
            <li class="flex items-start gap-4">
              <div class="p-2 bg-surface-container dark:bg-slate-800 rounded-lg text-primary dark:text-inverse-primary mt-1 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                </svg>
              </div>
              <div>
                <h4 class="text-xs font-semibold text-on-surface-variant uppercase mb-1">Hubungi Kami</h4>
                <p v-if="phone" class="text-base text-on-surface mb-1">
                  <span class="font-bold">Telp:</span> {{ phone }}
                </p>
                <p v-if="emailPublic" class="text-base text-on-surface">
                  <span class="font-bold">Email:</span> <a :href="`mailto:${emailPublic}`" class="hover:text-primary dark:hover:text-inverse-primary transition-colors">{{ emailPublic }}</a>
                </p>
              </div>
            </li>
            
            <!-- Operating Hours -->
            <li v-if="hours" class="flex items-start gap-4">
              <div class="p-2 bg-surface-container dark:bg-slate-800 rounded-lg text-primary dark:text-inverse-primary mt-1 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div>
                <h4 class="text-xs font-semibold text-on-surface-variant uppercase mb-1">Jam Operasional</h4>
                <p class="text-base text-on-surface leading-relaxed whitespace-pre-line">
                  {{ hours }}
                </p>
              </div>
            </li>
          </ul>
        </div>
        
        <!-- Map Card -->
        <div v-if="mapUrl" class="liquid-glass dark:liquid-glass-dark rounded-2xl p-4 w-full shadow-sm">
          <div class="rounded-xl overflow-hidden border border-outline-variant/20 relative">
            <iframe
              v-if="isEmbedMap"
              :src="mapUrl"
              class="w-full h-64 border-0"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              allowfullscreen
            ></iframe>
            <a class="absolute bottom-4 left-4 right-4 bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm text-primary dark:text-inverse-primary py-2 px-4 rounded-lg text-sm text-center shadow-sm hover:bg-white dark:hover:bg-slate-900 transition-colors flex items-center justify-center gap-2 font-bold" :href="mapUrl" target="_blank" rel="noopener">
              Buka di Google Maps
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
              </svg>
            </a>
          </div>
        </div>
      </section>
    </div>
  </PublicLayout>
</template>
