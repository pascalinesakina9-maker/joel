<template>
  <v-app>
    <v-app-bar color="primary" prominent>
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      
      <v-toolbar-title class="text-h6 font-weight-bold">
        <v-icon class="mr-2">mdi-school</v-icon>
        EP MATENDO - Gestion Inscriptions
      </v-toolbar-title>

      <v-spacer></v-spacer>

      <v-btn icon @click="$router.push('/')">
        <v-icon>mdi-home</v-icon>
      </v-btn>
    </v-app-bar>

    <v-navigation-drawer v-model="drawer" temporary>
      <v-list>
        <v-list-item
          prepend-icon="mdi-home"
          title="Accueil"
          @click="$router.push('/')"
        ></v-list-item>
        
        <v-list-item
          prepend-icon="mdi-account-plus"
          title="Inscription"
          @click="$router.push('/inscription')"
        ></v-list-item>
        
        <v-list-item
          prepend-icon="mdi-cog"
          title="Secrétariat"
          @click="$router.push('/secretariat')"
        ></v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <router-view />
    </v-main>

    <v-footer color="grey-lighten-3" class="text-center">
      <div>
        © 2025 EP MATENDO - Système de Gestion des Inscriptions
      </div>
    </v-footer>
  </v-app>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const drawer = ref(false)
</script>