# Monday Kardinal

Module Dolibarr qui envoie vers **monday.com** (sens unique : Dolibarr → monday), par une tâche planifiée toutes les 5 minutes (polling) :

| Dolibarr | Board monday | Ce qui est envoyé |
|---|---|---|
| Devis et factures clients | **Devis & Factures** | référence, type, statut, montants HT / TVA / TTC, dates, email client, détail des prestations |
| Commandes clients validées | **Suivi de production** | référence, montant HT, date de livraison, lien vers la commande |

## Installation

1. Copier le dossier `mondaykardinal` dans `custom/` de l'application.
2. **Accueil → Configuration → Modules/Applications** : activer **Monday Kardinal**.
3. Activer le module **Travaux planifiés** de Dolibarr, ouvrir sa configuration et y saisir une **clé de sécurité** (une suite aléatoire longue, propre à l'application).
4. Dans `.env` de l'application, mettre la même clé et activer le conteneur des tâches planifiées, puis `docker compose up -d` :

   ```env
   DOLI_CRON_KEY=<la même clé que dans Dolibarr>
   DOLI_CRON_USER=admin
   COMPOSE_PROFILES=cron
   ```

   Ne mettez jamais `DOLI_CRON=1` sur le conteneur `dolibarr` : l'image lancerait alors cron à la place d'Apache et le site ne répondrait plus. Le service `cron` du `docker-compose.yml` s'en charge dans un conteneur séparé.

5. Ouvrir la configuration du module (roue crantée) et saisir le **jeton API monday** (monday → avatar → Developers → My access tokens), puis **Enregistrer**.
6. Cliquer sur **Tester la configuration** : le module contrôle le jeton, les boards, les colonnes (existence et type) et les libellés de statut, sans rien modifier.
7. Si quelque chose manque, cliquer sur **Créer / réparer les boards**.
8. Cliquer sur **Synchroniser maintenant** pour un premier passage manuel.

## Créer / réparer les boards

Le bouton est sans danger à relancer. Pour chaque board activé :

- **Board supprimé ou identifiant vide** : le board est recréé (dans l'espace de travail indiqué, ou celui par défaut du jeton) avec toutes ses colonnes, et les identifiants sont enregistrés dans la configuration.
- **Colonne manquante** : elle est créée.
- **Colonne existante de même titre et de même type** : elle est reliée (son identifiant est enregistré), sans rien dupliquer.
- **Colonne configurée mais de mauvais type** : signalée, jamais modifiée.
- **Libellé de statut absent** : signalé ; il est créé automatiquement à la première synchro.

Rien n'est jamais supprimé dans monday. Le jeton doit avoir le droit de créer des boards.

Structure créée pour chaque board (même structure que les boards d'origine) :

- **Devis & Factures** : Client et Deal (relations vers les boards Contacts et Deals), ID Dolibarr, Réf. Dolibarr, Type de document, Statut, Montant HT, TVA, Montant TTC, Date d'émission, Échéance / Validité, Email client, Détail des prestations.
- **Suivi de production** : Client et Deal (relations), ID Dolibarr, Réf. commande Dolibarr, Étape de production, Montant HT, Date de pose / livraison, Lien Dolibarr, Type de prestation, Responsable, Planning, Notes atelier.

Les boards liés (Contacts et Deals) se règlent dans la configuration. Si l'un est vide, la colonne de relation correspondante n'est pas créée (avertissement dans le résultat).

**Sous-éléments** : l'API monday ne permet pas de créer cette colonne (« not supported yet in the API »). Elle n'est pas nécessaire à la synchro. Si vous en avez besoin, ajoutez-la à la main dans monday (le premier sous-élément créé fait apparaître la colonne).

Les réglages par défaut correspondent aux boards actuels de Kardinal. Pour une autre application ou un autre compte monday, il suffit de saisir un autre jeton (et éventuellement l'espace de travail), de vider les identifiants de board, puis de cliquer sur **Créer / réparer**.

## Fonctionnement

- Clé de rapprochement : la colonne monday **ID Dolibarr**, valeur `PROPAL-<id>`, `FACTURE-<id>` ou `COMMANDE-<id>`. On utilise l'`id` Dolibarr et non la référence, car celle d'un brouillon change à la validation (`(PROV7)` → `PR2610-0007`).
- Un élément est créé s'il n'existe pas, sinon mis à jour. Rien n'est écrit si le document n'a pas changé (empreinte stockée dans `llx_mondaykardinal_map`).
- Si un élément est supprimé dans monday, il est recréé au passage suivant. S'il existe déjà (ID Dolibarr saisi à la main), il est réutilisé : pas de doublon.
- Les brouillons de devis et de factures ne sont pas envoyés par défaut (option). Les commandes brouillon ou annulées ne le sont jamais.

### Statuts des devis et factures

| Document | Statut Dolibarr | Libellé monday par défaut |
|---|---|---|
| Devis | brouillon / validé / signé / refusé / facturé | En cours / Envoyé / Accepté / Refusé / Fait |
| Facture | brouillon / impayée / en retard / payée / abandonnée | En cours / En attente de paiement / En retard / Payée / Bloqué |

Les libellés se modifient dans la configuration.

### Suivi de production : ce qui appartient à l'atelier

Dolibarr ne connaît pas l'avancement de la fabrication. La synchro ne met donc à jour que les colonnes venant de Dolibarr (référence, montant, date de livraison, lien). L'**étape de production** n'est renseignée qu'**à la création** de l'élément (par défaut « BAT en cours »), puis l'atelier la fait évoluer dans monday. **Type de prestation**, **Responsable**, **Planning** et **Notes atelier** ne sont jamais écrits par le module. Le nom de l'élément n'est fixé qu'à la création.

## Limites actuelles

- Sens unique : les modifications faites dans monday ne reviennent pas dans Dolibarr.
- Un document supprimé définitivement dans Dolibarr n'est pas supprimé dans monday.
- Les colonnes de relation **Client** et **Deal** sont créées mais **pas alimentées** : elles restent à remplir à la main dans monday.
- Le jeton monday est stocké tel quel dans la table des constantes de Dolibarr (comme les autres secrets de configuration). Accès réservé aux administrateurs.
