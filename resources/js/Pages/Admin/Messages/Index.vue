<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
  messages: {
    type: Object,
    required: true
  }
});

const activeMessage = ref(null);
const replyForm = useForm({
  reply_message: ''
});
const showReplyModal = ref(false);

const openReplyModal = (msg) => {
  activeMessage.value = msg;
  replyForm.reply_message = '';
  showReplyModal.value = true;
};

const closeReplyModal = () => {
  showReplyModal.value = false;
  activeMessage.value = null;
  replyForm.reset();
};

const submitReply = () => {
  if (!activeMessage.value) return;
  
  replyForm.post(route('admin.messages.reply', activeMessage.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeReplyModal();
    }
  });
};

const markAsRead = (id) => {
  router.post(route('admin.messages.markAsRead', id), {}, { preserveScroll: true });
};

const handleDelete = (id) => {
  if (confirm('Yakin ingin menghapus pesan ini secara permanen?')) {
    router.delete(route('admin.messages.destroy', id), { preserveScroll: true });
  }
};
</script>

<template>
  <AdminLayout title="Pesan Kontak">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-on-surface tracking-tight">Pesan Kontak</h2>
        <p class="text-sm text-on-surface-variant mt-1">Kelola dan balas pesan yang masuk dari halaman kontak.</p>
      </div>
    </div>

    <!-- Content Area -->
    <div class="grid gap-4">
      <div v-for="m in messages.data" :key="m.id" :class="[m.is_read ? 'bg-surface/50 dark:bg-slate-900/50' : 'bg-surface dark:bg-slate-800 border-primary/30']" class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl shadow-sm p-5 sm:p-6 transition-all hover:border-primary/50 group flex flex-col gap-4">
        
        <!-- Header: Info and Actions -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
          <!-- Left: Meta Info -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-3 mb-2">
              <span class="font-bold" :class="[m.is_read ? 'text-on-surface-variant' : 'text-on-surface']">{{ m.name }}</span>
              <span class="text-xs font-mono text-on-surface-variant bg-surface-variant/20 dark:bg-slate-800 px-2 py-0.5 rounded">{{ m.email }}</span>
              
              <span v-if="m.replied_at" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-900 uppercase tracking-wider">Telah Dibalas</span>
              <span v-else-if="!m.is_read" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-850 dark:bg-blue-950/60 dark:text-blue-200 border border-blue-250 dark:border-blue-900 uppercase tracking-wider">Baru</span>
            </div>
            
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-on-surface-variant font-semibold">
              <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ new Date(m.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span>
              </div>
            </div>
          </div>
          
          <!-- Right: Actions -->
          <div class="flex flex-row gap-2 shrink-0 w-full sm:w-auto">
            <button
              v-if="!m.is_read"
              @click="markAsRead(m.id)"
              class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 text-xs font-bold rounded-xl bg-surface-variant/30 text-on-surface border border-outline-variant/30 hover:bg-slate-600 hover:text-white transition-all select-none"
            >
              Tandai Dibaca
            </button>
            
            <button
              @click="openReplyModal(m)"
              class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 text-xs font-bold rounded-xl bg-primary/10 text-primary border border-primary/20 hover:bg-primary hover:text-white transition-all select-none"
            >
              Balas
            </button>
            
            <button
              @click="handleDelete(m.id)"
              class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 text-xs font-bold rounded-xl bg-error/10 text-error border border-error/20 hover:bg-error hover:text-white transition-all select-none"
            >
              Hapus
            </button>
          </div>
        </div>

        <!-- Content Box -->
        <div class="text-sm text-on-surface leading-relaxed p-4 rounded-xl border border-outline-variant/30 relative w-full" :class="[m.is_read ? 'bg-surface/50 dark:bg-slate-900/50' : 'bg-surface dark:bg-slate-800']">
          <svg class="w-6 h-6 text-outline-variant/20 absolute top-4 left-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
          <div class="pl-10 relative z-10 whitespace-pre-wrap">{{ m.message }}</div>
        </div>
      </div>

      <div v-if="messages.data.length === 0" class="liquid-glass dark:liquid-glass-dark border border-outline-variant/30 rounded-xl p-12 text-center">
        <p class="text-sm font-semibold text-on-surface-variant">Belum ada pesan kotak masuk.</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="messages.links && messages.links.length > 3" class="mt-6">
      <Pagination :links="messages.links" />
    </div>

    <!-- Reply Modal -->
    <div v-if="showReplyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div class="bg-surface dark:bg-slate-800 rounded-2xl w-full max-w-2xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-outline-variant/30 flex justify-between items-center bg-surface-variant/30">
          <h3 class="text-lg font-bold text-on-surface">Balas Pesan: {{ activeMessage?.name }}</h3>
          <button @click="closeReplyModal" class="text-on-surface-variant hover:text-error transition-colors p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        
        <div class="p-6 overflow-y-auto">
          <div class="mb-4 p-4 bg-surface-variant/30 dark:bg-slate-900/50 rounded-xl border border-outline-variant/30">
            <div class="text-xs text-on-surface-variant mb-1">Pesan asli dari <span class="font-bold">{{ activeMessage?.email }}</span>:</div>
            <div class="text-sm text-on-surface whitespace-pre-wrap">{{ activeMessage?.message }}</div>
          </div>
          
          <form @submit.prevent="submitReply">
            <div class="mb-4">
              <label class="block text-sm font-semibold text-on-surface mb-2">Pesan Balasan</label>
              <textarea 
                v-model="replyForm.reply_message" 
                rows="6" 
                class="w-full rounded-xl border border-outline-variant/50 bg-surface dark:bg-slate-900 px-4 py-3 text-sm text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                placeholder="Tulis balasan Anda di sini... (Balasan akan dikirim langsung ke email pengirim)"
                required
              ></textarea>
              <p v-if="replyForm.errors.reply_message" class="text-xs text-error mt-1">{{ replyForm.errors.reply_message }}</p>
            </div>
          </form>
        </div>
        
        <div class="px-6 py-4 border-t border-outline-variant/30 bg-surface-variant/30 flex justify-end gap-3 shrink-0">
          <button @click="closeReplyModal" type="button" class="px-5 py-2.5 text-sm font-bold text-on-surface bg-surface-variant hover:bg-slate-300 dark:hover:bg-slate-600 rounded-xl transition-all">
            Batal
          </button>
          <button @click="submitReply" type="button" :disabled="replyForm.processing" class="px-5 py-2.5 text-sm font-bold text-white bg-primary hover:bg-primary-dark rounded-xl transition-all disabled:opacity-50">
            <span v-if="replyForm.processing">Mengirim...</span>
            <span v-else>Kirim Balasan</span>
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
