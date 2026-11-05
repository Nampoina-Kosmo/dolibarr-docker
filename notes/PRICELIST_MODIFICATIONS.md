# Module PriceList : correctif de compatibilité Dolibarr 19+

## Contexte
- Module : PriceList v2.0.1 (SM2i)
- Environnement : Dolibarr récent (≥ 19) sous Docker (Apache/PHP + MariaDB)

## Problème
L'ouverture du formulaire d'ajout d'un tarif sur la fiche produit :

`/custom/pricelist/product.php?id=<ID>&action=add`

provoque l'erreur SQL suivante :

```
DB_ERROR_SYNTAX You have an error in your SQL syntax; ... near 'error - Bad syntax of the search string) ORDER BY nom ASC' at line 1
```

Requête générée :

```sql
SELECT s.rowid, s.nom as name, ... FROM llx_societe as s
WHERE s.entity IN (1) AND (Filter error - Bad syntax of the search string)
ORDER BY nom ASC
```

## Cause
Dans `includes/view.inc.php`, le sélecteur de tiers est appelé avec un filtre en SQL brut :

```php
$form->select_company($socid, 'socid', 's.client = 1 OR s.client = 2 OR s.client = 3', 1);
```

Depuis Dolibarr 19, le paramètre `$filter` de `Form::select_company()` n'accepte plus de SQL brut (protection contre l'injection SQL). Il doit respecter la syntaxe de recherche universelle `(champ:opérateur:valeur)`. Sinon `forgeSQLFromUniversalSearchCriteria()` retourne la chaîne `Filter error - Bad syntax of the search string`, qui est insérée telle quelle dans le `WHERE`.

## Modification effectuée

Fichier : `custom/pricelist/includes/view.inc.php` (ligne 156)

Avant :

```php
print $form->select_company($socid, 'socid', 's.client = 1 OR s.client = 2 OR s.client = 3', 1);
```

Après :

```php
print $form->select_company($socid, 'socid', version_compare(DOL_VERSION, '19.0.0') >= 0 ? '(s.client:IN:1,2,3)' : 's.client = 1 OR s.client = 2 OR s.client = 3', 1);
```

La syntaxe `(s.client:IN:1,2,3)` est utilisée à partir de Dolibarr 19. L'ancien format SQL est conservé en dessous, pour que le module reste compatible avec les anciennes versions.

## Autres points relevés lors de l'analyse (non modifiés)
1. **SQL non sécurisé** : `$id`, `$productid` et `$socid` sont concaténés sans `(int)` dans `Pricelist::fetch()`, `search()` et `get_price()`. `product.php` passe `GETPOST('id')` sans cast.
2. **`Pricelist::fetch()`** ne charge pas `fk_cat` : un `fetch()` suivi d'un `update()` écrase la catégorie par `null`.
3. **`get_price()`, tarifs de catégories** : `ORDER BY from_qty DESC, price ASC` place les tarifs en remise (`price` NULL) avant les prix fixes, sans les comparer.
4. **Validation avec `empty()`** : un prix ou une remise à `0` est traité comme vide et ne peut pas être saisi.
5. **Hook `doActions`** : `$object->fetch_thirdparty()` est appelé pour tous les contextes (y compris `productcard` et `thirdpartycard`), ce qui n'est pas pertinent. Les blocs commande, devis et facture sont quasi identiques et pourraient être factorisés.
6. **Formatage des prix** : `price($obj->price)` (nombre formaté) est passé à `updateline()` ; `price2num()` serait plus approprié.
7. **Trigger `CATEGORY_DELETE`** : double `break` (`interface_99_modPriceList_PriceListTriggers.class.php`).
8. **`view.inc.php:128`** : `$user->rights->service` est testé sans `->creer`.
9. **Dolibarr 21+** : l'usage de `$user->rights->...` et `$conf->global->...` est déprécié (`hasRight()` et `getDolGlobalString()` sont recommandés).
10. **Table `llx_pricelist`** : pas de contrainte d'unicité sur `(fk_product, fk_soc, fk_cat, from_qty)`, ce qui permet des doublons.
