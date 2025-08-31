<template>
  <v-card 
    class="student-card" 
    elevation="2" 
    hover
    @click="$emit('view-details', student)"
  >
    <div class="card-header">
      <v-avatar size="80" class="profile-avatar">
        <v-img 
          :src="student.profile_image_url || '/assets/profiles/default-avatar.png'"
          :alt="`Photo de ${student.prenom} ${student.nom}`"
          cover
        >
          <template v-slot:error>
            <v-icon size="40" color="grey-lighten-2">mdi-account</v-icon>
          </template>
        </v-img>
      </v-avatar>
      
      <div class="student-info">
        <h3 class="text-h6 font-weight-bold">
          {{ student.prenom }} {{ student.nom }}
        </h3>
        <p class="text-body-2 text-grey-darken-1">
          {{ student.classe_nom || student.classe }}
        </p>
      </div>
      
      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn
            icon="mdi-dots-vertical"
            variant="text"
            size="small"
            v-bind="props"
            @click.stop
          ></v-btn>
        </template>
        
        <v-list density="compact">
          <v-list-item @click.stop="$emit('edit', student)">
            <v-list-item-title>
              <v-icon class="mr-2" size="small">mdi-pencil</v-icon>
              Modifier
            </v-list-item-title>
          </v-list-item>
          
          <v-list-item @click.stop="$emit('upload-photo', student)">
            <v-list-item-title>
              <v-icon class="mr-2" size="small">mdi-camera</v-icon>
              Photo
            </v-list-item-title>
          </v-list-item>
          
          <v-divider></v-divider>
          
          <v-list-item @click.stop="$emit('delete', student.id)">
            <v-list-item-title class="text-error">
              <v-icon class="mr-2" size="small" color="error">mdi-delete</v-icon>
              Supprimer
            </v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </div>

    <v-card-text class="pt-2">
      <div class="student-details">
        <div class="detail-item">
          <v-icon size="16" class="mr-2" color="grey-darken-1">mdi-account-tie</v-icon>
          <span class="text-body-2">{{ student.parent_nom }}</span>
        </div>
        
        <div class="detail-item">
          <v-icon size="16" class="mr-2" color="grey-darken-1">mdi-phone</v-icon>
          <span class="text-body-2">{{ student.parent_tel }}</span>
        </div>
        
        <div class="detail-item">
          <v-icon size="16" class="mr-2" color="grey-darken-1">mdi-calendar</v-icon>
          <span class="text-body-2">{{ formatDate(student.date_naissance) }}</span>
        </div>
      </div>
    </v-card-text>

    <v-card-actions class="pt-0">
      <v-chip
        :color="getStatusColor(student.statut)"
        variant="tonal"
        size="small"
        class="mr-2"
      >
        <v-icon size="16" class="mr-1">{{ getStatusIcon(student.statut) }}</v-icon>
        {{ student.statut }}
      </v-chip>
      
      <v-chip
        :color="student.paiement_statut === 'payé' ? 'success' : 'warning'"
        variant="tonal"
        size="small"
      >
        <v-icon size="16" class="mr-1">
          {{ student.paiement_statut === 'payé' ? 'mdi-check-circle' : 'mdi-clock' }}
        </v-icon>
        {{ student.paiement_statut }}
      </v-chip>
      
      <v-spacer></v-spacer>
      
      <v-btn
        v-if="student.paiement_statut === 'non payé'"
        color="success"
        variant="outlined"
        size="small"
        @click.stop="$emit('payment', student)"
      >
        <v-icon size="16" class="mr-1">mdi-credit-card</v-icon>
        Payer
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup lang="ts">
import type { Inscription } from '../stores/inscriptions'

defineProps<{
  student: Inscription
}>()

defineEmits<{
  'view-details': [student: Inscription]
  'edit': [student: Inscription]
  'delete': [id: number]
  'payment': [student: Inscription]
  'upload-photo': [student: Inscription]
}>()

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString('fr-FR')
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

function getStatusIcon(status: string) {
  const icons = {
    'reçu': 'mdi-file-document',
    'incomplet': 'mdi-alert-circle',
    'validé': 'mdi-check-circle',
    'rejeté': 'mdi-close-circle',
  }
  return icons[status as keyof typeof icons] || 'mdi-help-circle'
}
</script>

<style scoped>
.student-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  border-radius: 12px;
}

.student-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.card-header {
  display: flex;
  align-items: center;
  padding: 16px 16px 8px 16px;
  gap: 16px;
}

.profile-avatar {
  border: 3px solid rgba(var(--v-theme-primary), 0.1);
  transition: border-color 0.3s ease;
}

.student-card:hover .profile-avatar {
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.student-info {
  flex: 1;
  min-width: 0;
}

.student-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.detail-item {
  display: flex;
  align-items: center;
  opacity: 0.8;
  transition: opacity 0.2s ease;
}

.student-card:hover .detail-item {
  opacity: 1;
}

@media (max-width: 600px) {
  .card-header {
    flex-direction: column;
    text-align: center;
    gap: 12px;
  }
  
  .student-info {
    text-align: center;
  }
}
</style>