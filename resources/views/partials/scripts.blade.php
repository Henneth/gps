<!-- REQUIRED JS SCRIPTS -->

<!-- jQuery 2.1.4 -->
<script src="{{ asset('/plugins/jQuery/jQuery-2.1.4.min.js') }}"></script>
<!-- Bootstrap 3.3.2 JS -->
<script src="{{ asset('/js/bootstrap.min.js') }}" type="text/javascript"></script>
<!-- Moment.js -->
<script src="{{ asset('/js/moment.min.js') }}" type="text/javascript"></script>
{{-- <!-- Date Range Picker -->
<script src="{{ asset('/js/daterangepicker.js') }}" type="text/javascript"></script> --}}
<!-- Date Time Picker -->
<script src="{{ asset('/js/bootstrap-datetimepicker.min.js') }}" type="text/javascript"></script>
<!-- Bootstrap Slider -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.0.0/bootstrap-slider.min.js" integrity="sha256-ssw743RfM8cbNhwou26tmmPhiNhq3buUbRG/RevtfG4=" crossorigin="anonymous"></script>
<!-- Datatables -->
<script src="{{ asset('/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>
<!-- AdminLTE App -->
<script src="{{ asset('/js/app.min.js') }}" type="text/javascript"></script>
<!-- Custom -->
<script src="{{ asset('/js/custom.js') }}" type="text/javascript"></script>

@yield('js')

<!-- Optionally, you can add Slimscroll and FastClick plugins.
      Both of these plugins are recommended to enhance the
      user experience. Slimscroll is required when using the
      fixed layout. -->
