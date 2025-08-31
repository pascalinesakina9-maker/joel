<template>
  <v-app>
    <v-app-bar 
      color="primary" 
      prominent 
      elevation="4"
      class="app-bar"
    >
      <v-app-bar-nav-icon 
        @click="drawer = !drawer"
        class="d-md-none"
      ></v-app-bar-nav-icon>
      
      <v-toolbar-title class="text-h6 font-weight-bold d-flex align-center">
        <v-icon class="mr-2" size="32">mdi-school</v-icon>
        <div>
          <div class="title-main">EP MATENDO</div>
          <div class="title-sub d-none d-sm-block">Gestion des Inscriptions</div>
        </div>
      </v-toolbar-title>

      <v-spacer></v-spacer>

      <!-- Desktop Navigation -->
      <div class="d-none d-md-flex align-center">
        <v-btn
          v-for="item in navigationItems"
          :key="item.path"
          :to="item.path"
          variant="text"
          color="white"
          class="mx-1"
          :class="{ 'v-btn--active': $route.path === item.path }"
        >
          <v-icon class="mr-2">{{ item.icon }}</v-icon>
          {{ item.title }}
        </v-btn>
      </div>

      <!-- Notification Bell -->
      <v-btn icon class="ml-2">
        <v-badge :content="notificationCount" :model-value="notificationCount > 0" color="error">
          <v-icon>mdi-bell</v-icon>
        </v-badge>
      </v-btn>
    </v-app-bar>

    <!-- Mobile Navigation Drawer -->
    <v-navigation-drawer v-model="drawer" temporary class="nav-drawer">
      <v-list>
        <v-list-item class="drawer-header pa-4">
          <div class="text-center">
            <v-icon size="48" color="primary" class="mb-2">mdi-school</v-icon>
            <div class="text-h6 font-weight-bold text-primary">EP MATENDO</div>
            <div class="text-body-2 text-grey">Gestion Inscriptions</div>
          </div>
        </v-list-item>
        
        <v-divider></v-divider>
        
        <v-list-item
          v-for="item in navigationItems"
          :key="item.path"
          :prepend-icon="item.icon"
          :title="item.title"
          :to="item.path"
          @click="drawer = false"
          class="nav-item"
        ></v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-main class="main-content">
      <router-view v-slot="{ Component }">
        <transition name="page" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </v-main>

    <v-footer color="grey-lighten-3" class="footer">
      <v-container>
        <v-row align="center">
          <v-col cols="12" md="6">
            <div class="d-flex align-center">
              <v-icon class="mr-2" color="primary">mdi-school</v-icon>
              <span class="font-weight-bold">EP MATENDO</span>
            </div>
          </v-col>
          
          <v-col cols="12" md="6" class="text-md-right">
            <div class="text-body-2 text-grey-darken-1">
              © 2025 Système de Gestion des Inscriptions
            </div>
          </v-col>
        </v-row>
      </v-container>
    </v-footer>
  </v-app>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useInscriptionsStore } from './stores/inscriptions'

const inscriptionsStore = useInscriptionsStore()
const drawer = ref(false)

const navigationItems = [
  { title: 'Accueil', path: '/', icon: 'mdi-home' },
  { title: 'Inscription', path: '/inscription', icon: 'mdi-account-plus' },
  { title: 'Élèves', path: '/students', icon: 'mdi-account-group' },
  { title: 'Classes', path: '/classes', icon: 'mdi-google-classroom' },
  { title: 'Secrétariat', path: '/secretariat', icon: 'mdi-cog' },
]

const notificationCount = computed(() => {
  const stats = inscriptionsStore.statistiques
  return stats.recu + stats.incomplet
})

onMounted(() => {
  inscriptionsStore.fetchInscriptions()
})
</script>

<style scoped>
.app-bar {
  backdrop-filter: blur(10px);
}

.title-main {
  font-size: 1.25rem;
  line-height: 1.2;
}

.title-sub {
  font-size: 0.75rem;
  opacity: 0.9;
  line-height: 1;
}

.nav-drawer {
  border-radius: 0 16px 16px 0;
}

.drawer-header {
  background: linear-gradient(135deg, rgba(25, 118, 210, 0.05) 0%, rgba(66, 165, 245, 0.05) 100%);
  margin-bottom: 8px;
}

.nav-item {
  border-radius: 8px;
  margin: 4px 8px;
  transition: all 0.2s ease;
}

.nav-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.1);
}

.main-content {
  min-height: calc(100vh - 64px - 60px);
  background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
}

.footer {
  border-top: 1px solid rgba(0, 0, 0, 0.1);
}

/* Page Transitions */
.page-enter-active,
.page-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.page-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.page-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}

/* Active navigation button */
.v-btn--active {
  background-color: rgba(255, 255, 255, 0.2) !important;
}

@media (max-width: 960px) {
  .title-main {
    font-size: 1.1rem;
  }
  
  .title-sub {
    display: none !important;
  }
}
</style>