@extends('voyager::master')

@section('page_title', 'Rapports')


@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="icon voyager-pie-chart"></i> Rapports
        </h1>
    </div>
@stop



@section('content')
    <div class="page-content browse container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-primary" style="margin-bottom:5px;">
                    <div class="panel-heading">
                        <h3 class="panel-title">Recherche</h3>
                        <div class="panel-actions">
                            <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                        </div>
                    </div>

                    <div class="panel-body" style="padding-top: 15px;">
                        <form class="form-horizontal" id="form">
                            {{ csrf_field() }}

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Dates</label>
                                <div class="col-sm-7">
                                  <input type="text" class="form-control" name="date" id="daterange">
                                </div>
                            </div>
                             <div class="form-group">
                                <label class="col-sm-3 control-label">Chauffeur</label>
                                <div class="col-sm-7">
                                    <select class="form-control select2-ajax" name="chauffeur"
                                        data-get-items-route="{{ url('reservations/relation') }}"
                                        data-get-items-field="reservation_belongsto_chauffeur_relationship" >
                                            <option value="" selected="selected">-- ALL --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Agence</label>
                                <div class="col-sm-7">
                                    <select class="form-control select2-ajax" name="agence"
                                        data-get-items-route="{{ url('reservations/relation') }}"
                                        data-get-items-field="reservation_belongsto_agence_relationship" >
                                            <option value="">-- ALL --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Fournisseurs</label>
                                <div class="col-sm-7">
                                    <select class="form-control select2-ajax" name="forni"
                                        data-get-items-route="{{ url('reservations/relation') }}"
                                        data-get-items-field="reservation_belongsto_fournisseur_relationship" >
                                            <option value="">-- ALL --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Vehicule</label>
                                <div class="col-sm-7">
                                    <select class="form-control select2-ajax" name="vehicule"
                                        data-get-items-route="{{ url('reservations/relation') }}"
                                        data-get-items-field="reservation_belongsto_vehicule_relationship" >
                                            <option value="" >-- ALL --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Type</label>
                                <div class="col-sm-7">
                                    <select class="form-control select2" name="type">
                                            <option value="">-- ALL --</option>
                                            <option value="depart">Transfert départ</option>
                                            <option value="arrive">Transfert arrivée</option>
                                            <option value="intertransfert">Inter Transfert</option>
                                            <option value="excursioncircuit">Excursions / Circuit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Excursion / Circuit </label>
                                <div class="col-sm-7">
                                    <select class="form-control select2-ajax" name="tour"
                                        data-get-items-route="{{ url('reservations/relation') }}"
                                        data-get-items-field="reservation_belongsto_tour_relationship" >
                                            <option value="">-- ALL --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group  col-md-12 ">

                                <label class="control-label col-sm-3" for="name">Reclamations</label>
                                <div class="col-sm-7">
                                    <ul class="checkbox" style="display: inline-flex;">
                                                <li>
                                            <input class="checkbox_check" type="checkbox" id="option-reclamation-mr" name="reclamation" value="1" >
                                            <label for="option-reclamation-mr">Avec</label>
                                            <div class="check"></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-8">
                                  <button class="btn btn-success" id="search" style="margin-top: 0;">Génerer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>



        <div id="result" style="display: none;">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default" style="margin-bottom:5px;">
                        <div class="panel-heading">
                            <h3 class="panel-title">Resultat</h3>
                        </div>

                        <div class="panel-body" style="padding-top: 15px;">
                            <table class="table table-hover" id="topcontent" style="margin-bottom: 15px;">
                                <tr>
                                    <td>Nombre Total</td>
                                    <td><strong id="nbtotal"></strong></td>
                                </tr>
                                <tr>
                                    <td>Prix Total</td>
                                    <td><strong id="prixtotal"></strong></td>
                                </tr>
                                <tr>
                                    <td>Dépenses Total</td>
                                    <td><strong id="costotal"></strong></td>
                                </tr>
                                <tr>
                                    <td>État Total</td>
                                    <td><strong id="daystotal"></strong></td>
                                </tr>
                                <tr>
                                    <td>Gasoil Total</td>
                                    <td><strong id="gaztotal"></strong></td>
                                </tr>
                                <tr>
                                    <td>Reclamations Total</td>
                                    <td><strong id="reclatotal"></strong></td>
                                </tr>
                            </table>

                            <table class="table table-hover" id="dataTable">
                                <thead>
                                    <th>Date</th>
                                    <th>RÉF</th>
                                    <th>Type</th>
                                    <th>Excursion</th>
                                    <th>Vol / Cmt</th>
                                    <th>Agence</th>
                                    <th>Chauffeur</th>
                                    <th>Client</th>
                                    <!--<th>E-mail</th>-->
                                    <th>Pax</th>
                                    <th>Prix Total</th>
                                    <th>Mode paiement</th>        
                                    <th>Statut</th>
                                    <th>Hotel</th>
                                    <th>Actions</th>
                                </thead>
                                <tbody id="content"> </tbody>  
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="preloader" style="display: none;">
            <div class="preloader-container">
                <img src="{{ url('img/loader.gif') }}"> 
            </div>
        </div>
    </div>
   @stop

