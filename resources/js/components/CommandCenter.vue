<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">
      <i class="fas fa-terminal mr-2"></i> Command Center
    </h3>

    <div class="space-y-4">
      <!-- Crawl Command -->
      <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <div class="flex justify-between items-center mb-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Crawl</label>
          <span class="text-xs text-gray-500 italic">Fetch photos from a specific user or group</span>
        </div>
        <div class="flex gap-2">
          <input 
            v-model="crawlUrl" 
            type="text" 
            placeholder="Enter Flickr URL or NSID" 
            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
          >
          <button 
            @click="execute('crawl', { url: crawlUrl })" 
            :disabled="loading || !crawlUrl"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md disabled:opacity-50"
          >
            <i class="fas fa-play"></i>
          </button>
        </div>
        <p class="text-xs text-gray-500 mt-1">
          <i class="fas fa-info-circle mr-1"></i>
          Example: <code>https://www.flickr.com/photos/username/</code> or <code>12345678@N00</code>
        </p>
      </div>

      <!-- Download Command -->
      <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <div class="flex justify-between items-center mb-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Download Photos</label>
          <span class="text-xs text-gray-500 italic">Download actual image files to local storage</span>
        </div>
        <div class="flex gap-2 mb-2">
          <input 
            v-model="downloadNsid" 
            type="text" 
            placeholder="Enter NSID or URL" 
            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
          >
          <input 
            v-model.number="downloadLimit" 
            type="number" 
            placeholder="Limit (optional)" 
            class="w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white px-3 py-2"
          >
          <button 
            @click="execute('download', { nsid: downloadNsid, limit: downloadLimit })" 
            :disabled="loading || !downloadNsid"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md disabled:opacity-50"
          >
            <i class="fas fa-download"></i>
          </button>
        </div>
        <p class="text-xs text-gray-500">
          <i class="fas fa-info-circle mr-1"></i>
          Downloads photos that have been crawled but not yet saved locally. Use limit to control batch size.
        </p>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-2 gap-4">
        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded">
          <button 
            @click="execute('retry', { all: true })" 
            :disabled="loading"
            class="w-full flex items-center justify-center gap-2 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md disabled:opacity-50 mb-2"
          >
            <i class="fas fa-redo"></i> Retry Failed
          </button>
          <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
            Re-queue all failed tasks for processing.
          </p>
        </div>
        
        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded">
          <button 
            @click="execute('cleanup', { older_than: '7d' })" 
            :disabled="loading"
            class="w-full flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md disabled:opacity-50 mb-2"
          >
            <i class="fas fa-trash"></i> Cleanup
          </button>
          <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
            Remove tasks older than 7 days.
          </p>
        </div>
      </div>

      <!-- Output Console -->
      <div v-if="output" class="mt-4">
        <p class="text-xs font-bold text-gray-500 uppercase mb-1">Output</p>
        <pre class="bg-gray-900 text-green-400 p-3 rounded-md text-xs overflow-x-auto max-h-40">{{ output }}</pre>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const crawlUrl = ref('');
const downloadNsid = ref('');
const downloadLimit = ref(null);
const loading = ref(false);
const output = ref('');

const execute = async (command, params) => {
  loading.value = true;
  output.value = 'Executing...';
  
  try {
    const response = await axios.post('/api/flick/commands', { command, params });
    output.value = response.data.output || response.data.message;
    if (command === 'crawl') crawlUrl.value = '';
    if (command === 'download') {
      downloadNsid.value = '';
      downloadLimit.value = null;
    }
  } catch (error) {
    output.value = error.response?.data?.message || error.message;
  } finally {
    loading.value = false;
  }
};
</script>
