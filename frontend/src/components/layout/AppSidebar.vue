<template>
  <v-navigation-drawer
    v-model="drawerOpen"
    :rail="isCollapsed && !isMobile"
    :temporary="isMobile"
    :permanent="!isMobile"
    :width="260"
    :rail-width="64"
    class="app-sidebar"
  >
    <!-- Logo -->
    <div class="sidebar-logo" :class="{ collapsed: isCollapsed && !isMobile }">
      <v-icon icon="mdi-briefcase-account" color="white" size="32" />
      <span v-if="!isCollapsed || isMobile" class="logo-text">Candidate Portal</span>
    </div>
    <v-divider color="rgba(255,255,255,0.1)" />

    <!-- Nav Items -->
    <v-list class="nav-list" nav>
      <v-tooltip
        v-for="item in navItems"
        :key="item.to"
        :text="item.title"
        location="right"
        :disabled="!isCollapsed || isMobile"
      >
        <template #activator="{ props: tooltipProps }">
          <v-list-item
            v-bind="tooltipProps"
            :to="item.to"
            :prepend-icon="item.icon"
            :title="(!isCollapsed || isMobile) ? item.title : ''"
            class="nav-item"
            active-class="nav-item-active"
            rounded="0"
          />
        </template>
      </v-tooltip>
    </v-list>

    <template #append>
      <div class="sidebar-bottom">
        <!-- Language Toggle -->
        <div v-if="!isCollapsed || isMobile" class="lang-toggle">
          <v-btn
            variant="outlined"
            size="x-small"
            :class="{ 'lang-active': currentLang === 'en' }"
            @click="setLang('en')"
          >EN</v-btn>
          <v-btn
            variant="outlined"
            size="x-small"
            :class="{ 'lang-active': currentLang === 'de' }"
            @click="setLang('de')"
          >DE</v-btn>
        </div>

        <v-divider color="rgba(255,255,255,0.1)" class="my-2" />

        <!-- User Info -->
        <div class="user-section" :class="{ collapsed: isCollapsed && !isMobile }">
          <div class="user-avatar">{{ userInitials }}</div>
          <div v-if="!isCollapsed || isMobile" class="user-info">
            <div class="user-name">{{ authStore.user?.name }}</div>
            <div class="user-company">{{ authStore.user?.company_name }}</div>
          </div>
          <v-tooltip text="Logout" location="right" :disabled="!isCollapsed || isMobile">
            <template #activator="{ props: tip }">
              <v-btn
                v-bind="tip"
                icon="mdi-logout"
                variant="text"
                size="small"
                class="logout-btn"
                @click="authStore.logout()"
              />
            </template>
          </v-tooltip>
        </div>

        <!-- Collapse Toggle (desktop only) -->
        <div v-if="!isMobile" class="collapse-toggle">
          <v-btn
            :icon="isCollapsed ? 'mdi-chevron-right' : 'mdi-chevron-left'"
            variant="text"
            size="small"
            class="collapse-btn"
            @click="isCollapsed = !isCollapsed"
          />
        </div>
      </div>
    </template>
  </v-navigation-drawer>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { useDisplay } from 'vuetify'

const props = defineProps({ modelValue: Boolean })
const emit = defineEmits(['update:modelValue'])

const { t, locale } = useI18n()
const authStore = useAuthStore()
const { mobile } = useDisplay()

const isMobile = computed(() => mobile.value)
const isCollapsed = ref(false)
const currentLang = computed(() => locale.value)

const drawerOpen = computed({
  get: () => isMobile.value ? props.modelValue : true,
  set: (v) => emit('update:modelValue', v)
})

const userInitials = computed(() => {
  const name = authStore.user?.name || ''
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

const adminNavItems = computed(() => [
  { to: '/admin/dashboard', icon: 'mdi-view-dashboard', title: t('admin.dashboard') },
  { to: '/admin/candidates', icon: 'mdi-account-group', title: t('nav.adminCandidates') },
  { to: '/admin/users', icon: 'mdi-account-tie', title: t('nav.adminUsers') },
  { to: '/admin/requests', icon: 'mdi-clipboard-list', title: t('nav.adminRequests') },
  { to: '/reports/candidates', icon: 'mdi-chart-box', title: t('nav.reports') },
  { to: '/admin/faq', icon: 'mdi-robot', title: t('nav.adminFaq') },
])

const clientNavItems = computed(() => [
  { to: '/candidates', icon: 'mdi-account-group', title: t('nav.candidates') },
  { to: '/reports/candidates', icon: 'mdi-chart-box', title: t('nav.reports') },
  { to: '/my-requests', icon: 'mdi-clipboard-list', title: t('nav.myRequests') },
])

const navItems = computed(() => authStore.isAdmin ? adminNavItems.value : clientNavItems.value)

function setLang(lang) {
  authStore.updateLanguage(lang)
}
</script>

<style scoped>
.app-sidebar {
  background: #0D1B2A !important;
  border-right: none !important;
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 16px;
  min-height: 72px;
}
.sidebar-logo.collapsed {
  justify-content: center;
  padding: 20px 8px;
}
.logo-text {
  color: white;
  font-weight: 700;
  font-size: 16px;
  white-space: nowrap;
}

.nav-list { padding: 8px 0; }

:deep(.nav-item) {
  color: #B0BEC5 !important;
  border-radius: 0 24px 24px 0 !important;
  margin: 2px 8px 2px 0;
  font-weight: 500;
  letter-spacing: 0.3px;
}
:deep(.nav-item:hover) {
  background: #1B2E45 !important;
  color: white !important;
}
:deep(.nav-item-active),
:deep(.v-list-item--active.nav-item) {
  background: #1565C0 !important;
  color: white !important;
  border-left: 3px solid #90CAF9 !important;
}
:deep(.nav-item .v-list-item__prepend .v-icon) {
  color: inherit !important;
  opacity: 1 !important;
}

.sidebar-bottom { padding: 8px 12px 16px; }

.lang-toggle { display: flex; gap: 8px; padding: 8px 0; }
:deep(.lang-toggle .v-btn) {
  border-color: rgba(255,255,255,0.4) !important;
  color: rgba(255,255,255,0.7) !important;
  min-width: 36px;
}
:deep(.lang-active) {
  border-color: white !important;
  color: white !important;
  background: rgba(255,255,255,0.1) !important;
}

.user-section {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 0;
}
.user-section.collapsed { justify-content: center; }

.user-avatar {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: #1565C0;
  color: white;
  display: flex; align-items: center; justify-content: center;
  font-weight: 600; font-size: 13px;
  flex-shrink: 0;
}
.user-info { flex: 1; overflow: hidden; }
.user-name {
  color: white; font-size: 13px; font-weight: 500;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.user-company {
  color: #B0BEC5; font-size: 11px;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
:deep(.logout-btn) { color: #B0BEC5 !important; }
:deep(.logout-btn:hover) { color: #ef5350 !important; }

.collapse-toggle { display: flex; justify-content: flex-end; padding-top: 8px; }
:deep(.collapse-btn) { color: #B0BEC5 !important; }
</style>
