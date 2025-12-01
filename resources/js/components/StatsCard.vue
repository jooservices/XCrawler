<template>
  <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow cursor-pointer hover:shadow-lg transition" @click="$emit('click')">
    <div class="flex items- center justify-between">
      <div>
        <p class="text-sm text-gray-500 dark:text-gray-400 uppercase font-bold">{{ title }}</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ value }}</p>
      </div>
      <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300">
        <i :class="icon" class="text-xl"></i>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  value: [Number, String],
  icon: String
});
</script>
