import { createRouter, createWebHistory } from 'vue-router';

import Home from './views/Home.vue';
import ArticleDetail from './views/ArticleDetail.vue';
import About from './views/About.vue';
import Contacts from './views/Contacts.vue';
import NotFound from './views/NotFound.vue';

const routes = [
    { path: '/', name: 'home', component: Home, meta: { title: 'F4X - Останні новини' } },
    { path: '/news/:slug', name: 'article', component: ArticleDetail, meta: { title: 'Новина - F4X' } },
    { path: '/about', name: 'about', component: About, meta: { title: 'Про F4X' } },
    { path: '/contacts', name: 'contacts', component: Contacts, meta: { title: 'Контакти F4X' } },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound, meta: { title: 'Сторінку не знайдено - F4X' } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

const appName = import.meta.env.VITE_APP_NAME || 'F4X';

router.afterEach((to) => {
    // Each route carries its full, already-branded document title. The article
    // route overrides this with the resolved headline once the article loads.
    document.title = to.meta?.title || appName;
});

export default router;
