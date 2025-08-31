<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <v-card elevation="2">
          <v-card-title class="pa-6 bg-primary">
            <span class="text-h5 text-white">
              <v-icon class="mr-2" color="white">mdi-cog</v-icon>
              Tableau de Bord - Secrétariat
            </span>
          </v-card-title>

          <v-card-text class="pa-6">
            <!-- Statistics Cards -->
            <v-row class="mb-6">
              <v-col cols="12" sm="6" md="3">
                <v-card color="info" variant="tonal">
                  <v-card-text class="text-center">
                    <v-icon size="40" color="info" class="mb-2">mdi-file-document</v-icon>
                    <div class="text-h6 font-weight-bold">{{ stats.total }}</div>
                    <div class="text-body-2">Total Inscriptions</div>
                  </v-card-text>
                </v-card>
              </v-col>
              
              <v-col cols="12" sm="6" md="3">
                <v-card color="success" variant="tonal">
                  <v-card-text class="text-center">
                    <v-icon size="40" color="success" class="mb-2">mdi-check</v-icon>
                    <div class="text-h6 font-weight-bold">{{ stats.valide }}</div>
                    <div class="text-body-2">Validées</div>
                  </v-card-text>
                </v-card>
              </v-col>
              
              <v-col cols="12" sm="6" md="3">
                <v-card color="warning" variant="tonal">
                  <v-card-text class="text-center">
                    <v-icon size="40" color="warning" class="mb-2">mdi-clock</v-icon>
                    <div class="text-h6 font-weight-bold">{{ stats.recu }}</div>
                    <div class="text-body-2">En Attente</div>
                  </v-card-text>
                </v-card>
              </v-col>
              
              <v-col cols="12" sm="6" md="3">
                <v-card color="success" variant="tonal">
                  <v-card-text class="text-center">
                    <v-icon size="40" color="success" class="mb-2">mdi-credit-card</v-icon>
                    <div class="text-h6 font-weight-bold">{{ stats.paye }}</div>
                    <div class="text-body-2">Paiements</div>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>

            <!-- Filters -->
            <v-row class="mb-4">
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="search"
                  label="Rechercher..."
                  prepend-inner-icon="mdi-magnify"
                  variant="outlined"
                  density="compact"
                  clearable
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" md="3">
                <v-select
                  v-model="statusFilter"
                  label="Filtrer par statut"
                  :items="statusOptions"
                  variant="outlined"
                  density="compact"
                  clearable
                ></v-select>
              </v-col>
              
              <v-col cols="12" md="3">
                <v-select
                  v-model="paymentFilter"
                  label="Filtrer par paiement"
                  :items="paymentOptions"
                  variant="outlined"
                  density="compact"
                  clearable
                ></v-select>
              </v-col>
              
              <v-col cols="12" md="2">
                <v-btn
                  color="success"
                  block
                  @click="exportData"
                  variant="outlined"
                >
                  <v-icon class="mr-1">mdi-download</v-icon>
                  Export
                </v-btn>
              </v-col>
            </v-row>

            <!-- Data Table -->
            <v-data-table
              :headers="headers"
              :items="filteredInscriptions"
              :loading="loading"
              :search="search"
              class="elevation-1"
              :items-per-page="10"
              :sort-by="[{ key: 'date_inscription', order: 'desc' }]"
            >
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
                <v-menu>
                  <template v-slot:activator="{ props }">
                    <v-btn
                      icon="mdi-dots-vertical"
                      variant="text"
                      size="small"
                      v-bind="props"
                    ></v-btn>
                  </template>
                  
                  <v-list density="compact">
                    <v-list-item @click="updateStatus(item.id, 'validé')">
                      <v-list-item-title>
                        <v-icon class="mr-2" color="success">mdi-check</v-icon>
                        Valider
                      </v-list-item-title>
                    </v-list-item>
                    
                    <v-list-item @click="updateStatus(item.id, 'incomplet')">
                      <v-list-item-title>
                        <v-icon class="mr-2" color="warning">mdi-alert</v-icon>
                        Marquer incomplet
                      </v-list-item-title>
                    </v-list-item>
                    
                    <v-list-item @click="updateStatus(item.id, 'rejeté')">
                      <v-list-item-title>
                        <v-icon class="mr-2" color="error">mdi-close</v-icon>
                        Rejeter
                      </v-list-item-title>
                    </v-list-item>
                    
                    <v-divider></v-divider>
                    
                    <v-list-item @click="viewDetails(item)">
                      <v-list-item-title>
                        <v-icon class="mr-2" color="info">mdi-eye</v-icon>
                        Voir détails
                      </v-list-item-title>
                    </v-list-item>
                    
                    <v-list-item @click="deleteInscription(item.id)">
                      <v-list-item-title>
                        <v-icon class="mr-2" color="error">mdi-delete</v-icon>
                        Supprimer
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </template>

              <template v-slot:no-data>
                <v-empty-state
                  icon="mdi-database"
                  title="Aucune inscription"
                  text="Il n'y a encore aucune inscription enregistrée."
                ></v-empty-state>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Details Dialog -->
    <v-dialog v-model="detailsDialog" max-width="600">
      <v-card v-if="selectedInscription">
        <v-card-title class="pa-4 bg-info">
          <span class="text-h6 text-white">
            Détails de l'inscription
          </span>
        </v-card-title>

        <v-card-text class="pa-6">
          <v-row>
            <v-col cols="12" md="6">
              <div class="mb-3">
                <div class="text-caption text-grey">Nom complet</div>
                <div class="text-h6">{{ selectedInscription.nom }} {{ selectedInscription.prenom }}</div>
              </div>
            </v-col>
            
            <v-col cols="12" md="6">
              <div class="mb-3">
                <div class="text-caption text-grey">Date de naissance</div>
                <div class="text-body-1">{{ formatDate(selectedInscription.date_naissance) }}</div>
              </div>
            </v-col>
            
            <v-col cols="12" md="6">
              <div class="mb-3">
                <div class="text-caption text-grey">Classe</div>
                <div class="text-body-1">{{ selectedInscription.classe }}</div>
              </div>
            </v-col>
            
            <v-col cols="12" md="6">
              <div class="mb-3">
                <div class="text-caption text-grey">Parent/Tuteur</div>
                <div class="text-body-1">{{ selectedInscription.parent_nom }}</div>
              </div>
            </v-col>
            
            <v-col cols="12">
              <div class="mb-3">
                <div class="text-caption text-grey">Téléphone</div>
                <div class="text-body-1">{{ selectedInscription.parent_tel }}</div>
              </div>
            </v-col>
            
            <v-col cols="12">
              <div class="mb-3">
                <div class="text-caption text-grey">Document fourni</div>
                <div class="text-body-1">{{ selectedInscription.document || 'Aucun document' }}</div>
              </div>
            </v-col>
          </v-row>
        </v-card-text>

        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn color="primary" @click="detailsDialog = false">Fermer</v-btn>
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
import { useInscriptionsStore, type Inscription } from '../stores/inscriptions'

