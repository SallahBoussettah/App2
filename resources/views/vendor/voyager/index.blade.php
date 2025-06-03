@extends('voyager::master')

@section('content')
    <div class="page-content">
        @include('voyager::alerts')
        
        @if(Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Admin'))
            @include('voyager::dimmers')
        @endif
        
        <div class="analytics-container">
            <div class="row">
                <div class="col-md-8">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">Dernières réservations</h3>
                        </div>
                        <div class="panel-body">
                            <table id="dataTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Agence</th>
                                        <th>Type</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Client</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reservations as $key => $reservation)
                                    <tr>
                                        <td>{{ $reservation->date_resa }}</td>
                                        <td>
                                            @if($reservation->agence() !== NULL)
                                                {{ $reservation->agence()->nom }}
                                            @endif
                                        </td>
                                        <td>{{ $reservation->typeresa }}</td>
                                        <td>{{ $reservation->departureacc() }}</td>
                                        <td>
                                            {{ $reservation->to_tour() }}
                                        </td>

                                        <td>{{ $reservation->client_civilite.' '.$reservation->client_nom.' '.$reservation->client_prenom }}</td>
                                        
                                        <td>
                                            <a href="{{ url('reservations/'.$reservation->id.'/edit')}}" data-placement="bottom" data-toggle="tooltip" title="" class="btn btn-sm btn-primary pull-right edit" data-original-title="Editer">
                                                <i class="voyager-edit"></i> 
                                            </a>
                                            <a href="{{ url('reservations?key=id&filter=equals&s='.$reservation->id)}}" class="btn btn-warning btn-sm"><i class="voyager-eye"></i></a>

                                        </td>
                                    </tr>   
                                    @endforeach
                                </tbody>
                            </table>                            
                        </div>
                    </div>
                </div>
            @if(Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Admin'))
                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title">Aujourd'hui <small style="color:#fff;font-weight: bold;">( {{ date('d/m/Y') }} )</small></h3>
                        </div>
                        <div class="panel-body">
                            <canvas id="today"><canvas>                         
                        </div>
                    </div>
                </div>
            @endif
            </div>
    </div>
@stop

@section('javascript')
    <script type="text/javascript" src="{{ asset('js/Chart.min.js') }}"></script>
    <!-- CHART JS -->
    <script type="text/javascript">
        $(document).ready(function () {

            chartID = document.getElementById("today");
            var data = new Array();

            var circleChart = new Chart(chartID, {
                type: 'doughnut',
                data: {
                    labels: ["Transfert départ","Transfert arrivée","Excursions & Circuit"],
                    datasets: [{
                        label: ["Transfert départ","Transfert arrivée","Excursions & Circuit"],
                        data: [{{ implode(",",$today) }}],
                        backgroundColor:["#e74c3c","#3498db","#1abc9c"],

                    }]
                }
            });
        });
    </script>
@stop
