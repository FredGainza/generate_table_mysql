<?php 
session_start();

require '../app/db.inc.php';
require 'includes/init-table.php';

?>



<!doctype html>
<html lang="fr">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">

    <link rel="stylesheet" href="../assets/css/bootstrap-4.6.2.min.css">
    <link rel="stylesheet" href="../assets/css/choices.min.css">
    <link rel="stylesheet" href="../assets/css/fontawesome-5.12.0.all.min.css">

    <!-- Custom styles -->
    <link rel="stylesheet" href="custom.css" >

    <title>Generate MySql Table</title>
</head>

<body>
<div id="backpage" class="btn-backpage pt-0 mt-0">
    <button id="iconDisplay" class="btn" style="border: none; padding: 0;"><i class="fas fa-angle-double-left mr-1"></button></i>
    <button type="button" class="btn btn-info btn-sm pr-3">
        <i class="fas fa-arrow-alt-circle-left mr-2"></i><a href="../" style="color:white; text-decoration:none;">Back</a>
    </button>
</div>
    <?php
        echo tableDatabase(
            $conn = $conn,
            $table = "users",
            $idTab = "usersTab",
            $title = "Table Users",
            $limit = 25,
            $nb_between = 2,
            $array_select_limit = [3, 5, 10, 25, 50, 100, 500, 1000],
            $cols_selected = [
                "user_id",
                "user_firstname",
                "user_lastname",
                "user_company",
                "user_email",
                "user_nb_pass_fail",
                "user_verified",
                "user_created_at",
                "user_updated_at"
            ],
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
            ],
            $formats = NULL,
            $table_permissions = NULL,
            $displayPagination = "both",
            $order_column = NULL,
            $order_sort = "asc"
        );
    ?>



    <script src="../assets/js/jquery-3.6.3.min.js"></script>
    <script src="../assets/js/popper.min.js"></script>
    <script src="https://unpkg.com/tippy.js@6/dist/tippy-bundle.umd.js"></script>
    <script src="../assets/js/bootstrap-4.6.2.min.js"></script>
    <script src="../assets/js/choices.min.js"></script>
    <script src="../assets/js/display-table-db.js"></script>

    <script>
        $('#backpage')
        .on('mouseover', function() {
            setTimeout(() => {
                $('#iconDisplay').css({
                    'opacity': 0,
                    'transition': '500ms'
                })
            }, 300);
        })
        .on('mouseleave', function() {
            setTimeout(() => {
                $('#iconDisplay').css({
                    'opacity': 100,
                    'transition': '600ms'
                })
            }, 300);
        })
    </script>
</body>

</html>