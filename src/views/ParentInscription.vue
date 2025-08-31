<template>
  <v-container>
    <v-row justify="center">
      <v-col cols="12" md="10" lg="8">
        <div class="inscription-page">
          <!-- Progress Stepper -->
          <v-card elevation="2" class="mb-6">
            <v-card-text class="pa-4">
              <v-stepper v-model="currentStep" :items="steps" hide-actions>
                <template v-slot:item.1>
                  <v-icon color="primary">mdi-account-edit</v-icon>
                </template>
                <template v-slot:item.2>
                  <v-icon color="primary">mdi-camera</v-icon>
                </template>
                <template v-slot:item.3>
                  <v-icon color="primary">mdi-credit-card</v-icon>
                </template>
              </v-stepper>
            </v-card-text>
          </v-card>

          <!-- Main Form Card -->
          <v-card elevation="4" class="form-card">
            <v-card-title class="pa-6 bg-primary">
              <span class="text-h5 text-white">
                <v-icon class="mr-2" color="white">mdi-account-plus</v-icon>
                {{ steps[currentStep - 1].title }}
              </span>
            </v-card-title>

            <v-card-text class="pa-6">
              <!-- Step 1: Information Form -->
              <div v-if="currentStep === 1">
                <v-form ref="form" v-model="valid">
                  <v-row>
                    <v-col cols="12">
                      <h3 class="text-h6 mb-4 text-primary">
                        <v-icon class="mr-2">mdi-account</v-icon>
                        Informations de l'élève
                      </h3>
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
                        :items="classeOptions"
                        :rules="requiredRules"
                        variant="outlined"
                        prepend-inner-icon="mdi-school"
                        required
                        @update:model-value="onClasseChange"
                      ></v-select>
                    </v-col>

                    <v-col cols="12">
                      <v-textarea
                        v-model="inscription.adresse"
                        label="Adresse complète"
                        variant="outlined"
                        prepend-inner-icon="mdi-map-marker"
                        rows="2"
                      ></v-textarea>
                    </v-col>

                    <v-col cols="12">
                      <v-divider class="my-4"></v-divider>
                      <h3 class="text-h6 mb-4 text-primary">
                        <v-icon class="mr-2">mdi-account-tie</v-icon>
                        Informations du parent/tuteur
                      </h3>
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

                    <!-- Frais Information -->
                    <v-col cols="12" v-if="selectedClasseInfo">
                      <v-alert type="info" variant="tonal">
                        <div class="d-flex align-center">
                          <v-icon class="mr-3">mdi-information</v-icon>
                          <div>
                            <strong>Frais de scolarité pour {{ selectedClasseInfo.nom }}:</strong>
                            <div class="text-h6 text-success mt-1">
                              {{ formatCurrency(selectedClasseInfo.frais_scolarite) }}
                            </div>
                          </div>
                        </div>
                      </v-alert>
                    </v-col>
                  </v-row>
                </v-form>
              </div>

              <!-- Step 2: Photo Upload -->
              <div v-else-if="currentStep === 2" class="text-center">
                <div class="photo-upload-section">
                  <h3 class="text-h6 mb-4 text-primary">Photo de l'élève (optionnel)</h3>
                  
                  <div class="current-photo mb-4">
                    <v-avatar size="150" class="mb-3">
                      <v-img 
                        :src="photoPreview || '/assets/profiles/default-avatar.png'"
                        cover
                      >
                        <template v-slot:error>
                          <v-icon size="80" color="grey-lighten-2">mdi-account</v-icon>
                        </template>
                      </v-img>
                    </v-avatar>
                  </div>

                  <v-file-input
                    v-model="profileImageFile"
                    label="Choisir une photo"
                    accept="image/*"
                    prepend-icon="mdi-camera"
                    variant="outlined"
                    show-size
                    @change="onPhotoSelect"
                    class="mb-4"
                  ></v-file-input>

                  <v-alert type="info" variant="text" class="text-left">
                    <ul class="text-body-2">
                      <li>Formats acceptés: JPG, PNG, GIF, WebP</li>
                      <li>Taille maximale: 5 MB</li>
                      <li>L'image sera automatiquement redimensionnée</li>
                      <li>Vous pouvez passer cette étape et ajouter la photo plus tard</li>
                    </ul>
                  </v-alert>
                </div>
              </div>

              <!-- Step 3: Payment -->
              <div v-else-if="currentStep === 3" class="text-center">
                <div class="payment-section">
                  <v-icon size="80" color="success" class="mb-4">mdi-check-circle</v-icon>
                  <h3 class="text-h6 mb-4">Inscription enregistrée avec succès !</h3>
                  
                  <v-alert type="info" variant="tonal" class="mb-6">
                    <div class="text-center">
                      <div class="text-h6 font-weight-bold mb-2">
                        Frais de scolarité
                      </div>
                      <div class="text-h4 text-success">
                        {{ formatCurrency(selectedClasseInfo?.frais_scolarite || 50000) }}
                      </div>
                    </div>
                  </v-alert>

                  <p class="text-body-1 mb-6">
                    Cliquez sur "Payer" pour simuler le paiement des frais de scolarité 
                    et finaliser l'inscription.
                  </p>

                  <div class="payment-buttons">
                    <v-btn
                      color="success"
                      size="x-large"
                      @click="simulatePayment"
                      :loading="paymentLoading"
                      class="mb-4 mr-4"
                    >
                      <v-icon class="mr-2">mdi-credit-card</v-icon>
                      Payer Maintenant (Simulation)
                    </v-btn>
                    
                    <v-btn
                      color="grey"
                      size="large"
                      variant="outlined"
                      @click="skipPayment"
                      class="mb-4"
                    >
                      Payer Plus Tard
                    </v-btn>
                  </div>
                </div>
              </div>
            </v-card-text>

            <!-- Navigation Buttons -->
            <v-card-actions class="pa-6 pt-0">
              <v-btn
                v-if="currentStep > 1"
                color="grey"
                variant="outlined"
                @click="previousStep"
                :disabled="loading"
              >
                <v-icon class="mr-2">mdi-arrow-left</v-icon>
                Précédent
              </v-btn>
              
              <v-spacer></v-spacer>
              
              <v-btn
                v-if="currentStep < 3"
                color="primary"
                @click="nextStep"
                :loading="loading"
                :disabled="currentStep === 1 && !valid"
              >
                {{ currentStep === 1 ? 'Enregistrer' : 'Suivant' }}
                <v-icon class="ml-2">mdi-arrow-right</v-icon>
              </v-btn>
            </v-card-actions>
          </v-card>
        </div>
      </v-col>
    </v-row>

    <!-- Success Dialog -->
    <v-dialog v-model="successDialog" max-width="500" persistent>
      <v-card>
        <v-card-text class="pa-8 text-center">
          <v-icon size="100" color="success" class="mb-4">mdi-check-circle</v-icon>
          <h2 class="text-h5 font-weight-bold text-success mb-4">
            Inscription Complétée !
          </h2>
          <p class="text-body-1 mb-4">
            L'inscription et le paiement ont été traités avec succès.
          </p>
          <v-alert type="success" variant="tonal" class="mb-4">
            Vous recevrez une confirmation par téléphone dans les prochains jours.
          </v-alert>
        </v-card-text>
        
        <v-card-actions class="justify-center pa-4">
          <v-btn color="primary" size="large" @click="goHome">
            <v-icon class="mr-2">mdi-home</v-icon>
            Retour à l'accueil
          </v-btn>
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
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useInscriptionsStore } from '../stores/inscriptions'
import { useClassesStore } from '../stores/classes'

