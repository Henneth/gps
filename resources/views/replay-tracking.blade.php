@extends('app')

@section('htmlheader_title')
    Replay Tracking
@endsection

@section('contentheader_title')
    Replay Tracking
@endsection

@section('main-content')
<div class="container-flex flex-container">
    <button type="button" class="replay-controls btn btn-primary">Play</button>
    <button type="button" class="replay-controls btn btn-default" disabled>Pause</button>
    <button type="button" class="replay-controls btn btn-default" disabled>Stop</button>
    <div class="slider-wrapper">
        <input type="text" value="" class="slider form-control" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="0" data-slider-orientation="horizontal" data-slider-selection="before" data-slider-tooltip="show" data-slider-id="aqua" autocomplete="off">
    </div>
</div>
@endsection

@section('js')
    <script>
    $(function () {
        /* BOOTSTRAP SLIDER */
        $('.slider').slider({
        	formatter: function(value) {
        		return value + '%';
        	}
        })

        data = {!! $data !!};
        console.log(data);

        // SLIDER
        $('input.slider').change(function() {
            var pc = $(this).val();

        })
    })
    </script>
@endsection

@section('css')
    <style>
    .flex-container {
        display: flex;
    }
    .replay-controls {
        margin-right: 4px;
    }
    .slider-wrapper {
        padding: 4px 20px;
        flex-grow: 100;
    }
    .slider.slider-horizontal {
        width: 100%;
    }
    .slider-handle {
        position: absolute;
        width: 20px;
        height: 20px;
        background-color: #444;
        -webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05);
        -moz-box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05);
        opacity: 1;
        border: 0px solid transparent;
    }
    .slider-handle.round {
        -webkit-border-radius: 20px;
        -moz-border-radius: 20px;
        border-radius: 20px;
    }
    .slider-disabled .slider-selection {
        opacity: 0.5;
    }

    #red .slider-selection {
        background: #f56954;
    }

    #blue .slider-selection {
        background: #3c8dbc;
    }

    #green .slider-selection {
        background: #00a65a;
    }

    #yellow .slider-selection {
        background: #f39c12;
    }

    #aqua .slider-selection {
        background: #00c0ef;
    }

    #purple .slider-selection {
        background: #932ab6;
    }
    </style>
@endsection
