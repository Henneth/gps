@extends('app')

@section('htmlheader_title')
    Home
@endsection

@section('contentheader_title')
    Home
@endsection

@section('main-content')
<div class="container-flex">
	{{-- <div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Home</div>

				<div class="panel-body">
					You are logged in!
				</div>
			</div>
		</div>
	</div> --}}
    Hi {{ Auth::user()->name }}, you are logged in!
</div>
@endsection
