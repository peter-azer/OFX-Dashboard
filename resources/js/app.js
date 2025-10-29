import './bootstrap';
import 'vuetify/styles';

import { createApp } from 'vue';
import vuetify from './vuetify';

import App from './components/App.vue';

const app = createApp(App);

app.use(vuetify);

app.mount('#app');
