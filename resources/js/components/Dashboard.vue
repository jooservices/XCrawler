<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900 font-sans text-gray-900 dark:text-gray-100">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          <i class="fas fa-spider text-blue-600 mr-2"></i> XCrawler Dashboard
        </h1>
        <div class="text-sm text-gray-500">
          Last updated: {{ lastUpdated }}
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <StatsCard title="Total Contacts" :value="stats.contacts?.total || 0" icon="fas fa-users" @click="openContactsModal" />
        <StatsCard title="Monitored" :value="stats.contacts?.monitored || 0" icon="fas fa-eye" />
        <StatsCard title="Photos Fetched" :value="stats.photos?.total || 0" icon="fas fa-images" @click="openPhotosModal" />
        <StatsCard title="Downloaded" :value="stats.photos?.downloaded || 0" icon="fas fa-download" />
        <StatsCard title="Missed Files" :value="stats.photos?.missed || 0" icon="fas fa-exclamation-triangle" class="text-red-600" />
      </div>

      <!-- Task Status Grid -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border-l-4 border-gray-400">
          <div class="text-gray-500 text-xs uppercase font-bold">Pending</div>
          <div class="text-2xl font-bold">{{ stats.tasks?.pending || 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border-l-4 border-blue-500">
          <div class="text-blue-500 text-xs uppercase font-bold">Processing</div>
          <div class="text-2xl font-bold">{{ stats.tasks?.processing || 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border-l-4 border-green-500">
          <div class="text-green-500 text-xs uppercase font-bold">Completed</div>
          <div class="text-2xl font-bold">{{ stats.tasks?.completed || 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border-l-4 border-red-500">
          <div class="text-red-500 text-xs uppercase font-bold">Failed</div>
          <div class="text-2xl font-bold">{{ stats.tasks?.failed || 0 }}</div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content: Task Monitor -->
        <div class="lg:col-span-2">
          <TaskMonitor :tasks="tasks" :pagination="taskPagination" @refresh="fetchTasks" @page-change="changeTaskPage" />
        </div>

        <!-- Sidebar: Command Center -->
        <div>
          <CommandCenter />
        </div>
      </div>
    </main>

    <!-- Contacts Modal -->
    <Modal 
      :isOpen="contactsModalOpen" 
      title="All Contacts" 
      icon="fas fa-users"
      :pagination="contactsPagination"
      @close="contactsModalOpen = false"
      @page-change="changeContactsPage"
    >
      <table class="min-w-full">
        <thead>
          <tr class="border-b dark:border-gray-700">
            <th class="px-4 py-2 text-left">NSID</th>
            <th class="px-4 py-2 text-left">Username</th>
            <th class="px-4 py-2 text-left">Real Name</th>
            <th class="px-4 py-2 text-left">Monitored</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="contact in contacts" :key="contact.id" class="border-b dark:border-gray-700">
            <td class="px-4 py-2 text-sm">{{ contact.nsid }}</td>
            <td class="px-4 py-2 text-sm">{{ contact.username || '-' }}</td>
            <td class="px-4 py-2 text-sm">{{ contact.realname || '-' }}</td>
            <td class="px-4 py-2">
              <span v-if="contact.is_monitored" class="text-green-600"><i class="fas fa-check"></i></span>
              <span v-else class="text-gray-400">-</span>
            </td>
          </tr>
        </tbody>
      </table>
    </Modal>

    <!-- Photos Modal -->
    <Modal 
      :isOpen="photosModalOpen" 
      title="All Photos" 
      icon="fas fa-images"
      :pagination="photosPagination"
      @close="photosModalOpen = false"
      @page-change="changePhotosPage"
    >
      <table class="min-w-full">
        <thead>
          <tr class="border-b dark:border-gray-700">
            <th class="px-4 py-2 text-left">ID</th>
            <th class="px-4 py-2 text-left">Title</th>
            <th class="px-4 py-2 text-left">Owner</th>
            <th class="px-4 py-2 text-left">Downloaded</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="photo in photos" :key="photo.id" class="border-b dark:border-gray-700">
            <td class="px-4 py-2 text-sm">{{ photo.flickr_id }}</td>
            <td class="px-4 py-2 text-sm">{{ photo.title || '-' }}</td>
            <td class="px-4 py-2 text-sm">{{ photo.owner?.username || photo.owner_nsid }}</td>
            <td class="px-4 py-2">
              <span v-if="photo.downloaded_at" class="text-green-600"><i class="fas fa-check"></i></span>
              <span v-else class="text-gray-400">-</span>
            </td>
          </tr>
        </tbody>
      </table>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import StatsCard from './StatsCard.vue';
import CommandCenter from './CommandCenter.vue';
import TaskMonitor from './TaskMonitor.vue';
import Modal from './Modal.vue';

const stats = ref({});
const tasks = ref([]);
const lastUpdated = ref('');
const taskPagination = ref({ current_page: 1, last_page: 1 });

// Modal states
const contactsModalOpen = ref(false);
const contacts = ref([]);
const contactsPagination = ref({ current_page: 1, last_page: 1 });

const photosModalOpen = ref(false);
const photos = ref([]);
const photosPagination = ref({ current_page: 1, last_page: 1 });

let timer = null;

const fetchStats = async () => {
  try {
    const res = await axios.get('/api/flick/stats');
    stats.value = res.data;
    lastUpdated.value = new Date().toLocaleTimeString();
  } catch (error) {
    console.error("Failed to fetch stats", error);
  }
};

const fetchTasks = async (page = 1) => {
  try {
    const res = await axios.get('/api/flick/tasks', { params: { page, per_page: 20 } });
    tasks.value = res.data.data;
    taskPagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      total: res.data.total
    };
  } catch (error) {
    console.error("Failed to fetch tasks", error);
  }
};

const openContactsModal = async () => {
  contactsModalOpen.value = true;
  await fetchContacts(1);
};

const fetchContacts = async (page = 1) => {
  try {
    const res = await axios.get('/api/flick/contacts', { params: { page, per_page: 50 } });
    contacts.value = res.data.data;
    contactsPagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      total: res.data.total
    };
  } catch (error) {
    console.error("Failed to fetch contacts", error);
  }
};

const openPhotosModal = async () => {
  photosModalOpen.value = true;
  await fetchPhotos(1);
};

const fetchPhotos = async (page = 1) => {
  try {
    const res = await axios.get('/api/flick/photos', { params: { page, per_page: 50 } });
    photos.value = res.data.data;
    photosPagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      total: res.data.total
    };
  } catch (error) {
    console.error("Failed to fetch photos", error);
  }
};

const changeTaskPage = (page) => fetchTasks(page);
const changeContactsPage = (page) => fetchContacts(page);
const changePhotosPage = (page) => fetchPhotos(page);

onMounted(() => {
  fetchStats();
  fetchTasks();
  timer = setInterval(() => {
    fetchStats();
    fetchTasks(taskPagination.value.current_page);
  }, 5000); // Refresh every 5s
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>
