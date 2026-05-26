<!-- main -->
<p align="center"><a href="https://github.com/clementBernard130/Scorea"><img src="https://img.shields.io/badge/Projet-DSNS%20CCI-green.svg" alt="Projet DSNS CCI"/></a>&nbsp;&nbsp;<a href="https://symfony.com/"><img src="https://img.shields.io/badge/Réalisé%20en-Symfony%207.3-black.svg" alt="Symfony"/></a>&nbsp;&nbsp;<a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg" alt="PHP"/></a>&nbsp;&nbsp;<a href="https://github.com/clementBernard130/Scorea/projects"><img src="https://img.shields.io/badge/Avec-GitHub%20Projects-1f425f.svg" alt="GitHub Projects"/></a>&nbsp;&nbsp;<a href="https://github.com/clementBernard130/Scorea/blob/main/LICENSE"><img src="https://img.shields.io/badge/License-GPLv3-blue.svg" alt="License: GPL v3"/></a></p>
<!-- main -->
<p align="center"><a href="https://github.com/clementBernard130/Scorea/releases"><img src="https://img.shields.io/github/v/release/clementBernard130/Scorea" alt="release"/></a>&nbsp;&nbsp;<a href="https://github.com/clementBernard130/Scorea"><img src="https://img.shields.io/github/languages/code-size/clementBernard130/Scorea" alt="code size"/></a>&nbsp;&nbsp;<a href="https://github.com/clementBernard130/Scorea/graphs/contributors"><img src="https://img.shields.io/github/contributors/clementBernard130/Scorea" alt="contributors"/></a></p>
<!-- ci -->
<p align="center"><a href="https://github.com/clementBernard130/Scorea/actions/workflows/tests.yml"><img src="https://github.com/clementBernard130/Scorea/actions/workflows/tests.yml/badge.svg" alt="CI Tests"/></a></p>
<!-- divers -->
<p align="center"><a href="https://github.com/clementBernard130/Scorea/branches"><img src="https://badgen.net/github/branches/clementBernard130/Scorea" alt="branches"/></a>&nbsp;&nbsp;<a href="https://github.com/clementBernard130/Scorea/commits/"><img src="https://badgen.net/github/commits/clementBernard130/Scorea" alt="commits"/></a></p>
<!-- issues -->
<p align="center"><a href="https://github.com/clementBernard130/Scorea/issues"><img src="https://badgen.net/github/issues/clementBernard130/Scorea" alt="issues"/></a>&nbsp;&nbsp;<a href="https://github.com/clementBernard130/Scorea/issues?q=is%3Aissue%20state%3Aopen"><img src="https://badgen.net/github/open-issues/clementBernard130/Scorea" alt="open issues"/></a>&nbsp;&nbsp;<a href="https://github.com/clementBernard130/Scorea/issues?q=is%3Aissue%20state%3Aclosed"><img src="https://badgen.net/github/closed-issues/clementBernard130/Scorea" alt="closed issues"/></a></p>
<!-- labels issues -->
<p align="center"><a href="https://github.com/clementBernard130/Scorea/issues?q=is%3Aissue%20label%3Afeature"><img src="https://img.shields.io/github/issues-search/clementBernard130/Scorea?query=is%3Aissue%20label%3Afeature&label=feature&color=blue" alt="feature issues"/></a>&nbsp;&nbsp;<a href="https://github.com/clementBernard130/Scorea/issues?q=is%3Aissue%20label%3Abug"><img src="https://img.shields.io/github/issues-search/clementBernard130/Scorea?query=is%3Aissue%20label%3Abug&label=fix&color=red" alt="bug issues"/></a></p>
<!-- prs -->
<p align="center"><a href="https://github.com/clementBernard130/Scorea/pulls"><img src="https://badgen.net/github/prs/clementBernard130/Scorea" alt="prs"/></a>&nbsp;&nbsp;<a href="https://github.com/clementBernard130/Scorea/pulls?q=is%3Aopen+is%3Apr"><img src="https://badgen.net/github/open-prs/clementBernard130/Scorea" alt="open prs"/></a>&nbsp;&nbsp;<a href="https://github.com/clementBernard130/Scorea/pulls?q=is%3Apr+is%3Aclosed"><img src="https://badgen.net/github/closed-prs/clementBernard130/Scorea" alt="closed prs"/></a>&nbsp;&nbsp;<a href="https://github.com/clementBernard130/Scorea/pulls?q=is%3Apr+is%3Amerged"><img src="https://badgen.net/github/merged-prs/clementBernard130/Scorea" alt="merged prs"/></a></p>

