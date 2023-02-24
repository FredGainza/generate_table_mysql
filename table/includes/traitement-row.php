<?php
session_start();

require '../../app/db.inc.php';
require '../../app/toolbox.php';
require '../includes/functions.php';

$array_types = [
    "type_int" => [
        "TINYINT",
        "SMALLINT",
        "MEDIUMINT",
        "INT",
        "INTEGER",
        "BIGINT",
        "BIGINTEGER"
    ],

    "type_decimal" => [
        "FLOAT",
        "DOUBLE",
        "DOUBLE PRECISION",
        "REAL",
        "DECIMAL"
    ],

    "type_date" => [
        "DATE",
        "DATETIME",
        "TIMESTAMP",
        "TIME",
        "YEAR"
    ],

    "type_bool" => [
        "BIT",
        "BOOL"
    ],

    "type_text" => [
        "CHAR",
        "VARCHAR",
        "TINYBLOB",
        "TINYTEXT",
        "BLOB",
        "TEXT",
        "MEDIUMBLOB",
        "MEDIUMTEXT",
        "LONGBLOB",
        "LONGTEXT"
    ],

    "type_list" => [
        "ENUM",
        "SET"
    ]

];

$array_type_form = [
    "TINYINT" => "input",
    "SMALLINT" => "input",
    "MEDIUMINT" => "input",
    "INT" => "input",
    "INTEGER" => "input",
    "BIGINT" => "input",
    "FLOAT" => "input",
    "DOUBLE" => "input",
    "REAL" => "input",
    "DECIMAL" => "input",
    "DATE" => "input",
    "DATETIME" => "input",
    "TIMESTAMP" => "input",
    "TIME" => "input",
    "YEAR" => "input",
    "CHAR" => "input",
    "BIT" => "radio",
    "BOOL" => "radio",
    "VARCHAR" => "input",
    "TINYBLOB " => "input",
    "TINYTEXT" => "input",
    "BLOB" => "textarea",
    "TEXT" => "textarea",
    "MEDIUMBLOB" => "textarea",
    "MEDIUMTEXT" => "textarea",
    "LONGBLOB" => "textarea",
    "LONGTEXT" => "textarea",
    "ENUM" => "select",
    "SET" => "select_multi",
];

$replaceType = [
    "BIGINTEGER" => "BIGINT",
    "INTEGER" => "INT",
    "DOUBLE PRECISION" => "DOUBLE",
    "NUMERIC" => "DECIMAL"
];
$array_type_defs = getDefsTypes();

function traitementType(
    $type,
    $array_types,
    $replaceType,
    $array_type_form,
    $array_type_defs
) {
    $a = [];
    // $type = strtoupper($type);
    $a['type_original'] = $type;
    $c = "(";
    if (strpos($type, $c) !== false) {
        $t = explode($c, $type);
        if (in_array(strtoupper($t[0]), $array_types["type_int"])) {
            $a["type_famille"] = "int";
        } else if (in_array(strtoupper($t[0]), $array_types["type_decimal"])) {
            $a["type_famille"] = "decimal";
        } else if (in_array(strtoupper($t[0]), $array_types["type_date"])) {
            $a["type_famille"] = "date";
        } else if (in_array(strtoupper($t[0]), $array_types["type_bool"])) {
            $a["type_famille"] = "bool";
        } else if (in_array(strtoupper($t[0]), $array_types["type_text"])) {
            $a["type_famille"] = "text";
        } else if (in_array(strtoupper($t[0]), $array_types["type_list"])) {
            $a["type_famille"] = "list";
        } else {
            $a["type_famille"] = "UNKNOWN";
        }

        $a["type"] = in_array(strtoupper($t[0]), array_keys($replaceType)) ? $replaceType[strtoupper($t[0])] : strtoupper($t[0]);

        $prec = [];
        $ar_prec = str_split($t[1]);
        $s = "";
        $cc = ")";
        foreach ($ar_prec as $v) {

            if ($v != $cc) {
                if ($v != "," && $v != "'")
                    $s .= $v;
                else if ($v == ",") {
                    array_push($prec, trim($s));
                    $s = "";
                }
            }
        }
        $prec[] = $s != "" ? $s : "";
        $a["complement"] = $prec;
    } else {
        foreach ($array_types as $k => $ar) {
            if ($type == $k) {
                $a["type_famille"] = explode("_", $k)[1];
                break;
            }
        }
        $type_temp = in_array(strtoupper($type), array_keys($replaceType)) ? $replaceType[strtoupper($type)] : strtoupper($type);
        $a["type"] = strpos($type_temp, " ") ? explode(" ", $type_temp)[0] : $type_temp;
        $a["complement"] = [];
        $a["test"] = [];
    }

    $a['formulaire'] = $array_type_form[$a["type"]];
    $a['type_def'] = $array_type_defs[$a["type"]];
    return $a;
}


