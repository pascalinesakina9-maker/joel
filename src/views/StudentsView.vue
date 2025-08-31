<template>
  <v-container fluid>
    <div class="students-page">
      <!-- Header -->
      <div class="page-header mb-6">
        <h1 class="text-h4 font-weight-bold text-primary">
          <v-icon class="mr-3" size="40">mdi-account-group</v-icon>
          Gestion des Élèves
        </h1>
        <p class="text-h6 text-grey-darken-1 mt-2">
          {{ filteredStudents.length }} élève{{ filteredStudents.length > 1 ? 's' : '' }} 
          {{ activeFilters.length > 0 ? '(filtré' + (filteredStudents.length > 1 ? 's' : '') + ')' : '' }}
        </p>
      </div>

      <!-- Filters -->
      <ClassFilter 
        class="mb-6" 
        @filter-change="onFilterChange"
      />

      <!-- View Toggle -->
      <div class="view-controls mb-4">
        <v-btn-toggle v-model="viewMode" mandatory>
          <v-btn value="grid" variant="outlined">
            <v-icon>mdi-view-grid</v-icon>
            <span class="ml-2 d-none d-sm-inline">Grille</span>
          </v-btn>
          <v-btn value="list" variant="outlined">
            <v-icon>mdi-view-list</v-icon>
            <span class="ml-2 d-none d-sm-inline">Liste</span>
          </v-btn>
        </v-btn-toggle>
        
        <v-spacer></v-spacer>
        
        <v-btn
          color="primary"
          @click="$router.push('/inscription')"
          class="ml-4"
        >
          <v-icon class="mr-2">mdi-plus</v-icon>
          Nouvel Élève
        </v-btn>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-8">
        <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
        <p class="text-h6 mt-4">Chargement des élèves...</p>
      </div>

      <!-- Grid View -->
      <div v-else-if="viewMode === 'grid'">
        <v-row v-if="filteredStudents.length > 0">
          <v-col 
            cols="12" 
            sm="6" 
            md="4" 
            lg="3"
            v-for="student in filteredStudents" 
            :key="student.id"
          >
            <StudentCard
              :student="student"
              @view-details="viewStudentDetails"
              @edit="editStudent"
              @delete="deleteStudent"
              @payment="initiatePayment"
              @upload-photo="openPhotoUpload"
            />
          </v-col>
        </v-row>
        
        <v-empty-state
          v-else
          icon="mdi-account-search"
          title="Aucun élève trouvé"
          text="Aucun élève ne correspond aux critères de recherche."
        >
          <template v-slot:actions>
            <v-btn color="primary" @click="clearFilters">
              Effacer les filtres
            </v-btn>
          </template>
        </v-empty-state>
      </div>

      <!-- List View -->
      <div v-else>
        <v-data-table
          :headers="tableHeaders"
          :items="filteredStudents"
          :loading="loading"
          class="elevation-2 rounded-lg"
          :items-per-page="15"
          :sort-by="[{ key: 'date_inscription', order: 'desc' }]"
        >
          <template v-slot:item.profile_image="{ item }">
            <v-avatar size="40" class="my-2">
              <v-img 
                :src="item.profile_image_url || '/assets/profiles/default-avatar.png'"
                cover
              >
                <template v-slot:error>
                  <v-icon color="grey-lighten-2">mdi-account</v-icon>
                </template>
              </v-img>
            </v-avatar>
          </template>

          <template v-slot:item.nom_complet="{ item }">
            <div>
              <div class="font-weight-bold">{{ item.prenom }} {{ item.nom }}</div>
              <div class="text-caption text-grey">{{ item.classe_nom || item.classe }}</div>
            </div>
          </template>

          <template v-slot:item.statut="{ item }">
            <v-chip
              :color="getStatusColor(item.statut)"
              variant="tonal"
              size="small"
            >
              {{ item.statut }}
            </v-chip>
          </template>

          <template v-slot:item.paiement_statut="{ item }">
            <v-chip
              :color="item.paiement_statut === 'payé' ? 'success' : 'warning'"
              variant="tonal"
              size="small"
            >
              {{ item.paiement_statut }}
            </v-chip>
          </template>

          <template v-slot:item.actions="{ item }">
            <div class="d-flex gap-1">
              <v-btn
                icon="mdi-eye"
                variant="text"
                size="small"
                @click="viewStudentDetails(item)"
              ></v-btn>
              
              <v-btn
                icon="mdi-pencil"
                variant="text"
                size="small"
                color="info"
                @click="editStudent(item)"
              ></v-btn>
              
              <v-btn
                v-if="item.paiement_statut === 'non payé'"
                icon="mdi-credit-card"
                variant="text"
                size="small"
                color="success"
                @click="initiatePayment(item)"
              ></v-btn>
            </div>
          </template>
        </v-data-table>
      </div>
    </div>

    <!-- Student Details Dialog -->
    <v-dialog v-model="detailsDialog" max-width="700">
      <v-card v-if="selectedStudent">
        <v-card-title class="pa-4 bg-info">
          <div class="d-flex align-center">
            <v-avatar size="50" class="mr-4">
              <v-img 
                :src="selectedStudent.profile_image_url || '/assets/profiles/default-avatar.png'"
                cover
              >
                <template v-slot:error>
                  <v-icon color="white">mdi-account</v-icon>
                </template>
              </v-img>
            </v-avatar>
            <div>
              <span class="text-h6 text-white">
                {{ selectedStudent.prenom }} {{ selectedStudent.nom }}
              </span>
              <div class="text-body-2 text-blue-lighten-3">
                {{ selectedStudent.classe_nom || selectedStudent.classe }}
              </div>
            </div>
          </div>
        </v-card-title>

        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12" md="6">
              <div class="detail-section">
                <h4 class="text-h6 mb-3 text-primary">Informations Élève</h4>
                <div class="detail-item">
                  <strong>Date de naissance:</strong>
                  {{ formatDate(selectedStudent.date_naissance) }}
                </div>
                <div class="detail-item">
                  <strong>Âge:</strong>
                  {{ calculateAge(selectedStudent.date_naissance) }} ans
                </div>
                <div class="detail-item">
                  <strong>Classe:</strong>
                  {{ selectedStudent.classe_nom || selectedStudent.classe }}
                </div>
              </div>
            </v-col>
            
            <v-col cols="12" md="6">
              <div class="detail-section">
                <h4 class="text-h6 mb-3 text-primary">Contact Parent</h4>
                <div class="detail-item">
                  <strong>Nom:</strong>
                  {{ selectedStudent.parent_nom }}
                </div>
                <div class="detail-item">
                  <strong>Téléphone:</strong>
                  {{ selectedStudent.parent_tel }}
                </div>
                <div v-if="selectedStudent.parent_email" class="detail-item">
                  <strong>Email:</strong>
                  {{ selectedStudent.parent_email }}
                </div>
              </div>
            </v-col>
            
            <v-col cols="12" v-if="selectedStudent.adresse">
              <div class="detail-section">
                <h4 class="text-h6 mb-3 text-primary">Adresse</h4>
                <p>{{ selectedStudent.adresse }}</p>
              </div>
            </v-col>
            
            <v-col cols="12">
              <div class="detail-section">
                <h4 class="text-h6 mb-3 text-primary">Statut & Paiement</h4>
                <div class="d-flex gap-2 mb-3">
                  <v-chip :color="getStatusColor(selectedStudent.statut)" variant="tonal">
                    {{ selectedStudent.statut }}
                  </v-chip>
                  <v-chip 
                    :color="selectedStudent.paiement_statut === 'payé' ? 'success' : 'warning'" 
                    variant="tonal"
                  >
                    {{ selectedStudent.paiement_statut }}
                  </v-chip>
                </div>
                <div class="detail-item">
                  <strong>Frais de scolarité:</strong>
                  {{ formatCurrency(selectedStudent.frais_scolarite || 50000) }}
                </div>
                <div class="detail-item">
                  <strong>Date d'inscription:</strong>
                  {{ formatDate(selectedStudent.date_inscription) }}
                </div>
              </div>
            </v-col>
          </v-row>
        </v-card-text>

        <v-card-actions class="pa-4">
          <v-btn
            color="info"
            variant="outlined"
            @click="openPhotoUpload(selectedStudent)"
          >
            <v-icon class="mr-2">mdi-camera</v-icon>
            Changer Photo
          </v-btn>
          
          <v-spacer></v-spacer>
          
          <v-btn color="primary" @click="detailsDialog = false">
            Fermer
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Photo Upload Dialog -->
    <PhotoUploadDialog
      v-model="photoDialog"
      :student="selectedStudent"
      @uploaded="onPhotoUploaded"
    />

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
import { useInscriptionsStore, type Inscription } from '../stores/inscriptions'
import { useClassesStore } from '../stores/classes'
import StudentCard from '../components/StudentCard.vue'
import ClassFilter from '../components/ClassFilter.vue'
import PhotoUploadDialog from '../components/PhotoUploadDialog.vue'