# Scorea

- [Scorea](#scorea)
  - [Présentation](#présentation)
  - [Stack technique](#stack-technique)
  - [Recette](#recette)
  - [Lancer le projet](#lancer-le-projet)
  - [Utilisation](#utilisation)
    - [Portail enseignant](#portail-enseignant)
    - [Portail étudiant](#portail-étudiant)
    - [Interface d'administration](#interface-dadministration)
  - [Gestion de projet](#gestion-de-projet)
    - [Changelog : version 1.0](#changelog--version-10)
    - [TODO : version 1.1](#todo--version-11)
  - [Diagrammes](#diagrammes)
    - [Base de données](#base-de-données)
    - [Entités](#entités)
    - [Diagramme de séquence](#diagramme-de-séquence)
    - [Diagramme d'activité](#diagramme-dactivité)
    - [Diagramme d'exigences](#diagramme-dexigences)
  - [Coding Style](#coding-style)
  - [Équipe de développement](#équipe-de-développement)

---

## Présentation

**Scorea** est une application web permettant de modéliser le règlement d'un diplôme.

Elle offre aux enseignants la possibilité de :

- Définir des **formations** et leurs **sections** (promotions)
- Organiser les **unités de compétences** (blocs) et les **compétences** associées
- Créer des **évaluations** (tests) avec des types de notes et des pondérations
- Saisir et consulter les **notes** des étudiants
- Calculer automatiquement les **moyennes pondérées** selon les règles du diplôme

Les étudiants disposent d'un portail dédié pour consulter leurs résultats et leur progression.

## Stack technique

| Composant        | Technologie                       |
| ---------------- | --------------------------------- |
| Langage          | PHP 8.2+                          |
| Framework        | Symfony 7.3                       |
| ORM              | Doctrine ORM 3.5                  |
| Base de données  | PostgreSQL 16                     |
| Templates        | Twig 3                            |
| CSS              | Tailwind                          |
| Conteneurisation | Docker / Docker Compose           |
| CI               | GitHub Actions                    |

## Recette

| **Fonctionnalité**                                 | **À faire** | **En cours** | **Terminé** |
| -------------------------------------------------- | :---------: | :----------: | :---------: |
| Gestion des formations et sections                 |             |              |      ✅      |
| Gestion des unités de compétences et compétences   |             |              |      ✅      |
| Gestion des évaluations (tests & types de notes)  |             |              |      ✅      |
| Saisie des notes                                   |             |              |      ✅      |
| Calcul des moyennes pondérées                      |             |              |      ✅      |
| Gestion du poids des compétences                   |             |              |      ✅      |
| Portail étudiant (consultation des résultats)      |             |              |      ✅      |
| Authentification et gestion des rôles              |             |              |      ✅      |
| Interface d'administration (EasyAdmin)            |             |              |      ✅      |
| Pipeline CI (tests automatisés)                    |             |              |      ✅      |
| Gestion des maîtres d'apprentissage               |            |        ⌛      |             |
| Notifications et alertes                           |            |        ⌛      |             |

## Lancer le projet

### Prérequis

- [Docker](https://www.docker.com/) installé
- [Docker Compose](https://docs.docker.com/compose/) (inclus avec Docker Desktop)
- Cloner le dépôt :

```bash
git clone https://github.com/clementBernard130/Scorea.git
cd Scorea
```

### 1. Environnement de Développement
**Nettoyage complet (Effacement physique)**
Supprime tout : conteneurs, images, volumes (base de données), et réseaux.
```bash
docker compose -f compose.yaml down -v --rmi all --remove-orphans
```
Creation de l'environnement 
```bash
 cp .env.dist .env
```

Construction et Lancement

```bash
docker compose -f compose.yaml --env-file .env up --build -d
```
Initialisation de la Base de Données
```bash
docker compose -f compose.yaml exec web php bin/console doctrine:migrations:migrate --no-interaction
# Charger les fixtures
docker compose -f compose.yaml exec web php bin/console doctrine:fixtures:load --no-interaction
```

### 2. Environnement de Production (Prod)
Nettoyage complet (Effacement physique)
Supprime tout pour repartir sur une base saine avant un nouveau déploiement.

```bash
docker compose -f compose.prod.yaml --env-file .env.prod.local down -v --rmi all --remove-orphans
```

Création de l'environnement 

```bash
cp .env.dist .env.prod.local
```

Changer APP_SECRET et POSTGRES_PASSWORD

Construction et Lancement
```bash
docker compose -f compose.prod.yaml --env-file .env.prod.local up --build -d
```

Initialisation de la Base de Données
```bash
# Appliquer les migrations uniquement
docker compose -f compose.prod.yaml --env-file .env.prod.local exec web php ...bin/console doctrine:migrations:migrate --no-interaction
```

Arrêt avec les données
```bash
docker compose -f compose.prod.yaml --env-file .env.prod.local down
#Relancement
docker compose -f compose.prod.yaml --env-file .env.prod.local up -d
```

## Utilisation

### Portail enseignant

1. **Connexion** — Accédez à l'application via un navigateur et connectez-vous avec un compte enseignant.
2. **Formations** — Créez ou sélectionnez une formation, puis gérez ses sections (promotions).
3. **Unités de compétences** — Définissez les blocs de compétences rattachés à la formation.
4. **Évaluations** — Créez des tests, associez-y des types de notes et configurez les pondérations.
5. **Saisie des notes** — Renseignez les notes des étudiants ; les moyennes sont calculées automatiquement.

### Portail étudiant

1. **Connexion** — Connectez-vous avec votre compte étudiant.
2. **Tableau de bord** — Consultez vos notes, moyennes et votre progression par unité de compétence.

### Interface d'administration

Accessible à `/admin` (compte administrateur requis), elle permet la gestion complète des utilisateurs, formations, sections et paramètres de l'application via **EasyAdmin**.

## Gestion de projet

### Changelog : version 1.0

- [X] Mise en place de l'infrastructure Docker (Symfony + PostgreSQL)
- [X] Modélisation des entités : formations, sections, unités de compétences, compétences, évaluations, notes
- [X] Authentification et gestion des rôles (étudiant, enseignant, administrateur)
- [X] Portail enseignant : gestion des formations, sections, évaluations et saisie des notes
- [X] Portail étudiant : consultation des résultats et de la progression
- [X] Calcul des moyennes pondérées par type de note et par compétence
- [X] Interface d'administration EasyAdmin
- [X] Pipeline CI avec GitHub Actions (tests PHPUnit sur PostgreSQL)

### TODO : version 1.1

- [ ] Gestion complète des maîtres d'apprentissage (`ApprenticeMentors`)
- [ ] Système de notifications et alertes pour les étudiants
- [ ] Export des résultats (PDF / CSV)
- [ ] Tableau de bord avec statistiques visuelles (graphiques de progression)
- [ ] Support de l'import en masse d'étudiants (CSV)

## Diagrammes

### Base de données

![Diagramme de la base de données](./docs/DatabaseDiagram.png)

### Entités

![Diagramme des entités](./docs/Entity.png)

### Diagramme de séquence

![Diagramme de séquence](./docs/SeqDiagram.png)

### Diagramme d'activité

![Diagramme d'activité](./docs/ActivityDiagram.png)

### Diagramme d'exigences

![Diagramme d'exigences](./docs/ExigenceDiagram.png)

## Coding Style

Les conventions de code du projet sont décrites dans [docs/CodingStyle.md](./docs/CodingStyle.md).

## Équipe de développement

- [BERNARD Clément](https://github.com/clementBernard130)
- [OU Philippe](https://github.com/OuPhilippeCci)
- [CLEMENT Aymeric](https://github.com/aclement0)
- [SORIA BONET Enzo](https://github.com/esoriabonet)

---

&copy; 2025-2026 DSNS DEV — CCI Avignon
