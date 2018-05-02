@extends('app')

@section('htmlheader_title')
    Athletes
@endsection

@section('contentheader_title')
    Athletes
@endsection

@section('contentheader_class')
    display-inline-block
@endsection

@section('contentheader_right')
<div class="pull-right"><button class="btn btn-primary" onclick="toggleExcelImport();return false;"><i class="fas fa-upload"></i>&nbsp; Import from Excel</button></div>
@endsection

@section('main-content')
    @include('partials/alerts')
    <div class="container-flex">
        <div id="excelImportBox" class="box box-primary" style="display: none;">
            <div class="box-header with-border">
                <h3 class="box-title">Import from Excel</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form role="form" action="{{url('/')}}/event/{{$event_id}}/athletes/import-from-excel" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="box-body">
                    <div class="form-group">
                        <label for="excelFile">Excel file upload</label>
                        <input type="file" id="excelFile" name="fileToUpload">

                        <p class="help-block">.xls or .xlsx files only.</p>
                        <p class="help-block">Please refer to this example excel file for the required format: <a href={{ asset('examples/athletes_example.xls') }}>athletes_example.xls</a>.</p>
                    </div>
                </div>
                <!-- /.box-body -->

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
        <div class="box">
            <div class="box-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Athlete ID</th>
                            <th>Bib Number</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Chinese Full Name</th>
                            <th>Is Public</th>
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
                                {{-- <td>@include('partials/countries-dropdown')</td> --}}
                                <td><input form="form_add" class="form-control" name="zh_full_name" placeholder="Chinese Full Name"></td>
                                <td>
                                    <div>
                                        <input class="tgl tgl-ios" id="1" name="is_public" type="checkbox" checked="checked"/>
                                        <label class="tgl-btn" for="1"></label>
                                    </div>
                                </td>
                                <td>
                                    <select name="country_code" class="form-control">
                                        <option disabled selected>---- Select a country ----</option>
                                        @foreach($countries as $country)
                                            <option value="{{$country->code}}">{{$country->country}}</option>
                                        @endforeach
                                    </select>
                                </td>

                                <td>
                                    <input type="text" form="form_add" name="colour_code" class="pick-a-color form-control">
                                </td>
                                {{-- <td><input form="form_add" type="color" id="html5colorpicker" class="form-control" onchange="clickColor(0, -1, -1, 5)" name="colour_code" value="#0000ff" style="width:100%;"></td> --}}
                                <td><button form="form_add" type="submit" class="btn btn-primary">Add</button></td>
                            </form>
                        </tr>
                        <?php $count = 2 ?>
                        @foreach ($athletes as $key => $athlete)
                            <tr>
                                <form id="form_edit_{{$athlete->athlete_id}}" method="post" action="{{url('/')}}/event/{{$event_id}}/athletes/edit">
                                    {{ csrf_field() }}
                                    <input form="form_edit_{{$athlete->athlete_id}}" type="hidden" name="athlete_id" value="{{$athlete->athlete_id}}"></input>
                                    <td>{{$athlete->athlete_id}}</td>
                                    <td class="text"><span>{{$athlete->bib_number}}</span><input form="form_edit_{{$athlete->athlete_id}}" class="form-control" style="display: none;" name="bib_number" value="{{$athlete->bib_number}}" placeholder="Bib Number"></td>
                                    <td class="text"><span>{{$athlete->first_name}}</span><input form="form_edit_{{$athlete->athlete_id}}" class="form-control" style="display: none;" name="first_name" value="{{$athlete->first_name}}" placeholder="First Name"></td>
                                    <td class="text"><span>{{$athlete->last_name}}</span><input form="form_edit_{{$athlete->athlete_id}}" class="form-control" style="display: none;" name="last_name" value="{{$athlete->last_name}}" placeholder="Last Name"></td>
                                    <td class="text"><span>{{$athlete->zh_full_name}}</span><input form="form_edit_{{$athlete->athlete_id}}" class="form-control" style="display: none;" name="zh_full_name" value="{{$athlete->zh_full_name}}" placeholder="zh_full_name"></td>
                                    <td>
                                        <div>
                                            <input class="tgl tgl-ios" name="is_public" id="{{$count}}" type="checkbox"  {{($athlete->is_public == 1) ? ' checked="checked" ' :''}}/>
                                            <label class="tgl-btn" for="{{$count}}"></label>
                                        </div>
                                    </td>
                                    <td class="country_code" data-code="{{$athlete->country_code}}"><span>{{$athlete->country}}</span><div style="display: none;">
                                        <select name="country_code" class="form-control">
                                            @foreach($countries as $country)
                                                <option value="{{$country->code}}">{{$country->country}}</option>
                                            @endforeach
                                        </select>
                                    </div></td>
                                    <td class="colour_code">
                                        @if ($athlete->colour_code)
                                            <div class="read-only" style="padding-left: 4px; background: #{{$athlete->colour_code}}">#{{$athlete->colour_code}}</div>
                                        @endif
                                        <div class="editable" style="display: none; width:100%;">
                                            <input type="text" form="form_edit_{{$athlete->athlete_id}}" name="colour_code" class="pick-a-color form-control" value="{{!empty($athlete->colour_code) ? $athlete->colour_code : '#0000ff'}}">
                                        </div>

                                       {{--  <input form="form_edit_{{$athlete->athlete_id}}" type="color" id="html5colorpicker" class="form-control" onchange="clickColor(0, -1, -1, 5)" name="colour_code" value="{{!empty($athlete->colour_code) ? $athlete->colour_code : '#0000ff'}}" style="display: none; width:100%;"> --}}
                                    </td>
                                    <td><button type="button" class="edit-btn btn btn-default">Edit</button><button form="form_edit_{{$athlete->athlete_id}}" type="submit" class="btn btn-default" style="display: none;">Save</button></td>
                                </form>
                            </tr>
                        <?php $count++ ?>
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
            form.find('.colour_code div.read-only').hide();
            form.find('.colour_code div.editable').show();
            $(this).next().show();
        })
        function toggleExcelImport() {
            $('#excelImportBox').toggle();
        }
        $(document).ready(function () {
            $(".pick-a-color").pickAColor({
                showSpectrum            : true,
                showSavedColors         : true,
                saveColorsPerElement    : false,
                fadeMenuToggle          : true,
                showHexInput            : true,
                showBasicColors         : true,
                allowBlank              : true,
                inlineDropdown          : true
            });
        });
    </script>
@endsection
