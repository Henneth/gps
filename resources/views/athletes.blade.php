@extends('app')

@section('htmlheader_title')
    Athletes
@endsection

@section('contentheader_title')
    Athletes
@endsection

@section('main-content')
    @include('partials/alerts')
    <div class="container-flex">
        <div class="box">
            <div class="box-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Athlete ID</th>
                            <th>Bib Number</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Country</th>
                            <th>Colour Code</th>
                            <th style="width: 64px;">&nbsp;</th>
                            {{-- <th style="width: 40px">Label</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <form id="form_add" method="post" action="{{url('/')}}/event/{{$event_id}}/athletes/add">
                                {{ csrf_field() }}
                                <td>&nbsp;</td>
                                <td><input form="form_add" class="form-control" name="bib_number" placeholder="Bib Number"></td>
                                <td><input form="form_add" class="form-control" name="first_name" placeholder="First Name"></td>
                                <td><input form="form_add" class="form-control" name="last_name" placeholder="Last Name"></td>
                                <td>@include('partials/countries-dropdown')</td>
                                <td><input form="form_add" type="color" id="html5colorpicker" class="form-control" onchange="clickColor(0, -1, -1, 5)" name="colour_code" value="#0000ff" style="width:100%;"></td>
                                <td><button form="form_add" type="submit" class="btn btn-primary">Add</button></td>
                            </form>
                        </tr>
                        @foreach ($athletes as $key => $athlete)
                            <tr>
                                <form id="form_edit_{{$athlete->athlete_id}}" method="post" action="{{url('/')}}/event/{{$event_id}}/athletes/edit">
                                    {{ csrf_field() }}
                                    <input form="form_edit_{{$athlete->athlete_id}}" type="hidden" name="athlete_id" value="{{$athlete->athlete_id}}"></input>
                                    <td>{{$athlete->athlete_id}}</td>
                                    <td class="text"><span>{{$athlete->bib_number}}</span><input form="form_edit_{{$athlete->athlete_id}}" class="form-control" style="display: none;" name="bib_number" value="{{$athlete->bib_number}}" placeholder="Bib Number"></td>
                                    <td class="text"><span>{{$athlete->first_name}}</span><input form="form_edit_{{$athlete->athlete_id}}" class="form-control" style="display: none;" name="first_name" value="{{$athlete->first_name}}" placeholder="First Name"></td>
                                    <td class="text"><span>{{$athlete->last_name}}</span><input form="form_edit_{{$athlete->athlete_id}}" class="form-control" style="display: none;" name="last_name" value="{{$athlete->last_name}}" placeholder="Last Name"></td>
                                    <td class="country_code" data-code="{{$athlete->country_code}}"><span>{{$athlete->country}}</span><div style="display: none;">@include('partials/countries-dropdown')</div></td>
                                    <td class="colour_code">
                                        @if ($athlete->colour_code)
                                            <div style="padding-left: 4px; background: {{$athlete->colour_code}}">{{$athlete->colour_code}}</div>
                                        @endif
                                        <input form="form_edit_{{$athlete->athlete_id}}" type="color" id="html5colorpicker" class="form-control" onchange="clickColor(0, -1, -1, 5)" name="colour_code" value="{{!empty($athlete->colour_code) ? $athlete->colour_code : '#0000ff'}}" style="display: none; width:100%;">
                                    </td>
                                    <td><button type="button" class="edit-btn btn btn-default">Edit</button><button form="form_edit_{{$athlete->athlete_id}}" type="submit" class="btn btn-default" style="display: none;">Save</button></td>
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- /.box-body -->
            {{-- <div class="box-footer clearfix">
                <button type="submit" class="btn btn-primary">Save</button>
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

@section('js')
    <script>
        $('.edit-btn').click(function() {
            $(this).hide();
            var form = $(this).parent().parent();
            form.find('.text').each(function() {
                $(this).find('span').hide();
                $(this).find('input').show();
            });
            var countryCode = form.find('.country_code').attr('data-code');
            form.find('.country_code span').hide();
            form.find('.country_code div').show();
            form.find('.country_code div select').val(countryCode);
            form.find('.colour_code div').hide();
            form.find('.colour_code input').show();
            $(this).next().show();
        })
    </script>
@endsection
