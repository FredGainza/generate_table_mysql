<?php 

function traitementColors($ar_colors, $element, $s, $i){
    $ar = [];
    $element_new = $element;
    $element_test = strtolower($element);
    $s_test = strtolower($s);
    $len = 0;
    if (strpos($element_test, $s_test) !== false) {
        $start = strpos($element_test, $s_test)+$len;
        $len = strlen($s_test);
        $s_to_test = substr($element, $start, $len);

        $s_replace = '<mark class="bg-mark-'.$ar_colors[$i].'">' . $s_to_test . '</mark>';
        $element_new = str_replace($s_to_test, $s_replace, $element_new);
        $ar = [$s, $element_new, $s_replace];
    }
    return $ar;
}

function mark_search($element, $ar_ss) {
    $ar_colors = ["green", "orange", "blue", "cadetBlue", "LawnGreen", "#ff8c00", "#00bfff"];
    $element_new = "";
    $ar_s = [];
    foreach($ar_ss as $s){
        if ($s != ""){
            array_push($ar_s, $s);
        }
    }

    $res = [];
    foreach($ar_s as $i=>$s){
        if (is_array($s)){
            foreach ($s as $ss){
                $ar_add = traitementColors($ar_colors, $element, $ss, $i);
                if (!empty($ar_add)){
                    array_push($res, $ar_add);
                }
            }
        } else {
            $ar_add = traitementColors($ar_colors, $element, $s, $i);
            if (!empty($ar_add)){
                array_push($res, $ar_add);
            }
        }

    }
    if (count($res) != 0){
        if (count($res) == 1){
            return $res[0][1];
        } else {
            foreach($res as $i=>$ar){
                $ar_s_test = str_split($ar[1]);
                array_push($res[$i], $ar_s_test);
            }
            $element_new = "";
            $ar_test = str_split($element);
            $i_verif_max = null;
            foreach($ar_test as $i=>$c){

                $pattern_tt = null;
                foreach($res as $j=>$ar){
                    $aa = $ar[3];
                    if (count($aa) >= $i){
                        if ($aa[$i] == "<"){
                            $pattern_tt = $ar[2];
                            $res[$j][1] = substr_replace($ar[1], $ar[0], $i, strlen($ar[2]));
                            $res[$j][3] = str_split($res[$j][1]);
                            if (strlen($ar[0]) > 1){
                                $i_verif_max = $i + strlen($ar[0]) - 1;
                            } else {
                                $i_verif_max = null;
                            }
                        }
                    }
                }

                if ($pattern_tt != null){
                    $element_new .= $pattern_tt;
                    $pattern_tt = null;
                } else {
                    if ($i_verif_max == null || $i > $i_verif_max){
                        $element_new .= $c;
                    }
                }
            }
            return $element_new;
        }
    } else {
        return $element;
    }
}

