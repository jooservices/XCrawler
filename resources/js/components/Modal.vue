<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="$emit('close')">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full m-4 max-h-[90vh] overflow-hidden">
      <!-- Header -->
      <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
          <i :class="icon" class="mr-2"></i>{{ title }}
        </h2>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 overflow-y-auto max-h-[calc(90vh-180px)]">
        <slot></slot>
      </div>

      <!-- Pagination -->
      <div v-if="pagination && pagination.last_page > 1" class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <div class="text-sm text-gray-500">
          Page {{ pagination.current_page }} of {{ pagination.last_page }} (Total: {{ pagination.total }})
        </div>
        <div class="flex gap-2">
          <button 
            @click="$emit('page-change', pagination.current_page - 1)" 
            :disabled="pagination.current_page === 1"
            class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50"
          >
            Previous
          </button>
          <button 
            @click="$emit('page-change', pagination.current_page + 1)" 
            :disabled="pagination.current_page === pagination.last_page"
            class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  isOpen: Boolean,
  title: String,
  icon: String,
  pagination: Object
});
</script>
