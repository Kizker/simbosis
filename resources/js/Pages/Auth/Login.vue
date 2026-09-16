<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import FormInput from '@/Components/FormInput.vue';
import BaseButton from '@/Components/BaseButton.vue';
import ToastNotification from '@/Components/ToastNotification.vue';

const form = useForm({
  email: '',
  password: '',
  remember: false
});

const submit = () => {
  form.post(route('login.store'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <Head title="Login - Simbiosis News" />

  <div class="auth-shell min-h-screen flex items-center justify-center bg-background dark:bg-slate-950 transition-colors duration-200">
    <main class="container-safe flex min-h-screen items-center justify-center py-10 w-full px-6">
      <div class="auth-card w-full max-w-md bg-surface/80 dark:bg-surface-slate-900/60 backdrop-blur-md border border-outline-variant/20 p-8 rounded-2xl shadow-lg">
        <!-- Brand identity -->
        <div class="mb-6 flex items-center gap-3">
          <img class="h-10 w-auto dark:hidden" :src="'/brand/simbiosis.png'" alt="" aria-hidden="true">
          <img class="h-10 w-auto hidden dark:block" :src="'/brand/simbiosis-putih.png'" alt="" aria-hidden="true">
          <div>
            <div class="text-lg font-extrabold leading-none text-on-surface">Simbiosis News</div>
            <div class="mt-1 text-[10px] font-bold uppercase tracking-[0.16em] text-on-surface-variant/70">Admin CMS</div>
          </div>
        </div>

        <h1 class="text-2xl font-extrabold tracking-tight text-on-surface">Masuk Admin</h1>
        <p class="text-sm text-on-surface-variant/80 mt-1">Kelola redaksi, publikasi, dan kanal visual.</p>

        <form @submit.prevent="submit" class="mt-6 grid gap-4">
          <!-- Email field -->
          <FormInput
            id="email"
            type="email"
            label="Email"
            v-model="form.email"
            :error="form.errors.email"
            required
            autofocus
            placeholder="nama@harmony.com"
          />

          <!-- Password field -->
          <FormInput
            id="password"
            type="password"
            label="Password"
            v-model="form.password"
            :error="form.errors.password"
            required
            placeholder="••••••••"
          />

          <!-- Remember me checkbox -->
          <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-on-surface-variant cursor-pointer">
              <input
                type="checkbox"
                v-model="form.remember"
                class="rounded border-outline-variant/30 text-primary focus:ring-primary/20"
              />
              <span>Ingat saya</span>
            </label>
          </div>

          <!-- Submit button -->
          <BaseButton
            type="submit"
            variant="mesh-primary"
            class="w-full mt-2"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Menghubungkan...' : 'Masuk' }}
          </BaseButton>
        </form>
      </div>
    </main>
    <ToastNotification />
  </div>
</template>