function pagi($pagiTest, $page, $nb_total, $limit, $order_column, $order_sort, $nb_between) {
    $pagi = "";
    if ($pagiTest) {
        $pagi .= "<nav>";
        $pagi .= "<ul class=\"pagination\">";
        $nb_pages = (int)ceil($nb_total / $limit);
        if ($page > 0) {
            $precedent = $page;
            $pagi .= "<li class=\"page-item\">";
            $pagi .= "<a class=\"page-link\" href=\"?limit=" . $limit . "&page=" . $precedent . "&column=" . $order_column . "&sort=" . $order_sort . "\" aria-label=\"Previous\">";
            $pagi .= "<span aria-hidden=\"true\">&laquo;</span>";
            $pagi .= "<span class=\"sr-only\">Previous</span>";
            $pagi .= "</a>";
            $pagi .= "</li>";
        }
        $i = 0; // page_index
        $j = 1; // page
        $testTooPages = $nb_pages - (2 * ($nb_between - 1)) < 1 ? false : true;
        $classLast = $i == $nb_pages-1 ? " last-page" : "";

        if ($nb_total > $limit) {
            while ($i < ($nb_pages)) {
                if ($i != $page && abs($page - $i) < $nb_between) {
                    $pagi .= "<li class=\"page-item\">";
                    $pagi .= "<a class=\"page-link".$classLast."\" href=\"?limit=" . $limit . "&page=" . $j . "&column=" . $order_column . "&sort=" . $order_sort . "\">" . $j . "</a>";
                    $pagi .= "</li>";
                }
                if (abs($page - $i) >= $nb_between) {
                    if ($page - $i >= $nb_between) {
                        if ($page - $i - 1 < $nb_between) {
                            if ($page != 0) {
                                $pagi .= "<li class=\"page-item\">";
                                $pagi .= "<a class=\"page-link".$classLast."\" href=\"?limit=" . $limit . "&page=1&column=" . $order_column . "&sort=" . $order_sort . "\">";
                                $pagi .= "1";
                                $pagi .= "</a>";
                                $pagi .= "</li>";
                            }
                            if ($testTooPages && $page != $nb_between) {
                                $pagi .= "<li class=\"page-item disabled\">";
                                $pagi .= "<a class=\"page-link\" href=\"#\" tabindex=\"-1\">&hellip;</a>";
                                $pagi .= "</li>";
                            }
                        }
                    }
                }

                if ($i == $page) {
                    $pagi .= "<li class=\"page-item active\">";
                    $pagi .= "<a class=\"page-link".$classLast."\" href=\"?limit=" . $limit . "&page=" . $j . "&column=" . $order_column . "&sort=" . $order_sort . "\">";
                    $pagi .= "<b>" . $j . "</b>";
                    $pagi .= "</a>";
                    $pagi .= "</li>";
                }
                if (abs($page - $i) >= $nb_between) {
                    if ($i - $page >= $nb_between) {
                        if ($i - $page - 1 < $nb_between) {

                            if ($testTooPages && $page != $nb_pages - $nb_between - 1) {
                                $pagi .= "<li class=\"page-item disabled\">";
                                $pagi .= "<a class=\"page-link".$classLast."\" href=\"#\" tabindex=\"-1\">&hellip;</a>";
                                $pagi .= "</li>";
                            }
                            if ($page != $nb_pages - 1) {
                                $pagi .= "<li class=\"page-item\">";
                                $pagi .= "<a class=\"page-link".$classLast."\" href=\"?limit=" . $limit . "&page=" . $nb_pages . "&column=" . $order_column . "&sort=" . $order_sort . "\">";
                                $pagi .= $nb_pages;
                                $pagi .= "</a>";
                                $pagi .= "</li>";
                            }
                        }
                    }
                }
                $i++;
                $j++;
            }

            if ($page < ($nb_pages - 1)) {
                $next = $page + 2;
                $pagi .= "<li class=\"page-item\">";
                $pagi .= "<a class=\"page-link next\" href=\"?limit=" . $limit . "&page=" . $next . "&column=" . $order_column . "&sort=" . $order_sort . "\" aria-label=\"Next\">";
                $pagi .= "<span aria-hidden=\"true\">&raquo;</span>";
                $pagi .= "<span class=\"sr-only\">Next</span>";
                $pagi .= "</a>";
                $pagi .= "</li>";
            }
        }
        $pagi .= "</ul>";
        $pagi .= "</nav>";
    }
    return $pagi;
}

