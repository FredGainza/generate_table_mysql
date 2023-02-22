<?php
    // dumpPre($table_permissions);
    if ($table_permissions != NULL){
        $primary_key = $table_permissions["primary_key"];

    } else {
        // lister les champs de la table avec leur type
        $sel_cols = $conn->prepare("SHOW FIELDS FROM " . $table);
        $sel_cols->execute();
        $res_cols_db = $sel_cols->fetchAll(PDO::FETCH_OBJ);
    
        // retrouver la clé primaire
        $primary_key = "";
        foreach ($res_cols_db as $v) {
            if ($v->Key == "PRI" && $v->Extra == "auto_increment")
                $primary_key = $v->Field;
        }
    }


    // lister le nom des colonnes de la table TABLE
    if ($cols_selected != NULL) {
        $columns = $cols_selected;
    } else {
        foreach ($res_cols_db as $v) {
            $columns[] = $v->Field;
        }
    }

    if (!$order_column) {
        $order_column = $columns[0];
    }

    $columns_display = [];
    $cols_with_perso_name = [];
    if ($cols != NULL)
        $cols_with_perso_name = array_keys($cols);
    foreach ($columns as $col) {
        if (in_array($col, $cols_with_perso_name))
            $columns_display[] = $cols[$col];
        else
            $columns_display[] = $col;
    }


    // Effet toogle : quand on clic sur un <th> du <thead>, l"ordre de tri change 
    $asc_or_desc = $order_sort == "asc" ? "desc" : "asc";
    // Quand une colonne est sélectionnée, on applique à l"entête une classe particulière
    $add_class = " class=\"select_col\"";

    $ar_s = [];
    $ar_ss = [];
    $el_exclu = "";
    $search_req = "";
    $ar_test_mark = [];
    $ar_mark = [];
    $testExcl = false;
    $testAnd = false;
    $testOr = false;
    if ($searchTest) {
        if (strpos($search, "-") != false){
            if (substr_count($search, "-") > 1){
                $notif_search = 'Dans une recherche, le caractère "-" permet d\'exclure un élément de la recherche.<br>'
                .'Il ne peut être utilisé qu\'une seule fois dans une recherche.';
            } else {
                $test = explode("-", $search)[1];
                if (strpos($test, "+") != false || strpos($test, "|") != false){
                    $notif_search = 'Dans une recherche, le caractère "-" permet d\'exclure un élément de la recherche.<br>'
                    .'Il doit se placer en dernier : il ne peut pas y avoir de caractère "+" ou "|" après lui.';
                } else {
                    $testExcl = true;
                    $el_exclu = $test;
                    $search = trim(explode("-", $search)[0]);
                }
            }
        }
        if (strpos($search, "|") != false){
            $testOr = true;
            $ar_test = explode("|", $search);
            foreach ($ar_test as $t){
                if (strpos($search, "+") != false){
                    $testAnd = true;
                    $aa = explode("+", $t);
                    array_push($ar_ss, $aa);
                } else {
                    array_push($ar_ss, $t);
                }
            }

            foreach($ar_ss as $ss){
                if (is_array($ss)){
                    $r = [];
                    foreach($ss as $sss){
                        array_push($r, trim($sss));
                    }
                    array_push($ar_s, $r);
                }else {
                    array_push($ar_s, trim($ss));
                }
            }

        } else if (strpos($search, "+") != false){
            $testAnd = true;
            $ar_s_temp = explode("+", $search);
            foreach($ar_s_temp as $ss){
                array_push($ar_s, trim($ss));
            }
        } else {
            array_push($ar_s, trim($search));
        }

        function arrayLike($columns, $str, $table_permissions){
            $columns_ok = [];
            if ($table_permissions != NULL){
                $req = $table_permissions['query'];
                $str_temp = trim(explode('FROM', $req)[0]);
                $str_ = trim(explode('SELECT', $str_temp)[1]);
                $ar_fields = explode(',', $str_);
                $array_fields = [];
                foreach($ar_fields as $f){
                    $ar = explode('.', trim($f));
                    $array_fields[$ar[1]] = trim($f);
                }

                foreach($columns as $c) {
                    if (!in_array($c, $table_permissions["toggleFields"])){
                        array_push($columns_ok, $array_fields[$c]);
                    }
                }
            } else {
                $columns_ok = $columns;
            }

            $r = "";
            foreach ($columns_ok as $i => $col) {
                if ($i == 0)
                    $r .= $col . " LIKE '%" . $str . "%'";
                else
                    $r .= " OR " . $col . " LIKE '%" . $str . "%'";
            }
            return $r;
        }

        $ar_like = [];
        foreach($ar_s as $s){
            $r = "";
            if (!is_array($s)){
                $r = arrayLike($columns, $s, $table_permissions);
            } else {
                if (count($s) == 1){
                    $ss = $s[0];
                    $r = arrayLike($columns, $ss, $table_permissions);
                } else {
                    foreach($s as $j=>$sss){
                        if ($j == 0){
                            $r = "(";
                            $r .= arrayLike($columns, $sss, $table_permissions);
                        } else {
                            $r .= ") AND (";
                            $r .= arrayLike($columns, $sss, $table_permissions);
                            $r .= ")";
                        }
                    }
                }
            }
            array_push($ar_like, $r);
        }

        $search_req = " WHERE ";
        foreach($ar_like as $i=>$l){
            $search_req .= "(" .$l. ")";
            if ($i < count($ar_like) - 1){
                if ($testAnd && !$testOr){
                    $search_req .= " AND ";
                } else if (($testOr && !$testAnd) || ($testOr && $testAnd)){
                    $search_req .= " OR ";
                }
            }
        }
    }

    // On compte le nombre de rows
    if ($table_permissions != NULL){
        $req = $table_permissions["query"]."".$search_req;
    } else {
        $req = "SELECT * FROM " . $table . "" . $search_req;
    }
    $sel_nb_rec = $conn->prepare($req);
    $sel_nb_rec->execute();
    $res_nb_rec = $sel_nb_rec->fetchAll(PDO::FETCH_OBJ);
    $nb_total = $res_nb_rec ? count($res_nb_rec) : 0;

    if ($table_permissions != NULL){
        $req_start = $table_permissions["query"]."".$search_req;
    } else {
        $req_start = "SELECT * FROM " . $table . "" . $search_req;
    }
    $limit_str = "LIMIT " . $page * $limit . ", $limit";
    $req_data = $req_start . " ORDER BY " .  $order_column . " " . strtoupper($order_sort) . " " . $limit_str;

    $sel_data = $conn->prepare($req_data);
    $sel_data->execute();
    $data = $sel_data->fetchAll(PDO::FETCH_OBJ);

    if ($testExcl){
        $cpt = 0;
        $el_exclu = strtolower($el_exclu);
        foreach($data as $j=>$d){
            $test = false;
            foreach ($columns as $i => $col){
                $el = strtolower($d->$col);
                if (strpos($el, $el_exclu) != false){
                    $test = true;
                    break;
                }
            }
            if ($test){
                unset($data[$j]);
                $cpt += 1;
            }
        }
        if ($cpt != 0){
            $nb_total -= $cpt;
        }
    }

    // dumpPre($nb_total);
    $pagiTest = ceil($nb_total / $limit) > 1 ? true : false;
    $up_or_down = $order_sort == "asc" ? "up" : "down";
    $nb_between += 1;

