<template>
  <v-container fluid>
    <v-row justify="center" align="center" style="min-height: 80vh">
      <v-col cols="12" md="8" lg="6">
        <v-card class="mx-auto" elevation="8">
          <v-card-title class="text-center pa-6">
            <div>
              <v-icon size="64" color="primary" class="mb-4">mdi-school</v-icon>
              <h1 class="text-h4 font-weight-bold text-primary">
                EP MATENDO
              </h1>
              <p class="text-h6 text-grey-darken-1 mt-2">
                Système de Gestion des Inscriptions
              </p>
            </div>
          </v-card-title>

          <v-card-text class="pa-6">
            <v-row>
              <v-col cols="12" md="6">
                <v-card 
                  class="h-100" 
                  color="primary" 
                  variant="outlined"
                  hover
                  @click="$router.push('/inscription')"
                  style="cursor: pointer"
                >
                  <v-card-text class="text-center pa-6">
                    <v-icon size="48" color="primary" class="mb-3">
                      mdi-account-plus
                    </v-icon>
                    <h3 class="text-h6 font-weight-bold mb-2">
                      Espace Parents
                    </h3>
                    <p class="text-body-2">
                      Inscrire votre enfant et effectuer le paiement des frais
                    </p>
                  </v-card-text>
                </v-card>
              </v-col>

              <v-col cols="12" md="6">
                <v-card 
                  class="h-100" 
                  color="success" 
                  variant="outlined"
                  hover
                  @click="$router.push('/secretariat')"
                  style="cursor: pointer"
                >
                  <v-card-text class="text-center pa-6">
                    <v-icon size="48" color="success" class="mb-3">
                      mdi-cog
                    </v-icon>
                    <h3 class="text-h6 font-weight-bold mb-2">
                      Secrétariat
                    </h3>
                    <p class="text-body-2">
                      Gérer et valider les dossiers d'inscription
                    </p>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
// Page d'accueil simple avec navigation
</script>

<style scoped>
.v-card {
  transition: transform 0.2s ease-in-out;
}

.v-card:hover {
  transform: translateY(-2px);
}
</style>