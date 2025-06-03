
@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing').' '.$dataType->display_name_plural)

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="{{ $dataType->icon }}"></i> {{ $dataType->display_name_plural }}
        </h1>
        @can('add', app($dataType->model_name))
            <a href="{{ route('voyager.'.$dataType->slug.'.create') }}" class="btn btn-success btn-add-new">
                <i class="voyager-plus"></i> <span>{{ __('voyager::generic.add_new') }}</span>
            </a>
        @endcan
        @can('delete', app($dataType->model_name))
            @include('voyager::partials.bulk-delete')
        @endcan
        @can('edit', app($dataType->model_name))
            @if(isset($dataType->order_column) && isset($dataType->order_display_column))
                <a href="{{ route('voyager.'.$dataType->slug.'.order') }}" class="btn btn-primary btn-add-new">
                    <i class="voyager-list"></i> <span>{{ __('voyager::bread.order') }}</span>
                </a>
            @endif
        @endcan

        @can('delete', app($dataType->model_name))
            @if($usesSoftDeletes)
                <input type="checkbox" @if ($showSoftDeleted) checked @endif id="show_soft_deletes" data-toggle="toggle" data-on="{{ __('voyager::bread.soft_deletes_off') }}" data-off="{{ __('voyager::bread.soft_deletes_on') }}">
            @endif
        @endcan

        @foreach(Voyager::actions() as $action)
            @if (method_exists($action, 'massAction'))
                @include('voyager::bread.partials.actions', ['action' => $action, 'data' => null])
            @endif
        @endforeach
        @include('voyager::multilingual.language-selector')
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-primary" style="margin-bottom: 0;">
                        <div class="panel panel-heading" style="margin-bottom: 0;">
                            <h3 class="panel-title">Rechercher et Filtrer</h3>
                            <div class="panel-actions">
                                <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>
                        <div class="panel-body" style="padding-top: 20px;">
                            <div class="row">
                                <div class="col-sm-6">
                                    <form class="form-horizontal" autocomplete="off">
                                        <h4>Recherche</h4>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">RÉF</label>
                                            <div class="col-sm-10">
                                                <input type="number" id="id" class="form-control" placeholder="Réf ...">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">Nom complet</label>
                                            <div class="col-sm-5">
                                              <input type="text" id="first_name" class="form-control" placeholder="Nom ...">
                                            </div>
                                            <div class="col-sm-5">
                                              <input type="text" id="last_name" class="form-control" placeholder="Prénom ...">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">Email</label>
                                            <div class="col-sm-10">
                                              <input type="text" id="email" class="form-control" placeholder="Email ...">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-offset-2 col-sm-10">
                                                <button type="submit" class="btn btn-primary search">Rechercher</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="col-sm-6">
                                    <form class="form-horizontal" autocomplete="off">
                                        <h4>Filtre</h4>

                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">Status</label>
                                            <div class="col-sm-10">
                                              <input class="magic-checkbox" type="checkbox" name="status" value="En cours" id="c1">
                                              <label for="c1"></label> <label for="c1">En cours</label><br>

                                              <input class="magic-checkbox" type="checkbox" name="status" value="Confirmé" id="c2">
                                              <label for="c2"></label> <label for="c2">Confirmé</label><br>

                                              <input class="magic-checkbox" type="checkbox" name="status" value="Annulé" id="c3">
                                              <label for="c3"></label> <label for="c3">Annulé</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-offset-2 col-sm-10">
                                                <button type="submit" class="btn btn-primary filter">Filtrer</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="pull-right" style="font-size: 16px;">
                            <span style="color:#F39C12;">■ En cours</span>
                            <span style="color:#2ECC71;">■ Confirmé</span>
                            <span style="color:#FA2A00;">■ Annulé</span>
                        </div>
                        <div class="clearfix"></div>
                        <br>
                        <div class="loader-spinner" id="calendar-spinner" style="display: none;">
                            <img src="{{ url('img/loader.gif') }}" />
                        </div>
                        <div id='calendar'></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Single delete modal --}}
    <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> {{ __('voyager::generic.delete_question') }} {{ strtolower($dataType->display_name_singular) }}?</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="delete_form" method="POST">
                        {{ method_field('DELETE') }}
                        {{ csrf_field() }}
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="{{ __('voyager::generic.delete_confirm') }}">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

     {{-- Search modal --}}
     <div class="modal modal-success fade" tabindex="-1" id="search_modal" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-list"></i> Résultat de la recherche : </h4>
                    </div>
                    <div class="modal-body" style="background-color: #ddd;min-height: 400px;">
                        <div class="loader-spinner" id="search-spinner">
                            <img src="{{ url('img/loader.gif') }}" />
                        </div>
                        <div id="search-table" class="table-responsive" style="display: none;">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>RÉF</th>
                                        <th>Nom client</th>
                                        <th>Email</th>
                                        <th>Date</th>
                                        <th>Groupe</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@stop