@section('css')
    <link rel ="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}"/>
    <style type="text/css">
        .preloader-container {width: 100%;text-align: center;}
        .preloader-container img {width: 20%;margin-top: 10%;}
        .dt-buttons {float: right;margin-bottom: 25px;}
        .dt-button {
            color: #fff;
            opacity: .9;
            background: #22a7f0;
            border: 0;
            border-radius: 3px;
            padding: 6px 15px;
            font-size: 14px;
            line-height: 1.57142857;
            border-radius: 3px;
        }
        .page-content.browse .form-search {
            position: absolute !important;
        }
    </style>
@stop

@section('javascript')
    <script type="text/javascript" src="{{ asset('js/dataTables.buttons.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jszip.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/pdfmake.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/vfs_fonts.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/buttons.html5.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/daterangepicker.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/Chart.min.js') }}"></script>

    <script type="text/javascript">
        $('[data-toggle="tooltip"]').tooltip();
        var url = '{{ url('') }}/';

        $(document).ready(function () {

            $('#daterange').daterangepicker({
                "locale": {
                    "format": "YYYY-MM-DD",
                    "separator": " | ",
                    "applyLabel": "Appliquer",
                    "cancelLabel": "Annuler",
                    "fromLabel": "De",
                    "toLabel": "à",
                    "customRangeLabel": "Personnalisé",
                    "daysOfWeek": [
                        "di",
                        "lu",
                        "ma",
                        "me",
                        "je",
                        "ve",
                        "sa"
                    ],
                    "monthNames": [
                        "Janvier",
                        "Février",
                        "Mars",
                        "Avril",
                        "Mai",
                        "Juin",
                        "Juillet",
                        "Août",
                        "Septembre",
                        "Octobre",
                        "Novembre",
                        "Décembre"
                    ],
                    "firstDay": 1
                }
            });
            var toptext = '';

            table = $('#dataTable').DataTable({
                        dom:"Brt",
                        paging:false,
                        buttons: [
                            {
                                text: 'Copy',
                                extend: 'copy',
                                className: 'orange'
                            },
                        ],
                        columnDefs: [{
                            targets: -1,
                            searchable:false, 
                            orderable:false
                        }],
                    });

            

            // SEARCH
            $('#form').submit(function (e) {
                e.preventDefault();
                search();
            });
        });
        function search(){
            var nbtotal = 0;
            var prixtotal = 0;
            

            
            $('#result').fadeOut(300);
            $('#preloader').delay(100).fadeIn(300);
            $('#nbtotal').html('');
            $('#totaltotal').html('');
            
            var form = $('#form').serialize();

            $.ajax({
                type:'POST',
                url:'{{ url('rapports/get') }}',
                dataType: 'json',
                data:form,
                success:function (result){
                    

                   

                    $('#dataTable').DataTable().clear().draw();
                    


                    $.each(result.reservations,function( index, item ) {
                        console.log(item);
                        var typeresa = {
                            "arrive":"Transfert arrivée",
                            "depart":"Transfert départ",
                            "intertransfert" : "Inter Transfert",
                            "excursioncircuit":"Excursions / Circuit"
                        }

                        var table = $('#dataTable').DataTable().row.add([
                            item.date_resa,
                            item.ref_reservation,
                            typeresa[item.typeresa],
                            item.tour,
                            item.vol_cmt,
                            item.agence,
                            item.chauffeur,
                            item.client_nom,
                           // item.client_email,
                            item.pax_total+'( '+item.pax_adulte+' Ad, '+item.pax_enfant12+' Enf, '+item.pax_enfant3+' Enf(-3) )',
                            item.prixtotal+' '+item.devise,
                            item.paiement,
                            item.status,
                            item.hotel_nom,
                            '<a href="'+url+'/reservations?key=id&filter=equals&s='+item.id+'" target="_blank" class="btn btn-sm btn-warning" data-placement="bottom" data-toggle="tooltip" data-original-title="Vue"><i class="voyager-eye"></i></a>'
                        ]).draw()
                        .nodes()




                        nbtotal += 1;
                        if(item.status != "noshow" && item.status !="canceled"){
                            prixtotal += parseInt(item.prix);
                            
                        }else{

                        $(table).addClass('danger');
                            //  $('#dataTable').DataTable().row.addClass('danger')
                            console.log('im here');
                        }
                        
                    });
                    if ($('input.checkbox_check').is(':checked')) {
                        $('#reclatotal').html(nbtotal);

                    }else{
                        $('#reclatotal').html("__");
                    }

                    text = 'Nombre Total :'+ nbtotal+" Réservation(s) <br>";
                    console.log(result);
                    $('#nbtotal').html(nbtotal+" Réservation(s)");
                    $('#prixtotal').html(prixtotal +" "+result.currency);
                    $('#costotal').html(result.cost +" "+result.currency);
                    $('#daystotal').html(result.total_day +" "+result.currency);
                    $('#gaztotal').html(result.totalgaz +" "+result.currency);
                    $('#preloader').fadeOut(300);
                    $('#result').delay(200).fadeIn(300);

                    if ($('input.checkbox_check').is(':checked')) {
                        var toptext = 'Nombre Total  : '+nbtotal+' Réservation(s) \n Prix Total : '+prixtotal+' DH . \r Dépenses Total : '+result.cost+' DH . \n État Total : '+result.total_day+' DH \n Gasoil Total  : '+result.totalgaz+' DH . \n Reclamations Total :  '+nbtotal+' \n';
                    }else{
                        var toptext = 'Nombre Total  : '+nbtotal+' Réservation(s) \n Prix Total : '+prixtotal+' DH . \r Dépenses Total : '+result.cost+' DH . \n État Total : '+result.total_day+' DH \n Gasoil Total  : '+result.totalgaz+' DH . \n Reclamations Total :  __ \n';
                    }
                    table.buttons( '.csv' ).remove();
                    table.buttons( '.exel' ).remove();
                    table.buttons( '.pdf' ).remove();
                    table.button().add( 1, {
                            text: 'CSV',
                            extend: 'csvHtml5',
                            className: 'csv'

                    } );
                    table.button().add( 2, {
                            text: 'Excel',
                            extend: 'excel',
                            messageTop: toptext,
                            className: 'exel'

                    } );
                    table.button().add(3, {
                            text: 'PDF',
                            extend: 'pdfHtml5',
                            orientation: 'landscape',
                            messageTop: toptext,
                            className: 'pdf'

                    } );

                    var text = $('#topcontent').html();
            console.log(text);
                }
            });
        }
    </script>
@stop