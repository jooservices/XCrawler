import './bootstrap';
import { createApp } from 'vue';
import Dashboard from './components/Dashboard.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const app = createApp(Dashboard);
app.mount('#app');
