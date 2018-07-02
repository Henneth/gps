$(function() {
    $('.sidebar-select').change(function() {
        var url = window.location.href; // or window.location.toString()
        // var currentPath = (url.split("/")[5]);
        var getUrl = window.location;
        var currentPath = getUrl.pathname.split("/")[3];
        var baseUrl = getUrl.protocol + "//" + getUrl.host;

        if (currentPath && currentPath != "checkpoint" && currentPath != "live-tracking"){
            window.location.replace(baseUrl + '/event/' + $(this).val() + '/' + currentPath);
        } else {
            window.location.replace(baseUrl + '/event/' + $(this).val() + '/' + 'replay-tracking');
        }

    })

    $('section.sidebar .sidebar-menu li.header').click(function(e) {
        if ($(e.target).is('select')) {
            return;
        }
        $(this).nextUntil(".header").toggle();
        $(this).find(".fa-plus").toggle();
        $(this).find(".fa-minus").toggle();
    })
    if (live_event_off) {
        var live_event_header = $('section.sidebar .sidebar-menu li.header.live-event');
        live_event_header.nextUntil(".header").toggle();
        live_event_header.find(".fa-plus").toggle();
        live_event_header.find(".fa-minus").toggle();
    }
});
