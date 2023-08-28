import { createApp } from 'vue';
import App from './index.vue';
import router from './router';
import './assets/sass/main.sass';

createApp(App)
    .use(router)
    .mount('#app')
