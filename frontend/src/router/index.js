import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
import CandidatesView from '@/views/CandidatesView.vue'
import CandidateDetailView from '@/views/CandidateDetailView.vue'
import MyRequestsView from '@/views/MyRequestsView.vue'
import ReportsLayout from '@/views/reports/ReportsLayout.vue'
import CandidateListReport from '@/views/reports/CandidateListReport.vue'
import RequestsStatsReport from '@/views/reports/RequestsStatsReport.vue'
import CandidateProfileReport from '@/views/reports/CandidateProfileReport.vue'
import AdminLayout from '@/views/admin/AdminLayout.vue'
import AdminDashboard from '@/views/admin/AdminDashboard.vue'
import AdminCandidates from '@/views/admin/AdminCandidates.vue'
import AdminUsers from '@/views/admin/AdminUsers.vue'
import AdminRequests from '@/views/admin/AdminRequests.vue'
import AdminFaq from '@/views/admin/AdminFaq.vue'
import AdminOccupations from '@/views/admin/AdminOccupations.vue'

const routes = [
  { path: '/', redirect: '/candidates' },
  { path: '/login', component: LoginView, meta: { public: true } },
  { path: '/candidates', component: CandidatesView, meta: { auth: true } },
  { path: '/candidates/:id', component: CandidateDetailView, meta: { auth: true } },
  { path: '/my-requests', component: MyRequestsView, meta: { auth: true, role: 'client' } },
  {
    path: '/reports',
    component: ReportsLayout,
    meta: { auth: true },
    children: [
      { path: '', redirect: '/reports/candidates' },
      { path: 'candidates', component: CandidateListReport },
      { path: 'requests-stats', component: RequestsStatsReport },
      { path: 'candidate-profile', component: CandidateProfileReport },
    ]
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { auth: true, role: 'admin' },
    children: [
      { path: '', redirect: '/admin/dashboard' },
      { path: 'dashboard', component: AdminDashboard },
      { path: 'candidates', component: AdminCandidates },
      { path: 'users', component: AdminUsers },
      { path: 'requests', component: AdminRequests },
      { path: 'faq', component: AdminFaq },
      { path: 'occupations', component: AdminOccupations },
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || 'null')

  if (to.meta.public) return next()
  if (!token) return next('/login')
  if (to.meta.role === 'admin' && user?.role !== 'admin') return next('/candidates')
  if (to.meta.role === 'client' && user?.role === 'admin') return next('/admin/dashboard')
  next()
})

export default router
