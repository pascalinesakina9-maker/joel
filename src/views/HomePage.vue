<template>
  <div class="home-page">
    <!-- Hero Section -->
    <section class="hero-section">
      <v-container>
        <v-row justify="center" align="center" class="min-height-80vh">
          <v-col cols="12" md="8" lg="6">
            <div class="hero-content text-center">
              <div class="school-logo mb-6">
                <v-icon size="120" color="primary" class="logo-icon">mdi-school</v-icon>
              </div>
              
              <h1 class="hero-title text-h3 font-weight-bold text-primary mb-4">
                EP MATENDO
              </h1>
              
              <p class="hero-subtitle text-h5 text-grey-darken-1 mb-8">
                Système de Gestion des Inscriptions Scolaires
              </p>
              
              <div class="hero-stats mb-8">
                <v-row>
                  <v-col cols="4">
                    <div class="stat-item">
                      <div class="stat-number text-h4 font-weight-bold text-primary">
                        {{ stats.total }}
                      </div>
                      <div class="stat-label text-body-2 text-grey">
                        Inscriptions
                      </div>
                    </div>
                  </v-col>
                  <v-col cols="4">
                    <div class="stat-item">
                      <div class="stat-number text-h4 font-weight-bold text-success">
                        {{ stats.valide }}
                      </div>
                      <div class="stat-label text-body-2 text-grey">
                        Validées
                      </div>
                    </div>
                  </v-col>
                  <v-col cols="4">
                    <div class="stat-item">
                      <div class="stat-number text-h4 font-weight-bold text-info">
                        {{ classes.length }}
                      </div>
                      <div class="stat-label text-body-2 text-grey">
                        Classes
                      </div>
                    </div>
                  </v-col>
                </v-row>
              </div>
              
              <div class="action-buttons">
                <v-btn
                  color="primary"
                  size="x-large"
                  class="mr-4 mb-4"
                  elevation="4"
                  @click="$router.push('/inscription')"
                >
                  <v-icon class="mr-2">mdi-account-plus</v-icon>
                  Nouvelle Inscription
                </v-btn>
                
                <v-btn
                  color="success"
                  size="x-large"
                  variant="outlined"
                  class="mb-4"
                  @click="$router.push('/secretariat')"
                >
                  <v-icon class="mr-2">mdi-cog</v-icon>
                  Secrétariat
                </v-btn>
              </div>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Features Section -->
    <section class="features-section py-16">
      <v-container>
        <div class="text-center mb-12">
          <h2 class="text-h4 font-weight-bold text-primary mb-4">
            Fonctionnalités Principales
          </h2>
          <p class="text-h6 text-grey-darken-1">
            Un système complet pour gérer efficacement les inscriptions
          </p>
        </div>
        
        <v-row>
          <v-col cols="12" md="4" v-for="feature in features" :key="feature.title">
            <v-card 
              class="feature-card h-100" 
              elevation="2"
              hover
            >
              <v-card-text class="text-center pa-6">
                <div class="feature-icon mb-4">
                  <v-icon :size="60" :color="feature.color">{{ feature.icon }}</v-icon>
                </div>
                <h3 class="text-h6 font-weight-bold mb-3">{{ feature.title }}</h3>
                <p class="text-body-2 text-grey-darken-1">{{ feature.description }}</p>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Quick Access Section -->
    <section class="quick-access-section py-16 bg-grey-lighten-4">
      <v-container>
        <div class="text-center mb-8">
          <h2 class="text-h4 font-weight-bold text-primary mb-4">
            Accès Rapide
          </h2>
        </div>
        
        <v-row justify="center">
          <v-col cols="12" sm="6" md="4" lg="3" v-for="action in quickActions" :key="action.title">
            <v-card 
              class="quick-action-card text-center" 
              :color="action.color"
              variant="tonal"
              hover
              @click="$router.push(action.route)"
            >
              <v-card-text class="pa-6">
                <v-icon :size="48" :color="action.color" class="mb-3">{{ action.icon }}</v-icon>
                <h4 class="text-h6 font-weight-bold mb-2">{{ action.title }}</h4>
                <p class="text-body-2">{{ action.description }}</p>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useInscriptionsStore } from '../stores/inscriptions'
