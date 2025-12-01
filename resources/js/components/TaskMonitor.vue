<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
      <h3 class="text-lg font-bold text-gray-900 dark:text-white">
        <i class="fas fa-tasks mr-2"></i> Recent Tasks
      </h3>
      <button @click="$emit('refresh')" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-sync-alt"></i>
      </button>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Contact</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Updated</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="task in tasks" :key="task.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">#{{ task.id }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ task.type }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ task.contact }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="statusClass(task.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ task.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ task.updated_at }}</td>
          </tr>
          <tr v-if="tasks.length === 0">
            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No recent tasks</td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="px-6 py-3 bg-gray-50 dark:bg-gray-700 flex justify-between items-center">
      <div class="text-sm text-gray-500">
        Page {{ pagination.current_page }} of {{ pagination.last_page }}
      </div>
      <div class="flex gap-2">
        <button 
          @click="$emit('page-change', pagination.current_page - 1)" 
          :disabled="pagination.current_page === 1"
          class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded disabled:opacity-50"
        >
          Previous
        </button>
        <button 
          @click="$emit('page-change', pagination.current_page + 1)" 
          :disabled="pagination.current_page === pagination.last_page"
          class="px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded disabled:opacity-50"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  tasks: Array,
  pagination: {
    type: Object,
    default: () => ({ current_page: 1, last_page: 1 })
  }
});

const statusClass = (status) => {
  switch (status) {
    case 'completed': return 'bg-green-100 text-green-800';
    case 'failed': return 'bg-red-100 text-red-800';
    case 'processing': 
    case 'queued_at_hub': return 'bg-blue-100 text-blue-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};
</script>
