@extends('voyager::master')


@section('page_title', 'Statistiques')

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="icon voyager-pie-chart"></i> Statistiques
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
                    </div>

                    <div class="panel-body" style="padding-top: 15px;">
                        <div class="row">
                            <div class="col-sm-5">
                                <input class="form-control" type="text" name="date" id="daterange">    
                            </div>

                            <div class="col-sm-5">
                                <button class="btn btn-success" id="search" style="margin-top: 0;">Génerer</button>
                            </div>

                            <div class="col-sm-2">
                                <p class="pull-right"><strong id="duree"></strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="result" style="display: none;">
            <div class="row" id="content">
                <div class="col-md-6" >
                	<div class="panel panel-warning" style="margin-bottom:20px;">
	                    <div class="panel-heading">
	                        <h3 class="panel-title">Nombre des réservations par type</h3>
	                    </div>

	                    <div class="panel-body" style="padding-top: 15px;" id="chart1"></div>
	                </div>
                </div>

                <div class="col-md-6">
                	<div class="panel panel-warning" style="margin-bottom:20px;">
	                    <div class="panel-heading">
	                        <h3 class="panel-title">
	                        	Progression par intervalle des dates
	                        	<strong class="pull-right" id="count"></strong>
	                        </h3>
	                    </div>

	                    <div class="panel-body" style="padding-top: 15px;" id="chart2"></div>
	                </div>

                </div>

                <div class="col-md-12">
                	<div class="panel panel-info" style="margin-bottom:20px;">
	                    <div class="panel-heading">
	                        <h3 class="panel-title">
	                        	Progression des prix par intervalle des dates
	                        	<strong class="pull-right" id="sum"></strong>
	                        </h3>
	                    </div>

	                    <div class="panel-body" style="padding-top: 15px;" id="chart3"></div>
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
	</style>
@stop



@section('javascript')
	<script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/daterangepicker.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/Chart.min.js') }}"></script>
    <script type="text/javascript">
    	var token = '{{ csrf_token() }}';
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

            // SEARCH
            $('#search').click(function (e) {
                search();
            });
    	});

    	function search(){
            $('#result').fadeOut(300);
            $('#preloader').delay(100).fadeIn(300);

            var date = $('#daterange').val();

            $.ajax({
                type:'POST',
                url:'{{ url('statistiques/get') }}',
                dataType: 'json',
                data:{_token:token,date:date},
                success:function (result){
                	$('#duree').empty();
                	$('#duree').html('Durée : '+result.progress.duree);

                    $('#chart1Canvas').remove(); 
                    $('#chart1').append('<canvas id="chart1Canvas"><canvas>');

                    $('#chart2Canvas').remove(); 
                    $('#chart2').append('<canvas id="chart2Canvas"><canvas>');

                    $('#chart3Canvas').remove(); 
                    $('#chart3').append('<canvas id="chart3Canvas"><canvas>');

                    chart1 = document.querySelector('#chart1Canvas');
                    chart2 = document.querySelector('#chart2Canvas');
					chart3 = document.querySelector('#chart3Canvas');

					/* CHART 1 */
					var chartCanvas1 = new Chart(chart1, {
		                type: 'pie',
		                data: {
		                    labels: ["Transfert départ","Transfert arrivée","Excursions & Circuit"],
		                    datasets: [{
		                        label: ["Transfert départ","Transfert arrivée","Excursions & Circuit"],
		                        data: result.nb_reservations,
		                        backgroundColor:["#e74c3c","#3498db","#1abc9c"],
		                    }]
		                }
		            });

		            /* CHART 2/3 */
		            var sum_all = 0;
		            var count_all = 0;
		            var data2 = [];
                    var data3 = [];

                    for(i = 0 ; i < result.progress.labels.length ; i++) {
                        sum = 0;
                        count = 0;

                        for(j = 0 ; j < result.progress.data.length ; j++) {
                            if(result.progress.labels[i] == result.progress.data[j].date_resa_n) {
                                sum = result.progress.data[j].sum;
                                count = result.progress.data[j].count;
                                break;
                            }
                        }

                        sum_all += sum;
                        count_all += count;

                        data2.push(count);
                        data3.push(sum);
                    }

                    $('#sum').html(sum_all+" "+result.currency);
					$('#count').html(count_all);

                    var json2 = [{
                        "label": "Nombre Total",
                        "data": data2,
                        "backgroundColor": "rgb(54, 162, 235)",
                        "borderWidth": 0
                    }];

                    var json3 = [{
                        "label": "Prix Total",
                        "data": data3,
                        "backgroundColor": "rgb(54, 162, 235)",
                        "borderWidth": 0
                    }];

                    var chartCanvas2 = new Chart(chart2, {
                        type: 'line',
                        data: {
                            labels:result.progress.labels,
                            datasets: json2
                        }
                    });

                    var chartCanvas3 = new Chart(chart3, {
                        type: 'bar',
                        data: {
                            labels:result.progress.labels,
                            datasets: json3
                        }
                    });

                    $('#preloader').fadeOut(300);
                    $('#result').delay(200).fadeIn(300);
                }
            });
        }
    </script>
@stop