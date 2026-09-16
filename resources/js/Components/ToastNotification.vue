<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const toasts = ref([]);
let idCounter = 0;

const addToast = (message, type) => {
  const id = idCounter++;
  toasts.value.push({ id, message, type });
  setTimeout(() => {
    removeToast(id);
  }, 5000);
};

const removeToast = (id) => {
  toasts.value = toasts.value.filter(t => t.id !== id);
};

// Keep track of last seen flash values to avoid duplicate triggers
let lastFlash = { success: null, error: null, status: null };

watch(
  () => page.props.flash,
  (newFlash) => {
    if (!newFlash) return;
    
    if (newFlash.success && newFlash.success !== lastFlash.success) {
      addToast(newFlash.success, 'success');
    }
    if (newFlash.error && newFlash.error !== lastFlash.error) {
      addToast(newFlash.error, 'error');
    }
    if (newFlash.status && newFlash.status !== lastFlash.status) {
      addToast(newFlash.status, 'info');
    }
    
    lastFlash = { 
      success: newFlash.success || null, 
      error: newFlash.error || null, 
      status: newFlash.status || null 
    };
  },
  { deep: true, immediate: true }
);
</script>

<template>
  <div class="fixed top-4 right-4 z-50 flex flex-col gap-3 max-w-md w-full px-4 sm:px-0 pointer-events-none">
    <TransitionGroup
      name="toast"
      tag="div"
      class="flex flex-col gap-3 w-full items-end"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-start gap-3 p-4 rounded-xl border shadow-lg backdrop-blur-md w-full sm:max-w-sm transition-all duration-300"
        :class="{
          'bg-emerald-50/90 dark:bg-emerald-950/40 border-emerald-500/30 dark:border-emerald-500/20 text-emerald-900 dark:text-emerald-200': toast.type === 'success',
          'bg-red-50/90 dark:bg-red-950/40 border-red-500/30 dark:border-red-500/20 text-red-900 dark:text-red-200': toast.type === 'error',
          'bg-blue-50/90 dark:bg-blue-950/40 border-blue-500/30 dark:border-blue-500/20 text-blue-900 dark:text-blue-200': toast.type === 'info',
        }"
      >
        <!-- Icon -->
        <span class="flex-shrink-0 mt-0.5">
          <!-- Success (Check Icon) -->
          <svg v-if="toast.type === 'success'" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          
          <!-- Error (Alert Icon) -->
          <svg v-else-if="toast.type === 'error'" class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          
          <!-- Info (Bell/Info Icon) -->
          <svg v-else class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </span>

        <!-- Message Content -->
        <div class="flex-grow text-sm font-medium leading-relaxed">
          {{ toast.message }}
        </div>

        <!-- Dismiss button -->
        <button
          @click="removeToast(toast.id)"
          class="flex-shrink-0 hover:opacity-75 transition-opacity"
          :class="{
            'text-emerald-700 dark:text-emerald-400': toast.type === 'success',
            'text-red-700 dark:text-red-400': toast.type === 'error',
            'text-blue-700 dark:text-blue-400': toast.type === 'info',
          }"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-enter-from {
  transform: translateX(100%);
  opacity: 0;
}
.toast-enter-to {
  transform: translateX(0);
  opacity: 100%;
}
.toast-leave-from {
  transform: translateX(0);
  opacity: 100%;
}
.toast-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