@section('css')
@if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
    <link rel="stylesheet" href="{{ voyager_asset('lib/css/responsive.dataTables.min.css') }}">
@endif
<link rel="stylesheet" href="{{ asset('css/fullcalendar.min.css') }}">
<style type="text/css">
    .btn-s {padding: 4px 10px !important;}
    .popover {padding: 7px;z-index: 999;}
    .popover-content {padding: 0px;}
    .loader-spinner {
        position: absolute;
        top: 35%;
        left: 40%;
        z-index: 999;
    }
    .loader-spinner img {
        background-color: #fff;
        width: 40%;
        border-radius: 15px;
        border : 1px solid #ccc;
    }
    #search-table .table tbody td {
        background-color: #fff;
    }
</style>
@stop

@section('javascript')
    <!-- DataTables -->
    @if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
        <script src="{{ voyager_asset('lib/js/dataTables.responsive.min.js') }}"></script>
    @endif
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/fullcalendar.min.js') }}"></script>
    <script src="{{ asset('js/fr.js') }}"></script>

    <script>

        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();
            $('[data-toggle="popover"]').popover();

            // CALENDAR
            calendar = $('#calendar');
            calendar.fullCalendar({
                locale: 'fr',
                customButtons: {
                    refresh: {
                    text: 'Refresh',
                    click: function() {
                            calendar.fullCalendar('refetchEvents');
                        }
                    }
                },
                header: {
                    right: 'refresh today prev,next',
                },
                events : function( start, end, timezone, callback ) {
                    $('#calendar-spinner').fadeIn(300);
                    var date = calendar.fullCalendar('getDate');
                    date = new Date(date);
                    var year = date.getFullYear();
                    var month = date.getMonth()+1;

                    var status_array = new Array();
                    var status_checkbox = $('input:checked[name=status]').each(function(){
                        status_array.push($(this).val());
                    });

                    $.ajax({
                        url: "{{ url('reservations/get_resa') }}",
                        method: "POST",
                        dataType: 'json',
                        cache: false,
                        data: {
                            year : year,
                            month : month,
                            status : status_array
                        },
                        success: function(result) {
                            callback(result);
                            $('#calendar-spinner').fadeOut(300);
                        }
                    });
                },
                eventRender: function(event, element) {
                    element.html(event.id+" - "+event.title);

                    if(event.status == 'Annulé'){
                        element.css('background-color','#FA2A00');
                    }
                    else if(event.status == 'Confirmé'){
                        element.css('background-color','#2ECC71');
                    }
                    else{
                        element.css('background-color','#F39C12');
                    }

                    var url = '{{ url('reservations') }}';
                    element.popover({
                        placement:'bottom',
                        html:true,
                        content: '<a href="javascript:;" class="btn btn-s btn-danger btn-xs pull-right delete" data-id="'+event.id+'" id="delete-'+event.id+'">'+
                                '<i class="voyager-trash"></i>'+
                            '</a>'+
                            '<a href="'+url+'/'+event.id+'/edit" class="btn btn-s btn-primary btn-xs pull-right edit">'+
                                '<i class="voyager-edit"></i>'+
                            '</a>'+
                            '<a href="'+url+'/'+event.id+'"  class="btn btn-s btn-warning btn-xs pull-right view">'+
                                '<i class="voyager-eye"></i>'+
                            '</a>'
                    });
                }
            });
            // END CALENDAR

            // BTN FILTRE
            $('.filter').click(function (e) {
                e.preventDefault();
                calendar.fullCalendar('refetchEvents');
            });

            // BTN SEARCH
            $('.search').click(function (e) {
                e.preventDefault();
                $('#search_modal').modal('show');
                $('#search-spinner').fadeIn(300);
                $('#search-table').fadeOut(300);
                $('#search-table .table tbody').html('');

                var id = $('#id').val();
                var first_name = $('#first_name').val();
                var last_name = $('#last_name').val();
                var email = $('#email').val();

                $.ajax({
                    url: "{{ url('reservations/search') }}",
                    method: "POST",
                    dataType: 'json',
                    cache: false,
                    data: {
                        id    : id,
                        first_name : first_name,
                        last_name  : last_name,
                        email : email
                    },
                    success: function(result) {
                        if(result.length == 0) {
                            $('#search-table .table tbody').append('<tr><td colspan="7" style="text-align:center;">Aucun résultat trouvé</td></tr>');
                        }
                        else {
                            for(i=0;i<result.length;i++) {
                                $('#search-table .table tbody').append('<tr>'+
                                    '<td>'+result[i].id+'</td>'+
                                    '<td>'+result[i].client_nom+' '+result[i].client_prenom+'</td>'+
                                    '<td>'+result[i].client_email+'</td>'+
                                    '<td>'+result[i].date_resa+'</td>'+
                                    '<td>'+result[i].pax_adulte+' adults<br/>'+
                                    result[i].pax_enfant12+' childs(-12)<br/>'+
                                    result[i].pax_enfant3+' childs(-3)</td>'+
                                    '<td>'+result[i].prixtotal+'</td>'+
                                    '<td><a href="{{ url('') }}/reservations/'+result[i].id+'" class="btn btn-primary">Vue</a></td>'+
                                '</tr>');
                            }
                        }

                        $('#search-spinner').fadeOut(300);
                        $('#search-table').fadeIn(300);
                    }
                });
            });
            // END BTN SEARCH
        });


        var deleteFormAction;
        $('td').on('click', '.delete', function (e) {
            $('#delete_form')[0].action = '{{ route('voyager.'.$dataType->slug.'.destroy', ['id' => '__id']) }}'.replace('__id', $(this).data('id'));
            $('#delete_modal').modal('show');
        });

        @if($usesSoftDeletes)
            @php
                $params = [
                    's' => $search->value,
                    'filter' => $search->filter,
                    'key' => $search->key,
                    'order_by' => $orderBy,
                    'sort_order' => $sortOrder,
                ];
            @endphp
            $(function() {
                $('#show_soft_deletes').change(function() {
                    if ($(this).prop('checked')) {
                        $('#dataTable').before('<a id="redir" href="{{ (route('voyager.'.$dataType->slug.'.index', array_merge($params, ['showSoftDeleted' => 1]), true)) }}"></a>');
                    }else{
                        $('#dataTable').before('<a id="redir" href="{{ (route('voyager.'.$dataType->slug.'.index', array_merge($params, ['showSoftDeleted' => 0]), true)) }}"></a>');
                    }

                    $('#redir')[0].click();
                })
            })
        @endif
        $('input[name="row_id"]').on('change', function () {
            var ids = [];
            $('input[name="row_id"]').each(function() {
                if ($(this).is(':checked')) {
                    ids.push($(this).val());
                }
            });
            $('.selected_ids').val(ids);
        });
    </script>
@stop
