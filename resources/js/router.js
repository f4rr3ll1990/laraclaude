import { createRouter, createWebHistory } from 'vue-router';

import Home from './views/Home.vue';
import ArticleDetail from './views/ArticleDetail.vue';
import About from './views/About.vue';
import Contacts from './views/Contacts.vue';
import NotFound from './views/NotFound.vue';
import AdminLogin from './views/admin/AdminLogin.vue';
import AdminNewsList from './views/admin/AdminNewsList.vue';
import AdminNewsForm from './views/admin/AdminNewsForm.vue';
import { isAuthenticated, isAdmin } from './auth';

const routes = [
    { path: '/', name: 'home', component: Home, meta: { title: 'F4X - Останні новини' } },
    { path: '/news/:slug', name: 'article', component: ArticleDetail, meta: { title: 'Новина - F4X' } },
    { path: '/about', name: 'about', component: About, meta: { title: 'Про F4X' } },
    { path: '/contacts', name: 'contacts', component: Contacts, meta: { title: 'Контакти F4X' } },

    // Admin panel. Every /admin route except login requires an admin session.
    { path: '/admin/login', name: 'admin-login', component: AdminLogin, meta: { title: 'Вхід — Адмінпанель F4X' } },
    { path: '/admin', name: 'admin-news', component: AdminNewsList, meta: { title: 'Новини — Адмінпанель F4X', requiresAdmin: true } },
    { path: '/admin/news/create', name: 'admin-news-create', component: AdminNewsForm, meta: { title: 'Нова новина — Адмінпанель F4X', requiresAdmin: true } },
    { path: '/admin/news/:slug/edit', name: 'admin-news-edit', component: AdminNewsForm, meta: { title: 'Редагування — Адмінпанель F4X', requiresAdmin: true } },

    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound, meta: { title: 'Сторінку не знайдено - F4X' } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

// Guard admin routes: send unauthenticated/non-admin visitors to the login
// page, and keep already-logged-in admins out of the login page.
router.beforeEach((to) => {
    if (to.meta?.requiresAdmin && !(isAuthenticated.value && isAdmin.value)) {
        return { name: 'admin-login', query: { redirect: to.fullPath } };
    }
    if (to.name === 'admin-login' && isAuthenticated.value && isAdmin.value) {
        return { name: 'admin-news' };
    }
    return true;
});

const appName = import.meta.env.VITE_APP_NAME || 'F4X';

router.afterEach((to) => {
    // Each route carries its full, already-branded document title. The article
    // route overrides this with the resolved headline once the article loads.
    document.title = to.meta?.title || appName;
});

export default router;
