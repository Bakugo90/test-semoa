# Frontend - Système de Gestion de Tickets

Application Next.js 16 développée avec React 19 et TypeScript pour gérer les tickets de support.

## Stack Technique

- **Framework**: Next.js 16.1.6 (App Router, Turbopack)
- **React**: 19.2.3
- **TypeScript**: 5
- **UI Library**: shadcn/ui (composants pré-construits)
- **HTTP Client**: axios 1.13.4
- **Styling**: Tailwind CSS

## Architecture

### Command Pattern
Toutes les interactions avec l'API suivent le pattern Command pour respecter les principes Clean Architecture :
- `AuthCommands` : LoginCommand, RegisterCommand
- `TicketCommands` : CreateTicketCommand, UpdateTicketCommand, DeleteTicketCommand, ListTicketsCommand, GetTicketCommand


## Composants shadcn/ui Utilisés

Pour respecter la contrainte de temps, j'ai utilisé **shadcn/ui**, une collection de composants React pré-construits, accessibles et personnalisables basés sur Radix UI et Tailwind CSS.

### Liste des composants installés :
- **button** - Boutons d'action (créer, supprimer, déconnexion)
- **input** - Champs de saisie (email, password, titre)
- **textarea** - Description des tickets
- **card** - Cartes pour afficher les tickets
- **form** - Gestion des formulaires avec validation
- **select** - Dropdowns (statut, priorité, filtres)
- **badge** - Badges de statut et priorité
- **label** - Labels de formulaire
- **dialog** - Modales de confirmation
- **sonner** - Notifications toast (configurées en haut à droite)
- **table** - Tableaux de données

## Installation et Démarrage

```bash
# Installation des dépendances
pnpm install

# Lancer le serveur de développement
pnpm dev
```

L'application démarre sur **http://localhost:3000**

## Variables d'Environnement

Fichier `.env.local` :
```
NEXT_PUBLIC_API_URL=http://localhost:8000/api
```

## Fonctionnalités

### Authentification
- Inscription avec validation (email, mot de passe ≥ 6 caractères)
- Connexion avec JWT stocké en localStorage
- Redirection automatique selon l'état d'authentification
- Déconnexion avec suppression du token

### Gestion des Tickets
- **Liste** : Affichage avec filtres (statut, priorité)
- **Création** : Formulaire avec titre, description, priorité
- **Détail** : Vue complète avec actions
- **Modification** : Changement de statut (OPEN → IN_PROGRESS/DONE)
- **Suppression** : Avec confirmation

### Filtres
- **Statut** : Tous, Ouvert, En cours, Terminé
- **Priorité** : Toutes, Basse, Moyenne, Haute


