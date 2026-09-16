<script setup>
import { ref, watch, onMounted } from 'vue';
import Editor from '@tinymce/tinymce-vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Tulis isi berita di sini...'
  },
  error: String,
  disabled: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);

// Local content state
const content = ref(props.modelValue);

// Sync prop to local state
watch(() => props.modelValue, (newVal) => {
  if (content.value !== newVal) {
    content.value = newVal;
  }
});

// Sync local state to prop
const handleUpdate = (value) => {
  content.value = value;
  emit('update:modelValue', value);
};

function csrfToken() {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  if (match) {
    return decodeURIComponent(match[1]);
  }
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

const isReady = ref(false);

const editorInit = ref({
  license_key: 'gpl',
  promotion: false,
  branding: false,
  menubar: false,
  skin: 'oxide',
  content_css: 'default',
  plugins: [
    'autolink', 'lists', 'link', 'image', 'media',
    'table', 'wordcount', 'fullscreen', 'code'
  ],
  toolbar_mode: 'wrap',
  toolbar: 'blocks fontsize | bold italic underline strikethrough | ' +
    'alignleft aligncenter alignright | bullist numlist | ' +
    'link image media table | removeformat fullscreen',
  font_size_formats: '12pt 14pt 16pt 18pt 24pt 36pt',
  image_advtab: true,
  image_caption: true,
  image_title: true,
  media_live_embeds: true,
  placeholder: props.placeholder,
  readonly: props.disabled,
  height: 600,
  content_style: `
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    body {
      font-family: "Poppins", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      font-size: 16px;
      line-height: 1.75;
      color: #334155;
      background-color: #ffffff;
      padding: 8px 16px;
    }
    p { margin: 0 0 0.75em; }
    h1,h2,h3,h4,h5,h6 { margin: 1em 0 0.5em; font-weight: 600; }
    figure.image { margin: 1.5em auto; text-align: center; }
    figure.image img { max-width: 100%; height: auto; border-radius: 8px; }
    a { color: #3b82f6; }
    blockquote { border-left: 4px solid #3b82f6; margin: 1em 0; padding: 0.5em 1em; opacity: 0.85; }
    table { border-collapse: collapse; width: 100%; }
    td, th { border: 1px solid #e2e8f0; padding: 8px 12px; }
  `,
  images_upload_handler: async (blobInfo, progress) => {
    return new Promise(async (resolve, reject) => {
      try {
        const formData = new FormData();
        const token = csrfToken();
        formData.append('file', blobInfo.blob(), blobInfo.filename());
        if (token) {
          formData.append('_token', token);
        }

        const res = await fetch('/harmony-access/media', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'X-CSRF-TOKEN': token,
            'X-XSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        });

        if (!res.ok) {
          reject(`Upload gagal (${res.status}).`);
          return;
        }

        const data = await res.json();
        const finalUrl = data?.url || data?.location;
        if (finalUrl) {
          resolve(finalUrl);
        } else {
          reject('Format respons tidak valid dari server.');
        }
      } catch (e) {
        reject('Terjadi kesalahan jaringan.');
      }
    });
  }
});

onMounted(() => {
  isReady.value = true;
});
</script>

<template>
  <div class="tinymce-wrapper" :class="{ 'has-error': !!error }">
    <template v-if="!disabled && isReady">
      <Editor
        license-key="gpl"
        tinymce-script-src="/tinymce/tinymce.min.js"
        :init="editorInit"
        :model-value="content"
        @update:model-value="handleUpdate"
      />
    </template>
    <div v-else-if="disabled" class="prose max-w-none rounded-xl border border-outline-variant/50 p-6 bg-surface/50 text-on-surface min-h-[400px]" v-html="modelValue"></div>
    <div v-else class="min-h-[400px] flex items-center justify-center border border-white/10 rounded-xl bg-surface/50">
      <div class="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
    </div>
    <span v-if="error" class="text-xs text-error font-medium mt-1 block">{{ error }}</span>
  </div>
</template>

<style>
/* ═══════════════════════════════════════════════════
   TinyMCE Styling — Simbiosis CMS (Light Theme)
   ═══════════════════════════════════════════════════ */

/* ── 1. Editor Shell (outer border & focus ring) ── */
.tinymce-wrapper .tox-tinymce {
  border-radius: 0.75rem !important;
  border: 1px solid rgba(0, 0, 0, 0.12) !important;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 0 0 0 transparent;
  transition: border-color 0.25s ease, box-shadow 0.25s ease;
}
.tinymce-wrapper:focus-within .tox-tinymce {
  border-color: rgba(0, 97, 148, 0.6) !important;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 0 0 3px rgba(0, 97, 148, 0.12) !important;
}
.tinymce-wrapper.has-error .tox-tinymce {
  border-color: rgba(239, 68, 68, 1) !important;
}

/* ── 2. Toolbar area ── */
.tox .tox-toolbar-overlord,
.tox .tox-toolbar__primary {
  background: #f8fafc !important; /* Very light slate for toolbar background */
}
.tox .tox-editor-header {
  border-bottom: 1px solid rgba(0,0,0,0.08) !important;
  box-shadow: none !important;
  padding: 4px 4px 2px !important;
}

/* ── 3. Toolbar buttons (icons) ── */
.tox .tox-tbtn {
  border-radius: 0.375rem !important;
  margin: 1px !important;
  transition: background 0.15s ease, color 0.15s ease !important;
}
.tox .tox-tbtn:hover {
  background: rgba(0, 97, 148, 0.1) !important; /* Clear visible light primary hover */
  color: #006194 !important; /* Icon changes to primary color on hover */
}
.tox .tox-tbtn--enabled,
.tox .tox-tbtn--enabled:hover {
  background: rgba(0, 97, 148, 0.15) !important;
  color: #006194 !important;
}
.tox .tox-tbtn svg {
  fill: currentColor !important;
}

/* ── 4. Toolbar group separators ── */
.tox .tox-toolbar__group:not(:last-of-type) {
  border-right: 1px solid rgba(0,0,0,0.08) !important;
  padding-right: 4px !important;
  margin-right: 4px !important;
}

/* ── 5. Select / dropdown buttons (Paragraph, Font Size) ── */
.tox .tox-tbtn--select {
  border-radius: 0.375rem !important;
  padding: 0 8px !important;
}
.tox .tox-tbtn--bespoke .tox-tbtn__select-label {
  width: auto !important;
  min-width: 4em;
}

/* ── 6. Dropdown menus & collection panels ── */
.tox .tox-menu,
.tox .tox-collection--list {
  border-radius: 0.5rem !important;
  border: 1px solid rgba(0,0,0,0.1) !important;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
  overflow: hidden;
  background: #ffffff !important;
}
.tox .tox-collection__item {
  border-radius: 0.25rem !important;
  margin: 2px 4px !important;
  transition: background 0.12s ease, color 0.12s ease !important;
  color: #334155 !important;
}
.tox .tox-collection__item:hover,
.tox .tox-collection__item--active {
  background: rgba(0, 97, 148, 0.1) !important;
  color: #006194 !important; /* Text becomes primary color on hover to ensure visibility */
}
.tox .tox-collection__item-label {
  color: inherit !important;
}

/* ── 7. Statusbar ── */
.tox .tox-statusbar {
  border-top: 1px solid rgba(0,0,0,0.08) !important;
  padding: 4px 12px !important;
  font-size: 11px !important;
  background: #f8fafc !important;
}
.tox .tox-statusbar__text-container {
  opacity: 0.7;
  color: #64748b;
}

/* ── 8. Dialogs / Modals (Insert Link, Image, Media, Table) ── */
.tox .tox-dialog-wrap__backdrop {
  background-color: rgba(15, 23, 42, 0.4) !important;
  backdrop-filter: blur(4px) !important;
}
.tox .tox-dialog {
  border-radius: 1rem !important;
  border: 1px solid rgba(0,0,0,0.1) !important;
  box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
  overflow: hidden;
  background: #ffffff !important;
}
.tox .tox-dialog__header {
  padding: 16px 20px !important;
  border-bottom: 1px solid rgba(0,0,0,0.08) !important;
  background: #f8fafc !important;
}
.tox .tox-dialog__title {
  font-family: "Poppins", sans-serif !important;
  font-weight: 600 !important;
  font-size: 15px !important;
  color: #0f172a !important;
}
.tox .tox-dialog__body {
  padding: 8px 0 !important;
}
.tox .tox-dialog__body-content {
  padding: 12px 20px !important;
  color: #334155 !important;
}
.tox .tox-dialog__footer {
  padding: 12px 20px !important;
  border-top: 1px solid rgba(0,0,0,0.08) !important;
  background: #f8fafc !important;
}
.tox .tox-textfield,
.tox .tox-textarea,
.tox .tox-selectfield select {
  border-radius: 0.5rem !important;
  border: 1px solid rgba(0,0,0,0.15) !important;
  padding: 8px 12px !important;
  transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
  font-family: "Poppins", sans-serif !important;
  font-size: 13px !important;
  background: #ffffff !important;
  color: #334155 !important;
}
.tox .tox-textfield:focus,
.tox .tox-textarea:focus {
  border-color: rgba(0, 97, 148, 0.6) !important;
  box-shadow: 0 0 0 2px rgba(0, 97, 148, 0.12) !important;
  outline: none !important;
}
.tox .tox-label,
.tox .tox-toolbar-label {
  font-family: "Poppins", sans-serif !important;
  font-weight: 500 !important;
  font-size: 12px !important;
  margin-bottom: 4px !important;
  color: #475569 !important;
}
.tox .tox-button {
  border-radius: 0.5rem !important;
  font-family: "Poppins", sans-serif !important;
  font-weight: 500 !important;
  font-size: 13px !important;
  padding: 6px 18px !important;
  transition: all 0.2s ease !important;
}
.tox .tox-button--naked {
  border-radius: 0.375rem !important;
  color: #475569 !important;
}
.tox .tox-button--naked:hover {
  background: rgba(0,0,0,0.05) !important;
  color: #0f172a !important;
}
.tox .tox-button:not(.tox-button--naked):not(.tox-button--secondary) {
  background: linear-gradient(135deg, rgba(0, 97, 148, 1), rgba(0, 97, 148, 0.8)) !important;
  border-color: transparent !important;
  color: #ffffff !important;
}
.tox .tox-button:not(.tox-button--naked):not(.tox-button--secondary):hover {
  filter: brightness(1.1) !important;
  transform: translateY(-1px);
}
.tox .tox-button--secondary {
  background: #f1f5f9 !important;
  border: 1px solid #cbd5e1 !important;
  color: #475569 !important;
}
.tox .tox-button--secondary:hover {
  background: #e2e8f0 !important;
  color: #0f172a !important;
}

/* ── 9. Dialog tabs ── */
.tox .tox-dialog__body-nav-item {
  border-radius: 0.375rem !important;
  font-family: "Poppins", sans-serif !important;
  font-size: 13px !important;
  padding: 6px 14px !important;
  transition: all 0.15s ease !important;
  color: #475569 !important;
}
.tox .tox-dialog__body-nav-item--active {
  border-bottom-color: rgba(0, 97, 148, 1) !important;
  color: #006194 !important;
}

/* ── 10. Tooltip ── */
.tox .tox-tooltip {
  border-radius: 0.375rem !important;
  background-color: #1e293b !important;
}
.tox .tox-tooltip__body {
  font-family: "Poppins", sans-serif !important;
  font-size: 11px !important;
  padding: 4px 8px !important;
  color: #ffffff !important;
}

/* ── 11. Notifications ── */
.tox .tox-notification {
  border-radius: 0.5rem !important;
  font-family: "Poppins", sans-serif !important;
}

/* ═══════════════════════════════════════════════════
   12. Nuclear Fullscreen Defeat + Modal Styling
   ═══════════════════════════════════════════════════ */

/* Make the editor cover the screen with inner margins */
body.tox-fullscreen .tox.tox-tinymce.tox-fullscreen {
  z-index: 999999 !important;
  position: fixed !important;
  top: 24px !important;
  bottom: 24px !important;
  left: 24px !important;
  right: 24px !important;
  width: auto !important;
  height: auto !important;
  border-radius: 1rem !important;
  border: 1px solid rgba(0,0,0,0.15) !important;
  box-shadow: 0 25px 80px rgba(0,0,0,0.3), 0 0 0 100vw rgba(15, 23, 42, 0.8) !important;
}

/* Hide the problematic sticky/fixed layout elements (Sidebar & Header) */
body.tox-fullscreen aside,
body.tox-fullscreen header {
  display: none !important;
}

/* ULTIMATE STACKING CONTEXT DEFEAT using :has() */
body.tox-fullscreen *:has(.tox-tinymce.tox-fullscreen) {
  position: static !important;
  z-index: auto !important;
  transform: none !important;
  filter: none !important;
  backdrop-filter: none !important;
  contain: none !important;
  perspective: none !important;
}
</style>

