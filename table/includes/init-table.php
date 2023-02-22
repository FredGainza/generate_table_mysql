<?php
/**
 * Générer une table depuis une database sql
 *
 * @param [instance PDO] $conn :            connecteur base de donnée (cf app/db.inc.php)
 * @param [string] $table :                 Nom de la table
 * 
 * OPTIONNELS:
    *
    * @param [type] $title :                Nom de la table
    *
    * @param [integer] $limit :             Nombre d'éléments par page (par défaut 25)
    *
    * @param [integer] $nb_between :        Nb max de pagination autour de la page active (par défaut 2)
    *
    * @param [array] $array_select_limit :  Tableau des nb d'éléments par page possibles (par défaut [3, 5, 10, 25, 50, 100, 500, 1000])
    *
    * @param [array] $cols_selected :       Liste des champs à afficher
    *   (seront affichés dans l'ordre indiqué)
    *   Par défaut, tous les champs sont affichés
    *
    * @param [array] $cols :                Tableau associatif [db_field => intitulé thead th] pour définir les intitulés à afficher pour les champs de la table
    *
    * @param [array] $formats :             Définir un type différent d'affichage que celui de la table
    *   Tableau associatif [db_field => [new format, [old_value => new_value]]]
    *   rq: permet notamment de transformer un champ varchar, bit, tinyint... en variable booleen
    *   (dans ce cas, indiquer "boolean" pour le nouveau format)
    *
    * @param [array] $table_permissions :    Cas particulier de création d'une table de gestion des droits (voir l'exemple pour une meilleure compréhension)
    *   Ce tableau ne comprendra que des booléens comme champs éditables
    *
    * @param [string] $displayPagination :  Définir le placement de la pagination (au-dessus et/ou au-dessous du tableau)
    *   Valeurs possibles: "top", "bottom", "both" (par défaut "both")
    *
    * @param [string] $order_column :       Définir la colonne triée par défaut (par défaut la clé primaire)
    *
    * @param [string] $order_sort :         Définir l'ordre de tri par défaut

 * @return tableau html

*/
function tableDatabase(
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
) {

    $searchTest = false;
    $search = "";
    $page = 0;
    include 'preparationData.php';
    require 'functions.php';

    $pagiHtml = pagi($pagiTest, $page, $nb_total, $limit, $order_column, $order_sort, $nb_between);
    $tableHtml = table($columns, $order_column, $add_class, $limit, $page, $asc_or_desc, $columns_display, $up_or_down, $data, $primary_key, $formats, $searchTest, $search, $table_permissions);
    $init_html = "";
    $idTabOk = $idTab != NULL ? "displayTable__".$idTab : "displayTable__".$table;
    $init_html .= "<div id=\"".$idTabOk."\">";
    $init_html .= "<div class=\"container-fluid w-container-main-resp mx-auto\">";
    $init_html .= "<h2 class=\"text-info mt-4 mb-2\">" . $title . "</h2>";
    $init_html .= "<hr class=\"mt-0 mb-4-5\">";

    $init_html .= "<div class=\"row justify-content-between align-items-center my-2 mr-0\">";
    $init_html .= "<div id=\"container_select_pagination\" class=\"col-12 mb-3 mb-md-0 col-md-4\">";
    $init_html .= "<label>";
    $init_html .= "Afficher &nbsp;";
    $init_html .= "</label>";
    $init_html .= "<select id=\"select_pagination\" name=\"select_pagination\" class=\"table-select\">";

    foreach ($array_select_limit as $lim) {
        if ($lim != $limit)
            $init_html .= "<option value=\"" . $lim . "\">" . $lim . "</option>";
        else
            $init_html .= "<option value=\"" . $lim . "\" selected=\"selected\">" . $lim . "</option>";
    }
    $init_html .= "</select>";

    $init_html .= "<label>";
    $init_html .= "&nbsp; entrées";
    $init_html .= "</label>";
    $init_html .= "</div>";
    $init_html .= "<div id=\"container_input_search\" class=\"d-flex align-items-center col-12 col-md-auto mr-0\">";
    $init_html .= "<div class=\"input-group align-items-center\">";
    $init_html .= "<label>";
    $init_html .= "Rechercher : &nbsp;";
    $init_html .= "</label>";

    $init_html .= "<input id=\"input_search\" type=\"search\" class=\"table-input border-right-0 border\">";
    $init_html .= "<i class=\"fa fa-times btn-input-close-search\"\"></i>";
    $init_html .= "</div>";

    $init_html .= "</div>";
    $init_html .= "</div>";

    $init_html .= "<div class=\"row justify-content-between align-items-center my-2 mr-2\">";
    $init_html .= "<div class=\"col-12 col-md-4 mb-3 mb-md-0\">";
    $init_html .= "Nb total d'élements : <span id=\"displayNbTotalTop\" class=\"nb-total-elements\">" . $nb_total . "</span>";
    $init_html .= "</div>";
    $init_html .= "<div class=\"col-12 col-md-auto\">";
    $init_html .= "<div class=\"row d-flex align-items-end ml-row-resp mb-3 mb-md-0\">";

    $display_jump = $pagiTest ? "" : " style=\"display: none;\"";
    $init_html .= "<div id=\"rowJumpTop\" class=\"row d-none d-md-flex align-items-center mr-2rem\"" . $display_jump . ">";
    $init_html .= "<label class=\"mr-2 label-jump\" for=\"jump\">Aller à la page</label>";
    $init_html .= "<input class=\"table-input-jump ml-2\" type=\"text\" name=\"jumpTop\">";
    $init_html .= "<button id=\"valid-jump-top\" type=\"button\" class=\"btn btn-info btn-sm btn-sm-valid-jump\">OK</button>";
    $init_html .= "</div>";
    $init_html .= "<div id=\"paginationGeneratedTop\">" . $pagiHtml . "</div>";
    $init_html .= "</div>";
    $init_html .= "</div>";
    $init_html .= "</div>";

    if ($table_permissions == NULL){
        $init_html .= "<div class=\"row mt-2 mb-1 mt-3\">";
        $init_html .= "<button id=\"btnAddRow\" type=\"button\" class=\"btn btn-warning btn-sm btn-add-row ml-auto mr-3\">";
        $init_html .= "<i class=\"fas fa-plus-square mr-2\"></i>AJOUTER";
        $init_html .= "</button>";
        $init_html .= "</div>";
    }

    $classTableTop = $table_permissions != NULL ? " mt-4" : "";
    $tableId = $idTab != NULL ? "tableGenerated__".$idTab : "tableGenerated__".$table;
    $tableResponsiveId = $idTab != NULL ? "tableResponsive__".$idTab : "tableResponsive__".$table;
    $init_html .= "<div id=\"".$tableResponsiveId."\" class=\"table-reponsive".$classTableTop."\">";
    $init_html .= "<table id=\"".$tableId."\" class=\"table table-striped table-sm table-generated\">";
    $init_html .= $tableHtml;
    $init_html .= "</table>";
    $init_html .= "</div>";

    $init_html .= "<div class=\"row justify-content-between align-items-center mt-4 mb-5 mr-2\">";
    $init_html .= "<div class=\"col-4 col-md-3\">";
    $init_html .= "Nb total d'élements : <span id=\"displayNbTotalBottom\" class=\"nb-total-elements\">" . $nb_total . "</span>";
    $init_html .= "</div>";
    $init_html .= "<div class=\"col-8 col-md-auto\">";
    $init_html .= "<div class=\"row d-flex align-items-end\">";
    $init_html .= "<div id=\"rowJumpBottom\" class=\"row d-flex align-items-center mr-2rem\"" . $display_jump . ">";
    $init_html .= "<label class=\"mr-2 label-jump\" for=\"jump\">Aller à la page</label>";
    $init_html .= "<input class=\"table-input-jump ml-2\" type=\"text\" name=\"jumpBottom\">";
    $init_html .= "<button id=\"valid-jump-bottom\" type=\"button\" class=\"btn btn-info btn-sm btn-sm-valid-jump\">OK</button>";
    $init_html .= "</div>";
    $init_html .= "<div id=\"paginationGeneratedBottom\">" . $pagiHtml . "</div>";
    $init_html .= "</div>";
    $init_html .= "</div>";
    $init_html .= "</div>";

    $init_html .= "<!-- Modal confirmation suppression row -->";
    $init_html .= "<div id=\"confirmDeleteRow\" class=\"modal fade\">";
    $init_html .= "<div class=\"modal-dialog modal-confirm-info\">";
    $init_html .= "<div class=\"modal-content bg-notif\">";
    $init_html .= "<div class=\"modal-header bg-notif-header\">";
    $init_html .= "<div class=\"icon-box-info text-white\">";
    $init_html .= "<i class=\"fas fa-question\"></i>";
    $init_html .= "</div>";
    $init_html .= "</div>";
    $init_html .= "<div class=\"modal-body bg-notif\">";
    $init_html .= "<h4 class=\"modal-title text-center w-100 mt-2 color-title-notif\">Demande de confirmation</h4>";
    $init_html .= "<div id=\"msgConfirmDeleteRow\" class=\"ft-18px mt-3 mb-4 text-center mx-auto text-dark\"></div>";
    $init_html .= "<div class=\"row justify-content-between mt-3\">";
    $init_html .= "<button id=\"btn-del-row-cancel\" type=\"button\" class=\"btn btn-danger btn-shadow modif-ref-btn ml-4\" data-dismiss=\"modal\" aria-label=\"Close\"><i class=\"far fa-times-circle mr-2\"></i>Annuler</button>";
    $init_html .= "<button id=\"btn-del-row-valid\" type=\"submit\" class=\"btn btn-success btn-shadow modif-ref-btn mr-4\"><i class=\"fas fa-check mr-2\"></i>Confirmer</button>";
    $init_html .= "</div>";
    $init_html .= "</div>";
    $init_html .= "</div>";
    $init_html .= "</div>";
    $init_html .= "</div>";

    $init_html .= "<!-- Modal notif delete row -->";
    $init_html .= "<div id=\"notifDeleteRow\" class=\"modal fade\">";
    $init_html .= "<div class=\"modal-dialog\">";
    $init_html .= "<div class=\"modal-content bb-shadow bg-danger-light\">";
    $init_html .= "<div class=\"py-1 border-bottom-notif\">";
    $init_html .= "<button type=\"button\" class=\"close ml-auto mr-2 mb-1\" data-dismiss=\"modal\" aria-label=\"Close\">";
    $init_html .= "<span aria-hidden=\"true\">&times;</span>";
    $init_html .= "</button>";
    $init_html .= "</div>";
    $init_html .= "<div class=\"pt-3 pb-2-3 px-3 text-danger-perso\">";
    $init_html .= "<div id=\"msgNotifDeleteRow\" class=\"ft-110\"></div>";
    $init_html .= "</div>";
    $init_html .= "</div>";
    $init_html .= "</div>";
    $init_html .= "</div>";

    $init_html .= "<!-- Modal édition row -->";
    $init_html .= "<div id=\"modalRow\" class=\"modal fade\" tabindex=\"-1\">";
    $init_html .= "<div class=\"modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg\">";
    $init_html .= "<div class=\"modal-content\">";
    $init_html .= "<div class=\"modal-header\">";
    $init_html .= "<h5 id=\"rowTitle\" class=\"modal-title\"></h5>";
    $init_html .= "<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">";
    $init_html .= "<span aria-hidden=\"true\">&times;</span>";
    $init_html .= "</button>";
    $init_html .= "</div>";
    $init_html .= "<div id=\"rowContent\" class=\"modal-body\"></div>";
    $init_html .= "</div>";
    $init_html .= "</div>";
    $init_html .= "</div>";

    $init_html .= "<!-- Modal notif édition row -->";
    $init_html .= "<div id=\"notifEditRow\" class=\"modal fade\">";
    $init_html .= "<div class=\"modal-dialog\">";
    $init_html .= "<div class=\"modal-content bb-shadow bg-success-light\">";
    $init_html .= "<div class=\"py-1 border-bottom-notif\">";
    $init_html .= "<button type=\"button\" class=\"close ml-auto mr-2 mb-1\" data-dismiss=\"modal\" aria-label=\"Close\">";
    $init_html .= "<span aria-hidden=\"true\">&times;</span>";
    $init_html .= "</button>";
    $init_html .= "</div>";
    $init_html .= "<div class=\"pt-3 pb-2-3 px-3 text-success-perso\">";
    $init_html .= "<div id=\"msgNotifEditRow\" class=\"ft-110\"></div>";
    $init_html .= "</div>";
    $init_html .= "</div>";
    $init_html .= "</div>";
    $init_html .= "</div>";
    $init_html .= "</div>";

    $_SESSION[$tableId]['pagi_table'] = $table;
    $_SESSION[$tableId]['pagi_primaryKey'] = $primary_key;
    $_SESSION[$tableId]['pagi_limit'] = $limit;
    $_SESSION[$tableId]['pagi_page'] = $page;
    $_SESSION[$tableId]['pagi_column'] = $order_column;
    $_SESSION[$tableId]['pagi_sort'] = $order_sort;
    
    $_SESSION[$tableId]['cols_selected'] = $cols_selected;
    $_SESSION[$tableId]['cols'] = $cols;
    $_SESSION[$tableId]['nb_between'] = $nb_between;
    $_SESSION[$tableId]['array_select_limit'] = $array_select_limit;
    $_SESSION[$tableId]['formats'] = $formats;
    $_SESSION[$tableId]['table_permissions'] = $table_permissions;
    $_SESSION[$tableId]['displayPagination'] = $displayPagination;

    return $init_html;
}