function checkForm($conn, $table, $field, $val, $v, $ar_type, $array_types) {
    $errors_field = [];
    $ar_val = [];
    $type_param = $ar_type["type"];
    if ($type_param == "BOOL") {
        $pdo_param = PDO::PARAM_BOOL;
    } elseif (in_array($type_param, $array_types["type_int"])) {
        $pdo_param = PDO::PARAM_INT;
    } else {
        $pdo_param = PDO::PARAM_STR;
    }
    $null = $v->Null == "NO" ? "Not Nullable" : "Nullable";
    $default = $v->Default;
    $key = "";
    if ($v->Key != "")
        $key = $v->Key == "PRI" ? "PRIMARY KEY" : ($v->Key == "UNI" ? "UNIQUE INDEX" : "MULTIPLE INDEX");
    $extra = $v->Extra;

    $testTimes = false;
    if (strtolower($extra) == "on update current_timestamp()") {
        $testTimes = true;
    }

    if (!$testTimes) {
        if ($val != "") {
            if ($key == "UNIQUE INDEX") {
                // recup de toutes les valeurs car val uniques 
                $sel = $conn->prepare("SELECT " . $field . " FROM " . $table);
                $sel->execute();
                $res = $sel->fetchAll(PDO::FETCH_COLUMN);

                if (!in_array($v, $res)) {
                    $ar_val = [$val, $pdo_param];
                } else {
                    array_push($errors_field, "Cette valeur existe déjà pour ce champ (index unique)");
                }
            } else {
                $ar_val = [$val, $pdo_param];
            }
        } else {
            if ($extra != "on update current_timestamp()") {
                if ($default == "") {
                    if ($null == "Not Nullable" && $extra != "auto_increment") {
                        array_push($errors_field, "La valeur ne peut pas être NULL + Absence de valeur par défaut");
                    }
                } else {
                    $ar_val = [$default, $pdo_param];
                }
            }
        }
    }
    return [$errors_field, $ar_val];
}



$html_info = "";
$html = "";

