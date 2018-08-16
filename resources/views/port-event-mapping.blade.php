@extends('app')

@section('htmlheader_title')
    Port/Event Mapping
@endsection

@section('contentheader_title')
    Port/Event Mapping
@endsection

@section('main-content')
    @include('partials/alerts')
    <div class="container-flex">
        <div class="box">
            <div class="box-body">
                <form id="mapping" method="post" action="{{url('/')}}/port-event-mapping/post">
                    {{ csrf_field() }}
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Ports</th>
                                <th>Events</th>
                            </tr>
                            <tr>
                                <td><span style="color:#3b8dbc;text-transform: uppercase;font-style: italic;font-weight: bold;">40000</span></td>
                                <td>
                                    <select class="form-control" disabled>
                                        <option value="">Achive</option>
                                    </select>
                                </td>
                            </tr>

                            <?php
                                $portsArray = ['40001', '40002', '40003', '40004', '40005'];
                            ?>
                            @for ($i=0; $i < sizeof($portsArray); $i++)
                                <tr>
                                    <td><span style="color:#3b8dbc;text-transform: uppercase;font-style: italic;font-weight: bold;">{{$portsArray[$i]}}</span></td>
                                    <td>
                                      <select class="form-control" name="{{$portsArray[$i]}}">
                                        <option value="">Achive (default)</option>
                                        @foreach ($events as $event)
                                            <option value="{{$event->event_id}}" {{ (array_key_exists($portsArray[$i], $mappingArray) ? $mappingArray[$portsArray[$i]] : null) == $event->event_id ? 'selected' : ''}}>{{$event->event_name}}</option>
                                        @endforeach
                                      </select>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Save</button>
                </from>
            </div>
            <!-- /.box-body -->
            {{-- <div class="box-footer clearfix">
                <ul class="pagination pagination-sm no-margin pull-right">
                    <li><a href="#">«</a></li>
                    <li><a href="#">1</a></li>
                    <li><a href="#">2</a></li>
                    <li><a href="#">3</a></li>
                    <li><a href="#">»</a></li>
                </ul>
            </div> --}}
        </div>
    </div>
@endsection
