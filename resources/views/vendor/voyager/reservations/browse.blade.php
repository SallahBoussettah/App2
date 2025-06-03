@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing').' '.$dataType->display_name_plural)
@section('page_header')
<style type="text/css">
    .voyager .table .primary td{
        background-color: #cce5ff;
    }
    
    .voyager .table th:nth-child(25) {
        width : 90px !important;
    }
    .voyager .table .gmailgyg {
        background-color: #f3e887 !important;
    }
.btn .spinner-border {
    width: 1rem;
    height: 1rem;
    border: 2px solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    display: inline-block;
    animation: spinner-border .75s linear infinite;
    margin-right: 5px;
    vertical-align: middle;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}
</style>

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

            <div class="col-md-8">

                <div class="panel panel-primary">

                    <div class="panel panel-heading" style="margin-bottom: 0;">

                        <h3 class="panel-title">Filtre</h3>

                        <div class="panel-actions">

                            <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>

                        </div>



                    </div>



                    <div class="panel-body" style="padding-top: 20px;">



                    <form action="{{ route('voyager.reservations.index') }}" method="get">

                        <input value="" name="selected_ids[]" id="selected_ids" type="hidden" />

                            <div class="row">

                                <div class="col-sm-3">
                                    <label>Dates</label>
                                    <input name="date" type="text" class="form-control" id="daterange"/>
                                    <label>Statut</label>
                                    <select name="statut" class="form-control select2">
                                        <option value="" >Tous</option>
                                        <option value="pending" {{request()->get('statut')=='pending'?'selected':''}}>Pending</option>
                                        <option value="confirmed" {{request()->get('statut')=='confirmed'?'selected':''}}>Confirmed</option>
                                        <option value="canceled" {{request()->get('statut')=='canceled'?'selected':''}}>Canceled</option>
                                        <option value="noshow" {{request()->get('statut')=='noshow'?'selected':''}}>Noshow</option>
                                        <option value="gmail" {{request()->get('statut')=='gmail'?'selected':''}}>From Gmail</option>
                                    </select>
                                    <br>
                                    <label>Paiement</label>
                                    <select name="paiement" class="form-control select2">
                                        <option value="" >Tous</option>
                                        <option value="Virement" {{request()->get('paiement')=='confirmed'?'selected':''}}>Virement</option>
                                        <option value="Chéque" {{request()->get('paiement')=='confirmed'?'selected':''}}>Chéque</option>
                                        <option value="cash" {{request()->get('paiement')=='confirmed'?'selected':''}}>Cash</option>
                                        <option value="paypal" {{request()->get('paiement')=='confirmed'?'selected':''}}>Paypal</option>
                                    </select>
                                </div>
                                    

                                <div class="col-sm-3">
                                    <label>Type</label>
                                    <select name="type" class="form-control select2">
                                        <option value="" >Tous</option>
                                        <option value="arrive" {{request()->get('type')=='arrive'?'selected':''}}>Transfert arrivée</option>
                                        <option value="depart" {{request()->get('type')=='depart'?'selected':''}}>Transfert départ</option>
                                        <option value="intertransfert" {{request()->get('type')=='intertransfert'?'selected':''}}>Inter Transfert</option>
                                        <option value="excursioncircuit" {{request()->get('type')=='excursioncircuit'?'selected':''}}>Excursions & Circuit</option>
                                    </select>
                                    <label>Driver</label>
                                    <select name="chauffeur" class="form-control select2">
                                        <option value="" >Tous</option>
                                        @foreach($chauffeurs as $chauffeur)
                                            <option value="{{ $chauffeur->id }}" {{request()->get('chauffeur')==$chauffeur->id?'selected':''}}>{{ $chauffeur->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <label>From</label>
                                    <select name="from" class="form-control select2">
                                        <option value="" >Tous</option>
                                        @foreach($froms as $from)
                                            <option value="{{ $from->id }}" {{request()->get('from')==$from->id?'selected':''}}>{{ $from->titre }}</option>
                                        @endforeach
                                    </select>
                                    <label>To / Tours</label>

                                    <select name="tour" class="form-control select2">
                                        <option value="" >Tous</option>
                                        @foreach($tours as $tour)
                                            <option value="{{ $tour->id }}" {{request()->get('tour')==$tour->id?'selected':''}}>{{ $tour->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                     <label>Agences</label>
                                    <select name="agency" class="form-control select2">
                                        <option value="" >Tous</option>
                                        @foreach($agences as $agency)
                                            <option value="{{ $agency->id }}" {{request()->get('agency')==$agency->id?'selected':''}}>{{ $agency->nom }}</option>
                                        @endforeach
                                    </select>
                                    <label>Réf</label>
                                    <input type="text" class="form-control" name="ref" value="{{ request()->get('ref') }}">
                                    <input name="submit" type="submit" class="btn btn-primary btn-f" value="Filtrer"/>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">

                <div class="panel panel-primary">

                    <div class="panel panel-heading" style="margin-bottom: 0;">

                        <h3 class="panel-title">Total : </h3>

                        <div class="panel-actions">
                            <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                        </div>

                    </div>

                    <div class="panel-body" style="padding-top: 20px;font-weight: 700; color: #34395e; font-size: 18px;">
                        @foreach($totals as $total)
                        
                            @if(!empty($total->symbol))
                            <span style="font-size: 20px; font-weight: 500;">{{number_format($total->total, 2 , '.', ' ')}}</span> <span> {{$total->symbol}}</span> <br>
                            @endif
                        @endforeach
                        
                        <hr>
                        @php
                            $paxtotal = 0;
                        @endphp
                        
                        @foreach($dataTypeContent as $key2 => $data2)
                            @php
                                $paxtotal = $paxtotal + $data2->pax_total;
                            @endphp
                        @endforeach
                            <span style="font-size: 20px; font-weight: 500;">Pax : </span> <span>{{$paxtotal}} personnes</span>
                    </div>
                </div>
            </div>



            <div class="col-md-12">

                <div class="panel panel-bordered">

                    <div class="panel-body">

                        @if ($isServerSide)

                            <form method="get" class="form-search">



                                <div id="search-input">



                                    <select id="search_key" name="key">
                                        <option value="">All</option>
                                        <option value="ref_reservation">Ref Reservation </option>
                                        <option value="client_nom">Client Nom </option>
                                        <option value="hotel_nom">Hotel Nom </option>
                                    </select>



                                    <select id="filter" name="filter">
                                        <option value="contains" @if($search->filter == "contains"){{ 'selected' }}@endif>contains</option>
                                        <option value="equals" @if($search->filter == "equals"){{ 'selected' }}@endif>=</option>
                                    </select>



                                    <div class="input-group col-md-12">
                                        <input type="text" class="form-control" placeholder="{{ __('voyager::generic.search') }}" name="s" value="{{ $search->value }}">
                                        <span class="input-group-btn" style="width: 5%;">
                                            <button class="btn btn-info btn-lg" type="submit">
                                                <i class="voyager-search"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>

                                @if (Request::has('sort_order') && Request::has('order_by'))
                                    <input type="hidden" name="sort_order" value="{{ Request::get('sort_order') }}">

                                    <input type="hidden" name="order_by" value="{{ Request::get('order_by') }}">

                                @endif

                            </form>

                        @endif



                        <div class="table-responsive">
                            <table id="dataTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        @can('delete',app($dataType->model_name))
                                            <th>
                                                <input type="checkbox" name="select_all_reserv" class="select_all">
                                            </th>

                                        @endcan

                                        @foreach($dataType->browseRows as $row)

                                        <th>
                                            @if ($isServerSide)
                                                <a href="{{ $row->sortByUrl($orderBy, $sortOrder) }}">
                                            @endif

                                            @if($row->display_name == 'Civilité')

                                                Driver 

                                            @elseif($row->display_name == 'tours')

                                                To / Tours

                                            @else 

                                                {{ $row->display_name  }} 

                                            @endif

                                            





                                            @if ($isServerSide)



                                                @if ($row->isCurrentSortField($orderBy))



                                                    @if ($sortOrder == 'asc')



                                                        <i class="voyager-angle-up pull-right"></i>



                                                    @else



                                                        <i class="voyager-angle-down pull-right"></i>



                                                    @endif



                                                @endif



                                                </a>



                                            @endif



                                        </th>
                                        @if($row->display_name == 'Driver')
                                        <th>
                                            Changer driver
                                        </th>
                                        @endif


                                        @endforeach

                                        <th class="actions text-right">{{ __('voyager::generic.actions') }}</th>



                                    </tr>



                                </thead>



                                <tbody>

                                    @php

                                    $s = [



                                        "confirmed"=>"",

                                        "pending"=>"warning",

                                        "canceled"=>"danger",

                                        "noshow"=>"primary",

                                        ""=> "info",
                                        "gmail"=>"gmailgyg"

                                    ]

                                    @endphp

                                    @foreach($dataTypeContent as $key => $data)
    
                                    
                                    
                                    
                                @if($data->chauffeur_id == 13)
                                    @if($data->paiement_rest == 'cash' && $data->payment_statut != 1)
                                        <tr name="{{'copy'.$data->id}}" class="{{ $s[$data->status] }}" style="font-weight: bold;background-color: #46be8a;">
                                    @else
                                        <tr name="{{'copy'.$data->id}}" class="{{ $s[$data->status] }}" style="font-weight: bold;">
                                    @endif
                                    
                                @else
                                    @if($data->paiement_rest == 'cash' && $data->payment_statut != 1)
                                        <tr name="{{'copy'.$data->id}}" class="{{ $s[$data->status] }}" style="background-color: #46be8a;">
                                    @else
                                        <tr name="{{'copy'.$data->id}}" class="{{ $s[$data->status] }}">
                                    @endif
                                @endif


                                        @can('delete',app($dataType->model_name))



                                            <td>



                                               <input type="checkbox" name="row_id" id="checkbox_{{ $data->getKey() }}" value="{{ $data->getKey() }}">



                                            </td>



                                        @endcan



                                        <?php 

                                         $to = '';

                                        ?>

                                        @foreach($dataType->browseRows as $row)
                                            @php
                                            if ($data->{$row->field.'_browse'}) {

                                                $data->{$row->field} = $data->{$row->field.'_browse'};

                                            }

                                            @endphp



                                            <td name="copyline">

                                                @if($row->field == "prixtotal")

                                                    <span>{{ $data->prixtotal }} {{ $data->devise_symbol() }}</span>

                                                @elseif( $row->field == "hotel_adresse")

                                                    <div style="width: 150px">

                                                        {{ $data->hotel_adresse }}

                                                    </div>

                                                @elseif( $row->field == "hotel_tel")

                                                    <div style="width: 120px">

                                                        {{ $data->hotel_tel }}

                                                    </div>

                                                @elseif (isset($row->details->view))



                                                    @include($row->details->view, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $data->{$row->field}, 'action' => 'browse'])



                                                @elseif($row->type == 'image')



                                                    <img src="@if( !filter_var($data->{$row->field}, FILTER_VALIDATE_URL)){{ Voyager::image( $data->{$row->field} ) }}@else{{ $data->{$row->field} }}@endif" style="width:100px">



                                                @elseif($row->type == 'relationship')

                                                    @if($row->field == 'reservation_belongsto_chauffeur_relationship')

                                                    <div style="text-align: center;">

                                                        @include('voyager::formfields.relationship', ['view' => 'browse','options' => $row->details])

                                                    </div>
                                                        

                                                    @elseif($row->field == 'reservation_belongsto_tour_relationship')

                                                        {{$data->to_tour()}}

                                                    @else

                                                    @include('voyager::formfields.relationship', ['view' => 'browse','options' => $row->details])



                                                    @endif


                                                @elseif($row->type == 'select_multiple')

                                                    @if(property_exists($row->details, 'relationship'))

                                                        @foreach($data->{$row->field} as $item)

                                                            {{ $item->{$row->field} }}

                                                        @endforeach

                                                    @elseif(property_exists($row->details, 'options'))

                                                        @if (!empty(json_decode($data->{$row->field})))

                                                            @foreach(json_decode($data->{$row->field}) as $item)

                                                                @if (@$row->details->options->{$item})

                                                                    {{ $row->details->options->{$item} . (!$loop->last ? ', ' : '') }}

                                                                @endif

                                                            @endforeach

                                                        @else

                                                            {{ __('voyager::generic.none') }}

                                                        @endif

                                                    @endif

                                                    @elseif($row->type == 'multiple_checkbox' && property_exists($row->details, 'options'))

                                                        @if (@count(json_decode($data->{$row->field})) > 0)

                                                            @foreach(json_decode($data->{$row->field}) as $item)

                                                                @if (@$row->details->options->{$item})



                                                                    {{ $row->details->options->{$item} . (!$loop->last ? ', ' : '') }}



                                                                @endif


                                                            @endforeach



                                                        @else



                                                            {{ __('voyager::generic.none') }}



                                                        @endif







                                                @elseif(($row->type == 'select_dropdown' || $row->type == 'radio_btn'))


                                                    @if($row->field == 'client_civilite')

                                                        {{$data->chauffeur()}}
                                                    @else

                                                    {!! $row->details->options->{$data->{$row->field}} ?? '' !!}

                                                    @endif







                                                @elseif($row->type == 'date' || $row->type == 'timestamp')



                                                    {{ property_exists($row->details, 'format') ? \Carbon\Carbon::parse($data->{$row->field})->formatLocalized($row->details->format) : $data->{$row->field} }}

                                                @elseif($row->type == 'time')



                                                    {{ Carbon\Carbon::parse($data->{$row->field})->format('H:i')}}



                                                @elseif($row->type == 'checkbox')



                                                    @if(property_exists($row->details, 'on') && property_exists($row->details, 'off'))



                                                        @if($data->{$row->field})



                                                            <span class="label label-info">{{ $row->details->on }}</span>



                                                        @else



                                                            <span class="label label-primary">{{ $row->details->off }}</span>



                                                        @endif



                                                    @else



                                                    {{ $data->{$row->field} }}



                                                    @endif



                                                @elseif($row->type == 'color')



                                                    <span class="badge badge-lg" style="background-color: {{ $data->{$row->field} }}">{{ $data->{$row->field} }}</span>



                                                @elseif($row->type == 'text')

                                                  @if($row->field == 'pax_total')

                                                    <ul name="detailspax" style="list-style: none;padding:0">

                                                        <li style="width: 50px;"> {{ $data->pax_adulte }} Ad</li>

                                                        @if($data->pax_enfant12 > 0)

                                                        <li>{{ $data->pax_enfant12 }} Enf</li>

                                                        @endif

                                                        @if($data->pax_enfant3 > 0)

                                                        <li>{{ $data->pax_enfant3 }} B</li>

                                                        @endif

                                                    </ul>

                                                  @else

                                                    @include('voyager::multilingual.input-hidden-bread-browse')



                                                    <div>{{ mb_strlen( $data->{$row->field} ) > 200 ? mb_substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}</div>

                                                  @endif

                                                @elseif($row->type == 'text_area')



                                                    @include('voyager::multilingual.input-hidden-bread-browse')



                                                    <div>{{ mb_strlen( $data->{$row->field} ) > 200 ? mb_substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}</div>



                                                @elseif($row->type == 'file' && !empty($data->{$row->field}) )



                                                    @include('voyager::multilingual.input-hidden-bread-browse')



                                                    @if(json_decode($data->{$row->field}))



                                                        @foreach(json_decode($data->{$row->field}) as $file)



                                                            <a href="{{ Storage::disk(config('voyager.storage.disk'))->url($file->download_link) ?: '' }}" target="_blank">



                                                                {{ $file->original_name ?: '' }}



                                                            </a>



                                                            <br/>



                                                        @endforeach



                                                    @else



                                                        <a href="{{ Storage::disk(config('voyager.storage.disk'))->url($data->{$row->field}) }}" target="_blank">



                                                            Download



                                                        </a>



                                                    @endif



                                                @elseif($row->type == 'rich_text_box')



                                                    @include('voyager::multilingual.input-hidden-bread-browse')



                                                    <div>{{ mb_strlen( strip_tags($data->{$row->field}, '<b><i><u>') ) > 200 ? mb_substr(strip_tags($data->{$row->field}, '<b><i><u>'), 0, 200) . ' ...' : strip_tags($data->{$row->field}, '<b><i><u>') }}</div>



                                                @elseif($row->type == 'coordinates')



                                                    @include('voyager::partials.coordinates-static-image')



                                               



                                                @elseif($row->type == 'multiple_images')



                                                    @php $images = json_decode($data->{$row->field}); @endphp



                                                    @if($images)



                                                        @php $images = array_slice($images, 0, 3); @endphp



                                                        @foreach($images as $image)



                                                            <img src="@if( !filter_var($image, FILTER_VALIDATE_URL)){{ Voyager::image( $image ) }}@else{{ $image }}@endif" style="width:50px">



                                                        @endforeach



                                                    @endif



                                                @elseif($row->type == 'media_picker')



                                                    @php



                                                        if (is_array($data->{$row->field})) {



                                                            $files = $data->{$row->field};



                                                        } else {



                                                            $files = json_decode($data->{$row->field});



                                                        }



                                                    @endphp



                                                    @if ($files)



                                                        @if (property_exists($row->details, 'show_as_images') && $row->details->show_as_images)



                                                            @foreach (array_slice($files, 0, 3) as $file)



                                                            <img src="@if( !filter_var($file, FILTER_VALIDATE_URL)){{ Voyager::image( $file ) }}@else{{ $file }}@endif" style="width:50px">



                                                            @endforeach



                                                        @else



                                                            <ul>



                                                            @foreach (array_slice($files, 0, 3) as $file)



                                                                <li>{{ $file }}</li>



                                                            @endforeach



                                                            </ul>



                                                        @endif



                                                        @if (count($files) > 3)



                                                            {{ __('voyager::media.files_more', ['count' => (count($files) - 3)]) }}



                                                        @endif



                                                    @elseif (is_array($files) && count($files) == 0)



                                                        {{ trans_choice('voyager::media.files', 0) }}



                                                    @elseif ($data->{$row->field} != '')



                                                        @if (property_exists($row->details, 'show_as_images') && $row->details->show_as_images)



                                                            <img src="@if( !filter_var($data->{$row->field}, FILTER_VALIDATE_URL)){{ Voyager::image( $data->{$row->field} ) }}@else{{ $data->{$row->field} }}@endif" style="width:50px">



                                                        @else



                                                            {{ $data->{$row->field} }}



                                                        @endif



                                                    @else



                                                        {{ trans_choice('voyager::media.files', 0) }}



                                                    @endif



                                                @else



                                                    @include('voyager::multilingual.input-hidden-bread-browse')



                                                    <span>{{ $data->{$row->field} }}</span>



                                                @endif



                                            </td>   
                                             @if($row->field == 'reservation_belongsto_chauffeur_relationship')                                         
                                            <td>
                                                
                                                        <input type="hidden" value="{{ $data->id }}" id="mybookingid">

                                                        <select class="form-control" name="chauffeur_id" onchange="updatedriver(this.value , {{$data->id}})">

                                                          @foreach ($chauffeurs as $value)

                                                            <option value="{{ $value->id }}" <?php if ($value->id == $data->chauffeur_id) {echo 'selected' ;}?> > 

                                                                {{ $value->nom }} 

                                                            </option>

                                                          @endforeach

                                                        </select>
                                            </td>
                                            @endif


                                        @endforeach



                                      <!-- <td>

                                           <select class="form-control" name="status_id" onchange="updatestatus(this.value , {{$data->id}})">

                                                <option value="">Aucun</option>

                                                <option value="pending" {{ $data->status == "pending" ? 'selected' : '' }}>Pending</option>

                                                <option value="confirmed" {{ $data->status == "confirmed" ? 'selected' : '' }}>Confirmed</option>

                                                <option value="canceled" {{ $data->status == "canceled" ? 'selected' : '' }}>Canceled</option>

                                                <option value="noshow" {{ $data->status == "noshow" ? 'selected' : '' }}>No Show</option>

                                            </select>

                                       </td>-->



                                        <td class="no-sort no-click" id="bread-actions">



                                            @foreach(Voyager::actions() as $action)

                                                

                                                @if (!method_exists($action, 'massAction'))

                                                    @include('voyager::bread.partials.actions', ['action' => $action])

                                                @endif



                                            @endforeach



                                            <a href="{{ url('reservations/imprimer/pancarte/'.$data->id) }}" target="_blank" data-placement="bottom" data-toggle="tooltip" title="" class="btn btn-sm btn-success pull-right pancarte" data-original-title="Pancarte">
                                                <i class="voyager-receipt"></i> 
                                            </a>
                                            <a href="#" class="btn btn-sm btn-info send-confirmation-email" data-id="{{ $data->id }}">
                                               
                                                <i class="voyager-mail"></i>
                                            </a>

                                            <button data-toggle="tooltip" data-placement="left" data-original-title="Copier" class="btn btn-sm btn-success pull-right" onclick="copyToClipboard({{$data->id}})" style="margin-right: 5px;">

                                                <i class="voyager-scissors"></i>

                                            </button>







                                        </td>



                                    </tr>



                                    @endforeach



                                </tbody>



                            </table>



                        </div>



                        @if ($isServerSide)



                            <div class="pull-left">



                                <div role="status" class="show-res" aria-live="polite">{{ trans_choice(



                                    'voyager::generic.showing_entries', $dataTypeContent->total(), [



                                        'from' => $dataTypeContent->firstItem(),



                                        'to' => $dataTypeContent->lastItem(),



                                        'all' => $dataTypeContent->total()



                                    ]) }}</div>



                            </div>



                            <div class="pull-right">



                                {{ $dataTypeContent->appends([



                                    's' => $search->value,



                                    'filter' => $search->filter,



                                    'key' => $search->key,



                                    'order_by' => $orderBy,



                                    'sort_order' => $sortOrder,



                                    'showSoftDeleted' => $showSoftDeleted,

                                    'selected_ids' => request()->get('selected_ids'),
                                    'date' => request()->get('date'),
                                    'type' => request()->get('type'),
                                    'from' => request()->get('from'),
                                    'tour' => request()->get('tour'),
                                    'statut' => request()->get('statut'),
                                    'agency' => request()->get('agency'),


                                ])->links() }}



                            </div>



                        @endif



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



    



     {{-- Single Edit Commercial modal --}}



     



    <div class="modal fade" id="favoritesModal" tabindex="-1" role="dialog" aria-labelledby="favoritesModalLabel">



      <div class="modal-dialog" role="document">



        <div class="modal-content">



         <form action="{{ url('reservations/edit_commercial') }}" method="POST" enctype="multipart/form-data">



            {{ csrf_field() }}



            <input type="hidden" value="" id="mycommercialid" name="mybookingid">



                   <div class="modal-header">



                        <button type="button" class="close"  data-dismiss="modal" aria-label="Close">



                            <span aria-hidden="true">&times;</span></button>



                        <h4 class="modal-title" id="favoritesModalLabel"> Changer commercial</h4>



                    </div>



                    <div class="modal-body">



                        <div class="form-group">



                            <select class="form-control" id="selectbox" name="commercial">







                              @foreach ($chauffeurs as $value)



                                <option value="{{ $value->id }}" > 



                                    {{ $value->nom }} 



                                </option>



                              @endforeach>



                            </select>



                        </div>



                        <div class="form-group" id="commission_radio">



                            <label class="radio-inline"><input type="radio" value="1" name="commissionstatut" checked >Avec commission</label>



                            <label class="radio-inline"><input type="radio" value="0" name="commissionstatut">Sans commission</label>



                        </div>



                    </div>



                    <div class="modal-footer">



                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>



                        <span class="pull-right">



                            <button type="submit" class="btn btn-primary">Save</button>



                        </span>



                    </div>



          </form>



        </div>



    </div>



   </div>



   



    {{-- Single Edit Chauffeur modal --}}



     



    <div class="modal fade" id="favoritesModal02" tabindex="-1" role="dialog" aria-labelledby="favoritesModalLabel02">



      <div class="modal-dialog" role="document">



        <div class="modal-content">



         <form action="{{ url('reservations/edit_chauffeur') }}" method="POST" enctype="multipart/form-data">



            {{ csrf_field() }}



            <input type="hidden" value="" id="mychauffeurid" name="mybookingid">



                   <div class="modal-header">



                        <button type="button" class="close"  data-dismiss="modal" aria-label="Close">



                            <span aria-hidden="true">&times;</span></button>



                        <h4 class="modal-title" id="favoritesModalLabel"> Changer chauffeur</h4>



                    </div>



                    <div class="modal-body">



                        <select class="form-control" id="selectbox2" name="chauffeur">



                          @foreach ($chauffeurs as $value)



                            <option value="{{ $value->id }}"  > 



                                {{ $value->nom }} 



                            </option>



                          @endforeach>



                        </select>



                    </div>



                    <div class="modal-footer">



                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>



                        <span class="pull-right">



                            <button type="submit" class="btn btn-primary">Save</button>



                        </span>



                    </div>



          </form>



        </div>



    </div>



   </div>

    <!-- Dots Loader -->
    <div class="loader-container" id="dotsLoader" style="display: none;">
        <div class="loader-dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>
    
@stop







@section('css')



<style>

    .table td , .table th {border: 1px solid #ccc;}

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

    #bread-actions .btn {

        padding: 3px 6px !important

    }

    #bread-actions a:last-child {

      margin-right: 4px;

}

        .loader-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loader {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Optional: Different loader styles */
        .loader-dots {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loader-dots .dot {
            width: 10px;
            height: 10px;
            margin: 0 5px;
            background: #3498db;
            border-radius: 50%;
            animation: bounce 0.5s ease-in infinite alternate;
        }

        .loader-dots .dot:nth-child(2) {
            animation-delay: 0.1s;
        }

        .loader-dots .dot:nth-child(3) {
            animation-delay: 0.2s;
        }

        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }

</style>







@if(!$dataType->server_side && config('dashboard.data_tables.responsive'))



    <link rel="stylesheet" href="{{ voyager_asset('lib/css/responsive.dataTables.min.css') }}">



@endif







<link rel ="stylesheet" type="text/css" href="{{ asset('css/daterangepicker.css') }}"/>



<style>



    @media (min-width:768px){



        .btn-f {margin-top:27px;}



    }    



</style>







@stop







@section('javascript')



    <!-- DataTables -->



    @if(!$dataType->server_side && config('dashboard.data_tables.responsive'))



        <script src="{{ voyager_asset('lib/js/dataTables.responsive.min.js') }}"></script>



    @endif



    <script type="text/javascript" src="{{ asset('js/dataTables.buttons.min.js') }}"></script>



    <script type="text/javascript" src="{{ asset('js/jszip.min.js') }}"></script>



    <script type="text/javascript" src="{{ asset('js/pdfmake.min.js') }}"></script>



    <script type="text/javascript" src="{{ asset('js/vfs_fonts.js') }}"></script>



    <script type="text/javascript" src="{{ asset('js/buttons.html5.min.js') }}"></script>







    <script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>



    <script type="text/javascript" src="{{ asset('js/daterangepicker.min.js') }}"></script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    



    <script>



      $('#favoritesModal').on('show.bs.modal', function (event) {

          var button = $(event.relatedTarget) 

          var id = button.data('id')

          var optionValue =  button.data('commercial')

          var radioption =  button.data('commission')

          console.log(radioption)


          $("#selectbox").val(optionValue).find("option[value=" + optionValue +"]").attr('selected', true);

          $("input[name=commissionstatut][value=" + radioption + "]").prop('checked', true);

          var modal = $(this)

          modal.find('.modal-content #mycommercialid').val(id);

      });



      $('#favoritesModal02').on('show.bs.modal', function (event) {


          var button = $(event.relatedTarget) 

          var id = button.data('id')

          var optionValue =  button.data('chauffeur')

          console.log(optionValue)

          var modal = $(this)



          modal.find('.modal-content #mychauffeurid').val(id);



          $("#selectbox2").val(optionValue).find("option[value=" + optionValue +"]").attr('selected', true);



      });



    </script>



    <script>







        $(document).ready(function () {

            $('#dataTable .edit').attr('target', '_blank');
            $('[data-toggle="tooltip"]').tooltip();

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

            @if(request()->get('date') !== null)
            var range = '{{ request()->get('date') }}';
            var split = range.split(' | ');
            //console.log(split)
            $('#daterange').data('daterangepicker').setStartDate(split[0]);
            $('#daterange').data('daterangepicker').setEndDate(split[1])
            @endif
            var ids = [];
            var table = $('#dataTable').DataTable({
                dom:"Brt",
                paging:false,
                buttons:[



                    {



                        text: 'Imprimer',



                        action: function ( e, dt, node, config ) {



                            var ids_res = $('#selected_ids').val();



                            //console.log(ids_res);



                            if(ids_res == ''){



                                console.log('hello1')



                                var date = $('input[name="date"]').val() ? $('input[name="date"]').val() : null ;



                                var type = $('select[name="type"]').val() ? $('select[name="type"]').val() : null;



                                var chauffeur = $('select[name="chauffeur"]').val() ? $('select[name="chauffeur"]').val() : null;



                                ids_res = null ;



                                var url = '{{ url('') }}/reservations/imprimer/liste/non/'+date+"/"+type+"/"+chauffeur+"/"+ids_res;



                            }else {



                                console.log(ids_res)



                                var date = null ;



                                var type = $('select[name="type"]').val() ? $('select[name="type"]').val() : null;



                                var chauffeur = $('select[name="chauffeur"]').val() ? $('select[name="chauffeur"]').val() : null;



                                var url = '{{ url('') }}/reservations/imprimer/liste/non/'+date+"/"+type+"/"+chauffeur+"/"+ids_res;



                            }



                            window.open(url);



                        }



                    },
                    {
                        text: 'Imprimer avec pancarte',
                        action: function ( e, dt, node, config ) {
                            var ids_res = $('#selected_ids').val();
                            if(ids_res == ''){
                                var date = $('input[name="date"]').val() ? $('input[name="date"]').val() : null ;
                                var type = $('select[name="type"]').val() ? $('select[name="type"]').val() : null;
                                var chauffeur = $('select[name="chauffeur"]').val() ? $('select[name="chauffeur"]').val() : null;
                                ids_res = null ;
                                var url = '{{ url('') }}/reservations/imprimer/liste/oui/'+date+"/"+type+"/"+chauffeur+"/"+ids_res;
                            }else {

                                var date = null ;
                                var type = $('select[name="type"]').val() ? $('select[name="type"]').val() : null;
                                var chauffeur = $('select[name="chauffeur"]').val() ? $('select[name="chauffeur"]').val() : null;
                                var url = '{{ url('') }}/reservations/imprimer/liste/oui/'+date+"/"+type+"/"+chauffeur+"/"+ids_res;
                            }
                            window.open(url);
                        }
                    },
                   {
                        text: 'Send Emails',
                        action: function (e, dt, node, config) {
                            var ids_res = $('#selected_ids').val(); // Assume selected IDs are stored here
                            if (ids_res != '') {
                                // Convert the selected IDs string into an array
                                //var idsArray = ids_res.split(',');
                                
                                // Show loader
                                Swal.fire({
                                    title: 'Sending Emails',
                                    text: 'Please wait while we send emails...',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                    
                                // Perform the AJAX request
                                $.ajax({
                                    url: '{{ url('reservations/sendmultiemail') }}',
                                    method: 'GET',
                                    data: { ids: ids_res }, // Pass the IDs as a parameter
                                    success: function (response) {
                                        Swal.close();
                                        // Handle success response
                                        if (response.success) {
                                            Swal.fire(
                                                'Success',
                                                response.message,
                                                'success'
                                            );
                                        } else {
                                            Swal.fire(
                                                'Partial Success',
                                                response.message,
                                                'warning'
                                            );
                                            console.log('Failed Emails:', response.failedEmails);
                                        }
                                    },
                                    error: function (xhr, status, error) {
                                        Swal.close();
                                        // Handle error response
                                        Swal.fire(
                                            'Error',
                                            "Échec de l'envoi des courriels. Veuillez réessayer plus tard.",
                                            'error'
                                        );
                                        console.error('Error:', error);
                                    }
                                });
                            } else {
                                Swal.fire(
                                    'Pas de sélection',
                                    "Veuillez sélectionner au moins une réservation pour l'envoi de courriels.",
                                    'info'
                                );
                            }
                        }
                    },
                    {
                        extend: 'excel',
                        exportOptions: {
                            columns: [ 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19]
                        }
                    },
                    'pdf'



                ],
                columnDefs: [{



                    targets: -1,



                    searchable:false, 



                    orderable:false



                }],
            });

            $('#search-input select').select2({



                minimumResultsForSearch: Infinity



            });

            @if ($isModelTranslatable)
                $('.side-body').multilingual();
                //Reinitialise the multilingual features when they change tab
                $('#dataTable').on('draw.dt', function(){
                    $('.side-body').data('multilingual').init();
                })
            @endif



            $('.select_all').on('click', function(e) {
                $('input[name="row_id"]').prop('checked', $(this).prop('checked'));
            });


            $('.send-confirmation-email').on('click', function(e) {
                e.preventDefault();
                
                var reservationId = $(this).data('id');
                var button = $(this);
                
                // Add loading spinner
                //button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
                button.prop('disabled', true);
                
                $.ajax({
                    url: "{{ url('/reservations') }}/" + reservationId + "/send-confirmation",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        // Show loader before request
                        $('#dotsLoader').show();
                    },
                    success: function(response) {
                        toastr.success(response.message);
                        //button.html('Send Confirmation Email');
                        button.prop('disabled', false);
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON.message || 'An error occurred';
                        toastr.error(errorMessage);
                        //button.html('Send Confirmation Email');
                        button.prop('disabled', false);
                    },
                    complete: function() {
                        // Hide loader after request completes
                       // hideLoader();
                       $('#dotsLoader').hide();
                        
                    }
                });
            });
            

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



                                  



        $('input[type="checkbox"][name="select_all_reserv"]').on('change', function () {



            console.log('heloooo');



            if($(this).is(":checked")){



            console.log('hello2')



            var ids = [];



           $('input[name="row_id"]').each(function() {



                if ($(this).is(':checked')) {



                    ids.push($(this).val());



                    console.log(ids);



                }



            });



                                    



            }



        $('#selected_ids').val(JSON.stringify(ids));



        });



        $('input[name="row_id"]').on('change', function () {



            var ids = [];



            $('input[name="row_id"]').each(function() {



                if ($(this).is(':checked')) {



                    ids.push($(this).val());



                    console.log(ids);



                }



            });



        $('#selected_ids').val(JSON.stringify(ids));



        });

    function copyToClipboard(element) {

      var $temp = $("<input>");
      $("body").append($temp);

      var text = 'Reservation : ';
      fulltext = '';
      var myname = 'copy'+element ; 

        

      $("tr[name="+myname+"] td[name='copyline'] ").each(function() {

        var select = $(this).find('select[name="chauffeur_id"]');

        var pax = $(this).find('ul[name="detailspax"]');

        if(select.length > 0){

            text  += $.trim(select.eq(0).find('option:selected').text())+' / ';

        }else if(pax.length > 0 ){

            var paxdetail = '';

            pax.children().each(function(){
                paxdetail += $(this).text()+' | ' ;
            })
            text  += $.trim(paxdetail.replace(/\n/ig, ''))+' / ';
        }

        else{

        text  += $.trim($(this).text().replace(/\n/ig, ''))+' / ';
          
        }

      })

      var trimStr = $.trim(text);

      console.log(trimStr);

      var arr = trimStr.split("/");
      var check = [3,4,5,2,7,9,8,21,12,11,22,14,17,10];


       $.each( check, function( index, value ){

          $.each( arr, function( key, val ){

            if(value == key){

                fulltext += val+', ' ;  

            }

          });

        });

       var newline = $.trim(fulltext);

      $temp.val(newline).select();

      document.execCommand("copy");

      $temp.remove();

    }



function updatedriver(chauffeur , id){

    $.ajaxSetup({

      headers: {

          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

      }

    });

    $.ajax({

        type:'POST',

        url:"{{ route('Updatedriver') }}",

        data:{"_token": "{{ csrf_token() }}" , "mybookingid": id , "chauffeur_id" : chauffeur },

        success:function(data){

            console.log('goood')

        }

    });

}



function updatestatus(status , id){

    $.ajaxSetup({

      headers: {

          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

      }

    });

    $.ajax({

        type:'POST',

        url:"{{ route('Updatestatus') }}",

        data:{"_token": "{{ csrf_token() }}" , "id": id , "status" : status },

        success: function(data) {

            var s = {

                "confirmed":"",

                "pending":"warning",

                "canceled":"danger",

                "noshow":"danger"

            }

            $('tr[name="copy'+id+'"]').removeClass().addClass(s[status]);

            console.log('updated');

        }

    });

}

</script>



@stop



