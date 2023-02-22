# Generate Table Mysql

<p align="right"><img src="https://img.shields.io/badge/KoPaTiK-Agency-blue"><p align="right">

* **Générer des tables de votre base de données MySQL**<br>
* **Mise à jour des champs intégrée**<br>
* **Pagination + recherche avancée**<br>
* **Mode gestion des droits utilisateurs possible**<br>

## Demo

[https://generate-table.fgainza.fr](https://generate-table.fgainza.fr)

## Requirement

PHP 7.4<br>
Bootstrap 4.6<br>
Jquery 3.6.3


## Install

Les différentes bibliothèques sont déjà installées

Appeler la fonction tableDatabase()

```php
echo tableDatabase(
    $conn,
    $table,
    $idTab = NULL,
    $title = NULL,
    $limit = 25,
    $nb_between = 2,
    $array_select_limit = [3, 5, 10, 25, 50, 100, 500, 1000],
    $cols_selected = NULL,
    $cols = NULL,
    $formats = NULL,
    $table_permissions = NULL,
    $displayPagination = "both",
    $order_column = NULL,
    $order_sort = "asc"
);
```

* **$conn** (instance PDO) : Connexion PDO (voir le dossier app/)

* **$table** (string) : Nom de la table

* **$idTab** (string) : id de la table

* **$title** (string) : Titre de la table

* **$limit** (integer) : Nombre d'éléments par page (par défaut 25)

* **$nb_between** (integer) : Nb max de pagination autour de la page active (par défaut 2)

* **$array_select_limit** (array) : Tableau des nb d'éléments par page possibles (par défaut [3, 5, 10, 25, 50, 100, 500, 1000])

* **$cols_selected** (array) : Liste des champs à afficher<br>
(seront affichés dans l'ordre indiqué)<br>
Par défaut, tous les champs sont affichés

* **$cols** (array) : Tableau associatif [db_field => intitulé thead th] pour définir les intitulés à afficher pour les champs de la table

Exemple:

```php
$cols = [
    "user_id" => "User Id",
    "user_firstname" => "Firstname",
    "user_lastname" => "Lastname",
    "user_company" => "Company",
    "user_email" => "eMail",
    "user_nb_pass_fail" => "Nb errors",
    "user_verified" => "Verified",
    "user_created_at" => "Date creation",
    "user_updated_at" => "Date last update"
]
```

* **$formats** (array) : Définir un type différent d'affichage que celui de la table<br>
Tableau associatif [db_field => [new format, [old_value => new_value]]]
rq: permet notamment de transformer un champ varchar, bit, tinyint... en variable booleen
(dans ce cas, indiquer "boolean" pour le nouveau format)

Exemple : 

```php
$formats = [
    "user_verified" => [
        "boolean",
        [0 => false, 1 => true]
    ]
];
```

* **$table_permissions** (array) : Cas particulier de création d'une table de gestion des droits (voir l'exemple pour une meilleure compréhension)<br>
Ce tableau ne comprendra que des booléens comme champs éditables

Exemple : 

```php
$permissions = [
    "query" => "
        SELECT
            users.user_id,
            users.user_firstname,
            users.user_lastname,
            users.user_company,
            permissions.admin,
            permissions.actifUser,
            permissions.canAdd,
            permissions.canEdit,
            permissions.canValid,
            permissions.canDelete,
            permissions.updated_at 
        FROM
            users
            LEFT JOIN permissions ON users.user_id = permissions.user_id
        ",

    "primary_key" => "user_id",

    "toggleFields" => [
        "admin", 
        "actifUser",
        "canAdd",
        "canEdit",
        "canValid",
        "canDelete",
    ],

    "tr_special" => [
        "class_special" => "bg-admin",
        "condition" => [
            "field" => "admin",
            "value" => "1",
            "disabled" => true
        ]
    ]
];
```

* **$displayPagination** (string) : Définir le placement de la pagination (au-dessus et/ou au-dessous du tableau)<br>
Valeurs possibles: "top", "bottom", "both" (par défaut "both")

* **$order_column** (string) : Définir la colonne triée par défaut (par défaut la clé primaire)

* **$order_sort** (string) : Définir l'ordre de tri par défaut<br>
valeurs possibles : "asc", "desc" (par défaut "asc")


## Author

* **Frédéric Gainza** _alias_ [@FredGainza](https://github.com/FredGainza)


## License

Licensed ``GNU General Public License v3.0`` - see [LICENSE](LICENSE) for more informations