const inscriptionsStore = useInscriptionsStore()
const classesStore = useClassesStore()

const viewMode = ref('grid')
const detailsDialog = ref(false)
const photoDialog = ref(false)
const selectedStudent = ref<Inscription | null>(null)
const snackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')
const activeFilters = ref<any>({})

const loading = computed(() => inscriptionsStore.loading)

const filteredStudents = computed(() => {
  let students = [...inscriptionsStore.inscriptions]
  
  // Appliquer les filtres
  if (activeFilters.value.classe_id) {
    students = students.filter(s => s.classe_id === activeFilters.value.classe_id)
  }
  
  if (activeFilters.value.statut) {
    students = students.filter(s => s.statut === activeFilters.value.statut)
  }
  
  if (activeFilters.value.paiement) {
    students = students.filter(s => s.paiement_statut === activeFilters.value.paiement)
  }
  
  if (activeFilters.value.search) {
    const search = activeFilters.value.search.toLowerCase()
    students = students.filter(s => 
      s.nom.toLowerCase().includes(search) ||
      s.prenom.toLowerCase().includes(search) ||
      s.parent_nom.toLowerCase().includes(search) ||
      s.parent_tel.includes(search)
    )
  }
  
  return students
})

const tableHeaders = [
  { title: 'Photo', value: 'profile_image', sortable: false },
  { title: 'Élève', value: 'nom_complet', sortable: false },
  { title: 'Parent', value: 'parent_nom', sortable: true },
  { title: 'Téléphone', value: 'parent_tel', sortable: false },
  { title: 'Statut', value: 'statut', sortable: true },
  { title: 'Paiement', value: 'paiement_statut', sortable: true },
  { title: 'Actions', value: 'actions', sortable: false },
]