function table($columns, $order_column, $add_class, $limit, $page, $asc_or_desc, $columns_display, $up_or_down, $data, $primary_key, $formats, $searchTest, $ar_s, $table_permissions) {
    $table = "";
    $table .= "<thead>";
    $table .= "<tr>";
    for ($i = 0; $i < count($columns); $i++) {
        $col = $columns[$i];
        $table .= "<th scope=\"col\"" . ($col == $order_column ? $add_class : "") . ">";
        $table .= "<a class=\"change-order\" href=\"?limit=" . $limit . "&page=" . ($page + 1) . "&column=" . $col . "&sort=" . $asc_or_desc . "\">";
        $table .= $columns_display[$i];
        $table .= "<i class=\"fas fa-sort" . ($col == $order_column ? "-" . $up_or_down . " color-darky ml-2" : " text-warning ml-2") . "\"></i>";
        $table .= "</a>";
        $table .= "</th>";
    }
    if ($table_permissions == NULL){
        $table .= "<th scope=\"col\"></th>";
    }
    $table .= "<th scope=\"col\"></th>";
    $table .= "</tr>";
    $table .= "</thead>";

    $table .= "<tbody>";


    // TABLE PERMISSIONS
    if ($table_permissions != NULL){
        $toggleFields = $table_permissions["toggleFields"];
        $class_special = $table_permissions["tr_special"]["class_special"];
        $conditionSpecial = $table_permissions["tr_special"]["condition"];
        $fieldSpecial = $conditionSpecial["field"];
        $valSpecial = $conditionSpecial["value"];
        $disabledSpecial = $conditionSpecial["disabled"];
    }


    foreach ($data as $i => $row) {

        $spe = '';
        $disabledSpe = false;
        if($table_permissions != NULL){
            if (!empty($conditionSpecial)){
                if ($row->$fieldSpecial == $valSpecial){
                    $spe = ' class="'.$class_special.'"';
                    $disabledSpe = $disabledSpecial == true ? true : false;
                } else {
                    $spe = '';
                    $disabledSpe = false;
                }
            }
        }

        $row_nb = "id_" . $row->$primary_key;
        $table .= "<tr id=\"" . $row_nb . "\"" . $spe . ">";

        $fields_new_formats = [];
        $dic_formats = [];
        if ($formats != NULL) {
            $fields_new_formats = array_keys($formats);
            foreach ($fields_new_formats as $f) {
                $dic_formats[$f] = [
                    $formats[$f][0][0], $formats[$f][0][1]
                ];
            }
        }

        foreach ($columns as $col) {
            if ($table_permissions != NULL && in_array($col, $toggleFields)){
                $disField = $disabledSpecial ? ($col == $fieldSpecial ? " disabled" : "") : "";
                $classDisabled = ($spe == '' && $disabledSpecial) ? ($col == $fieldSpecial ? " bg-disabled-check" : "") : "";
                $disabled = $disField == "" ? ($disabledSpe ? " disabled" : "") : "";
                $table .= "<td id=\"".$row_nb."_".$col."\" class=\"text-center".$classDisabled."\">";
                $table .= "<label class=\"custom-control custom-checkbox\">";
                $table .= "<input type=\"checkbox\" ";
                $table .= " id=\"".$row_nb."_".$col."\"".$disField."".$disabled."";
                $table .= " class=\"custom-control-input\"";
                $table .= (($row->$col == 1 || $row->$col == true) ? " checked" : "").">";
                $table .= "<span class=\"custom-control-indicator\"></span>";
                $table .= "</label>";
                $table .= "</td>";

            } else {
                $vv = "";
                if (in_array($col, $fields_new_formats)) {
                    foreach ($dic_formats[$col] as $ar) {
                        if ($ar[0] == $row->$col){
                            $vv = !$searchTest ? $ar[1] : mark_search($ar[1], $ar_s);
                            break;
                        }
                    }
                } else {
                    $vv = !$searchTest ? $row->$col : mark_search($row->$col, $ar_s);
                }
                $table .= "<td id=\"" . $row_nb . "_" . $col . "\" class=\"pl-2\">" . $vv . "</td>";
            }
        }

        if ($table_permissions == NULL){
            $table .= "<td id=\"" . $row_nb . "_delete_row\" class=\"text-center\">";
            $table .= "<button id=\"" . $row_nb . "_delete_row_btn\"";
            $table .= " class=\"btn btn-danger btn-sm rounded-1 delete_row\"";
            $table .= " type=\"button\" ";
            $table .= " data-toggle=\"tooltip\"";
            $table .= " data-placement=\"top\"";
            $table .= " title=\"Supprimer\">";
            $table .= "<i class=\"fa fa-trash\"></i></button>";
            $table .= "</td>";
        }

        $class_edit = $table_permissions != NULL ? "edit-table-permission" : "edit_row";
        if (!$disabledSpe){
            $color_ico = $table_permissions != NULL ? "success" : "info";
            $table .= "<td id=\"" . $row_nb . "_edit_row\" class=\"text-center\">";
            $table .= "<button id=\"" . $row_nb . "_edit_row_btn\" ";
            $table .= " class=\"btn btn-".$color_ico." btn-sm rounded-1 ".$class_edit."\"";
            $table .= " type=\"button\" ";
            $table .= " data-toggle=\"tooltip\"";
            $table .= " data-placement=\"top\"";
            $table .= " title=\"".($table_permissions != NULL ? "Valider" : "Editer")."\">";
            $table .= "<i class=\"".($table_permissions != NULL ? "fas fa-check" : "fa fa-pencil-alt")."\"></i></button>";
            $table .= "</td>";
        } else {
            $table .= "<td style=\"height:41.6px;\"></td>";
        }
        // $table .= "</tr>";

        $table .= "</tr>";
    }
    $table .= "</tbody>";

    return $table;
}


