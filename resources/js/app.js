import './bootstrap';
import 'vuetify/styles';
import '@fortawesome/fontawesome-free/css/all.min.css';

import { createApp } from 'vue';
import vuetify from './vuetify';

import App from './components/App.vue';

const app = createApp(App);

app.use(vuetify);

app.mount('#app');
