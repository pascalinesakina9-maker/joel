<template>
  <v-container fluid>
    <div class="classes-page">
      <!-- Header -->
      <div class="page-header mb-6">
        <h1 class="text-h4 font-weight-bold text-primary">
          <v-icon class="mr-3" size="40">mdi-google-classroom</v-icon>
          Gestion des Classes
        </h1>
        <p class="text-h6 text-grey-darken-1 mt-2">
          {{ classes.length }} classe{{ classes.length > 1 ? 's' : '' }} configurée{{ classes.length > 1 ? 's' : '' }}
        </p>
      </div>

      <!-- Actions -->
      <div class="actions-bar mb-6">
        <v-btn
          color="primary"
          size="large"
          @click="openAddDialog"
        >
          <v-icon class="mr-2">mdi-plus</v-icon>
          Nouvelle Classe
        </v-btn>
        
        <v-spacer></v-spacer>
        
        <v-btn-toggle v-model="viewMode" mandatory>
          <v-btn value="cards" variant="outlined">
            <v-icon>mdi-view-grid</v-icon>
            <span class="ml-2 d-none d-sm-inline">Cartes</span>
          </v-btn>
          <v-btn value="table" variant="outlined">
            <v-icon>mdi-table</v-icon>
            <span class="ml-2 d-none d-sm-inline">Tableau</span>
          </v-btn>
        </v-btn-toggle>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-8">
        <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
        <p class="text-h6 mt-4">Chargement des classes...</p>
      </div>

      <!-- Cards View -->
      <div v-else-if="viewMode === 'cards'">
        <v-row>
          <v-col cols="12" sm="6" md="4" lg="3" v-for="classe in classes" :key="classe.id">
            <v-card class="classe-card" elevation="3" hover>
              <v-card-title class="pa-4" :class="getNiveauColor(classe.niveau)">
                <div class="d-flex align-center justify-space-between">
                  <div>
                    <v-icon class="mr-2" color="white">mdi-google-classroom</v-icon>
                    <span class="text-white font-weight-bold">{{ classe.nom }}</span>
                  </div>
                  
                  <v-menu>
                    <template v-slot:activator="{ props }">
                      <v-btn
                        icon="mdi-dots-vertical"
                        variant="text"
                        size="small"
                        color="white"
                        v-bind="props"
                      ></v-btn>
                    </template>
                    
                    <v-list density="compact">
                      <v-list-item @click="editClasse(classe)">
                        <v-list-item-title>
                          <v-icon class="mr-2" size="small">mdi-pencil</v-icon>
                          Modifier
                        </v-list-item-title>
                      </v-list-item>
                      
                      <v-list-item @click="deleteClasse(classe.id!)">
                        <v-list-item-title class="text-error">
                          <v-icon class="mr-2" size="small" color="error">mdi-delete</v-icon>
                          Supprimer
                        </v-list-item-title>
                      </v-list-item>
                    </v-list>
                  </v-menu>
                </div>
              </v-card-title>

              <v-card-text class="pa-4">
                <div class="classe-stats">
                  <div class="stat-row">
                    <v-icon size="20" color="info" class="mr-2">mdi-account-group</v-icon>
                    <span>{{ classe.nombre_eleves || 0 }} / {{ classe.capacite_max }} élèves</span>
                  </div>
                  
                  <div class="stat-row">
                    <v-icon size="20" color="success" class="mr-2">mdi-credit-card</v-icon>
                    <span>{{ classe.eleves_payes || 0 }} paiements</span>
                  </div>
                  
                  <div class="stat-row">
                    <v-icon size="20" color="warning" class="mr-2">mdi-currency-usd</v-icon>
                    <span>{{ formatCurrency(classe.frais_scolarite) }}</span>
                  </div>
                </div>

                <v-progress-linear
                  :model-value="getOccupationPercentage(classe)"
                  :color="getOccupationColor(classe)"
                  height="8"
                  rounded
                  class="mt-3"
                ></v-progress-linear>
                
                <p class="text-caption text-center mt-2">
                  {{ getOccupationPercentage(classe) }}% d'occupation
                </p>

                <p v-if="classe.description" class="text-body-2 text-grey-darken-1 mt-3">
                  {{ classe.description }}
                </p>
              </v-card-text>

              <v-card-actions class="pa-4 pt-0">
                <v-btn
                  color="info"
                  variant="outlined"
                  block
                  @click="viewClassStudents(classe)"
                >
                  <v-icon class="mr-2">mdi-eye</v-icon>
                  Voir les élèves
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-col>
        </v-row>
      </div>

      <!-- Table View -->
      <div v-else>
        <v-data-table
          :headers="tableHeaders"
          :items="classes"
          :loading="loading"
          class="elevation-2 rounded-lg"
          :items-per-page="10"
        >
          <template v-slot:item.niveau="{ item }">
            <v-chip
              :color="item.niveau === 'primaire' ? 'primary' : 'success'"
              variant="tonal"
              size="small"
            >
              {{ item.niveau }}
            </v-chip>
          </template>

          <template v-slot:item.occupation="{ item }">
            <div class="d-flex align-center">
              <span class="mr-2">{{ item.nombre_eleves || 0 }}/{{ item.capacite_max }}</span>
              <v-progress-linear
                :model-value="getOccupationPercentage(item)"
                :color="getOccupationColor(item)"
                height="6"
                rounded
                style="width: 60px"
              ></v-progress-linear>
            </div>
          </template>

          <template v-slot:item.frais_scolarite="{ item }">
            {{ formatCurrency(item.frais_scolarite) }}
          </template>

          <template v-slot:item.actions="{ item }">
            <div class="d-flex gap-1">
              <v-btn
                icon="mdi-eye"
                variant="text"
                size="small"
                @click="viewClassStudents(item)"
              ></v-btn>
              
              <v-btn
                icon="mdi-pencil"
                variant="text"
                size="small"
                color="info"
                @click="editClasse(item)"
              ></v-btn>
              
              <v-btn
                icon="mdi-delete"
                variant="text"
                size="small"
                color="error"
                @click="deleteClasse(item.id!)"
              ></v-btn>
            </div>
          </template>
        </v-data-table>
      </div>
    </div>

    <!-- Add/Edit Dialog -->
    <v-dialog v-model="editDialog" max-width="600" persistent>
      <v-card>
        <v-card-title class="pa-4 bg-primary">
          <span class="text-h6 text-white">
            <v-icon class="mr-2" color="white">
              {{ editingClasse?.id ? 'mdi-pencil' : 'mdi-plus' }}
            </v-icon>
            {{ editingClasse?.id ? 'Modifier' : 'Ajouter' }} une Classe
          </span>
        </v-card-title>

        <v-card-text class="pa-6">
          <v-form ref="form" v-model="formValid">
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="editingClasse.nom"
                  label="Nom de la classe *"
                  :rules="nameRules"
                  variant="outlined"
                  prepend-inner-icon="mdi-google-classroom"
                  required
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-select
                  v-model="editingClasse.niveau"
                  label="Niveau *"
                  :items="niveauOptions"
                  :rules="requiredRules"
                  variant="outlined"
                  prepend-inner-icon="mdi-school"
                  required
                ></v-select>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model.number="editingClasse.frais_scolarite"
                  label="Frais de scolarité (FC)"
                  type="number"
                  :rules="priceRules"
                  variant="outlined"
                  prepend-inner-icon="mdi-currency-usd"
                ></v-text-field>
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model.number="editingClasse.capacite_max"
                  label="Capacité maximale"
                  type="number"
                  :rules="capacityRules"
                  variant="outlined"
                  prepend-inner-icon="mdi-account-group"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="editingClasse.description"
                  label="Description (optionnel)"
                  variant="outlined"
                  prepend-inner-icon="mdi-text"
                  rows="3"
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>

        <v-card-actions class="pa-4">
          <v-btn
            color="grey"
            variant="outlined"
            @click="closeEditDialog"
            :disabled="saving"
          >
            Annuler
          </v-btn>
          
          <v-spacer></v-spacer>
          
          <v-btn
            color="primary"
            @click="saveClasse"
            :loading="saving"
            :disabled="!formValid"
          >
            <v-icon class="mr-2">mdi-content-save</v-icon>
            {{ editingClasse?.id ? 'Modifier' : 'Ajouter' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar -->
    <v-snackbar v-model="snackbar" :color="snackbarColor" top>
      {{ snackbarMessage }}
      <template v-slot:actions>
        <v-btn variant="text" @click="snackbar = false">Fermer</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useClassesStore, type Classe } from '../stores/classes'
import { useRouter } from 'vue-router'

const classesStore = useClassesStore()
const router = useRouter()

const viewMode = ref('cards')
const editDialog = ref(false)
const formValid = ref(false)
const saving = ref(false)
const snackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const editingClasse = ref<Partial<Classe>>({
  nom: '',
  niveau: 'primaire',
  frais_scolarite: 50000,
  capacite_max: 30,
  description: ''
})

const loading = computed(() => classesStore.loading)
const classes = computed(() => classesStore.classes)

const tableHeaders = [
  { title: 'Nom', value: 'nom', sortable: true },
  { title: 'Niveau', value: 'niveau', sortable: true },
  { title: 'Occupation', value: 'occupation', sortable: false },
  { title: 'Frais', value: 'frais_scolarite', sortable: true },
  { title: 'Paiements', value: 'eleves_payes', sortable: true },
  { title: 'Actions', value: 'actions', sortable: false },
]

const niveauOptions = [
  { title: 'Primaire', value: 'primaire' },
  { title: 'Secondaire', value: 'secondaire' },
]

const nameRules = [
  (v: string) => !!v || 'Nom requis',
  (v: string) => v.length >= 2 || 'Au moins 2 caractères',
]

const requiredRules = [
  (v: string) => !!v || 'Ce champ est requis',
]

const priceRules = [
  (v: number) => !!v || 'Frais requis',
  (v: number) => v > 0 || 'Les frais doivent être positifs',
]

const capacityRules = [
  (v: number) => !!v || 'Capacité requise',
  (v: number) => v > 0 || 'La capacité doit être positive',
  (v: number) => v <= 50 || 'Capacité maximale: 50 élèves',
]

function getNiveauColor(niveau: string) {
  return niveau === 'primaire' ? 'bg-primary' : 'bg-success'
}

function getOccupationPercentage(classe: Classe) {
  if (!classe.capacite_max || !classe.nombre_eleves) return 0
  return Math.round((classe.nombre_eleves / classe.capacite_max) * 100)
}

function getOccupationColor(classe: Classe) {
  const percentage = getOccupationPercentage(classe)
  if (percentage >= 90) return 'error'
  if (percentage >= 70) return 'warning'
  return 'success'
}

function formatCurrency(amount: number) {
  return new Intl.NumberFormat('fr-CD', {
    style: 'currency',
    currency: 'CDF',
    minimumFractionDigits: 0,
  }).format(amount)
}

function openAddDialog() {
  editingClasse.value = {
    nom: '',
    niveau: 'primaire',
    frais_scolarite: 50000,
    capacite_max: 30,
    description: ''
  }
  editDialog.value = true
}

function editClasse(classe: Classe) {
  editingClasse.value = { ...classe }
  editDialog.value = true
}

function closeEditDialog() {
  editDialog.value = false
  editingClasse.value = {}
}

async function saveClasse() {
  saving.value = true
  
  try {
    if (editingClasse.value.id) {
      await classesStore.updateClasse(editingClasse.value.id, editingClasse.value)
      showSnackbar('Classe modifiée avec succès', 'success')
    } else {
      await classesStore.addClass(editingClasse.value as Omit<Classe, 'id'>)
      showSnackbar('Classe ajoutée avec succès', 'success')
    }
    closeEditDialog()
  } catch (error) {
    showSnackbar('Erreur lors de la sauvegarde', 'error')
  } finally {
    saving.value = false
  }
}

async function deleteClasse(id: number) {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette classe ?')) {
    try {
      await classesStore.deleteClasse(id)
      showSnackbar('Classe supprimée avec succès', 'success')
    } catch (error) {
      showSnackbar('Erreur lors de la suppression', 'error')
    }
  }
}

function viewClassStudents(classe: Classe) {
  router.push(`/students?classe=${classe.id}`)
}

function showSnackbar(message: string, color: string = 'success') {
  snackbarMessage.value = message
  snackbarColor.value = color
  snackbar.value = true
}

onMounted(() => {
  classesStore.fetchClasses()
})
</script>

<style scoped>
.classes-page {
  animation: fadeIn 0.5s ease-out;
}

.page-header {
  text-align: center;
  padding: 24px 0;
  background: linear-gradient(135deg, rgba(25, 118, 210, 0.05) 0%, rgba(66, 165, 245, 0.05) 100%);
  border-radius: 16px;
}

.actions-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
}

.classe-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 16px;
  overflow: hidden;
}

.classe-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.classe-stats {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.stat-row {
  display: flex;
  align-items: center;
  font-size: 0.875rem;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 960px) {
  .actions-bar {
    flex-direction: column;
    align-items: stretch;
  }
  
  .actions-bar .v-btn-toggle {
    align-self: center;
  }
}
</style>