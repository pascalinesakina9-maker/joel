<template>
  <v-container>
    <v-row justify="center">
      <v-col cols="12" md="8" lg="6">
        <v-card elevation="4">
          <v-card-title class="text-center pa-6 bg-primary">
            <span class="text-h5 text-white">
              <v-icon class="mr-2" color="white">mdi-account-plus</v-icon>
              Formulaire d'Inscription
            </span>
          </v-card-title>

          <v-card-text class="pa-6">
            <v-form ref="form" v-model="valid" @submit.prevent="submitInscription">
              <v-row>
                <v-col cols="12">
                  <h3 class="text-h6 mb-4 text-primary">Informations de l'élève</h3>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="inscription.nom"
                    label="Nom de famille *"
                    :rules="nameRules"
                    variant="outlined"
                    prepend-inner-icon="mdi-account"
                    required
                  ></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="inscription.prenom"
                    label="Prénom *"
                    :rules="nameRules"
                    variant="outlined"
                    prepend-inner-icon="mdi-account"
                    required
                  ></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="inscription.date_naissance"
                    label="Date de naissance *"
                    type="date"
                    :rules="dateRules"
                    variant="outlined"
                    prepend-inner-icon="mdi-calendar"
                    required
                  ></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-select
                    v-model="inscription.classe"
                    label="Classe *"
                    :items="classes"
                    :rules="requiredRules"
                    variant="outlined"
                    prepend-inner-icon="mdi-school"
                    required
                  ></v-select>
                </v-col>

                <v-col cols="12">
                  <v-divider class="my-4"></v-divider>
                  <h3 class="text-h6 mb-4 text-primary">Informations du parent/tuteur</h3>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="inscription.parent_nom"
                    label="Nom du parent *"
                    :rules="nameRules"
                    variant="outlined"
                    prepend-inner-icon="mdi-account-tie"
                    required
                  ></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="inscription.parent_tel"
                    label="Téléphone *"
                    :rules="phoneRules"
                    variant="outlined"
                    prepend-inner-icon="mdi-phone"
                    required
                  ></v-text-field>
                </v-col>

                <v-col cols="12">
                  <v-file-input
                    v-model="documentFile"
                    label="Document d'identité (photo/scan)"
                    accept="image/*,.pdf"
                    prepend-icon="mdi-paperclip"
                    variant="outlined"
                    show-size
                  ></v-file-input>
                </v-col>

                <v-col cols="12" class="text-center">
                  <v-btn
                    type="submit"
                    color="primary"
                    size="large"
                    :loading="loading"
                    :disabled="!valid"
                    class="px-8"
                  >
                    <v-icon class="mr-2">mdi-send</v-icon>
                    Soumettre l'inscription
                  </v-btn>
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Payment Modal -->
    <v-dialog v-model="paymentDialog" max-width="500" persistent>
      <v-card>
        <v-card-title class="text-center pa-6 bg-success">
          <span class="text-h5 text-white">
            <v-icon class="mr-2" color="white">mdi-credit-card</v-icon>
            Paiement des Frais
          </span>
        </v-card-title>

        <v-card-text class="pa-6 text-center">
          <v-icon size="64" color="success" class="mb-4">mdi-check-circle</v-icon>
          <h3 class="text-h6 mb-4">Inscription enregistrée avec succès !</h3>
          
          <v-alert type="info" variant="tonal" class="mb-4">
            Frais de scolarité: <strong>50 000 FC</strong>
          </v-alert>

          <p class="text-body-1 mb-4">
            Cliquez sur "Payer" pour simuler le paiement des frais de scolarité.
          </p>

          <v-row>
            <v-col cols="12" md="6">
              <v-btn
                color="success"
                block
                size="large"
                @click="simulatePayment"
                :loading="paymentLoading"
              >
                <v-icon class="mr-2">mdi-credit-card</v-icon>
                Payer (Simulation)
              </v-btn>
            </v-col>
            <v-col cols="12" md="6">
              <v-btn
                color="grey"
                block
                size="large"
                variant="outlined"
                @click="skipPayment"
              >
                Payer plus tard
              </v-btn>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Success Dialog -->
    <v-dialog v-model="successDialog" max-width="400">
      <v-card>
        <v-card-text class="pa-6 text-center">
          <v-icon size="64" color="success" class="mb-4">mdi-check-circle</v-icon>
          <h3 class="text-h6 mb-2">Paiement Confirmé !</h3>
          <p class="text-body-2">
            L'inscription a été complétée avec succès. Vous recevrez une confirmation par téléphone.
          </p>
        </v-card-text>
        <v-card-actions class="justify-center">
          <v-btn color="primary" @click="goHome">Retour à l'accueil</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Error Snackbar -->
    <v-snackbar v-model="snackbar" color="error" top>
      {{ errorMessage }}
      <template v-slot:actions>
        <v-btn variant="text" @click="snackbar = false">Fermer</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useInscriptionsStore } from '../stores/inscriptions'

const router = useRouter()
const store = useInscriptionsStore()

const valid = ref(false)
const loading = ref(false)
const paymentLoading = ref(false)
const paymentDialog = ref(false)
const successDialog = ref(false)
const snackbar = ref(false)
const errorMessage = ref('')
const documentFile = ref<File[]>([])
const currentInscriptionId = ref<number | null>(null)

const inscription = reactive({
  nom: '',
  prenom: '',
  date_naissance: '',
  classe: '',
  parent_nom: '',
  parent_tel: '',
  statut: 'reçu' as const,
  paiement_statut: 'non payé' as const,
})

const classes = [
  '1ère Primaire',
  '2ème Primaire', 
  '3ème Primaire',
  '4ème Primaire',
  '5ème Primaire',
  '6ème Primaire',
  '1ère Secondaire',
  '2ème Secondaire',
  '3ème Secondaire',
  '4ème Secondaire',
  '5ème Secondaire',
  '6ème Secondaire',
]

const nameRules = [
  (v: string) => !!v || 'Ce champ est requis',
  (v: string) => v.length >= 2 || 'Au moins 2 caractères requis',
]

const dateRules = [
  (v: string) => !!v || 'Date de naissance requise',
]

const phoneRules = [
  (v: string) => !!v || 'Numéro de téléphone requis',
  (v: string) => /^[+]?[\d\s-()]+$/.test(v) || 'Format de téléphone invalide',
]

const requiredRules = [
  (v: string) => !!v || 'Ce champ est requis',
]

async function submitInscription() {
  loading.value = true
  
  try {
    // Simulate file upload (in real app, you'd upload to server)
    const documentName = documentFile.value[0]?.name || ''
    
    const inscriptionData = {
      ...inscription,
      document: documentName,
    }
    
    const result = await store.addInscription(inscriptionData)
    currentInscriptionId.value = result.id
    paymentDialog.value = true
  } catch (error) {
    errorMessage.value = 'Erreur lors de l\'inscription. Veuillez réessayer.'
    snackbar.value = true
  } finally {
    loading.value = false
  }
}

async function simulatePayment() {
  if (!currentInscriptionId.value) return
  
  paymentLoading.value = true
  
  try {
    await store.simulatePayment(currentInscriptionId.value, 50000)
    paymentDialog.value = false
    successDialog.value = true
  } catch (error) {
    errorMessage.value = 'Erreur lors du paiement. Veuillez réessayer.'
    snackbar.value = true
  } finally {
    paymentLoading.value = false
  }
}

function skipPayment() {
  paymentDialog.value = false
  router.push('/confirmation/' + currentInscriptionId.value)
}

function goHome() {
  successDialog.value = false
  router.push('/')
}
</script>