function onFilterChange(filters: any) {
  activeFilters.value = filters
  inscriptionsStore.fetchInscriptions(filters)
}

function clearFilters() {
  activeFilters.value = {}
  inscriptionsStore.fetchInscriptions()
}

function viewStudentDetails(student: Inscription) {
  selectedStudent.value = student
  detailsDialog.value = true
}

function editStudent(student: Inscription) {
  // TODO: Implémenter l'édition
  showSnackbar('Fonctionnalité d\'édition à venir', 'info')
}

async function deleteStudent(id: number) {
  if (confirm('Êtes-vous sûr de vouloir supprimer cet élève ?')) {
    try {
      await inscriptionsStore.deleteInscription(id)
      showSnackbar('Élève supprimé avec succès', 'success')
    } catch (error) {
      showSnackbar('Erreur lors de la suppression', 'error')
    }
  }
}

function initiatePayment(student: Inscription) {
  // TODO: Implémenter le paiement
  showSnackbar('Redirection vers le paiement...', 'info')
}

function openPhotoUpload(student: Inscription) {
  selectedStudent.value = student
  photoDialog.value = true
}

function onPhotoUploaded(student: Inscription) {
  showSnackbar('Photo mise à jour avec succès', 'success')
  inscriptionsStore.fetchInscriptions()
}

function showSnackbar(message: string, color: string = 'success') {
  snackbarMessage.value = message
  snackbarColor.value = color
  snackbar.value = true
}

function getStatusColor(status: string) {
  const colors = {
    'reçu': 'info',
    'incomplet': 'warning',
    'validé': 'success',
    'rejeté': 'error',
  }
  return colors[status as keyof typeof colors] || 'grey'
}

function formatDate(dateString: string | undefined) {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('fr-FR')
}

function formatCurrency(amount: number) {
  return new Intl.NumberFormat('fr-CD', {
    style: 'currency',
    currency: 'CDF',
    minimumFractionDigits: 0,
  }).format(amount)
}

function calculateAge(birthDate: string) {
  const today = new Date()
  const birth = new Date(birthDate)
  let age = today.getFullYear() - birth.getFullYear()
  const monthDiff = today.getMonth() - birth.getMonth()
  
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--
  }
  
  return age
}

onMounted(() => {
  inscriptionsStore.fetchInscriptions()
  classesStore.fetchClasses()
})
</script>

<style scoped>
.students-page {
  animation: fadeIn 0.5s ease-out;
}

.page-header {
  text-align: center;
  padding: 24px 0;
  background: linear-gradient(135deg, rgba(25, 118, 210, 0.05) 0%, rgba(66, 165, 245, 0.05) 100%);
  border-radius: 16px;
  margin-bottom: 24px;
}

.view-controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
}

.detail-section {
  margin-bottom: 24px;
}

.detail-item {
  margin-bottom: 8px;
  line-height: 1.5;
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
  .view-controls {
    flex-direction: column;
    align-items: stretch;
  }
  
  .view-controls .v-btn-toggle {
    align-self: center;
  }
}
</style>