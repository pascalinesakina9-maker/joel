import { defineStore } from 'pinia'
import api from "../api.ts";


export interface Inscription {
  id?: number
  nom: string
  prenom: string
  date_naissance: string
  classe: string
  parent_nom: string
  parent_tel: string
  document?: string
  statut: 'reçu' | 'incomplet' | 'validé' | 'rejeté'
  date_inscription?: string
  paiement_statut: 'non payé' | 'payé'
}

export interface Payment {
  id?: number
  inscription_id: number
  montant: number
  mode: 'simulation'
  transaction_id: string
  date_paiement?: string
}

export const useInscriptionsStore = defineStore('inscriptions', {
  state: () => ({
    inscriptions: [] as Inscription[],
    loading: false,
    error: null as string | null,
  }),

  actions: {
    async fetchInscriptions() {
      this.loading = true
      try {
        const response = await api.get('/inscriptions.php')
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