const store = useInscriptionsStore()

const loading = ref(false)
const search = ref('')
const statusFilter = ref('')
const paymentFilter = ref('')
const detailsDialog = ref(false)
const selectedInscription = ref<Inscription | null>(null)
const snackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const headers = [
  { title: 'Nom', value: 'nom', sortable: true },
  { title: 'Prénom', value: 'prenom', sortable: true },
  { title: 'Classe', value: 'classe', sortable: true },
  { title: 'Parent', value: 'parent_nom', sortable: true },
  { title: 'Téléphone', value: 'parent_tel' },
  { title: 'Statut', value: 'statut', sortable: true },
  { title: 'Paiement', value: 'paiement_statut', sortable: true },
  { title: 'Date', value: 'date_inscription', sortable: true },
  { title: 'Actions', value: 'actions', sortable: false },
]

const statusOptions = [
  { title: 'Reçu', value: 'reçu' },
  { title: 'Incomplet', value: 'incomplet' },
  { title: 'Validé', value: 'validé' },
  { title: 'Rejeté', value: 'rejeté' },
]

const paymentOptions = [
  { title: 'Non payé', value: 'non payé' },
  { title: 'Payé', value: 'payé' },
]

const filteredInscriptions = computed(() => {
  let filtered = [...store.inscriptions]
  
  if (statusFilter.value) {
    filtered = filtered.filter(item => item.statut === statusFilter.value)
  }
  
  if (paymentFilter.value) {
    filtered = filtered.filter(item => item.paiement_statut === paymentFilter.value)
  }
  
  return filtered
})

const stats = computed(() => {
  const inscriptions = store.inscriptions
  return {
    total: inscriptions.length,
    valide: inscriptions.filter(i => i.statut === 'validé').length,
    recu: inscriptions.filter(i => i.statut === 'reçu').length,
    paye: inscriptions.filter(i => i.paiement_statut === 'payé').length,
  }
})

function getStatusColor(status: string) {
  const colors = {
    'reçu': 'info',
    'incomplet': 'warning',
    'validé': 'success',
    'rejeté': 'error',
  }
  return colors[status as keyof typeof colors] || 'grey'
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString('fr-FR')
}

async function updateStatus(id: number, newStatus: string) {
  try {
    await store.updateStatus(id, newStatus)
    showSnackbar(`Statut mis à jour: ${newStatus}`, 'success')
  } catch (error) {
    showSnackbar('Erreur lors de la mise à jour', 'error')
  }
}

async function deleteInscription(id: number) {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette inscription ?')) {
    try {
      await store.deleteInscription(id)
      showSnackbar('Inscription supprimée', 'success')
    } catch (error) {
      showSnackbar('Erreur lors de la suppression', 'error')
    }
  }
}

function viewDetails(inscription: Inscription) {
  selectedInscription.value = inscription
  detailsDialog.value = true
}

function showSnackbar(message: string, color: string = 'success') {
  snackbarMessage.value = message
  snackbarColor.value = color
  snackbar.value = true
}

function exportData() {
  // Simple CSV export
  const csv = [
    ['Nom', 'Prénom', 'Classe', 'Parent', 'Téléphone', 'Statut', 'Paiement', 'Date'],
    ...filteredInscriptions.value.map(item => [
      item.nom,
      item.prenom,
      item.classe,
      item.parent_nom,
      item.parent_tel,
      item.statut,
      item.paiement_statut,
      formatDate(item.date_inscription || ''),
    ])
  ].map(row => row.join(',')).join('\n')

  const blob = new Blob([csv], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `inscriptions_${new Date().toISOString().split('T')[0]}.csv`
  a.click()
  window.URL.revokeObjectURL(url)
  
  showSnackbar('Export réalisé avec succès', 'success')
}

onMounted(() => {
  store.fetchInscriptions()
})
</script>