<?php 
    session_start();

    require '../../app/db.inc.php';
    require '../includes/functions.php';

    $searchTest = false;
    $search = '';
    $notif = "";

    // INITIALISATION TABLE
    if (isset($_POST['status']) && $_POST['status'] != ''){
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
        }

        if ($status != "search" && isset($_POST['search']) && $_POST['search'] != ""){
            $searchTest = true;
            $search = $_POST['search'];
        }

        if ($status == 'init'){
            $limit = isset($_SESSION[$tableId]['pagi_limit']) ? $_SESSION[$tableId]['pagi_limit'] : 25;
            $page = isset($_SESSION[$tableId]['pagi_page']) ? (int)$_SESSION[$tableId]['pagi_page'] : 0;
            $order_column = isset($_SESSION[$tableId]['pagi_column']) ? $_SESSION[$tableId]['pagi_column'] : NULL;
            $order_sort = isset($_SESSION[$tableId]['pagi_sort']) ? $_SESSION[$tableId]['pagi_sort'] : 'asc';

        // CHANGE COLUMN / SORT ORDER
        } else if ($status == 'change_order'){
            $limit = $_SESSION[$tableId]['pagi_limit'];
            $page = 0;
            $order_column = $_POST['column'];
            $order_sort = $_POST['sort'];

        // CHANGE LIMIT
        } else if ($status == 'change_limit'){
            $limit = intval($_POST['limit']);
            $page = 0;
            $order_column = $_SESSION[$tableId]['pagi_column'];
            $order_sort = $_SESSION[$tableId]['pagi_sort'];

        // CHANGE PAGE
        } else if ($status == 'change_page'){
            $page = intval($_POST['page'])-1;
            $limit = $_SESSION[$tableId]['pagi_limit'];
            $order_column = $_SESSION[$tableId]['pagi_column'];
            $order_sort = $_SESSION[$tableId]['pagi_sort'];

        // DELETE_ROW
        } else if ($status == 'delete_row'){
            $row_id = intval($_POST['row_id']);
            if (isset($_SESSION[$tableId]["pagi_primaryKey"])){
                $primaryKey = $_SESSION[$tableId]["pagi_primaryKey"];
                $del = $conn->prepare("DELETE FROM " . $table . " WHERE " .$primaryKey. " = :".$primaryKey);
                $del->bindValue(":".$primaryKey, $row_id, PDO::PARAM_INT);
                $del->execute();

            } else {
                $notif = "Absence de clé primaire";
            }

            $page = $_SESSION[$tableId]['pagi_page'];
            $limit = $_SESSION[$tableId]['pagi_limit'];
            $order_column = $_SESSION[$tableId]['pagi_column'];
            $order_sort = $_SESSION[$tableId]['pagi_sort'];

        // SEARCH
        } else if ($status == 'search') {
            $searchTest = true;
            $search = $_POST['valsearch'];

            $page = 0;
            $limit = $_SESSION[$tableId]['pagi_limit'];
            $order_column = $_SESSION[$tableId]['pagi_column'];
            $order_sort = $_SESSION[$tableId]['pagi_sort'];

        }

        $_SESSION[$tableId]['pagi_limit'] = $limit;
        $_SESSION[$tableId]['pagi_page'] = $page;
        $_SESSION[$tableId]['pagi_column'] = $order_column;
        $_SESSION[$tableId]['pagi_sort'] = $order_sort;
    }

    include '../includes/preparationData.php';
    $pagiHtml = pagi($pagiTest, $page, $nb_total, $limit, $order_column, $order_sort, $nb_between);
    $tableHtml = table($columns, $order_column, $add_class, $limit, $page, $asc_or_desc, $columns_display, $up_or_down, $data, $primary_key, $formats, $searchTest, $ar_s, $table_permissions);


    $result = array();
    $result["nbTotal"] = $nb_total;
    $result["pagi"] = $pagiHtml;
    $result["table"] = $tableHtml;
    $result['ss'] = $search;
    $result['notif'] = $notif;
    echo json_encode($result);
