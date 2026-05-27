# Coding Style

Ce document décrit les conventions de code appliquées dans le projet Scorea.

---

## Sommaire

- [Généralités](#généralités)
- [PHP](#php)
  - [Indentation et formatage](#indentation-et-formatage)
  - [Nommage](#nommage)
  - [Classes et services](#classes-et-services)
  - [Contrôleurs](#contrôleurs)
  - [Entités Doctrine](#entités-doctrine)
  - [Base de données](#base-de-données)
- [Templates Twig](#templates-twig)
- [JavaScript / Stimulus](#javascript--stimulus)
- [Git](#git)

---

## Généralités

- Encodage : **UTF-8** sans BOM
- Fin de ligne : **LF** (`\n`)
- Pas d'espace en fin de ligne
- Une ligne vide à la fin de chaque fichier

---

## PHP

### Indentation et formatage

- **4 espaces** par niveau d'indentation (pas de tabulations)
- Accolades ouvrantes sur la même ligne que la déclaration (K&R style)
- Une instruction par ligne
- Le projet suit le standard **PSR-12**

```php
if ($condition) {
    doSomething();
} else {
    doSomethingElse();
}
```

### Nommage

| Élément                  | Convention          | Exemple                             |
| ------------------------ | ------------------- | ----------------------------------- |
| Variables                | `lowerCamelCase`    | `$userName`, `$maxValue`            |
| Méthodes / Fonctions     | `lowerCamelCase`    | `getUserData()`, `computeAverage()` |
| Classes                  | `PascalCase`        | `UserController`, `EmailService`    |
| Interfaces               | `PascalCase`        | `UserRepositoryInterface`           |
| Constantes               | `UPPER_SNAKE_CASE`  | `MAX_RETRY`, `DEFAULT_TIMEOUT`      |
| Fichiers PHP             | `PascalCase`        | `HomeController.php`                |
| Noms de routes Symfony   | `snake_case`        | `teacher_portal_tests_index`        |

### Classes et services

- Les **services** (dans `src/Service/`) sont déclarés `final`
- Utiliser la **promotion de propriété** dans le constructeur plutôt que des assignations manuelles
- Utiliser `readonly` pour les dépendances injectées dans les services

```php
// Bon
final class StudentSkillsPageBuilder
{
    public function __construct(
        private readonly SkillsUnitRepository $skillsUnitRepository,
        private readonly StudentSkillGradeResolver $resolver,
    ) {
    }
}
```

- Les dépendances sont injectées par le **constructeur** (injection via `__construct`)

### Contrôleurs

- Héritent de `AbstractController`
- Les routes sont définies avec l'attribut PHP `#[Route(...)]`
- Les noms de routes suivent le format `snake_case` préfixé par le module :
  `app_home`, `teacher_portal_test_new`, `grade_details`
- Les permissions utilisent `#[IsGranted(...)]` ou `$this->isGranted(...)` en méthode

```php
#[Route('/teacher-portal/tests', name: 'teacher_portal_tests_index')]
#[IsGranted('ROLE_TEACHER')]
public function testsIndex(Request $request): Response
{
    // ...
}
```

### Entités Doctrine

- Déclarées avec l'attribut `#[ORM\Entity(...)]`
- Le nom de la table est explicitement défini avec `#[ORM\Table(name: '...')]`
- Les getters/setters suivent la convention Symfony (`get`, `set`, les setters retournent `static`)

### Base de données

| Élément           | Convention                        | Exemple                          |
| ----------------- | --------------------------------- | -------------------------------- |
| Noms de tables    | `snake_case`, pluriel si possible | `grades`, `skill_units`, `users` |
| Noms de colonnes  | `snake_case`                      | `id`, `created_at`, `end_date`   |

---

## Templates Twig

- Fichiers nommés en **snake_case** : `tests_index.html.twig`, `grade_form.html.twig`
- Organisés en **sous-dossiers** par rôle ou fonctionnalité :
  - `templates/teacher/` — vues enseignant
  - `templates/student/` — vues étudiant
  - `templates/admin/` — vues administration
  - `templates/security/` — connexion / déconnexion
- Le layout de base est `templates/base.html.twig`

---

## JavaScript / Stimulus

- Variables et fonctions en **lowerCamelCase** : `userName`, `handleClick()`
- Les contrôleurs Stimulus sont nommés en **kebab-case** : `dialog-controller.js`, `csrf-protection-controller.js`
- Les fichiers JS et CSS sont placés dans `assets/`

---

## Git

### Branches

Le nommage des branches suit le format :

```
{numéro-issue}-{description-courte-en-kebab-case}
```

Exemples :
- `94-feature-gestion-du-poids-des-competences`
- `123-fix-correction-pipeline-ci`
- `126-documentation-ajout-de-la-documentation-du-projet`

### Messages de commit

Les commits utilisent un préfixe en majuscules suivi d'une description :

| Préfixe    | Usage                                            |
| ---------- | ------------------------------------------------ |
| `FEAT`     | Nouvelle fonctionnalité                          |
| `FIX`      | Correction de bug                                |
| `UPDATE`   | Mise à jour / modification d'existant            |
| `REFACTOR` | Refactorisation sans changement de comportement  |
| `TEST`     | Ajout ou modification de tests                   |
| `DOCS`     | Documentation uniquement                         |

Exemples :
```
FEAT Ajout de la méthode findWeightRowsBySubjectIds
FIX Correction du calcul de la moyenne globale
UPDATE Mise à jour des tests
DOCS Amélioration du README
```
