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
<div class="pull-right"><button class="btn btn-primary" onclick="toggleExcelImport();return false;" {{$is_live? 'disabled' : ''}}><i class="fas fa-upload"></i>&nbsp; Import from Excel</button></div>
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
                        <p class="help-block">Please refer to this example excel file for the required format: <a href={{ asset('examples/athletes_example.xlsx') }}>athletes_example.xlsx</a>.</p>
                    </div>
                </div>
                <!-- /.box-body -->

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary" {{$is_live? 'disabled' : ''}}>Submit</button>
                </div>
            </form>
        </div>
        <div class="box">
            <div class="box-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Bib Number</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Chinese Full Name</th>
                            <th>Gender</th>
                            <th>Country</th>
                            <th>Category</th>
                            <th>Is Public</th>
                            <th>Default Visibility</th>
                            <th style="width: 64px;">&nbsp;</th>
                            @if(!$is_live)
                                <th style="width: 64px;">&nbsp;</th>
                            @endif
                            {{-- <th style="width: 40px">Label</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <form id="form_add" method="post" action="{{url('/')}}/event/{{$event_id}}/athletes/add">
                                {{ csrf_field() }}
                                <td><input form="form_add" class="form-control" name="bib_number" placeholder="Bib Number" {{$is_live? 'disabled' : ''}}></td>
                                <td><input form="form_add" class="form-control" name="first_name" placeholder="First Name" {{$is_live? 'disabled' : ''}}></td>
                                <td><input form="form_add" class="form-control" name="last_name" placeholder="Last Name" {{$is_live? 'disabled' : ''}}></td>
                                {{-- <td>@include('partials/countries-dropdown')</td> --}}
                                <td><input form="form_add" class="form-control" name="zh_full_name" placeholder="Chinese Full Name" {{$is_live? 'disabled' : ''}}></td>
                                <td>
                                    <select name="gender" class="form-control" {{$is_live? 'disabled' : ''}}>
                                        <option disabled selected>---- Select a gender ----</option>
                                        <option value="M">Male</option>
                                        <option value="F">Female</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="country_code" class="form-control" {{$is_live? 'disabled' : ''}}>
                                        <option disabled selected>---- Select a country ----</option>
                                        @foreach($countries as $country)
                                            <option value="{{$country->code}}">{{$country->country}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input form="form_add" class="form-control" name="category" placeholder="Category" {{$is_live? 'disabled' : ''}}></td>
                                <td>
                                    <div>
                                        <input class="tgl tgl-ios" id="1" name="is_public" type="checkbox" checked="checked" {{$is_live? 'disabled' : ''}}/>
                                        <label class="tgl-btn" for="1"></label>
                                    </div>
                                </td>
                                <td>
                                    <select name="status" class="form-control" {{$is_live? 'disabled' : ''}}>
                                        <option value="visible">Visible</option>
                                        <option value="hidden">Hidden</option>
                                    </select>
                                </td>

                                {{-- <td>
                                    <input type="text" form="form_add" name="colour_code" class="pick-a-color form-control" {{$is_live? 'disabled' : ''}}>
                                </td> --}}
                                {{-- <td><input form="form_add" type="color" id="html5colorpicker" class="form-control" onchange="clickColor(0, -1, -1, 5)" name="colour_code" value="#0000ff" style="width:100%;"></td> --}}
                                <td><button form="form_add" type="submit" class="btn btn-primary" {{$is_live? 'disabled' : ''}}>Add</button></td>
                            </form>
                        </tr>
                        <?php $count = 2 ?>
                        @foreach ($athletes as $key => $athlete)
                            <tr>
                                <form id="form_edit_{{$athlete->bib_number}}" method="post" action="{{url('/')}}/event/{{$event_id}}/athletes/edit">
                                    {{ csrf_field() }}
                                    {{-- <input form="form_edit_{{$athlete->bib_number}}" type="hidden" name="athlete_id" value="{{$athlete->athlete_id}}"></input> --}}
                                    <td class="text"><span>{{$athlete->bib_number}}</span><input form="form_edit_{{$athlete->bib_number}}" class="form-control" style="display: none;" name="new_bib_number" value="{{$athlete->bib_number}}" placeholder="Bib Number" {{$is_live? 'disabled' : ''}}></td>
                                    <td class="text" hidden><span>{{$athlete->bib_number}}</span><input form="form_edit_{{$athlete->bib_number}}" class="form-control" style="display: none;" name="old_bib_number" value="{{$athlete->bib_number}}" placeholder="Bib Number"></td>
                                    <td class="text"><span>{{$athlete->first_name}}</span><input form="form_edit_{{$athlete->bib_number}}" class="form-control" style="display: none;" name="first_name" value="{{$athlete->first_name}}" placeholder="First Name"></td>
                                    <td class="text"><span>{{$athlete->last_name}}</span><input form="form_edit_{{$athlete->bib_number}}" class="form-control" style="display: none;" name="last_name" value="{{$athlete->last_name}}" placeholder="Last Name"></td>
                                    <td class="text"><span>{{$athlete->zh_full_name}}</span><input form="form_edit_{{$athlete->bib_number}}" class="form-control" style="display: none;" name="zh_full_name" value="{{$athlete->zh_full_name}}" placeholder="Chinese Full Name"></td>
                                    <td class="gender" data-gender="{{$athlete->gender}}">
                                        <span>{{$athlete->gender}}</span>
                                        <div style="display: none;">
                                            <select name="gender" class="form-control">
                                                <option value="M">Male</option>
                                                <option value="F">Female</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td class="country_code" data-code="{{$athlete->country_code}}">
                                        <span>{{$athlete->country}}</span>
                                        <div style="display: none;">
                                            <select name="country_code" class="form-control">
                                                @foreach($countries as $country)
                                                    <option value="{{$country->code}}">{{$country->country}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td class="text"><span>{{$athlete->category}}</span><input form="form_edit_{{$athlete->bib_number}}" class="form-control" style="display: none;" name="category" value="{{$athlete->category}}" placeholder="Category"></td>
                                    <td class="is_public">
                                        <span>{{($athlete->is_public == 1) ? 'True' :'False'}}</span>
                                        <div style="display: none;">
                                            <input class="tgl tgl-ios" name="is_public" id="{{$count}}" type="checkbox"  {{($athlete->is_public == 1) ? ' checked="checked" ' :''}}/>
                                            <label class="tgl-btn" for="{{$count}}"></label>
                                        </div>
                                    </td>
                                    <td class="status">
                                        <span><i class="fas fa-{{($athlete->status == 'visible' ? 'eye' : 'eye-slash')}}"></i> {{ucfirst($athlete->status)}}</span>
                                        <select name="status" class="form-control" style="display: none;">
                                            <option value="visible" {{($athlete->status == 'visible' ? 'selected' : '')}}>Visible</option>
                                            <option value="hidden" {{($athlete->status == 'hidden' ? 'selected' : '')}}>Hidden</option>
                                        </select>
                                    </td>
                                    {{-- <td class="colour_code"> --}}
                                        {{-- @if ($athlete->colour_code)
                                            <div class="read-only" style="padding-left: 4px; background: #{{$athlete->colour_code}}">#{{$athlete->colour_code}}</div>
                                        @endif
                                        <div class="editable" style="display: none; width:100%;">
                                            <input type="text" form="form_edit_{{$athlete->bib_number}}" name="colour_code" class="pick-a-color form-control" value="{{!empty($athlete->colour_code) ? $athlete->colour_code : '#0000ff'}}">
                                        </div> --}}
                                    {{-- </td> --}}
                                    <td><button type="button" class="edit-btn btn btn-default">Edit</button><button form="form_edit_{{$athlete->bib_number}}" type="submit" class="btn btn-default" style="display: none;">Save</button></td>
                                </form>
                                @if(!$is_live)
                                    <form class="delete-form" method="post" action="{{url('/')}}/event/{{$event_id}}/athletes/delete">
                                        {{ csrf_field() }}
                                        <td><button type="submit" class="btn btn-danger">Delete</button></td>
                                        <input id='deleteAthleteRow' hidden value="{{$athlete->bib_number}}" name="del_athlete">
                                    </form>
                                @endif
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
            form.find('.is_public span').hide();
            form.find('.is_public div').show();
            form.find('.status span').hide();
            form.find('.status select').show();
            var gender = form.find('.gender').attr('data-gender');
            form.find('.gender span').hide();
            form.find('.gender div').show();
            form.find('.gender div select').val(gender);
            var countryCode = form.find('.country_code').attr('data-code');
            form.find('.country_code span').hide();
            form.find('.country_code div').show();
            form.find('.country_code div select').val(countryCode);
            // form.find('.colour_code div.read-only').hide();
            // form.find('.colour_code div.editable').show();
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

        $(document).ready(function(){
            $( ".delete-form" ).submit(function( event ) {
                event.preventDefault();
                if (confirm('Are you sure you want to delete this record?')){
                    this.submit();
                }
            });
        });

    </script>
@endsection
