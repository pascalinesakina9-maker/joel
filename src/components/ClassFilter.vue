<template>
  <v-card class="filter-card" elevation="1">
    <v-card-text class="pa-4">
      <div class="filter-header">
        <h3 class="text-h6 font-weight-bold">
          <v-icon class="mr-2" color="primary">mdi-filter</v-icon>
          Filtres
        </h3>
        
        <v-btn
          v-if="hasActiveFilters"
          variant="text"
          size="small"
          color="error"
          @click="clearFilters"
        >
          <v-icon size="16" class="mr-1">mdi-close</v-icon>
          Effacer
        </v-btn>
      </div>

      <v-row class="mt-2">
        <v-col cols="12" md="6">
          <v-select
            v-model="selectedNiveau"
            label="Niveau"
            :items="niveauOptions"
            variant="outlined"
            density="compact"
            clearable
            prepend-inner-icon="mdi-school"
            @update:model-value="onNiveauChange"
          ></v-select>
        </v-col>
        
        <v-col cols="12" md="6">
          <v-select
            v-model="selectedClasse"
            label="Classe"
            :items="classeOptions"
            :disabled="!selectedNiveau"
            variant="outlined"
            density="compact"
            clearable
            prepend-inner-icon="mdi-google-classroom"
            @update:model-value="onClasseChange"
          ></v-select>
        </v-col>
        
        <v-col cols="12" md="4">
          <v-select
            v-model="selectedStatut"
            label="Statut"
            :items="statutOptions"
            variant="outlined"
            density="compact"
            clearable
            prepend-inner-icon="mdi-check-circle"
            @update:model-value="onStatutChange"
          ></v-select>
        </v-col>
        
        <v-col cols="12" md="4">
          <v-select
            v-model="selectedPaiement"
            label="Paiement"
            :items="paiementOptions"
            variant="outlined"
            density="compact"
            clearable
            prepend-inner-icon="mdi-credit-card"
            @update:model-value="onPaiementChange"
          ></v-select>
        </v-col>
        
        <v-col cols="12" md="4">
          <v-text-field
            v-model="searchQuery"
            label="Rechercher..."
            variant="outlined"
            density="compact"
            clearable
            prepend-inner-icon="mdi-magnify"
            @update:model-value="onSearchChange"
          ></v-text-field>
        </v-col>
      </v-row>

      <!-- Filtres actifs -->
      <div v-if="hasActiveFilters" class="active-filters mt-3">
        <v-chip
          v-if="selectedNiveau"
          closable
          size="small"
          color="primary"
          variant="tonal"
          class="mr-2 mb-2"
          @click:close="selectedNiveau = null; onNiveauChange()"
        >
          Niveau: {{ selectedNiveau }}
        </v-chip>
        
        <v-chip
          v-if="selectedClasse"
          closable
          size="small"
          color="primary"
          variant="tonal"
          class="mr-2 mb-2"
          @click:close="selectedClasse = null; onClasseChange()"
        >
          Classe: {{ getClasseNom(selectedClasse) }}
        </v-chip>
        
        <v-chip
          v-if="selectedStatut"
          closable
          size="small"
          color="info"
          variant="tonal"
          class="mr-2 mb-2"
          @click:close="selectedStatut = null; onStatutChange()"
        >
          Statut: {{ selectedStatut }}
        </v-chip>
        
        <v-chip
          v-if="selectedPaiement"
          closable
          size="small"
          color="success"
          variant="tonal"
          class="mr-2 mb-2"
          @click:close="selectedPaiement = null; onPaiementChange()"
        >
          Paiement: {{ selectedPaiement }}
        </v-chip>
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useClassesStore } from '../stores/classes'

const classesStore = useClassesStore()

const selectedNiveau = ref<string | null>(null)
const selectedClasse = ref<number | null>(null)
const selectedStatut = ref<string | null>(null)
const selectedPaiement = ref<string | null>(null)
const searchQuery = ref('')

const emit = defineEmits<{
  'filter-change': [filters: {
    niveau?: string
    classe_id?: number
    statut?: string
    paiement?: string
    search?: string
  }]
}>()

const niveauOptions = [
  { title: 'Primaire', value: 'primaire' },
  { title: 'Secondaire', value: 'secondaire' },
]

const classeOptions = computed(() => {
  if (!selectedNiveau.value) return []
  
  const classes = selectedNiveau.value === 'primaire' 
    ? classesStore.classesPrimaires 
    : classesStore.classesSecondaires
    
  return classes.map(c => ({
    title: `${c.nom} (${c.nombre_eleves || 0} élèves)`,
    value: c.id
  }))
})

const statutOptions = [
  { title: 'Reçu', value: 'reçu' },
  { title: 'Incomplet', value: 'incomplet' },
  { title: 'Validé', value: 'validé' },
  { title: 'Rejeté', value: 'rejeté' },
]

const paiementOptions = [
  { title: 'Non payé', value: 'non payé' },
  { title: 'Payé', value: 'payé' },
]

const hasActiveFilters = computed(() => 
  selectedNiveau.value || selectedClasse.value || selectedStatut.value || 
  selectedPaiement.value || searchQuery.value
)

function getClasseNom(classeId: number | null) {
  if (!classeId) return ''
  const classe = classesStore.getClasseById(classeId)
  return classe?.nom || ''
}

function emitFilters() {
  emit('filter-change', {
    niveau: selectedNiveau.value || undefined,
    classe_id: selectedClasse.value || undefined,
    statut: selectedStatut.value || undefined,
    paiement: selectedPaiement.value || undefined,
    search: searchQuery.value || undefined,
  })
}

function onNiveauChange() {
  selectedClasse.value = null // Reset classe when niveau changes
  emitFilters()
}

function onClasseChange() {
  emitFilters()
}

function onStatutChange() {
  emitFilters()
}

function onPaiementChange() {
  emitFilters()
}

function onSearchChange() {
  emitFilters()
}

function clearFilters() {
  selectedNiveau.value = null
  selectedClasse.value = null
  selectedStatut.value = null
  selectedPaiement.value = null
  searchQuery.value = ''
  emitFilters()
}

// Charger les classes au montage
classesStore.fetchClasses()
</script>

<style scoped>
.filter-card {
  border-radius: 12px;
  border: 1px solid rgba(var(--v-theme-primary), 0.1);
}

.filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.active-filters {
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  padding-top: 12px;
}
</style>