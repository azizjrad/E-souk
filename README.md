# E-Souk Tounsi - Plateforme E-commerce d'Artisanat Tunisien 🏺

<img alt="Version" src="https://img.shields.io/badge/version-1.0.0-blue.svg">
<img alt="PHP" src="https://img.shields.io/badge/PHP-7.4+-orange.svg">
<img alt="License" src="https://img.shields.io/badge/license-MIT-green.svg">

Une plateforme e-commerce moderne dédiée à la promotion et à la vente d'artisanat tunisien authentique.

---

![E-souk Banner](public/assets/images/banner.png)

## 📋 Présentation

E-Souk Tounsi met en relation les artisans tunisiens traditionnels avec des clients du monde entier, offrant une marketplace conviviale pour des produits artisanaux uniques. Notre plateforme célèbre le riche patrimoine culturel tunisien tout en créant des opportunités économiques pour les artisans locaux.

## ✨ Caractéristiques

### Système d'Authentification

- Inscription et connexion sécurisées
- Gestion de profil utilisateur
- Récupération de mot de passe

### Gestion des Produits

- Fiches produits détaillées avec images haute qualité
- Système de catégorisation
- Options avancées de filtrage et de tri
- Mise en avant des produits phares et best-sellers

### Expérience d'Achat

- Fonctionnalité intuitive de liste de souhaits
- Panier d'achats responsive
- Processus de paiement sécurisé
- Historique et suivi des commandes

### Profils d'Artisans

- Pages dédiées aux artisans
- Histoires et parcours des artisans
- Options de communication directe (à implémenter)

### Design Responsive

- Optimisé pour mobile, tablette et ordinateur
- Expérience utilisateur cohérente sur tous les appareils

### Administration

- Tableau de bord administrateur
- Gestion des produits, catégories, utilisateurs, commandes et avis
- Statistiques et rapports

## 🛠️ Technologies Utilisées

### Frontend

- HTML5, CSS3, JavaScript
- Bootstrap 5
- jQuery

### Backend

- PHP 7.4+
- MySQL Database
- PDO pour les connexions à la base de données

### Outils & Bibliothèques

- Font Awesome (icônes)
- noUiSlider (slider de prix)
- Animations et transitions personnalisées

## 🚀 Installation

### Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web Apache/Nginx (XAMPP recommandé)
- Navigateur web moderne

### Installation Étape par Étape

1. **Cloner le repository :**
   - git clone https://github.com/votre-username/E-souk-aziz.git
   - cd E-souk-aziz
2. **Configuration de la base de données :**
   - Démarrer XAMPP
   - Créer une nouvelle base de données nommée 'bd-esouk-2'
   - Importer le fichier SQL depuis `/database/bd-esouk-2.sql`
3. **Configuration :**
   - cp config/config.example.php config/config.php
   - Modifier `config/config.php` avec vos identifiants de base de données et paramètres du site
4. **Installer les dépendances :**
   - composer install
5. **Permissions :**
   - chmod 755 root_uploads/
   - chmod 755 root_uploads/products/
6. **Configuration du Serveur Web :**
   - Pointer votre serveur web vers le répertoire `public` si besoin
   - S'assurer que `mod_rewrite` est activé si vous utilisez Apache
7. **Accéder à l'application :**
   - Frontend : `http://localhost/E-souk-aziz`
   - Admin : `http://localhost/E-souk-aziz/admin`

## 📖 Utilisation

### Pour les Clients

- **Parcourir les Produits :** Explorez les catégories ou utilisez la fonction de recherche
- **Créer un Compte :** Inscrivez-vous pour accéder à des fonctionnalités supplémentaires
- **Gérer la Liste de Souhaits :** Enregistrez vos articles préférés pour référence future
- **Passer des Commandes :** Suivez le processus de paiement intuitif
- **Suivre les Commandes :** Surveillez l'état de vos commandes via votre tableau de bord

### Pour les Administrateurs

- **Connexion au Panneau d'Administration :** Accès via `login.php`
- **Gérer les Produits :** Ajouter, modifier ou supprimer des fiches produits
- **Traiter les Commandes :** Consulter et mettre à jour l'état des commandes
- **Gestion des Utilisateurs :** Surveiller et gérer les comptes utilisateurs
- **Mises à Jour du Contenu :** Modifier le contenu de la page d'accueil et les articles en vedette

## 📁 Structure du Projet

```
E-souk-aziz/
│
├── config/               # Fichiers de configuration
│   └── init.php          # Initialisation de l'application
│
├── database/             # Scripts SQL et migrations
│
├── public/               # Fichiers accessibles publiquement
│   ├── assets/           # CSS, JS, images
│   │   ├── css/          # Fichiers de style
│   │   ├── js/           # Fichiers JavaScript
│   │   └── images/       # Images générales
│   │
│   ├── pages/            # Fichiers PHP des pages
│   └── templates/        # Composants HTML/PHP réutilisables
│
├── root_uploads/         # Contenu téléchargé par les utilisateurs
│   └── products/         # Images de produits
│
├── admin/                # Section administrateur
│
└── README.md             # Documentation du projet
```

## 🔐 Comptes par Défaut

### Admin

- Email: admin@esouk.com
- Mot de passe: admin123

### Client Test

- Email: client@test.com
- Mot de passe: client123

## 🤝 Contribution

Les contributions sont les bienvenues et appréciées ! Voici comment vous pouvez contribuer :

1. Forkez le repository
2. Créez une branche pour votre fonctionnalité :
   ```bash
   - git checkout -b feature/ma-fonctionnalite
   ```
3. Faites vos modifications
4. Committez vos changements :
   ```bash
   - git commit -m 'Ajout d'une fonctionnalité incroyable'
   ```
5. Pushez vers votre branche :
   ```bash
   - git push origin feature/amazing-feature
   ```
6. Ouvrez une Pull Request

## 📄 License

Ce projet est sous licence MIT - voir le fichier LICENSE pour plus de détails.

## 📞 Contact

- **Email :** contact@esouk.com
- **Site Web :** www.esouk.com
- **LinkedIn :** [E-souk](https://linkedin.com/company/e-souk)

## 🙏 Remerciements

- Tous les artisans tunisiens qui font confiance à notre plateforme
- L'équipe de développement pour leur travail acharné
- La communauté open source pour leurs contributions précieuses

---

E-Souk Tounsi — Offrir les trésors artisanaux tunisiens au monde digital 🇹🇳

© 2025 E-souk. Tous droits réservés.