if (isset($_POST['status']) && $_POST['status'] != '') {
    $status = $_POST['status'];

    $table = "";
    $tableId = "";
    if (isset($_POST['tabId'])){
        $tableId = $_POST['tabId'];
        $table = $_SESSION[$tableId]['pagi_table'];
        $cols_selected = $_SESSION[$tableId]['cols_selected'];
        $cols = $_SESSION[$tableId]['cols'];
        $nb_between = $_SESSION[$tableId]['nb_between'];
        $array_select_limit = $_SESSION[$tableId]['array_select_limit'];
        $formats = $_SESSION[$tableId]['formats'];
        $table_permissions = $_SESSION[$tableId]['table_permissions'];
        $displayPagination = $_SESSION[$tableId]['displayPagination'];
        $primary_key = $_SESSION[$tableId]["pagi_primaryKey"];
    }


    // lister les champs de la table avec leur type
    $sel_cols = $conn->prepare("SHOW FIELDS FROM " . $table);
    $sel_cols->execute();
    $res_cols_db = $sel_cols->fetchAll(PDO::FETCH_OBJ);

    /*
        -----------------------------------------------------
            DISPLAY FORM
        -----------------------------------------------------
    */
    if ($status == "edit_row" || $status == "add_row") {

        $html = "";

        $html .= "<div id=\"topRow\" class=\"pt-3\"></div>";
        $html .= "<div id=\"containerNotifErrorsForm\" style=\"display: none;\">";
        $html .= "<div class=\"alert alert-warning alert-dismissible mx-auto fade show px-5\" role=\"alert\" style=\"width: 85%\">";
        $html .= "<div id=\"contentNotifErrorsForm\"></div>";
        $html .= "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">";
        $html .= "<span aria-hidden=\"true\">&times;</span>";
        $html .= "</button>";
        $html .= "</div>";
        $html .= "</div>";

        if ($status == "edit_row") {
            $id = (int)$_POST['row_id'];
            // Data de la row à éditer
            $sel_row = $conn->prepare("SELECT * FROM " . $table . " WHERE " . $primary_key . "=:" . $primary_key);
            $sel_row->bindValue(':' . $primary_key, $id, PDO::PARAM_INT);
            $sel_row->execute();
            $res_row = $sel_row->fetch(PDO::FETCH_OBJ);
        }

        foreach ($res_cols_db as $i => $v) {
            $html_info = "";

            // INFO COLUMN
            $field = $v->Field;
            $type = $v->Type;
            $ar_type = traitementType($type, $array_types, $replaceType, $array_type_form, $array_type_defs);
            $type_form = $ar_type['formulaire'];
            $null = $v->Null == "NO" ? "Not Nullable" : "Nullable";
            $default = $v->Default;
            $key = "";
            if ($v->Key != "")
                $key = $v->Key == "PRI" ? "PRIMARY KEY" : ($v->Key == "UNI" ? "UNIQUE INDEX" : "MULTIPLE INDEX");
            $extra = $v->Extra;


            /*
                --------------------------------------------------------------
                    HTML TOOLTIPS DEF OF TYPE
                --------------------------------------------------------------
            */
            $soloTooltip = "<span id=\"tooltip_" . $ar_type["type"] . "\" class=\"tooltip-info\">"
                . "<a class=\"infobox\">"
                . "<span class=\"far fa-question-circle color-ico ml-2 tip\" "
                . "data-container=\"#tooltip_" . $ar_type["type"] . "\" "
                . "data-tip=\"tip-type-" . strtolower($ar_type["type"]) . "\">"
                . "</span>"
                . "</a>"
                . "</span>";

            $newLineTemp = [$default, $key, $extra];
            $newLine = [];
            foreach ($newLineTemp as $t) {
                if ($t != "") {
                    array_push($newLine, $t);
                }
            }


            /*
                --------------------------------------------------------------
                    HTML INFO FOR EACH FIELD
                --------------------------------------------------------------
            */
            $html_info .= "<div class=\"mt-1 mb-2 ml-2 text-secondary\" style=\"font-size: 90%;\">";
            $html_info .= "<ul class=\"list-group list-group-horizontal pl-3\">";
            if ($type_form != "select" && $type_form != "select_multi")
                $html_info .= "<li class=\"flex-fill w-50\">TYPE: " . $ar_type["type_original"]
                    . $soloTooltip
                    . "</li>";
            else
                $html_info .= "<li class=\"flex-fill w-50\">TYPE: " . $ar_type["type"]
                    . $soloTooltip
                    . "</li>";

            $html_info .= "<li class=\"flex-fill w-50\">" . $null . "</li>";
            $html_info .= "</ul>";

            if (count($newLine) != 0) {
                $html_info .= "<ul class=\"list-group list-group-horizontal pl-3\">";
                if ($default != "")
                    $html_info .= "<li class=\"flex-fill w-50\">DEFAULT VALUE: " . $default . "</li>";
                if ($key != "")
                    $html_info .= "<li class=\"flex-fill w-50\">" . $key . "</li>";

                if (count($newLine) == 3) {
                    $html_info .= "</ul>";
                    $html_info .= "<ul class=\"list-group list-group-horizontal pl-3\">";
                }

                if ($extra != "")
                    $html_info .= "<li class=\"flex-fill w-50\">" . $extra . "</li>";

                $html_info .= "</ul>";
            }
            $html_info .= "</div>";


            /*
                --------------------------------------------------------------
                    HTML GLOBAL GENERE
                --------------------------------------------------------------
            */
            $value = "";
            if ($status == "edit_row") {
                $value = $res_row->$field;
            } else {
                if ($default != "") {
                    $value = $default;
                } elseif ($extra == "auto_increment") {
                    $sel_val = $conn->prepare("select max(" . $field . ") from " . $table);
                    $sel_val->execute();
                    $res_val = $sel_val->fetch(PDO::FETCH_COLUMN);
                    if ($res_val) {
                        $value = strval($res_val) + 1;
                    }
                }
            }

            $html .= "<form id=\"formRow\" class=\"mx-auto\" style=\"width: 80%;\" method=\"POST\">";
            $html .= "<div class=\"form-group mb-4\">";
            $html .= "<i class=\"fas fa-caret-right ml-2 color-puce-field\"></i>";
            $html .= "<label class=\"ml-2 text-info font-weight-bold fz-110\">" . $field . "</label>";

            if ($type_form == "input") {
                $primaryTest = "";
                $fieldTimes = "";
                if ($status == "edit_row") {
                    $primaryTest = $field == $primary_key ? " disabled" : "";
                    if (strtolower($extra) == "on update current_timestamp()") {
                        $fieldTimes = " disabled";
                    }
                // } else {
                //     if (strtolower($default) == "current_timestamp()" && $null == "Not Nullable") {
                //         $fieldTimes = " disabled";
                //     }
                }

                if ($primaryTest == "" && $fieldTimes == "") {
                    $html .= "<div class=\"input-group\">";
                    $html .= "<input type=\"text\" id=\"" . $field . "\" "
                        . "class=\"form-control form-control-close mb-0 py-2 border-right-0 border\" "
                        . "value=\"" . $value . "\">";
                    $html .= "<span class=\"input-group-append\">";
                    $html .= "<div class=\"input-group-text bg-white\" onclick=\"$('#" . $field . "').val(null).trigger('change');\">"
                        . "<i class=\"fa fa-times btn-input-close\"></i></div>";
                    $html .= "</span>";
                    $html .= "</div>";
                } else {
                    $html .= "<div class=\"input-group\">";
                    $html .= "<input type=\"text\" id=\"" . $field . "\" class=\"form-control form-control-not-close mb-0 py-2\" "
                        . "value=\"" . $value . "\" " . $primaryTest . "" . $fieldTimes . ">";
                    $html .= "</div>";
                }
            } else if ($type_form == "textarea") {
                $html .= "<textarea type=\"text mb-2\" id=\"" . $field . "\">";
                $html .= $value;
                $html .= "</textearea>";

            } else if ($type_form == "radio") {
                $selected = ($value == true || $value == 1) ? "1" : "";

                $html .= "<select id=\"" . $field . "\" class=\"form-control js-choice\">";
                $selected = ($value == true || $value == 1) ? " selected=\"selected\"" : "";
                foreach ($ar_type["complement"] as $i => $p) {
                    $html .= "<option value=\"" . $p . "\"" . $selected . ">" . $p . "</option>";
                }
                $html .= "</select>";

            } else if (strpos($type_form, "select") == 0) {
                $multiple = $type_form == "select_multi" ? " multiple" : "";
                $multipleChoices = $type_form == "select_multi" ? "select-multiple" : "select-one";
                $html .= "<select" . $multiple . " id=\"" . $field . "\" class=\"form-control js-choice\">";
                foreach ($ar_type["complement"] as $i => $p) {
                    $selected = (strtoupper($value) == $p) ? " selected=\"selected\"" : "";
                    $html .= "<option id=\"" . $field . "_" . $i . "\" value=\"" . $p . "\"" . $selected . ">" . $p . "</option>";
                }
                $html .= "</select>";
            }
            $html .= $html_info;
            $html .= "</div>";
        }

        $html .= "<hr class=\"hr-edit-row-bottom\">";
        $html .= "<div class=\"row py-4 footer-edit-row justify-content-between\">";
        $html .= "<button id=\"btn-edit-row-cancel\" type=\"button\" class=\"btn btn-danger btn-shadow modif-ref-btn ml-4\" data-dismiss=\"modal\" aria-label=\"Close\"><i class=\"far fa-times-circle mr-2\"></i>Annuler</button>";
        $html .= "<button id=\"btn-edit-row-valid\" type=\"submit\" class=\"btn btn-success btn-shadow modif-ref-btn mr-4\"><i class=\"fas fa-check mr-2\"></i>Confirmer</button>";
        $tooltips = displayTooltips($array_type_defs);
        $html .= $tooltips;
        $html .= "</form>";


        $result = array();
        $result["html"] = $html;
        echo json_encode($result);

    /*
        -----------------------------------------------------
            SAVE DATABASE
        -----------------------------------------------------
    */
    } else if ($status == "edit_row_changes" || $status == "add_row_save" || $status="edit_row_permission") {

        $errors = [];
        if ($status != "edit_row_permission"){
            // verif des contraintes
            $ar_values = []; // [field][val, pdo_param]
            foreach ($_POST as $k => $val) {
                foreach ($res_cols_db as $i => $v) {
                    if ($v->Field == $k) {
                        $field = $v->Field;
                        if (($status == "edit_row_changes" && $field != $primary_key) || $status == "add_row_save") {
                            $type = $v->Type;
                            $ar_type = traitementType($type, $array_types, $replaceType, $array_type_form, $array_type_defs);
                            $arrays = checkForm($conn, $table, $field, $val, $v, $ar_type, $array_types);
                            $testVal = testVal($table, $field, $val, $ar_type['type'], $conn);
    
                            // $errors_field = $arrays[0];
                            // $errors_val = $testVal[0];
    
                            $ar_errors = [];
                            if (count($arrays[0]) != 0){
                                $ar_errors = $arrays[0];
                            } else if (count($testVal[0]) != 0){
                                $ar_errors = $testVal[0];
                            }
    
                            if (!empty($ar_errors)) {
                                $errors[$field] = [];
                                foreach ($ar_errors as $e) {
                                    array_push($errors[$field], $e);
                                }
                            } else {
                                if (!empty($arrays[1]) && !$testVal[1]) {
                                    $ar_values[$field] = $arrays[1];
                                }
                            }
                        }
                    }
                }
            }
    
            $result = array();
            if (!empty($errors)) {
                $res_status = "error";
                $result['errors'] = $errors;
            } else {
    
                // SAVE EDIT ROW
                if ($status == "edit_row_changes") {
                    $id = (int)$_POST[$primary_key];
    
                    $req = "UPDATE " . $table . " SET ";
                    foreach (array_keys($ar_values) as $i => $f) {
                        if ($i == 0) {
                            $req .= $f . " = :" . $f;
                        } else {
                            $req .= ", " . $f . " = :" . $f;
                        }
                    }
                    $req .= " WHERE " . $primary_key . " = :" . $primary_key;
    
                    $up = $conn->prepare($req);
                    $up->bindValue(":" . $primary_key, $id, PDO::PARAM_INT);
                    foreach ($ar_values as $f => $ar) {
                        $up->bindValue(':' . $f, $ar[0], $ar[1]);
                    }
                    $up->execute();
    
                    $res_status = 'success';
                }
    
                // SAVE ADD ROW
                if ($status == "add_row_save") {
    
                    // test si timestamp
                    foreach ($ar_values as $f => $ar) {
                        if ($ar[0] == "current_timestamp()") {
                            $ar_values[$f] = [date("Y-m-d H:i:s"), $ar[1]];
                        }
                    }
    
                    $req = "INSERT INTO " . $table . " (";
                    $req .= implode(", ", array_keys($ar_values)) . ") VALUES (:";
                    $req .= implode(", :", array_keys($ar_values)) . ")";
    
                    $add = $conn->prepare($req);
                    foreach ($ar_values as $f => $ar) {
                        $add->bindValue(':' . $f, $ar[0], $ar[1]);
                    }
                    $add->execute();
    
                    $res_status = "success";
                }
            }



        /*
            -----------------------------------------------------
                TABLE PERMISSION - VALID UPDATE
            -----------------------------------------------------
        */
        } else {

            $fields_exclus = ["status", "tabId", "row_id"];
            if (!empty($table_permissions["tr_special"])){
                if($table_permissions["tr_special"]["condition"]["disabled"]){
                    array_push($fields_exclus, $table_permissions["tr_special"]["condition"]["field"]);
                }
            }

            $id = $_POST["row_id"];
            // requête pour vérifier si up ou non
            $req_test = "SELECT * FROM ".$table. " WHERE ". $primary_key ." = :". $primary_key;
            $sel_test = $conn->prepare($req_test);
            $sel_test->bindValue(':'.$primary_key, $id, PDO::PARAM_INT);
            $sel_test->execute();

            $res_test = $sel_test->fetch(PDO::FETCH_OBJ);

            $fields_val = [];
            $fields_val_req = [];
            foreach($_POST as $k=>$v){
                if (!in_array($k, $fields_exclus)){
                    $fields_val[$k] = $v;
                    $r = $k ." = :". $k;
                    array_push($fields_val_req, $r);
                }
            }
            $all_fields = array_keys($fields_val);

            $testUpdate = false;
            foreach ($all_fields as $f){
                $val_bdd = ($res_test->$f == 1 || $res_test->$f == true) ? "1" : "0";
                if ($val_bdd != $fields_val[$f]){
                    $testUpdate = true;
                    break;
                }
            }

            if (!$testUpdate){
                $res_status = "no_update";
            } else {
                $req = "";
                $req .= "UPDATE ".$table." SET ";
                $req .= implode(", ", $fields_val_req);
                $req .= " WHERE ". $primary_key ." = :". $primary_key; 
    
                $up = $conn->prepare($req);
                foreach($fields_val as $k=>$v){
                    $up->bindValue(':'.$k, (int)$v, PDO::PARAM_INT);
                }
                $up->bindValue(':'.$primary_key, $id, PDO::PARAM_INT);
                $up->execute();
    
                $res_status = "success";
            }

        }

        $limit = $_SESSION[$tableId]['pagi_limit'];
        $page = $_SESSION[$tableId]['pagi_page'];
        $order_column = $_SESSION[$tableId]['pagi_column'];
        $order_sort = $_SESSION[$tableId]['pagi_sort'];
        $search = "";
        $searchTest = false;
        if (isset($_POST['search']) && $_POST['search'] != "") {
            $search = $_POST['search'];
            $searchTest = true;
        }

        include '../includes/preparationData.php';
        $result["nbTotal"] = $nb_total;
        $pagiHtml = pagi($pagiTest, $page, $nb_total, $limit, $order_column, $order_sort, $nb_between);
        $tableHtml = table($columns, $order_column, $add_class, $limit, $page, $asc_or_desc, $columns_display, $up_or_down, $data, $primary_key, $formats, $searchTest, $ar_s, $table_permissions);

        $result['status'] = $res_status;
        $result['errors'] = $errors;
        $result["pagi"] = $pagiHtml;
        $result["table"] = $tableHtml;

        echo json_encode($result);
    }
}
