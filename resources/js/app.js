import "../css/app.css";
import "./bootstrap";
import "vuetify/styles";
import "../css/app.css";
import { createApp } from "vue";
import vuetify from "./vuetify";

import App from "./components/App.vue";

const app = createApp(App);

app.use(vuetify);

app.mount("#app");
