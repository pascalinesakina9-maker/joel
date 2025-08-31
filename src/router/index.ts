import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'Home',
    component: () => import('../views/HomePage.vue'),
  },
  {
    path: '/inscription',
    name: 'Inscription',
    component: () => import('../views/ParentInscription.vue'),
  },
  {
    path: '/students',
    name: 'Students',
    component: () => import('../views/StudentsView.vue'),
  },
  {
    path: '/classes',
    name: 'Classes',
    component: () => import('../views/ClassesView.vue'),
  },
  {
    path: '/secretariat',
    name: 'Secretariat',
    component: () => import('../views/SecretaryDashboard.vue'),
  },
  {
    path: '/confirmation/:id',
    name: 'Confirmation',
    component: () => import('../views/ConfirmationPage.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  },
})

export default router