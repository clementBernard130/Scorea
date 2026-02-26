# Scorea 📝

Scorea est une application pour modéliser le règlement d'un diplôme.
Elle permet de créer des blocs de compétences et d'y définir les compétences associées,
afin de représenter formellement l'organisation des savoirs et les règles d'obtention du diplôme.

# Lancer le projet avec Docker Compose

## Prérequis

- [Docker](https://www.docker.com/) installé
- [Docker Compose](https://docs.docker.com/compose/) (souvent inclus avec Docker Desktop)
- Cloner le dépôt du projet :
```bash
git clone <URL DU PROJET>
cd <NOM_DU_PROJET>
```

## Etapes pour lancer le projet :

**1. Construire et démarrer les conteneurs (WEB + BDD) :**
```bash
docker-compose up --build
```

*L’option --build force la reconstruction des images si nécessaire.*

**2. Attendre que les services soient prêts**
Certains conteneurs peuvent prendre quelques secondes pour démarrer complètement (ex: bases de données). Patientez quelques instants avant de continuer.

**3. Vérifier que les conteneurs sont actifs :**
```bash
docker-compose ps 
```

**4. Accéder à l'application (dev) :**
```bash
http://localhost
```

**5. Arrêter les conteneurs :**
```bash
docker-compose down
```

## Coding Style
Les conventions de code du projet sont décrites dans le fichier  
[CodingStyle.md](./docs/CodingStyle.md).

## Diagrammes

### Base de données

![Diagramme de la base de données](./docs/DatabaseDiagram.png)

*Figure : Diagramme de la base de données (/docs/DatabaseDiagram.png)*

## Contributeurs

- [Clement BERNARD](https://github.com/clementBernard130)
- [Philippe OU](https://github.com/OuPhilippeCci)
- [Aymeric CLEMENT](https://github.com/aclement0)
- [Enzo SORIA BONET](https://github.com/esoriabonet)
