<script setup>
defineProps({
  type: {
    type: String,
    default: 'button'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  variant: {
    type: String,
    default: 'mesh-primary' // 'mesh-primary', 'mesh-danger', 'secondary', 'ghost'
  }
});
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    class="btn-ui inline-flex items-center justify-center font-semibold text-sm px-5 py-2.5 rounded-full transition-all duration-200"
    :class="{
      'btn-mesh-primary': variant === 'mesh-primary',
      'btn-simbiosis-save': variant === 'success' || variant === 'save',
      'btn-simbiosis-edit': variant === 'edit' || variant === 'warning',
      'btn-simbiosis-delete': variant === 'danger' || variant === 'delete' || variant === 'mesh-danger',
      'btn-simbiosis-neutral': variant === 'secondary' || variant === 'neutral',
      'btn-ui-ghost': variant === 'ghost',
      'opacity-55 cursor-not-allowed scale-100': disabled
    }"
  >
    <slot />
  </button>
</template>
