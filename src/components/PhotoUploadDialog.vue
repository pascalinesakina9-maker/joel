<template>
  <v-dialog v-model="dialog" max-width="500" persistent>
    <v-card>
      <v-card-title class="pa-4 bg-primary">
        <span class="text-h6 text-white">
          <v-icon class="mr-2" color="white">mdi-camera</v-icon>
          Photo de Profil
        </span>
      </v-card-title>

      <v-card-text class="pa-6 text-center">
        <div class="current-photo mb-4">
          <v-avatar size="120" class="mb-3">
            <v-img 
              :src="currentImageUrl"
              :alt="`Photo de ${student?.prenom} ${student?.nom}`"
              cover
            >
              <template v-slot:error>
                <v-icon size="60" color="grey-lighten-2">mdi-account</v-icon>
              </template>
            </v-img>
          </v-avatar>
          
          <h3 class="text-h6 font-weight-bold">
            {{ student?.prenom }} {{ student?.nom }}
          </h3>
        </div>

        <v-file-input
          v-model="selectedFile"
          label="Choisir une nouvelle photo"
          accept="image/*"
          prepend-icon="mdi-camera"
          variant="outlined"
          show-size
          @change="onFileSelect"
        ></v-file-input>

        <v-alert v-if="preview" type="info" variant="tonal" class="mt-4">
          <div class="text-center">
            <p class="mb-2">Aperçu:</p>
            <v-avatar size="80">
              <v-img :src="preview" cover></v-img>
            </v-avatar>
          </div>
        </v-alert>

        <v-alert type="info" variant="text" class="mt-4 text-left">
          <ul class="text-body-2">
            <li>Formats acceptés: JPG, PNG, GIF, WebP</li>
            <li>Taille maximale: 5 MB</li>
            <li>L'image sera automatiquement redimensionnée</li>
          </ul>
        </v-alert>
      </v-card-text>

      <v-card-actions class="pa-4">
        <v-btn
          color="grey"
          variant="outlined"
          @click="closeDialog"
          :disabled="uploading"
        >
          Annuler
        </v-btn>
        
        <v-spacer></v-spacer>
        
        <v-btn
          color="primary"
          @click="uploadPhoto"
          :loading="uploading"
          :disabled="!selectedFile || selectedFile.length === 0"
        >
          <v-icon class="mr-2">mdi-upload</v-icon>
          Téléverser
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { Inscription } from '../stores/inscriptions'
import { useInscriptionsStore } from '../stores/inscriptions'

const props = defineProps<{
  modelValue: boolean
  student: Inscription | null
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'uploaded': [student: Inscription]
}>()

const store = useInscriptionsStore()

const selectedFile = ref<File[]>([])
const preview = ref<string | null>(null)
const uploading = ref(false)

const dialog = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const currentImageUrl = computed(() => {
  if (!props.student) return '/assets/profiles/default-avatar.png'
  return props.student.profile_image_url || '/assets/profiles/default-avatar.png'
})

function onFileSelect() {
  if (selectedFile.value && selectedFile.value.length > 0) {
    const file = selectedFile.value[0]
    
    // Validation côté client
    if (file.size > 5 * 1024 * 1024) {
      alert('Fichier trop volumineux. Maximum 5MB')
      selectedFile.value = []
      return
    }
    
    // Créer un aperçu
    const reader = new FileReader()
    reader.onload = (e) => {
      preview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  } else {
    preview.value = null
  }
}

async function uploadPhoto() {
  if (!props.student?.id || !selectedFile.value || selectedFile.value.length === 0) {
    return
  }
  
  uploading.value = true
  
  try {
    await store.uploadProfileImage(props.student.id, selectedFile.value[0])
    emit('uploaded', props.student)
    closeDialog()
  } catch (error) {
    console.error('Erreur upload:', error)
    alert('Erreur lors du téléversement de l\'image')
  } finally {
    uploading.value = false
  }
}

function closeDialog() {
  dialog.value = false
  selectedFile.value = []
  preview.value = null
}

// Reset when dialog closes
watch(dialog, (newValue) => {
  if (!newValue) {
    selectedFile.value = []
    preview.value = null
  }
})
</script>

<style scoped>
.current-photo {
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  padding-bottom: 16px;
}
</style>