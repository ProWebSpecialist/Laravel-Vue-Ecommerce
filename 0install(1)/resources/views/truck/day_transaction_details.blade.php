@extends('layout.main') 
@section('content')
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div> 
@endif
<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{trans('file.Transaction Details')}}</h3>
                <h3 class="text-center">{{trans('file.Maximum Capacity')}} [ {{$get_truck_data->capacity}} kg]</h3>
            </div>
            {!! Form::open(['route' => 'trucks.daytransaction', 'method' => 'post']) !!}
            <div class="row">
                <div class="col-md-4 offset-md-2 mt-3">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{trans('file.Choose Your Date')}}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <div class="input-group">
                                <input type="text" class="daterangepicker-field form-control" value="{{$start_date}} To {{$end_date}}" required />
                                <input type="hidden" name="start_date" value="{{$start_date}}" />
                                <input type="hidden" name="end_date" value="{{$end_date}}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mt-3">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{trans('file.Choose Trucks')}}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <select id="trucks_id" name="trucks_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" >
                               @foreach($truck_data as $key=>$truck)
                               <option 
                                @if($truck_id == $truck->id)
                                {{"selected"}}
                                @endif
                               value="{{$truck->id}}">{{$truck->name}}</option>
                               @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mt-3">
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">{{trans('file.submit')}}</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
            <div class="table-responsive mb-4">
                <table id="transation-table" class="table table-hover">
                    <thead>
                        <tr>
                            <th class="not-exported-transaction"></th>
                            <th>{{trans('file.product')}} {{trans('file.name')}}</th>
                            <th>{{trans('file.product')}} ({{trans('file.qty')}})</th> 
                            <th>{{trans('file.weight')}}</th>
                            <th>{{trans('file.Delivery')}} {{trans('file.reference')}}</th>                           
                            <th>{{trans('file.grand total')}}</th>                            
                            <th>{{trans('file.qty')}} * {{trans('file.weight')}}</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                         $total_grand=0;
                         $toal_qty_w=0;
                         ?>
                         
                        @foreach($lims_truck_data as $k=>$item)                       
                        <tr>
                            <?php 
                            $total_grand += $item->total;
                            $toal_qty_w += $item->qty*$item->weight;
                             ?>
                            <td>{{$k}}</td>
                            <td>{{$item->name}}</td>
                            <td>{{$item->qty}}</td>
                            <td>{{$item->weight}}</td>
                            <td>{{$item->reference_no}}</td>
                            <td>{{$item->total}}</td>                            
                            <td>{{$item->qty*$item->weight}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="tfoot active">                       
                        <tr>
                            <th></th>
                            <th>Total:</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>{{ number_format($total_grand,2)}}</th>
                            <th 
                            @if($toal_qty_w > $get_truck_data->capacity)
                            style="color:red"
                            @endif
                            >{{ number_format($toal_qty_w,2)}}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">

    $('#transation-table').DataTable( {
        "order": [],
        'columnDefs': [
            {
                "orderable": false,
                'targets': 0
            },
            {
                'checkboxes': {
                   'selectRow': true
                },
                'targets': 0
            }
        ],
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                exportOptions: {
                    columns: ':visible:Not(.not-exported-transaction)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                },
                footer:true
            },
            {
                extend: 'csv',
                exportOptions: {
                    columns: ':visible:Not(.not-exported-transaction)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                },
                footer:true
            },
            {
                extend: 'print',
                exportOptions: {
                    columns: ':visible:Not(.not-exported-transaction)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                },
                footer:true
            },
            {
                extend: 'colvis',
                columns: ':gt(0)'
            }
        ],
        drawCallback: function () {
            var api = this.api();
           console.log("tue");
        }
    } );
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });     
    $(".daterangepicker-field").daterangepicker({
    callback: function(startDate, endDate, period){
        var start_date = startDate.format('YYYY-MM-DD');
        var end_date = endDate.format('YYYY-MM-DD');
        var title = start_date + ' To ' + end_date;
        $(".daterangepicker-field").val(title);
        $('input[name="start_date"]').val(start_date);
        $('input[name="end_date"]').val(end_date);
    }
    });
</script>
@endsection