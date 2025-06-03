@extends('voyager::master')

@section('page_title', 'Chauffeurs Archivés')

@section('page_header')

    <div class="container-fluid">

        <h1 class="page-title">

            <i class="icon voyager-trash"></i> Chauffeurs Archivés

        </h1>

    </div>

@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Tél</th>
                                        <th>Email</th>
                                       
                                        <th class="actions text-right">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                     @foreach($chauffeurs as $chauffeur)
                                    <tr>  
                                        <td> <span>{{$chauffeur->nom}}</span></td>
                                        <td> <span>{{$chauffeur->tel}}</span></td>
                                        <td> <span>{{$chauffeur->email}}</span></td>
                                        <td class="no-sort no-click" id="bread-actions">
                                            <a href="{{url('/archive-chauffeurs/update/'.$chauffeur->id)}}"><i class="voyager-refresh pull-right">Récupérer</i> </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
<link rel="stylesheet" href="{{ voyager_asset('lib/css/responsive.dataTables.min.css') }}">
<link rel ="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}"/>
@stop
@section('javascript')
    <script src="{{ voyager_asset('lib/js/dataTables.responsive.min.js') }}"></script>
@stop