function getDefsTypes(){
    $array = [
        "TINYINT" => [
            "TINYINT [M] [UNSIGNED]",
            "Occupe 1 octet.",
            "Ce type peut stocker des nombres entiers de -128 à 127 si il ne porte pas l'attribut UNSIGNED, dans le cas contraire il peut stocker des entiers de 0 à 255."
        ],
        "SMALLINT" => [
            "SMALLINT [M] [UNSIGNED]",
            "Occupe 2 octets.",
            "Ce type de données peut stocker des nombres entiers de -32 768 à 32 767 si il ne porte pas l'attribut UNSIGNED, dans le cas contraire il peut stocker des entiers de 0 à 65 535."
        ],
        "MEDIUMINT" => [
            "MEDIUMINT [M] [UNSIGNED]",
            "Occupe 3 octets.",
            "Ce type de données peut stocker des nombres entiers de -8 388 608 à 8 388 607 si il ne porte porte pas l'attribut UNSIGNED, dans le cas contraire il peut stocker des entiers de 0 à 16 777 215."
        ],
        "INT" => [
            "INT [M] [UNSIGNED]",
            "Occupe 4 octets.",
            "Ce type de données peut stocker des nombres entiers de -2 147 483 648 à 2 147 483 647 si il ne porte pas l'attribut UNSIGNED, dans le cas contraire il peut stocker des entiers de 0 à 4 294 967 295."
        ],
        "BIGINT" => [
            "BIGINT [M] [UNSIGNED]",
            "Occupe 8 octets.",
            "Ce type de données stocke les nombres entiers allant de -9 223 372 036 854 775 808 à 9 223 372 036 854 775 807 sans l'attribut UNSIGNED, et de 0 à 18 446 744 073 709 551 615 avec."
        ],
        "FLOAT" => [
            "FLOAT[(M,D)] [UNSIGNED]",
            "Occupe 4 octets.",
            "M est le nombre de chiffres et D est le nombre de décimales.",
            "Ce type de données permet de stocker des nombres flottants à précision simple.",
            "Va de -1.175494351E-38 à 3.402823466E+38. Si UNSIGNED est activé, les nombres négatifs sont retirés mais ne permettent pas d'avoir des nombres positifs plus grands."
        ],
        "DOUBLE" => [
            "DOUBLE [(M,D)]",
            "Occupe 8 octets.",
            "Stocke des nombres flottants à double précision de -1.7976931348623157E+308 à -2.2250738585072014E-308, 0, et de 2.2250738585072014E-308 à 1.7976931348623157E+308.",
            "Si UNSIGNED est activé, les nombres négatifs sont retirés mais ne permettent pas d'avoir des nombres positifs plus grands."
        ],
        "REAL" => [
            "REAL[(M,D)]",
            "Occupe 8 octets.",
            "Stocke des nombres flottants à double précision de -1.7976931348623157E+308 à -2.2250738585072014E-308, 0, et de 2.2250738585072014E-308 à 1.7976931348623157E+308.",
            "Si UNSIGNED est activé, les nombres négatifs sont retirés mais ne permettent pas d'avoir des nombres positifs plus grands."
        ],
        "DECIMAL" => [
            "DECIMAL[(M[,D])]",
            "Occupe M+2 octets si D > 0, M+1 octets si D = 0.",
            "Contient des nombres flottants stockés comme des chaînes de caractères."
        ],
        "DATE" => [
            "DATE",
            "Occupe 3 octets.",
            "Stocke une date au format 'AAAA-MM-JJ' allant de '1000-01-01' à '9999-12-31'."
        ],
        "DATETIME" => [
            "DATETIME",
            "Occupe 8 octets.",
            "Stocke une date et une heure au format 'AAAA-MM-JJ HH:MM:SS' allant de '1000-01-01 00:00:00' à '9999-12-31 23:59:59'."
        ],
        "TIMESTAMP" => [
            "TIMESTAMP [M]",
            "Occupe 4 octets.",
            "Stocke une date sous forme numérique allant de '1970-01-01 00:00:00' à l'année 2037. ",
            "L'affichage dépend des valeurs de M : AAAAMMJJHHMMSS, AAMMJJHHMMSS, AAAAMMJJ, ou AAMMJJ pour M égal respectivement à 14, 12, 8, et 6."
        ],
        "TIME" => [
            "TIME",
            "Occupe 3 octets.",
            "Stocke l'heure au format 'HH:MM:SS', allant de '-838:59:59' à '838:59:59'."
        ],
        "YEAR" => [
            "YEAR",
            "Occupe 1 octet.",
            "Année à 2 ou 4 chiffres allant de 1901 à 2155 ( 4 chiffres) et de 1970-2069 (2 chiffres)."
        ],
        "CHAR" => [
            "[NATIONAL] CHAR(M) [BINARY]",
            "Occupe M octets, M allant jusqu'à 255.",
            "Chaîne de 255 caractères maximum remplie d'espaces à la fin.",
            "L'option BINARY est utilisée pour tenir compte de la casse."
        ],
        "BIT" => [
            "BIT",
            "Occupe 1 octet. Même chose que CHAR(1).",
            "Peut être Null.",
            "Valeur binaire."
        ],
        "BOOL" => [
            "BOOL",
            "Occupe 1 octet.",
            "Ne peut pas être Null.",
            "1 pour True, 0 pour False."
        ],
        "VARCHAR" => [
            "VARCHAR (M) [BINARY]",
            "Occupe L+1 octets (ou L représente la longueur de la chaîne).",
            "Ce type de données stocke des chaînes de 255 caractères maximum.",
            "L'option BINARY permet de tenir compte de la casse."
        ],
        "TINYBLOB " => [
            "TINYBLOB (L représente la longueur de la chaîne)",
            "Occupe L+1 octets.",
            "Stocke des chaînes de 255 caractères maximum.",
            "Ce champ est sensible à la casse."
        ],
        "TINYTEXT" => [
            "TINYTEXT",
            "Occupe L+1 octets.",
            "Stocke des chaînes de 255 caractères maximum.",
            "Ce champ est insensible à la casse."
        ],
        "BLOB" => [
            "BLOB",
            "Occupe L+1 octets.",
            "Stocke des Chaînes de 65535 caractères maximum.",
            "Ce champ est sensible à la casse."
        ],
        "TEXT" => [
            "TEXT",
            "Occupe L+2 octets.",
            "Stocke des chaînes de 65535 caractères maximum.",
            "Ce champ est insensible à la casse."
        ],
        "MEDIUMBLOB" => [
            "MEDIUMBLOB",
            "Occupe L+3 octets.",
            "Stocke des chaînes de 16777215 caractères maximum."
        ],
        "MEDIUMTEXT" => [
            "MEDIUMTEXT",
            "Occupe L+3 octets.",
            "Chaîne de 16 777 215 caractères maximum.",
            "Ce champ est insensible à la casse."
        ],
        "LONGBLOB" => [
            "LONGBLOB",
            "Occupe L+4 octets.",
            "Stocke des chaînes de 4 294 967 295 caractères maximum.",
            "Ce champ est sensible à la casse."
        ],
        "LONGTEXT" => [
            "LONGTEXT",
            "Occupe L+4 octets.",
            "Stocke des chaînes de 4 294 967 295 caractères maximum."
        ],
        "ENUM" => [
            "ENUM('valeur_possible1','valeur_possible2','valeur_possible3',...).",
            "Occupe 1 ou 2 octets (la place occupée est fonction du nombre de solutions possibles : 65 535 valeurs maximum."
        ],
        "SET" => [
            "SET('valeur_possible1','valeur_possible2',...)",
            "Occupe 1, 2, 3, 4 ou 8 octets, selon de nombre de solutions possibles (de 0 à 64 valeurs maximum)",
        ],
    ];
    return $array;
}

