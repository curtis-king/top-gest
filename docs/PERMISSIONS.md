# Système de permissions — conception

Document de référence pour l'implémentation du système de rôles/permissions, issu de l'analyse de l'existant et des décisions prises avant développement.

## 1. État actuel du code (avant changement)

- `users.role` : un seul champ, deux valeurs possibles — `User::ROLES = ['user', 'admin']` ([app/Models/User.php](../app/Models/User.php)).
- `RoleMiddleware` existe ([app/Http/Middleware/RoleMiddleware.php](../app/Http/Middleware/RoleMiddleware.php)) mais **n'est jamais enregistré** dans `bootstrap/app.php` ni utilisé sur aucune route — code mort.
- La directive Blade `@role(...)` ([app/Providers/AppServiceProvider.php](../app/Providers/AppServiceProvider.php)) est fonctionnelle côté vues.
- Le filtrage par agence (`agence_id`) est géré **à la main, dupliqué dans 13 endroits** : `EmployeeController`, `FactureController` (x2), `LivretBancaireController` (x2), `ContactController`, `DepotController`, `DocumentController` (x2), `DossierEmployeeController`, `MouvementStockController`, `StockController`, `TacheProjectController` (x2). Pattern type :
  ```php
  if (!auth()->user()->isAdmin()) {
      $userEmp = Employee::where('user_id', auth()->id())->first();
      if ($userEmp && $userEmp->agence_id) {
          $query->where('agence_id', $userEmp->agence_id);
      }
  }
  ```
- `employees.fonction_id` (poste RH + salaire) **n'a aucun lien** avec les permissions — c'est une donnée RH pure, à ne pas mélanger avec ce système.
- `employees.user_id` est le seul pont entre un compte de connexion et une fiche employé (sens employé → user, nullable).

## 2. Modèle cible — deux axes indépendants

### Axe 1 : Portée (scope)

Détermine **quelles agences/données** un utilisateur voit.

| Portée | Filtre |
|---|---|
| **DG** | Aucun filtre — toutes agences |
| **Chef d'agence** | `agence_id = son agence` — sur tous les domaines |
| **Agent** | `agence_id = son agence` — uniquement sur le(s) domaine(s) où il a des permissions |
| **Employé** (défaut) | `user_id = lui-même` — uniquement son propre dossier RH |

### Axe 2 : Domaines et permissions par action

Catalogue de permissions `{domaine}.{action}`, CRUD de base partout + actions sensibles confirmées dans le code :

| Domaine | Ressources couvertes | CRUD de base | Actions sensibles confirmées |
|---|---|---|---|
| `rh` | compagnies, agences, fonctions, employees, dossiers-employees, conges, retenus, primes, payements-employees | view, create, update, delete | — (aucun workflow d'approbation dans le code actuel) |
| `projets` | projects, taches-projects, affectation-taches | view, create, update, delete | `projets.status` (`ProjectController::updateStatus`, `TacheProjectController::updateStatusInline`) |
| `finance` | factures, banques, livrets-bancaires, contacts | view, create, update, delete | `finance.factures.valider` (`FactureController::updateStatut`) |
| `stock` | categories-produits, depots, produits, stocks, mouvements-stocks | view, create, update, delete | — |
| `archives` | categories-documents, documents | view, create, update, delete | — |
| `administration` | users, dashboard | view, create, update, delete | réservé DG uniquement |

> ⚠️ Hypothèses invalidées lors de la vérification du code : `rh.conges.approve` et `rh.payements.validate` n'existent pas — `CongeController` et `PayementEmployeeController` sont de simples CRUD sans workflow d'approbation. À ajouter uniquement si ce besoin métier est confirmé séparément ; ce n'est pas un prérequis du système de permissions.

## 3. Bundles de rôles (composition de l'axe 2)

- **Responsable {domaine}** = `{domaine}.view, .create, .update, .delete` + actions sensibles du domaine
- **Assistant {domaine}** = `{domaine}.view, .create, .update` (jamais `.delete`, jamais les actions sensibles)

## 4. Matrice positions → portée → permissions

| Poste | Portée | Permissions |
|---|---|---|
| Directeur Général | DG | Tous les bundles, tous domaines |
| Chef d'agence | Chef d'agence (son agence) | Union de tous les bundles "Responsable" |
| Responsable {domaine} | Agent (son agence) | Bundle "Responsable {domaine}" |
| Assistant {domaine} | Agent (son agence) | Bundle "Assistant {domaine}" |
| Employé | Employé (lui-même) | Aucune — accès à son seul dossier |

## 5. Implémentation recommandée

- Approche à deux axes (portée + bundles de permissions) plutôt que rôles à plat, pour éviter l'explosion combinatoire (DG, Chef d'agence, 5×Responsable, 5×Assistant = 12+ rôles sinon) et permettre la granularité par action (ex. Assistant peut créer mais pas supprimer).
- Suggéré : `spatie/laravel-permission` pour la table `roles`/`permissions`, + un champ `scope` custom (DG / chef-agence / agent / employe) sur `User` pour l'axe 1, qui reste indépendant du système de permissions du package.
- Centraliser le filtrage par agence (actuellement dupliqué 13 fois) dans un trait Eloquent réutilisable au moment de l'implémentation, au lieu de continuer à le copier dans chaque contrôleur.
- Brancher (ou supprimer) `RoleMiddleware`, aujourd'hui non utilisé.
