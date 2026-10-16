# Déploiement sur un hébergement mutualisé cPanel

## 0. Mettre à jour l'installation existante (sous-domaine cloné depuis GitHub)

C'est le cas actuel : le dossier du sous-domaine est un clone Git de la branche `main`, avec `vendor/` installé sur le serveur.

**Ce que contient cette mise à jour côté serveur :**
- **Aucune nouvelle dépendance.** `composer.json` et `composer.lock` n'ont pas changé, le `vendor/` en place reste valable.
- **Aucune nouvelle variable obligatoire** dans `.env`.
- **4 migrations** :
  - interlocuteur des factures ;
  - données SFEC des clients ;
  - paiements et historique des factures, avec le compte 443400 ;
  - lien entre paiements et livret bancaire.

### Étapes

1. **Sauvegarder** :
   - cPanel → phpMyAdmin → base de l'application → *Exporter* ;
   - garder une copie du fichier `.env` du sous-domaine.
2. **Récupérer le code** : cPanel → **Git™ Version Control** → dépôt du sous-domaine → *Gérer* → onglet *Pull or Deploy* → **Update from Remote** (branche `main`).
   > Ne cliquez **pas** sur *Deploy HEAD Commit* : ce bouton exécute `.cpanel.yml`, prévu pour une installation séparée dans `~/top-gest`. Sans `~/top-gest/.env`, il s'arrêterait de toute façon sans rien copier.
3. **Appliquer les migrations et vider les caches.** Dans le Terminal cPanel, depuis le dossier du sous-domaine :
   ```bash
   cd ~/chemin/du/sous-domaine        # le dossier qui contient « artisan »
   php -v                             # doit afficher 8.3 ou plus
   php artisan down
   php artisan migrate --force
   php artisan optimize:clear
   php artisan optimize
   php artisan up
   ```
   Si `php -v` affiche une version inférieure à 8.3, utilisez le chemin complet : `/opt/cpanel/ea-php83/root/usr/bin/php artisan …`.
4. **Lancer le diagnostic** (lecture seule, ne modifie rien) :
   ```bash
   php artisan mygest:diagnostic
   php artisan sfec:verifier-contacts
   ```
   Le diagnostic liste ce que les nouvelles règles vont changer pour vos utilisateurs :
   - comptes sans rôle ;
   - comptes non-DG sans agence ;
   - factures sans agence ;
   - factures certifiées restées en brouillon ;
   - clients incomplets pour le SFEC.
5. **Vérifier** :
   - connexion ;
   - une facture et son PDF ;
   - les nouvelles pages Finance → Encaissements et Créances ;
   - un rapport PDF.

**En cas de problème :**
- rétablir le code avec `git reset --hard <ancien commit>` dans le dossier du sous-domaine, puis réimporter la sauvegarde de la base ;
- `git log --oneline -3` indique le commit en place avant la mise à jour : notez-le à l'étape 1.

### Ce qui change pour les utilisateurs
- **Droits appliqués finement :**
  - un rôle « assistant » ne peut plus supprimer ;
  - le rôle DG ne peut plus créer de comptes ;
  - les Contacts demandent un rôle métier.
- **Cloisonnement par agence :** un compte non-DG ne voit que les données de son agence. Sans agence, il ne voit plus rien.
- **Factures :**
  - le statut de paiement découle des paiements enregistrés ;
  - une facture certifiée est figée ;
  - les avoirs se créent depuis « Nouvel avoir ».
- **Clients :** les champs SFEC (type, NIU…) sont exigés à l'enregistrement d'une fiche client.
- **Connexion :**
  - 5 tentatives par minute ;
  - les nouveaux mots de passe font 10 caractères minimum, avec majuscule, minuscule et chiffre ;
  - l'inscription publique est fermée.

---

*Les sections suivantes concernent une **installation neuve** en dehors de `public_html`, avec la branche `production` construite par GitHub Actions.*

## Principe

```
 git push (main) ──► GitHub Actions ──► branche « production » ──► cPanel (Git Version Control) ──► ~/top-gest
                     tests + vendor/                               « Deploy HEAD Commit » : .cpanel.yml
```

