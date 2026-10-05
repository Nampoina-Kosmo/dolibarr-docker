# Dolibarr avec Docker

Environnement Dolibarr (ERP/CRM) local, lancé avec Docker. Il contient deux conteneurs :

- `dolibarr-web` : l'application Dolibarr (Apache + PHP)
- `dolibarr-db` : la base de données MariaDB

## Prérequis

1. Installer **Docker Desktop** : https://www.docker.com/products/docker-desktop/
2. Lancer Docker Desktop et attendre que l'icône indique qu'il est démarré (« Engine running »).
3. Vérifier dans un terminal (PowerShell) :

```bash
docker --version
docker compose version
```

Si ces deux commandes affichent une version, c'est bon.

## Installation (première fois)

```bash
git clone <url-du-depot> docker-dolibarr
cd docker-dolibarr
```

Le fichier `.env` n'est pas versionné (il est dans `.gitignore`). S'il n'existe pas, le créer à la racine avec ce contenu :

```env
DOLIBARR_VERSION=latest
DOLIBARR_PORT=8095
MYSQL_ROOT_PASSWORD=root_dev
MYSQL_DATABASE=dolibarr
MYSQL_USER=dolibarr
MYSQL_PASSWORD=dolibarr_dev
DOLI_ADMIN_LOGIN=admin
DOLI_ADMIN_PASSWORD=admin
DOLI_INIT_DEMO=0
```

Puis démarrer :

```bash
docker compose up -d
```

- Le premier lancement télécharge les images, puis Dolibarr installe sa base automatiquement.
- **Compter 2 à 5 minutes** avant que le site réponde. C'est normal.

## Accès

| Élément | Valeur |
|---|---|
| URL | http://localhost:8095 |
| Login | `admin` |
| Mot de passe | `admin` |

Ce sont des identifiants de développement. Ne pas les utiliser en production.

## Commandes du quotidien

Toutes se lancent depuis le dossier `docker-dolibarr`.

| Action | Commande |
|---|---|
| Démarrer | `docker compose up -d` |
| Arrêter (les données sont conservées) | `docker compose stop` |
| Redémarrer | `docker compose restart` |
| Voir l'état des conteneurs | `docker compose ps` |
| Voir les logs de Dolibarr | `docker compose logs -f dolibarr` |
| Voir les logs de la base | `docker compose logs -f mariadb` |
| Ouvrir un shell dans Dolibarr | `docker exec -it dolibarr-web bash` |
| Ouvrir la console MariaDB | `docker exec -it dolibarr-db mariadb -u dolibarr -pdolibarr_dev dolibarr` |

`-d` signifie « en arrière-plan ». Pour quitter l'affichage des logs, faire `Ctrl+C` (les conteneurs continuent de tourner).

On peut aussi tout piloter depuis l'interface de Docker Desktop (onglet *Containers*).

## Arrêter et supprimer

| Action | Commande | Effet sur les données |
|---|---|---|
| Arrêter et supprimer les conteneurs | `docker compose down` | Données conservées |
| **Tout remettre à zéro** | `docker compose down -v` | **Base de données supprimée** |

Après un `down -v`, un nouveau `docker compose up -d` réinstalle un Dolibarr vierge. Pour repartir de zéro, il faut aussi vider le contenu de `dolibarr/documents/`.

## Où sont les données ?

- **Base de données** : dans le volume Docker `docker-dolibarr_db_data` (géré par Docker).
- **Dossier `dolibarr/`** (visible dans l'explorateur Windows) :
  - `dolibarr/documents/` : documents générés (devis, factures PDF, fichiers joints)
  - `dolibarr/custom/` : modules personnalisés

## Configuration

Tout se règle dans le fichier `.env`. Après toute modification :

```bash
docker compose up -d
```

| Variable | Rôle |
|---|---|
| `DOLIBARR_VERSION` | Version de l'image Dolibarr (ex. `latest`, `21.0.0`) |
| `DOLIBARR_PORT` | Port du site sur votre machine |
| `DOLI_ADMIN_LOGIN` / `DOLI_ADMIN_PASSWORD` | Compte administrateur créé à l'installation |
| `DOLI_INIT_DEMO` | `1` pour charger des données de démo (à l'installation uniquement) |
| `MYSQL_*` | Accès à la base de données |

Les identifiants admin et `DOLI_INIT_DEMO` ne sont pris en compte qu'à la **première installation**. Pour les changer ensuite, faire un `docker compose down -v`.

## Problèmes fréquents

**« port is already allocated » / le port est déjà utilisé**
Un autre conteneur ou programme utilise le port. Voir les ports occupés par Docker :

```bash
docker ps --format "table {{.Names}}\t{{.Ports}}"
```

Puis changer `DOLIBARR_PORT` dans `.env` (choisir un port libre) et relancer `docker compose up -d`. N'oubliez pas d'adapter l'URL.

**Le site ne répond pas juste après le démarrage**
L'installation est encore en cours. Suivre l'avancement avec `docker compose logs -f dolibarr` et attendre la ligne `resuming normal operations`.

**`error during connect` / `Cannot connect to the Docker daemon`**
Docker Desktop n'est pas lancé. Le démarrer et réessayer.

**Mauvais identifiants**
Vérifier `DOLI_ADMIN_LOGIN` et `DOLI_ADMIN_PASSWORD` dans `.env`. Si le fichier a été modifié après la première installation, faire un `docker compose down -v` puis `docker compose up -d`.

**Repartir proprement**

```bash
docker compose down -v
docker compose up -d
```
