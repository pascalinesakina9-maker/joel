import { defineStore } from 'pinia'
import api from '../api'

export interface Classe {
  id?: number
  nom: string
  niveau: 'primaire' | 'secondaire'
  frais_scolarite: number
  capacite_max: number
  description?: string
  nombre_eleves?: number
  eleves_payes?: number
  created_at?: string
  updated_at?: string
}

export const useClassesStore = defineStore('classes', {
  state: () => ({
    classes: [] as Classe[],
    loading: false,
    error: null as string | null,
  }),

  getters: {
    classesPrimaires: (state) => state.classes.filter(c => c.niveau === 'primaire'),
    classesSecondaires: (state) => state.classes.filter(c => c.niveau === 'secondaire'),
    
    getClasseById: (state) => (id: number) => 
      state.classes.find(c => c.id === id),
      
    getClasseByNom: (state) => (nom: string) => 
      state.classes.find(c => c.nom === nom),
  },

  actions: {
    async fetchClasses() {
      this.loading = true
      try {
        const response = await api.get('/classes.php')
        this.classes = response.data
        this.error = null
      } catch (error) {
        this.error = 'Erreur lors du chargement des classes'
        console.error(error)
      } finally {
        this.loading = false
      }
    },

    async addClass(classe: Omit<Classe, 'id'>) {
      this.loading = true
      try {
        const response = await api.post('/classes.php', classe)
        await this.fetchClasses()
        this.error = null
        return response.data
      } catch (error) {
        this.error = "Erreur lors de l'ajout de la classe"
        console.error(error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateClasse(id: number, classe: Partial<Classe>) {
      try {
        await api.put(`/classes.php?id=${id}`, classe)
        await this.fetchClasses()
        this.error = null
      } catch (error) {
        this.error = 'Erreur lors de la mise à jour de la classe'
        console.error(error)
        throw error
      }
    },

    async deleteClasse(id: number) {
      try {
        await api.delete(`/classes.php?id=${id}`)
        await this.fetchClasses()
        this.error = null
      } catch (error) {
        this.error = 'Erreur lors de la suppression de la classe'
        console.error(error)
        throw error
      }
    },
  },
})