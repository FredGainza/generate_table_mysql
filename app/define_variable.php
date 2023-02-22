<?php


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
    $array_toggleBool = NULL,
    $displayPagination = "both",
    $order_column = NULL,
    $order_sort = "asc"
);

/* 
    Nom de la table 
*/
if (!defined('TABLE')) 
    define('TABLE', "users");


/* 
Optionnel - Titre du tableau 
*/
if (!defined('TITLE')) 
    define('TITLE', "Table Users");


/* 
Optionnel (par défaut 25) - Nb éléments par page par défaut 
*/
if (!defined('LIMIT')) 
    define('LIMIT', 25);


/* 
Optionnel (par défaut 2) - Nb max de pagination autour de la page active 
*/
if (!defined('NB_BETWEEN')) 
    define('NB_BETWEEN', 2);


/* 
Optionnel - Tableau nb éléments par page possibles
*/
if (!defined('ARRAY_SELECTED_LIMIT')) 
    define('ARRAY_SELECTED_LIMIT', [3, 5, 10, 25, 50, 100]);


/* 
Optionnel (par defaut tous les champs) - Liste des champs à afficher 
(seront affichés dans l'ordre indiqué) 
*/
if (!defined('COLS_SELECTED')) 
    define('COLS_SELECTED', [
        "user_id",
        "user_firstname",
        "user_lastname",
        "user_company",
        "user_email",
        "user_nb_pass_fail",
        "user_verified",
        "user_created_at",
        "user_updated_at"
    ]);


/* 
Optionnel : permet de définir un intitulé pour les champs de la table 
$cols = Tableau  associatif [db_field => intitulé thead th]
exemple:
$cols = [
    "user_id" => "User Id", 
    "user_firstname" => "Firstname", 
    "user_lastname" => "Lastname", 
    "user_company" => "Company", 
    "user_email" => "eMail", 
    "user_nb_pass_fail" => "Nb errors", 
        "user_verified" => "Verified", 
        "user_created_at" => "Date creation"
    ];
    Si cols == NULL => 
    - l'intitulé correpondra aux noms des colonnes de la table
    */
if (!defined('COLS')) 
    define('COLS', [
        "user_id" => "User Id",
        "user_firstname" => "Firstname",
        "user_lastname" => "Lastname",
        "user_company" => "Company",
        "user_email" => "eMail",
        "user_nb_pass_fail" => "Nb errors",
        "user_verified" => "Verified",
        "user_created_at" => "Date creation",
        "user_updated_at" => "Date last update"
    ]);


/* 
Optionnel : définir un type différent d'affichage que celui de la table 
$types = Tableau associatif [db_field => [new format, [old_value => new_value]]]
rq: permet notamment de transformer un champ varchar, bit, tinyint... en variable booleen
(dans ce cas, indiquer "boolean" pour le nouveau format)
exemple:
$formats = [
    "user_verified" => [
        "boolean",
        [0 => false, 1 => true]
        ]
    ];
*/
if (!defined('FORMATS')) 
    define('FORMATS', NULL);


/* 
    Optionnel : afficher les champs booleens sous forme de toggle switch 
    $array_toggleBool = [fields to display with a toggle]
    $array_togglebool = [
        "querry" => "",
        "fields" => [],
        "th_particular" => [
            "color" => "",
            "condition" => ""
        ]
    ];
*/
if (!defined('ARRAY_TOGGLEBOOL')) 
    define('ARRAY_TOGGLEBOOL', []);


/* 
    Optionnel (par défaut "both") - définir le placement de la pagination 
    (au-dessus et/ou au-dessous du tableau)
    valeurs possibles : "top", "bottom", "both" 
*/
if (!defined('DISPLAY_PAGINATION')) 
    define('DISPLAY_PAGINATION', "both");


/*
    Optionnel (par défaut la clé primaire)
    définir la colonne triée
*/
if (!defined('ORDER_COLUMN_INIT')) 
    define('ORDER_COLUMN_INIT', false);


/*
    Optionnel (par défaut "asc")
    définir l'ordre de tri
    valeurs possibles : "asc", "desc"
*/
if (!defined('ORDER_SORT_INIT')) 
    define('ORDER_SORT_INIT', "asc");
