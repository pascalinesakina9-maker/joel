# Système de Gestion des Inscriptions - EP MATENDO

Application web complète pour la gestion des inscriptions scolaires à l'École Primaire MATENDO avec simulation de paiement.

## 🌟 Fonctionnalités

### Espace Parents
- ✅ Formulaire d'inscription responsive avec Vuetify
- ✅ Upload de documents (photos/scans)
- ✅ Simulation de paiement des frais de scolarité
- ✅ Confirmation d'inscription et de paiement
- ✅ Interface utilisateur moderne et intuitive

### Espace Secrétariat  
- ✅ Tableau de bord complet avec statistiques
- ✅ Gestion des statuts d'inscription (reçu, incomplet, validé, rejeté)
- ✅ Suivi des paiements en temps réel
- ✅ Système de recherche et filtrage avancé
- ✅ Export des données en CSV
- ✅ Interface d'administration professionnelle

### Backend API
- ✅ API REST complète en PHP
- ✅ Gestion CORS pour les requêtes cross-origin
- ✅ Validation des données côté serveur
- ✅ Gestion des erreurs et réponses JSON
- ✅ Base de données MySQL optimisée

## 🛠️ Technologies Utilisées

- **Frontend**: Vue.js 3, TypeScript, Vuetify 3, Pinia
- **Backend**: PHP 8+, MySQL
- **Build**: Vite
- **Icons**: Material Design Icons

## 📋 Installation

### Prérequis
- Node.js 16+
- PHP 8+
- MySQL/MariaDB
- Serveur web (Apache/Nginx) ou XAMPP/WAMP

### 1. Installation Frontend
```bash
npm install
npm run dev
```

### 2. Configuration Base de Données
1. Créer la base de données MySQL
2. Importer le fichier `public/api/database.sql`
3. Modifier les paramètres dans `public/api/config.php`

### 3. Configuration Serveur PHP
- Placer les fichiers API dans un serveur web
- Ou utiliser le serveur PHP intégré:
```bash
cd public && php -S localhost:8080
```

## 📊 Structure Base de Données

### Table `inscriptions`
- Informations complètes des élèves et parents
- Gestion des statuts et paiements
- Index optimisés pour les requêtes

### Table `paiements` 
- Historique des transactions simulées
- Traçabilité des paiements
- Relations avec les inscriptions

## 🎨 Interface Utilisateur

### Design System
- Palette de couleurs professionnelle
- Composants Material Design
- Responsive design pour tous les appareils
- Animations fluides et micro-interactions

### Accessibilité
- Contrastes de couleurs optimaux
- Navigation keyboard-friendly
- Messages d'erreur clairs
- Interface intuitive

## 🔐 Sécurité

- Validation des données côté client et serveur
- Protection contre les injections SQL avec PDO
- Gestion des erreurs sécurisée
- Headers CORS configurés

## 📱 Responsive Design

- Mobile-first approach
- Breakpoints adaptatifs
- Interface tactile optimisée
- Performance sur tous les appareils

## 🚀 Déploiement

### Environnement de Production
1. Build optimisé: `npm run build`
2. Configurer le serveur web
3. Paramètres de base de données de production
4. HTTPS recommandé

## 📧 Support

Pour toute question ou assistance technique, contactez l'équipe de développement.

---

**EP MATENDO** - Système de Gestion des Inscriptions 2025
Développé avec ❤️ pour l'éducation