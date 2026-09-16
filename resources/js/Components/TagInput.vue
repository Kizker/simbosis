<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  newTags: {
    type: Array,
    default: () => []
  },
  initialTags: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue', 'update:newTags']);

const query = ref('');
const showDropdown = ref(false);
const selectedTags = ref([]);
const selectedNewTags = ref([]);
const dropdownRef = ref(null);
const allTags = ref([]);

onMounted(async () => {
  // Initialize from initialTags (which have id and name)
  if (props.initialTags && props.initialTags.length > 0) {
    selectedTags.value = [...props.initialTags];
  }
  
  // Close dropdown when clicking outside
  document.addEventListener('click', handleClickOutside);
  
  // Pre-fetch all tags
  try {
    const response = await axios.get('/harmony-access/tags/search');
    allTags.value = response.data;
  } catch (error) {
    console.error('Error fetching tags:', error);
  }
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    showDropdown.value = false;
  }
};

const handleInput = () => {
  showDropdown.value = true;
};

const results = computed(() => {
  if (!query.value.trim()) return [];
  const q = query.value.trim().toLowerCase();
  return allTags.value.filter(tag => tag.name.toLowerCase().includes(q));
});

const addTag = (tag) => {
  if (!selectedTags.value.some(t => t.id === tag.id)) {
    selectedTags.value.push(tag);
    emitUpdates();
  }
  query.value = '';
  showDropdown.value = false;
};

const addNewTag = () => {
  const newTagName = query.value.trim();
  if (!newTagName) return;

  // Check if it's already added in selectedNewTags
  if (!selectedNewTags.value.includes(newTagName)) {
    // Check if it already exists in the backend results exactly
    const existing = allTags.value.find(t => t.name.toLowerCase() === newTagName.toLowerCase());
    if (existing) {
      addTag(existing);
      return;
    }

    // It's really new
    selectedNewTags.value.push(newTagName);
    emitUpdates();
  }
  query.value = '';
  showDropdown.value = false;
};

const removeTag = (tagId) => {
  selectedTags.value = selectedTags.value.filter(t => t.id !== tagId);
  emitUpdates();
};

const removeNewTag = (tagName) => {
  selectedNewTags.value = selectedNewTags.value.filter(t => t !== tagName);
  emitUpdates();
};

const emitUpdates = () => {
  emit('update:modelValue', selectedTags.value.map(t => t.id));
  emit('update:newTags', selectedNewTags.value);
};

const hasExactMatch = computed(() => {
  const q = query.value.trim().toLowerCase();
  if (!q) return true; // Don't show "Create" option if empty
  
  return results.value.some(t => t.name.toLowerCase() === q) || 
         selectedNewTags.value.some(t => t.toLowerCase() === q) ||
         selectedTags.value.some(t => t.name.toLowerCase() === q);
});

</script>

<template>
  <div class="relative" ref="dropdownRef">
    <!-- Selected Tags Area -->
    <div class="flex flex-wrap gap-2 mb-2 min-h-[38px] p-2 bg-surface-container-low dark:bg-slate-900 border border-outline-variant/30 rounded-xl items-center focus-within:ring-2 focus-within:ring-primary/20 focus-within:border-primary transition-all">
      <span v-for="tag in selectedTags" :key="tag.id" class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary/10 text-primary dark:bg-sky-500/20 dark:text-sky-300 rounded-full text-xs font-semibold">
        #{{ tag.name }}
        <button type="button" @click.prevent="removeTag(tag.id)" class="hover:bg-primary/20 dark:hover:bg-sky-500/30 rounded-full p-0.5 transition-colors">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </span>
      <span v-for="tag in selectedNewTags" :key="`new-${tag}`" class="inline-flex items-center gap-1.5 px-3 py-1 bg-secondary/10 text-secondary dark:bg-emerald-500/20 dark:text-emerald-300 rounded-full text-xs font-semibold">
        #{{ tag }}
        <button type="button" @click.prevent="removeNewTag(tag)" class="hover:bg-secondary/20 dark:hover:bg-emerald-500/30 rounded-full p-0.5 transition-colors">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </span>
      
      <!-- Input Field -->
      <input 
        type="text" 
        v-model="query" 
        @input="handleInput"
        @focus="showDropdown = true"
        @keydown.enter.prevent="hasExactMatch ? null : addNewTag()"
        class="flex-1 min-w-[120px] bg-transparent border-none p-0 text-sm text-on-surface focus:ring-0 outline-none" 
        placeholder="Cari atau ketik tag baru..."
      >
    </div>

    <!-- Dropdown Results -->
    <div v-if="showDropdown && (query || results.length > 0)" class="absolute z-50 w-full mt-1 bg-surface dark:bg-slate-800 border border-outline-variant/30 rounded-xl shadow-lg overflow-hidden max-h-60 overflow-y-auto">
        <!-- Existing Tags -->
        <button 
          v-for="tag in results" 
          :key="tag.id"
          type="button"
          @click="addTag(tag)"
          class="w-full text-left px-4 py-2.5 text-sm hover:bg-surface-variant/20 dark:hover:bg-slate-700/50 transition-colors border-b border-outline-variant/10 last:border-0 focus:bg-surface-variant/20 outline-none"
        >
          #{{ tag.name }}
        </button>
        
        <!-- Create New Tag Option -->
        <button 
          v-if="!hasExactMatch && query.trim() !== ''"
          type="button"
          @click="addNewTag()"
          class="w-full text-left px-4 py-2.5 text-sm text-primary dark:text-sky-400 font-semibold hover:bg-primary/5 dark:hover:bg-sky-500/10 transition-colors focus:bg-primary/10 outline-none flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
          Tambahkan tag "{{ query }}"
        </button>
        
        <div v-if="results.length === 0 && hasExactMatch && query.trim() !== ''" class="px-4 py-3 text-sm text-on-surface-variant text-center">
          Tag sudah ditambahkan.
        </div>
    </div>
  </div>
</template>
