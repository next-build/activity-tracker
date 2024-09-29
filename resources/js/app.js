import { createApp } from "vue";
import App from './App.vue';
import routes from './routes';
import { createMemoryHistory, createRouter } from 'vue-router';

window.ActivityTracker.basePath = '/' + window.ActivityTracker.path;
let routerBasePath = window.ActivityTracker.basePath + '/';
if (window.ActivityTracker.path === '' || window.ActivityTracker.path === '/') {
    routerBasePath = '/';
    window.ActivityTracker.basePath = '';
}

const router = createRouter({
    history: createMemoryHistory('/activity-tracker/'),
    routes,
    base: routerBasePath,
});

const app = createApp(App);
app.use(router);
app.mount("#app");