function precision($type) {
    $c = "(";
    $t = explode($c, $type);
    $prec = [];
    $ar_prec = str_split($t[1]);
    $s = "";
    $cc = ")";
    foreach($ar_prec as $v){
        if ($v != $cc){
            if ($v != "," && $v != "'")
                $s .= $v;
            else if ($v == ",") {
                array_push($prec, trim($s));
                $s = "";
            }
        }
    }
    $prec[] = $s != "" ? $s : "";
    return $prec;
}


function testVal($table, $field, $val, $typePerso, $conn){

    $int = [
        "tinyint",
        "smallint",
        "mediumint",
        "int",
        "integer",
        "bigint",
        "biginteger",
    ];

    $spec = [
        "tinyint" => [
            "signed" => [-128, 127],
            "unsigned" => [0, 255]
        ],
        "smallint" => [
            "signed" => [-32768, 32767],
            "unsigned" => [0, 65535]
        ],
        "int" => [
            "signed" => [-2147483648, 2147483647],
            "unsigned" => [0, 4294967295]
        ],
        "mediumint" => [
            "signed" => [-8388608, 8388607],
            "unsigned" => [0, 16777215]
        ],
        "integer" => [
            "signed" => [-2147483648 , 2147483647],
            "unsigned" => [0, 4294967295]
        ],
        "bigint" => [
            "signed" => [-9223372036854775808, 9223372036854775807],
            "unsigned" => [0, 18446744073709551615]
        ],
        "biginteger" => [
            "signed" => [-9223372036854775808, 9223372036854775807],
            "unsigned" => [0, 18446744073709551615]
        ],
        "char" => 255,
        "varchar" => 255,
        "tinyblob" => 255,
        "tinytext" => 255,
        "blob" => 65535,
        "text" => 65535,
        "mediumblob" => 16777215,
        "mediumtext" => 16777215,
        "longblob" => 4294967295,
        "longtext" => 4294967295,
        "bit" => 64,
    ];

    $decimal = [
        "float",
        "double",
        "double precision",
        "real",
        "decimal",
    ];

    $date = [
        "date",
        "datetime",
        "timestamp",
        "time",
        "year",
    ];

    $text = [
        "char",
        "varchar",
        "tinyblob",
        "tinytext",
        "blob",
        "text",
        "mediumblob",
        "mediumtext",
        "longblob",
        "longtext",
    ];

    $list = [
        "enum",
        "set",
    ];

    $errors = [];
    $delField = false;
    $typePerso = strtolower($typePerso);

    $req = "SHOW COLUMNS FROM ".$table." WHERE Field LIKE '%".$field."%'";
    $sel = $conn->prepare($req);
    $sel->execute();
    $res = $sel->fetch(PDO::FETCH_OBJ);

    $type = $res->Type;
    $null = $res->Null;
    $default = $res->Default;
    $extra = $res->Extra;

    // VALEUR NULL
    if ($val == ""){
        if ($null == "NO"){
            if ($default == NULL){
                if ($extra != "auto_increment"){
                    array_push($errors, "La valeur ne peut pas être NULL");
                } else {
                    $delField = true;
                }
            } else {
                $delField = true;
            }
        }

    // VALEUR NON NULL
    } else {

        // VALEUR ENTIER
        if (in_array($typePerso, $int)){
            if (preg_match("#[0-9]+#", $val)){
                if (strpos($type, 'unsigned') != 0){
                    $sig = 'unsigned';
                } else {
                    $sig = 'signed';
                }
                $min = $spec[$typePerso][$sig][0]-1;
                $max = $spec[$typePerso][$sig][1]+1;

                $val = intval($val);

                if ($val < $min){
                    array_push($errors, "La valeur doit être supérieure à ".$min);
                } elseif ($val > $max) {
                    array_push($errors, "La valeur doit être inférieure ".$max);
                } 

            } else {
                array_push($errors, "La valeur doit être un entier");
            }

        // VALEUR DECIMAL
        } elseif (in_array($typePerso, $decimal)){
            if (is_numeric($val)){
                if (strpos($type, 'unsigned') != 0){
                    if ($val < 0){
                        array_push($errors, "La valeur doit être positive ou nulle");
                    }
                }
            } else {
                array_push($errors, "La valeur doit être une valeur numérique");
            }

        // VALEUR TEXT & BIT
        } elseif (in_array($typePerso, $text) || $typePerso == "bit"){
            $c = "(";
            if (strpos($type, $c) !== false){
                $prec = precision($type);
                if (count($prec) == 1){
                    $max = $prec[0]+1;
                } else {
                    array_push($errors, "Impossible de déterminer la valeur limite");
                }

            } else {
                $max = $spec[$typePerso]+1;
            }
            if (strlen($val) >= $max){
                array_push($errors, "Le nombre de caractères doit être inférieur à ".$max);
            }

        // VALEUR BOOL
        } elseif ($typePerso == "bool"){
            if ($val != "1" || $val != "0" || $val != 1 || $val != 0){
                array_push($errors, "La valeur d'un booléen doit être 1 ou 0");
            }

        // LISTS
        } elseif (in_array($typePerso, $list)){
            $elements = precision($type);
            if ($typePerso == "enum"){
                if (!in_array($val, $elements)){
                    array_push($errors, "La valeur sélectionnée doit être choisie parmi la liste proposée");
                }
            } else {
                $elementsVal = [];
                if (strpos($val, ",") !== false){
                    $elementsVal = explode(",", $val);
                    foreach($elementsVal as $i=>$v){
                        $elementsVal[$i] = trim($v);
                    }
                } else {
                    array_push($elementsVal, trim($val));
                }

                foreach($elementsVal as $v){
                    if (!in_array($v, $elements)){
                        array_push($errors, "Les valeurs sélectionnées doivent être choisies parmi la liste proposée");
                    }
                }
            }

        // LISTS
        } elseif (in_array($typePerso, $date)){
            if ($typePerso == "date"){
                if (!preg_match("#\d{4}\-[0-1]{1}\d{1}-[0-3]{1}\d{1}#", $val)){
                    array_push($errors, "La date n'est pas au bon format (format à utiliser : 'YYYY-MM-DD')");
                }
            } elseif ($typePerso == "datetime"){
                if (!preg_match("#\d{4}\-[0-1]{1}\d{1}-[0-3]{1}\d{1} [0-2]{1}\d{1}:[0-5]{1}\d{1}:[0-5]{1}\d{1}#", $val)){
                    array_push($errors, "Le datetime n'est pas au bon format (format à utiliser : 'YYYY-MM-DD HH:MM:SS')");
                }
            } elseif ($typePerso == "timestamp"){
                if ($val != "current_timestamp()"){
                    if (!preg_match("#[1-2]{1}[09]{1}[0-37-9]{1}\d{1}\-[0-1]{1}\d{1}-[0-3]{1}\d{1} [0-2]{1}\d{1}:[0-5]{1}\d{1}:[0-5]{1}\d{1}#", $val)){
                        array_push($errors, "Le timestamp est incorrect (format à utiliser : 'YYYY-MM-DD HH:MM:SS' avec date min '1970-01-01 00:00:00' et date max '2038-01-09 03:14:07')");
                    }
                }
            } elseif ($typePerso == "time"){
                if (!preg_match("#-?[0-8]{0,1}\d{2}:[0-5]{0,1}\d{1}:[0-5]{1}\d{1}#", $val)){
                    array_push($errors, "Le datetime n'est pas au bon format (format à utiliser : '(-H)HH:MM:SS')");
                }
            } elseif ($typePerso == "year"){
                if (!preg_match("#[0-2]{1}[019]{1}\d{2}#", $val)){
                    array_push($errors, "L'année YEAR n'est pas au bon format (format à utiliser : 'YYYY' avec année min '1901' et année max '2155' ; la valeur '0000' est également autorisée)");
                }
            }
        }
    }
    return [$errors, $delField];
}


function displayTooltips(){
    $html = "";
    $array = getDefsTypes();
    foreach($array as $k=>$ar){
        $html .= "<div id=\"tip-type-".strtolower($k)."\" class=\"tip-content\" hidden>";
        $html .= "<div class=\"titre-tooltip mb-2\">".$k."</div>";
        $html .= "<ul class=\"ml-0 pl-2\">";
        foreach ($ar as $i=>$l){
            $bold = $i == 0 ? " font-weight-bold" : "";
            $mBot = $i != (count($ar)-1) ? " mb-1" : " mb-0";
            $html .= "<li class=\"ml-0 text-left".$bold."".$mBot."\">".$l."</li>";
        }
        $html .= "</ul>";
        $html .= "</div>";
    }
    return $html;
}

