<template>
  <v-container>
    <v-row justify="center">
      <v-col cols="12" md="6">
        <v-card elevation="4" class="text-center">
          <v-card-text class="pa-8">
            <v-icon size="80" color="success" class="mb-4">
              mdi-check-circle
            </v-icon>
            
            <h2 class="text-h4 font-weight-bold text-success mb-4">
              Inscription Confirmée !
            </h2>
            
            <p class="text-h6 mb-4">
              Merci pour votre inscription à l'EP MATENDO
            </p>
            
            <v-alert type="info" variant="tonal" class="mb-4">
              Votre dossier sera traité dans les prochains jours. 
              Vous serez contacté par téléphone pour confirmation.
            </v-alert>
            
            <p class="text-body-1 mb-6">
              N'oubliez pas d'effectuer le paiement des frais de scolarité 
              pour finaliser l'inscription.
            </p>
            
            <v-btn
              color="primary"
              size="large"
              @click="$router.push('/')"
              class="px-8"
            >
              <v-icon class="mr-2">mdi-home</v-icon>
              Retour à l'accueil
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
// Page de confirmation simple
</script>