- **GitHub prépare tout** : le workflow `.github/workflows/production.yml` lance les tests, installe les dépendances PHP de production et publie le résultat, `vendor/` compris, sur la branche `production`. Le serveur n'a donc besoin ni de Composer ni de Node.
- **cPanel installe** : à chaque déploiement, les tâches de `.cpanel.yml` copient le code dans `~/top-gest`, appliquent les migrations et remettent les caches à jour. Le fichier `.env` et le dossier `storage/` (logos, documents, sessions, journaux) ne sont jamais écrasés.
- **Pas de tâche cron ni de worker** : l'application n'utilise ni file d'attente, ni tâche planifiée.

## 1. Prérequis chez l'hébergeur

| Élément | Exigence |
|---|---|
| PHP | **8.3 ou plus** pour le domaine (cPanel → *Sélectionner une version de PHP* ou *MultiPHP Manager*) |
| Extensions PHP | pdo_mysql, mbstring, openssl, curl, dom, xml, ctype, fileinfo, tokenizer, bcmath, gd, zip, intl |
| Base de données | MySQL 8 ou MariaDB 10.6+ |
| Outils cPanel | *Git™ Version Control*, *Terminal* (ou accès SSH), certificat SSL (AutoSSL / Let's Encrypt) |

Le chemin de PHP 8.3 en ligne de commande est en général `/opt/cpanel/ea-php83/root/usr/bin/php`, c'est celui qu'utilise `.cpanel.yml`. S'il est différent chez votre hébergeur, adaptez la ligne `export PHP=` de ce fichier.

## 2. Première mise en ligne

### 2.1 Vérifier la branche `production`
Sur GitHub, onglet **Actions** : le workflow « Production (cPanel) » doit être vert. Il crée la branche `production`.

### 2.2 Créer la base de données
cPanel → **Bases de données MySQL** :
1. créer une base, par exemple `cpaneluser_mygest` ;
2. créer un utilisateur avec un mot de passe fort ;
3. lui donner **tous les privilèges** sur la base.

### 2.3 Donner à cPanel l'accès au dépôt
- **Dépôt public** : rien à faire, l'adresse `https://github.com/curtis-king/top-gest.git` suffit.
- **Dépôt privé (recommandé)** :
  1. cPanel → **Accès SSH** → *Gérer les clés SSH* → générer une clé, puis l'autoriser ;
  2. copier la clé publique, puis sur GitHub → *Settings* → *Deploy keys* → *Add deploy key* (lecture seule) ;
  3. utiliser l'adresse SSH `git@github.com:curtis-king/top-gest.git`.

### 2.4 Cloner le dépôt dans cPanel
cPanel → **Git™ Version Control** → *Créer* :
- URL de clonage : celle de l'étape précédente ;
- chemin du dépôt : `/home/<utilisateur>/repositories/top-gest` ;
- branche : **production**, à choisir dans *Gérer* après le clonage si cPanel a pris `main`.

### 2.5 Faire pointer le domaine sur `public/`
cPanel → **Domaines** → modifier le domaine ou sous-domaine → *Racine du document* : `/home/<utilisateur>/top-gest/public`.

Si le domaine principal est forcé sur `public_html`, remplacez ce dossier par un lien, une fois le premier déploiement fait (Terminal) :
```bash
mv ~/public_html ~/public_html.ancien
ln -s ~/top-gest/public ~/public_html
```

### 2.6 Créer le fichier `.env`
1. En local, générer la clé de l'application :
   ```bash
   php artisan key:generate --show
   ```
2. Dans le Terminal cPanel :
   ```bash
   mkdir -p ~/top-gest
   nano ~/top-gest/.env
   ```
3. Y coller le contenu de `.env.cpanel.example`, puis compléter :
   - `APP_KEY` ;
   - `APP_URL` (en https) ;
   - les accès `DB_*` ;
   - `SFEC_API_KEY` et, si besoin, `SFEC_SCIET` ;
   - les adresses `SEED_*`.
4. Protéger le fichier : `chmod 600 ~/top-gest/.env`.

> **SFEC :** `https://api.sfec.gouv.cg` est l'environnement de **production**, où les factures certifiées ont une valeur fiscale. Tant que vous êtes en phase de test, demandez au SFEC (contact@sfec.cg) l'adresse de son environnement de test et mettez-la dans `SFEC_BASE_URL`.

### 2.7 Déployer
cPanel → Git™ Version Control → *Gérer* → onglet **Pull or Deploy** :
1. *Update from Remote* ;
2. *Deploy HEAD Commit*.

Le journal du déploiement doit se terminer sans erreur. S'il signale « créez d'abord .env », reprenez l'étape 2.6.

### 2.8 Initialiser les données
Deux possibilités, à choisir une fois pour toutes.

**A. Démarrer à vide.** Ces commandes créent les rôles, les permissions, le plan comptable, les journaux et les catégories de dépenses :
```bash
cd ~/top-gest
/opt/cpanel/ea-php83/root/usr/bin/php artisan db:seed --force
```
La commande affiche **une seule fois** le mot de passe aléatoire des comptes administrateur système et DG : notez-le, puis changez-le dès la première connexion.
Ne lancez jamais `TestUserSeeder` en production : il est d'ailleurs bloqué.

**B. Reprendre les données de votre poste.** Vos données locales contiennent la société TOPINFO, des factures, dont FAC-00008 déjà certifiée, des contacts, etc.
1. En local :
   ```bash
   mysqldump -u root my-gest > my-gest.sql
   ```
2. Dans cPanel → **phpMyAdmin** → base de production → *Importer* `my-gest.sql`.
3. Redéployer (étape 2.7) pour appliquer les éventuelles migrations manquantes.
4. **Supprimer les comptes de test** (`…@test.local`, mot de passe `password`) et changer tous les mots de passe.
5. Copier les logos : le contenu de `storage/app/public/` local va dans `~/top-gest/storage/app/public/`, via le Gestionnaire de fichiers.

### 2.9 Vérifier
- [ ] La page de connexion s'affiche en **https** ; il n'y a pas de page d'erreur détaillée (`APP_DEBUG=false`).
- [ ] Connexion avec le compte administrateur, puis changement du mot de passe.
- [ ] Le logo de la société s'affiche (Compagnies) : le lien `public/storage` fonctionne.
- [ ] Un rapport PDF (Comptabilité → Balance → *Exporter en PDF*) s'ouvre avec l'en-tête TOPINFO.
- [ ] Le PDF d'une facture s'ouvre.
- [ ] Les fiches clients sont prêtes pour le SFEC : `php artisan sfec:verifier-contacts`.

## 3. Mettre à jour l'application

1. Pousser sur `main` (après relecture et tests locaux).
2. Attendre que le workflow GitHub soit **vert** : s'il échoue, la branche `production` n'est pas modifiée.
3. cPanel → Git™ Version Control → *Update from Remote*, puis *Deploy HEAD Commit*.

Pendant le déploiement, le site affiche une page de maintenance quelques secondes (`artisan down` / `up`).

## 4. Sauvegardes

- **Base de données :** sauvegarde quotidienne via les sauvegardes cPanel, ou par une tâche cron :
  ```bash
  mysqldump --single-transaction -u UTILISATEUR -p'MOTDEPASSE' BASE | gzip > ~/sauvegardes/mygest-$(date +\%F).sql.gz
  ```
- **Fichiers :** `~/top-gest/storage/app/` (logos, documents archivés) et `~/top-gest/.env`.
- **Durée :** la réglementation SFEC impose de conserver les factures **au moins 10 ans**. Gardez des sauvegardes hors du serveur.

## 5. Dépannage

| Symptôme | Piste |
|---|---|
| Erreur 500 | Lire `~/top-gest/storage/logs/laravel-AAAA-MM-JJ.log` |
| « Your PHP version… » / erreur de syntaxe | Le domaine ou la ligne de commande n'est pas en PHP 8.3 (étape 1, ligne `export PHP=` de `.cpanel.yml`) |
| Page blanche ou 403 | Racine du document mal réglée : elle doit viser `top-gest/public` (étape 2.5) |
| Logos absents | Lien manquant : `ln -sfn ~/top-gest/storage/app/public ~/top-gest/public/storage` |
| « Permission denied » sur storage | `chmod -R u+rwX ~/top-gest/storage ~/top-gest/bootstrap/cache` |
| Le déploiement ne change rien | Oubli de *Update from Remote*, ou workflow GitHub en échec |
| Certification SFEC refusée (401) | `SFEC_API_KEY` absente ou fausse, ou NIU de la société mal renseigné |
