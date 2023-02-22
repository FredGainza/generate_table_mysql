$(document).ready(function() {

    let tabId = $('table').attr('id');
    let test = testNoScroll(tabId);
    if (test){
        let $t_fixed = $('#'+tabId).clone();
        $t_fixed.find("tbody").remove().end().addClass("table-fixme-fixed").insertBefore($('#'+tabId)).attr('id', tabId+'Fixed');
    }
    fixMe(tabId);
    /*
    ------------------------------------------
    - UPDATE VALEUR LIMIT
    ------------------------------------------
    */
    $('#select_pagination').change(function() {
        let limit = $(this).find(":selected").val();
        let search = $('#input_search').val();
        $.ajax({
            url: 'includes/traitement-table.php',
            type: 'POST',
            data: {
                status: 'change_limit',
                limit: limit,
                search: search,
                tabId: tabId
            },
            dataType: 'json',
            success: function(result) {
                console.log('Number of elements by page changed');
                let nbRec = result.nbTotal;
                let pagi = result.pagi;
                let table = result.table;
                displayHtml(nbRec, pagi, table);
            },
            error: function(error) {
                console.log(error.responseText);
            }
        });
    });

    /*
    ------------------------------------------
    - CHANGEMENT PAGE COURANTE
    ------------------------------------------
    */
    let paginations = ['Top', 'Bottom'];
    let paginationBtns = ['-top', '-bottom'];
    $.each(paginations, function(key, val) {
        $(document).on('click', '#valid-jump' + paginationBtns[key], function(e) {
            e.preventDefault();
            let valJump = $('input[name=jump' + val + ']').val();
            let search = $('#input_search').val();
            if (valJump != "") {
                if (testInt(valJump) > 0) {
                    valJump = testInt(valJump);
                    // console.log(valJump);

                    // check page max
                    let lim = $('#select_pagination').find(":selected").val();
                    let totalEl = $('#displayNbTotal'+val).text();
                    let pageLast = Math.ceil(totalEl / lim);
                    if (valJump <= pageLast) {
                        // check page actuelle
                        let lisToTest = $('#paginationGenerated' + val + ' nav ul li');
                        $.each(lisToTest, function(k, v) {
                            if ($(v).hasClass('active')) {
                                const urlActual = $(this).children().attr('href');
                                let pageActual = testInt(getUrlParameter(urlActual, "page"));
                                // console.log("pageActual: "+pageActual);
                                pageActual = testInt(pageActual);
                                if (valJump != pageActual){
                                    // console.log(valJump);
                                    let type = "Jump";
                                    changePage(valJump, search, type);
                                }
                            } else {
                                $('input[name=jump' + val + ']').val('').trigger('change');
                            }
                        });
                    } else {
                        $('input[name=jump' + val + ']').val('').trigger('change');
                    }

                } else {
                    $('input[name=jump' + val + ']').val('').trigger('change');
                }

            }
        });
    });
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        if (!$(this).parent().hasClass('active')) {
            let url = $(this).attr('href');
            let page = getUrlParameter(url, "page");
            let search = $('#input_search').val();
            let type = "Button";
            changePage(page, search, type);
        }
    });

    function changePage(page, search, type) {
        $.ajax({
            url: 'includes/traitement-table.php',
            type: 'POST',
            data: {
                status: 'change_page',
                page: page,
                search: search,
                tabId: tabId
            },
            dataType: 'json',
            success: function(result) {
                console.log('Changement page par '+type);
                $('input[name=jumpTop]').val('').trigger('change');
                $('input[name=jumpBottom]').val('').trigger('change');
                let nbRec = null;
                let pagi = result.pagi;
                let table = result.table;
                displayHtml(nbRec, pagi, table);
            },
            error: function(error) {
                console.log(error.responseText);
            }
        });
    }

    /*
    ------------------------------------------
    - UPDATE COLUMN SORT ORDER
    ------------------------------------------
    */
    $(document).on('click', '.change-order', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        let column = getUrlParameter(url, "column");
        let sort = getUrlParameter(url, "sort");
        let search = $('#input_search').val();
        $.ajax({
            url: 'includes/traitement-table.php',
            type: 'POST',
            data: {
                status: 'change_order',
                column: column,
                sort: sort,
                search: search,
                tabId: tabId
            },
            dataType: 'json',
            success: function(result) {
                console.log('Changement column sort order');
                let nbRec = null;
                let pagi = result.pagi;
                let table = result.table;
                displayHtml(nbRec, pagi, table);
            },
            error: function(error) {
                console.log(error.responseText);
            }
        });
    });

    /*
    ------------------------------------------
    - ADD ROW
    ------------------------------------------
    */
    $(document).on('click', '#btnAddRow', function (e) {
        e.preventDefault();

        // display formulaire
        $.ajax({
            url: 'includes/traitement-row.php',
            type: 'POST',
            data: {
                status: 'add_row',
                tabId : tabId
            },
            dataType: 'json',
            success: function(result) {
                console.log('Add a new row started');
                let html = result.html;
                if (html != ""){
                    $('#rowTitle').html("Ajout d'un enregistrement");
                    $('#rowContent').html(html).trigger('change');
                    let testSelect = $('.js-choice');
                    if (testSelect.length != 0){
                        for (let i=0; i < testSelect.length; i++){
                            new Choices($('.js-choice')[0]);
                        }
                    }
                    $('#modalRow').modal('show');

                    // Tooltips
                    $('.tip').each(function() {
                        $(this).tooltip({
                            html: true,
                            title: $('#' + $(this).data('tip')).html(),
                            placement: 'bottom'
                        });
                    });

                    $("input, textarea").each(function(k,v) {
                        $(v).focus(function() {
                            $(this).css("background-color", "#ebf2f4");
                        });
                        $(v).blur(function() {
                            $(this).css("background-color", "white");
                        });
                    });

                    $('#formRow').submit(function(event){
                        event.preventDefault();
                        let res = {};
                        res.status = 'add_row_save';
                        res.tabId = tabId;
                        $('#formRow input, #formRow textarea').each(function(k,v) {
                            let id = $(v).attr('id');
                            let val = $(v).val();
                            res[id] = val;
                        });
                        $('#formRow select').each(function(k,v) {
                            let id = $(v).attr('id');
                            let val = $('#'+id+ ' option:selected').val();
                            res[id] = val;
                        });

                        // save new row
                        $.ajax({
                            url: 'includes/traitement-row.php',
                            type: 'POST',
                            data: res,
                            dataType: 'json',
                            success: function(result){
                                let status = result.status;
                                if (status == "success"){
                                    $('#modalRow').modal('hide');
                                    console.log('Add a new row succeed');
                                    let nbRec = result.nbTotal;
                                    let pagi = result.pagi;
                                    let table = result.table;
                                    displayHtml(nbRec, pagi, table);

                                    $('#msgNotifEditRow').html("<span class=\"text-success-perso\">Ajout de la nouvelle row correctement effectué</span>");
                                    $('#notifEditRow').modal('show');
                                    setTimeout(function() {
                                        $('#notifEditRow').modal('hide');
                                    }, 3000);
                                } else if (status == "error"){
                                    displayFormErrors(result);
                                }
                            },
                            error: function(error) {
                                console.log(error.responseText);
                            }
                        });
                    });
                }
            },
            error: function(error) {
                console.log(error.responseText);
            }
        });
    });

    /*
    ------------------------------------------
    - DELETE ROW
    ------------------------------------------
    */
    $(document).on('click', '.delete_row', function() {
        let rowId = $(this).closest('tr').attr('id');
        let row_id = rowId.substr(3);
        let search = $('#input_search').val();
        $('#msgConfirmDeleteRow').html("<span class=\"text-success-perso\">Confirmez la suppression de la row id = <b>" + row_id + "</b> ?</span>");
        $('#confirmDeleteRow').modal('show');
        $('#btn-del-row-valid').click(function() {
            $('#confirmDeleteRow').modal('hide');
            $.ajax({
                url: 'includes/traitement-table.php',
                type: 'POST',
                data: {
                    status: 'delete_row',
                    row_id: row_id,
                    search: search,
                    tabId: tabId
                },
                dataType: 'json',
                success: function(result) {
                    console.log('Row ' + row_id + ' deleted');
                    let nbRec = result.nbTotal;
                    let pagi = result.pagi;
                    let table = result.table;
                    displayHtml(nbRec, pagi, table);

                    let notif = result.notif;
                    if (notif != ""){
                        $('#msgNotifDeleteRow').html("<span class=\"text-danger\">La row <b>" + row_id + "</b> n'a pas été supprimé."+
                        "</span>"+
                        "<br>Msg Error: "+notif);
                        $('#notifDeleteRow').modal('show');

                    } else {
                        $('#msgNotifDeleteRow').html("<span class=\"text-success-perso\">La row <b>" + row_id + "</b> a bien été supprimé.</span>");
                        $('#notifDeleteRow').modal('show');
                        setTimeout(function() {
                            $('#notifDeleteRow').modal('hide');
                        }, 3000);
                    }
                },
                error: function(error) {
                    console.log(error.responseText);
                }
            });
        });
    });

    /*
    ------------------------------------------
    - UPDATE ROW - TABLE CLASSIQUE
    ------------------------------------------
    */
    $(document).on('click', '.edit_row', function(e) {
        e.preventDefault();
        let rowId = $(this).closest('tr').attr('id');
        let row_id = rowId.substr(3);

        // display formulaire
        $.ajax({
            url: 'includes/traitement-row.php',
            type: 'POST',
            data: {
                status: 'edit_row',
                row_id: row_id,
                tabId: tabId
            },
            dataType: 'json',
            success: function(result) {
                console.log('Update row ' + row_id + ' started');
                let html = result.html;
                if (html != ""){
                    $('#rowTitle').html("Edition de la row "+row_id);
                    $('#rowContent').html(html).trigger('change');
                    let testSelect = $('.js-choice');
                    if (testSelect.length != 0){
                        for (let i=0; i < testSelect.length; i++){
                            new Choices($('.js-choice')[0]);
                        }
                    }
                    $('#modalRow').modal('show');

                    // Tooltips
                    $('.tip').each(function() {
                        $(this).tooltip({
                            html: true,
                            title: $('#' + $(this).data('tip')).html(),
                            placement: 'bottom'
                        });
                    });

                    $("input, textarea").each(function(k,v) {
                        $(v).focus(function() {
                            $(this).css("background-color", "#ebf2f4");
                        });
                        $(v).blur(function() {
                            $(this).css("background-color", "white");
                        });
                    });

                    $('#formRow').submit(function(event){
                        event.preventDefault();
                        let res = {};
                        res.status = 'edit_row_changes';
                        res.search = $('#input_search').val();
                        res.tabId = tabId;
                        $('#formRow input, #formRow textarea').each(function(k,v) {
                            let id = $(v).attr('id');
                            let val = $(v).val();
                            res[id] = val;
                        });
                        $('#formRow select').each(function(k,v) {
                            let id = $(v).attr('id');
                            let val = $('#'+id+ ' option:selected').val();
                            res[id] = val;
                        });

                        // save changes
                        $.ajax({
                            url: 'includes/traitement-row.php',
                            type: 'POST',
                            data: res,
                            dataType: 'json',
                            success: function(result){
                                let status = result.status;
                                if (status == "success"){
                                    $('#modalRow').modal('hide');
                                    console.log('Update row ' + row_id + ' succeed');
                                    let nbRec = null;
                                    let pagi = result.pagi;
                                    let table = result.table;
                                    displayHtml(nbRec, pagi, table);

                                    $('#msgNotifEditRow').html("<span class=\"text-success-perso\">La row <b>" + row_id + "</b> a correctement été édité.</span>");
                                    $('#notifEditRow').modal('show');
                                    setTimeout(function() {
                                        $('#notifEditRow').modal('hide');
                                    }, 3000);

                                } else if (status == "error"){
                                    displayFormErrors(result);
                                }
                            },
                            error: function(error) {
                                console.log(error.responseText);
                            }
                        });
                    });
                }
            },
            error: function(error) {
                console.log(error.responseText);
            }
        });
    });

    /*
    ------------------------------------------
    - UPDATE ROW - TABLE PERMISSIONS
    ------------------------------------------
    */
    $(document).on('click', '.edit-table-permission', function(e) {
        e.preventDefault();
        let rowId = $(this).closest('tr').attr('id');
        let row_id = rowId.substr(3);

        let data = {};
        data.status = 'edit_row_permission';
        data.row_id = row_id;
        data.tabId = tabId;
        $('#'+rowId+' input[type=checkbox]').each(function(k,v) {
            let idCheck = $(v).attr('id');
            let field = idCheck.substr(rowId.length+1);
            let value = $(v).is(':checked') ? "1" : "0";
            data[field] = value;
        });

        $.ajax({
            url: 'includes/traitement-row.php',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(result){
                let status = result.status;
                if (status == "success" || status == "no_update"){
                    $('#modalRow').modal('hide');
                    console.log('Update row ' + row_id + ' succeed');
                    let nbRec = null;
                    let pagi = result.pagi;
                    let table = result.table;
                    displayHtml(nbRec, pagi, table);

                    if (status == "success")
                        $('#msgNotifEditRow').html("<span class=\"text-success-perso\">La row <b>" + row_id + "</b> a correctement été éditée.</span>");
                    else
                        $('#msgNotifEditRow').html("<span class=\"text-danger-perso\">La row <b>" + row_id + "</b> n'a pas été éditée car aucune valeur n'a été modifiée.</span>");
                    $('#notifEditRow').modal('show');
                    setTimeout(function() {
                        $('#notifEditRow').modal('hide');
                    }, 3000);

                } else if (status == "error"){
                    displayFormErrors(result);
                }
            },
            error: function(error) {
                console.log(error.responseText);
            }
        });
    });



    /*
    ------------------------------------------
    - SEARCH
    ------------------------------------------
    */
    $(document).on('click', '.btn-input-close-search', function(){
        let valClose = $('#input_search').val();
        if (valClose != ""){
            $('#input_search').val(null).trigger('change');
            $('.btn-input-close-search').css({
                'opacity': '0',
                'transition': '0.35s'
            });
            $.ajax({
                url: 'includes/traitement-table.php',
                type: 'POST',
                data: {
                    status: 'init',
                    tabId: tabId
                },
                dataType: 'json',
                success: function(result) {
                    console.log('Table initialised');
                    let nbRec = result.nbTotal;
                    let pagi = result.pagi;
                    let table = result.table;
                    displayHtml(nbRec, pagi, table);
                },
                error: function(error) {
                    console.log(error.responseText);
                }
            });
        }
    });
    $(document).on('focus', '#input_search', function(e) {
        e.preventDefault();
        $('.btn-input-close-search').css({
            'opacity': '0.3',
            'transition': '0.35s'
        });
        let old_valsearch = $('#input_search').val();
        $('#input_search').css("background-color", "#ebf2f4");
        $('#input_search').keyup(function(e) {
            e.preventDefault();
            let valsearch = $(this).val();

            if (valsearch != '' && valsearch != old_valsearch) {
                $.ajax({
                    url: 'includes/traitement-table.php',
                    type: 'POST',
                    data: {
                        status: 'search',
                        valsearch: valsearch,
                        tabId: tabId
                    },
                    dataType: 'json',
                    success: function(result) {
                        console.log('Results for search "' + valsearch + '"');
                        let nbRec = result.nbTotal;
                        let pagi = result.pagi;
                        let table = result.table;
                        displayHtml(nbRec, pagi, table);
                    },
                    error: function(error) {
                        console.log(error.responseText);
                    }
                });
            } else if (valsearch == '') {
                $.ajax({
                    url: 'includes/traitement-table.php',
                    type: 'POST',
                    data: {
                        status: 'init',
                        tabId: tabId
                    },
                    dataType: 'json',
                    success: function(result) {
                        console.log('Table initialised');
                        let nbRec = result.nbTotal;
                        let pagi = result.pagi;
                        let table = result.table;
                        displayHtml(nbRec, pagi, table);
                    },
                    error: function(error) {
                        console.log(error.responseText);
                    }
                });
            }
        });
    });
    $(document).on('blur', '#input_search', function(e) {
        e.preventDefault();
        $(this).css("background-color", "white");
        let valClose = $('#input_search').val();
        if (valClose == ""){
            $('.btn-input-close-search').css({
                'opacity': '0',
                'transition': '0.35s'
            });
        }
    });

    function displayFormErrors(result){
        $('#containerNotifErrorsForm').css('display', 'none');
        $('#contentNotifErrorsForm').html('').trigger('change');
        console.log('Error(s) on submit form');
        let errors = result.errors;
        console.log(errors);
        let cc;
        let msg = '';
        let fieldsErrors = [];
        if (Object.keys(errors).length != 1){
            cc = "Veuillez corriger les erreurs suivantes : ";
        } else {
            cc = "Veuillez corriger l'erreur suivante : ";
        }
        msg += '<i class="fas fa-exclamation-triangle text-danger fa-lg mr-2"></i><span class="ml-3 text-danger" style="font-weight: 700;">' + cc + '</span>';
        msg += '<hr class="my-2" style="border-top-color: #bf9f9275 !important;">';
        $.each(errors, function(k, v){
            fieldsErrors.push(k);
            $('#'+k).parent().css('border', '2px solid #bd2130');
            msg += '<i class="fas fa-caret-right mr-0 ml-4-5"></i><span class="ml-2-3 va-text-top fz-14px">Champ <b>'+k+ '</b> : </span>';
            msg += "<ul class=\"ml-4-5 mb-1\">";
            $(v).each(function(i, li){
                msg += '<li class=\"fz-14px\" style="line-height: 1.2;">' + li + '</li>';
            });
            msg += "</ul>";
        });

        $('#contentNotifErrorsForm').html(msg).trigger('change');
        window.location.href = '#topRow';
        $('#containerNotifErrorsForm').fadeIn();

        $(fieldsErrors).each(function(k,v){
            let oldVal = "";
            let newVal = "";
            $('#'+v)
            .focus(function() {
                oldVal = $(this).val();
                $(this).parent().css('border', 'none');
            })
            .blur(function(){
                newVal = $(this).val();
                if (oldVal == newVal){
                    $(this).parent().css('border', '2px solid #bd2130');
                }
            });
        });
    }

    function displayHtml(nbRec, pagi, table){
        let tabId = '';
        if ($('table').length == 2)
            tabId = $('table').eq(1).attr('id');
        else
            tabId = $('table').eq(0).attr('id');

        console.log(tabId);
        if (nbRec != null){
            $('#displayNbTotalTop').html(nbRec).trigger('change');
            $('#displayNbTotalBottom').html(nbRec).trigger('change');
        }

        $('#paginationGeneratedTop').html(pagi).trigger('change');
        $('#paginationGeneratedBottom').html(pagi).trigger('change');
        if (pagi != ""){
            $('#rowJumpTop').css('visibility', 'visible');
            $('#rowJumpBottom').css('visibility', 'visible');
        } else {
            $('#rowJumpTop').css('visibility', 'hidden');
            $('#rowJumpBottom').css('visibility', 'hidden');
        }

        $('#'+tabId).html(table).trigger('change');
        console.log(tabId);
        fixMe(tabId);
    }


    function getUrlParameter(url, sParam) {
        var sPageURL = url.substring(1),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    }

    function testInt(x) {
        const parsed = parseInt(x, 10);
        if (isNaN(parsed)) {
            return -1;
        }
        return parsed;
    }

    function deleteField(id){
        $('#'+id).val(null).trigger('change');
    }

    function testNoScroll(id) {
        let widthDocument = 0.95 * $(window).width();
        let widthHeader = $('#'+id).width();
        if (widthDocument > widthHeader)
            return true;
        else
            return false;
    }

    function fixMe(id) {
        let test = testNoScroll(id);
        // let idResp = id.split('__')[1];
        let $this = $('#'+id);
        let $t_fixed = $('#'+id+"Fixed");

        if (test){
            $(window).on('resize', function () {
                $t_fixed.find("th").each(function (index) {
                    $(this).css("width", $this.find("th").eq(index).outerWidth() + "px");
                });
            });

            $(window).on('scroll', function () {
                let offset = $(this).scrollTop(),
                    tableOffsetTop = $this.offset().top,
                    tableOffsetBottom = tableOffsetTop + $this.height() - $this.find("thead").height();

                if (offset < tableOffsetTop || offset > tableOffsetBottom)
                    $t_fixed.hide();
                else if(offset >= tableOffsetTop && offset <= tableOffsetBottom && $t_fixed.is(":hidden")){
                    $t_fixed.show();
                    $(window).resize();

                }

            });

        } else {
            let heightWindow = $(window).height();
            let offSetTop = $('#'+id).offset().top;
            let heightTable = $('#'+id).height();
            if (heightWindow - offSetTop < heightTable){
                $('#displayNbTotalBottom').parent().parent().css('display', 'none');
                let maxHeight = heightWindow - offSetTop - 25;
                let idResp = id.split('__')[1];
                $('#tableResponsive__'+idResp).addClass('table-scroll').css('height', maxHeight);
            }
        }

    }

});