import { useClassesStore } from '../stores/classes'

const inscriptionsStore = useInscriptionsStore()
const classesStore = useClassesStore()

const stats = computed(() => inscriptionsStore.statistiques)
const classes = computed(() => classesStore.classes)

const features = [
  {
    title: 'Inscription Simplifiée',
    description: 'Formulaire intuitif pour les parents avec validation en temps réel',
    icon: 'mdi-account-plus',
    color: 'primary'
  },
  {
    title: 'Gestion Complète',
    description: 'Tableau de bord pour le secrétariat avec toutes les fonctionnalités',
    icon: 'mdi-cog',
    color: 'success'
  },
  {
    title: 'Paiement Sécurisé',
    description: 'Système de paiement intégré avec suivi des transactions',
    icon: 'mdi-credit-card',
    color: 'info'
  },
  {
    title: 'Suivi en Temps Réel',
    description: 'Notifications et mises à jour automatiques du statut',
    icon: 'mdi-bell',
    color: 'warning'
  },
  {
    title: 'Export de Données',
    description: 'Exportation facile des données en format CSV',
    icon: 'mdi-download',
    color: 'purple'
  },
  {
    title: 'Interface Mobile',
    description: 'Optimisé pour tous les appareils et tailles d\'écran',
    icon: 'mdi-cellphone',
    color: 'teal'
  }
]

const quickActions = [
  {
    title: 'Nouvelle Inscription',
    description: 'Inscrire un nouvel élève',
    icon: 'mdi-account-plus',
    color: 'primary',
    route: '/inscription'
  },
  {
    title: 'Voir les Élèves',
    description: 'Consulter tous les élèves',
    icon: 'mdi-account-group',
    color: 'info',
    route: '/students'
  },
  {
    title: 'Gestion Classes',
    description: 'Gérer les classes',
    icon: 'mdi-google-classroom',
    color: 'success',
    route: '/classes'
  },
  {
    title: 'Secrétariat',
    description: 'Tableau de bord admin',
    icon: 'mdi-cog',
    color: 'warning',
    route: '/secretariat'
  }
]

onMounted(() => {
  inscriptionsStore.fetchInscriptions()
  classesStore.fetchClasses()
})
</script>

<style scoped>
.home-page {
  min-height: 100vh;
}

.hero-section {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 80vh;
  display: flex;
  align-items: center;
}

.min-height-80vh {
  min-height: 80vh;
}

.hero-content {
  animation: fadeInUp 0.8s ease-out;
}

.logo-icon {
  animation: pulse 2s infinite;
  filter: drop-shadow(0 4px 8px rgba(25, 118, 210, 0.3));
}

.hero-title {
  background: linear-gradient(45deg, #1976D2, #42A5F5);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-item {
  padding: 16px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  transition: transform 0.3s ease;
}

.stat-item:hover {
  transform: translateY(-2px);
}

.feature-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 16px;
}

.feature-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15) !important;
}

.feature-icon {
  background: rgba(var(--v-theme-surface), 0.1);
  border-radius: 50%;
  width: 100px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  transition: transform 0.3s ease;
}

.feature-card:hover .feature-icon {
  transform: scale(1.1);
}

.quick-action-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  border-radius: 16px;
}

.quick-action-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.features-section {
  background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

@media (max-width: 960px) {
  .hero-title {
    font-size: 2.5rem !important;
  }
  
  .hero-subtitle {
    font-size: 1.25rem !important;
  }
  
  .action-buttons {
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  
  .action-buttons .v-btn {
    width: 100%;
    max-width: 300px;
  }
}
</style>