const router = useRouter()
const inscriptionsStore = useInscriptionsStore()
const classesStore = useClassesStore()

const currentStep = ref(1)
const valid = ref(false)
const loading = ref(false)
const paymentLoading = ref(false)
const successDialog = ref(false)
const snackbar = ref(false)
const errorMessage = ref('')
const documentFile = ref<File[]>([])
const profileImageFile = ref<File[]>([])
const photoPreview = ref<string | null>(null)
const currentInscriptionId = ref<number | null>(null)

const steps = [
  { title: 'Informations', value: 1 },
  { title: 'Photo', value: 2 },
  { title: 'Paiement', value: 3 },
]

const inscription = reactive({
  nom: '',
  prenom: '',
  date_naissance: '',
  classe: '',
  parent_nom: '',
  parent_tel: '',
  adresse: '',
  statut: 'reçu' as const,
  paiement_statut: 'non payé' as const,
})

const classeOptions = computed(() => 
  classesStore.classes.map(c => ({
    title: `${c.nom} - ${formatCurrency(c.frais_scolarite)}`,
    value: c.nom
  }))
)

const selectedClasseInfo = computed(() => 
  classesStore.getClasseByNom(inscription.classe)
)

const nameRules = [
  (v: string) => !!v || 'Ce champ est requis',
  (v: string) => v.length >= 2 || 'Au moins 2 caractères requis',
]

