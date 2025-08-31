import { defineStore } from 'pinia'
import api from '../api'

export interface Inscription {
  id?: number
  nom: string
  prenom: string
  date_naissance: string
  classe: string
  classe_id?: number
  classe_nom?: string
  frais_scolarite?: number
  parent_nom: string
  parent_tel: string
  parent_id?: number
  parent_email?: string
  adresse?: string
  document?: string
  profile_image?: string
  profile_image_url?: string
  notes?: string
  statut: 'reçu' | 'incomplet' | 'validé' | 'rejeté'
  date_inscription?: string
  updated_at?: string
  paiement_statut: 'non payé' | 'payé'
}

export interface Payment {
  id?: number
  inscription_id: number
  montant: number
  mode: 'simulation'
  methode_paiement?: string
  transaction_id: string
  statut?: 'en_attente' | 'confirme' | 'echoue'
  notes?: string
  date_paiement?: string
}

export const useInscriptionsStore = defineStore('inscriptions', {
  state: () => ({
    inscriptions: [] as Inscription[],
    loading: false,
    error: null as string | null,
  }),

  getters: {
    inscriptionsByClasse: (state) => (classeId: number) =>
      state.inscriptions.filter(i => i.classe_id === classeId),
      
    inscriptionsByStatut: (state) => (statut: string) =>
      state.inscriptions.filter(i => i.statut === statut),
      
    inscriptionsPayees: (state) =>
      state.inscriptions.filter(i => i.paiement_statut === 'payé'),
      
    statistiques: (state) => ({
      total: state.inscriptions.length,
      recu: state.inscriptions.filter(i => i.statut === 'reçu').length,
      incomplet: state.inscriptions.filter(i => i.statut === 'incomplet').length,
      valide: state.inscriptions.filter(i => i.statut === 'validé').length,
      rejete: state.inscriptions.filter(i => i.statut === 'rejeté').length,
      paye: state.inscriptions.filter(i => i.paiement_statut === 'payé').length,
      nonPaye: state.inscriptions.filter(i => i.paiement_statut === 'non payé').length,
    }),
  },

  actions: {
    async fetchInscriptions(filters?: {
      classe_id?: number
      statut?: string
      paiement?: string
    }) {
      this.loading = true
      try {
        const params = new URLSearchParams()
        if (filters?.classe_id) params.append('classe_id', filters.classe_id.toString())
        if (filters?.statut) params.append('statut', filters.statut)
        if (filters?.paiement) params.append('paiement', filters.paiement)
        
        const url = `/inscriptions.php${params.toString() ? '?' + params.toString() : ''}`
        const response = await api.get(url)
        this.inscriptions = response.data
        this.error = null
      } catch (error) {
        this.error = 'Erreur lors du chargement des inscriptions'
        console.error(error)
      } finally {
        this.loading = false
      }
    },

    async addInscription(inscription: Omit<Inscription, 'id'>) {
      this.loading = true
      try {
        const response = await api.post('/inscriptions.php', inscription)
        await this.fetchInscriptions()
        this.error = null
        return response.data
      } catch (error) {
        this.error = "Erreur lors de l'ajout de l'inscription"
        console.error(error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateInscription(id: number, data: Partial<Inscription>) {
      try {
        await api.put(`/inscriptions.php?id=${id}`, data)
        await this.fetchInscriptions()
        this.error = null
      } catch (error) {
        this.error = 'Erreur lors de la mise à jour'
        console.error(error)
        throw error
      }
    },

    async updateStatus(id: number, statut: string) {
      try {
        await api.put(`/inscriptions.php?id=${id}`, { statut })
        await this.fetchInscriptions()
        this.error = null
      } catch (error) {
        this.error = 'Erreur lors de la mise à jour du statut'
        console.error(error)
        throw error
      }
    },

    async deleteInscription(id: number) {
      try {
        await api.delete(`/inscriptions.php?id=${id}`)
        await this.fetchInscriptions()
        this.error = null
      } catch (error) {
        this.error = 'Erreur lors de la suppression'
        console.error(error)
        throw error
      }
    },

    async uploadProfileImage(id: number, file: File) {
      try {
        const formData = new FormData()
        formData.append('profile_image', file)
        formData.append('inscription_id', id.toString())
        
        const response = await api.post('/upload.php', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })
        
        await this.fetchInscriptions()
        this.error = null
        return response.data
      } catch (error) {
        this.error = 'Erreur lors du téléversement de l\'image'
        console.error(error)
        throw error
      }
    },

    async simulatePayment(id: number, montant: number) {
      try {
        const response = await api.post('/payment.php', {
          inscription_id: id,
          montant: montant,
        })
        await this.fetchInscriptions()
        this.error = null
        return response.data
      } catch (error) {
        this.error = 'Erreur lors du paiement'
        console.error(error)
        throw error
      }
    },
  },
})