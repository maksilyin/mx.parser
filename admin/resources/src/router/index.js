import { createRouter, createWebHistory } from 'vue-router'
import App from "@/components/App";

const routes = [
  {
    path: '/',
    name: 'home',
    component: App,
  },
  {
    path: '/bitrix/admin/mx_parser.php',
    name: 'admin',
    component: App,
  },
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