const dateRules = [
  (v: string) => !!v || 'Date de naissance requise',
  (v: string) => {
    const date = new Date(v)
    const today = new Date()
    const age = today.getFullYear() - date.getFullYear()
    return age >= 5 && age <= 25 || 'Âge doit être entre 5 et 25 ans'
  }
]

const phoneRules = [
  (v: string) => !!v || 'Numéro de téléphone requis',
  (v: string) => /^[+]?[\d\s-()]+$/.test(v) || 'Format de téléphone invalide',
]

const requiredRules = [
  (v: string) => !!v || 'Ce champ est requis',
]

function onClasseChange() {
  // Mettre à jour les informations de frais
}

function onPhotoSelect() {
  if (profileImageFile.value && profileImageFile.value.length > 0) {
    const file = profileImageFile.value[0]
    
    if (file.size > 5 * 1024 * 1024) {
      errorMessage.value = 'Fichier trop volumineux. Maximum 5MB'
      snackbar.value = true
      profileImageFile.value = []
      return
    }
    
    const reader = new FileReader()
    reader.onload = (e) => {
      photoPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  } else {
    photoPreview.value = null
  }
}

async function nextStep() {
  if (currentStep.value === 1) {
    await submitInscription()
  } else {
    currentStep.value++
  }
}

function previousStep() {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

async function submitInscription() {
  loading.value = true
  
  try {
    const documentName = documentFile.value[0]?.name || ''
    
    const inscriptionData = {
      ...inscription,
      document: documentName,
    }
    
    const result = await inscriptionsStore.addInscription(inscriptionData)
    currentInscriptionId.value = result.id
    
    // Upload photo if provided
    if (profileImageFile.value && profileImageFile.value.length > 0) {
      try {
        await inscriptionsStore.uploadProfileImage(result.id, profileImageFile.value[0])
      } catch (error) {
        console.warn('Erreur upload photo:', error)
      }
    }
    
    currentStep.value = 2
  } catch (error) {
    errorMessage.value = 'Erreur lors de l\'inscription. Veuillez réessayer.'
    snackbar.value = true
  } finally {
    loading.value = false
  }
}

async function simulatePayment() {
  if (!currentInscriptionId.value || !selectedClasseInfo.value) return
  
  paymentLoading.value = true
  
  try {
    await inscriptionsStore.simulatePayment(
      currentInscriptionId.value, 
      selectedClasseInfo.value.frais_scolarite
    )
    successDialog.value = true
  } catch (error) {
    errorMessage.value = 'Erreur lors du paiement. Veuillez réessayer.'
    snackbar.value = true
  } finally {
    paymentLoading.value = false
  }
}

function skipPayment() {
  router.push('/confirmation/' + currentInscriptionId.value)
}

function goHome() {
  successDialog.value = false
  router.push('/')
}

function formatCurrency(amount: number) {
  return new Intl.NumberFormat('fr-CD', {
    style: 'currency',
    currency: 'CDF',
    minimumFractionDigits: 0,
  }).format(amount)
}

onMounted(() => {
  classesStore.fetchClasses()
})
</script>

<style scoped>
.inscription-page {
  animation: fadeIn 0.6s ease-out;
}

.form-card {
  border-radius: 16px;
  overflow: hidden;
}

.photo-upload-section {
  padding: 24px;
}

.current-photo {
  border: 3px dashed rgba(var(--v-theme-primary), 0.3);
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
  transition: border-color 0.3s ease;
}

.current-photo:hover {
  border-color: rgba(var(--v-theme-primary), 0.6);
}

.payment-section {
  padding: 24px;
}

.payment-buttons {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 600px) {
  .payment-buttons {
    width: 100%;
  }
  
  .payment-buttons .v-btn {
    width: 100%;
    max-width: 300px;
  }
}
</style>