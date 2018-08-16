$(function() {
    $('.live-events.sidebar-select').change(function() {
        var url = window.location.href; // or window.location.toString()
        // var currentPath = (url.split("/")[5]);
        var getUrl = window.location;
        var currentPath = getUrl.pathname.split("/")[3];
        var baseUrl = getUrl.protocol + "//" + getUrl.host;

        if (currentPath && currentPath != "checkpoint" && currentPath != "replay-tracking"){
            window.location.replace(baseUrl + '/event/' + $(this).val() + '/' + currentPath);
        } else {
            window.location.replace(baseUrl + '/event/' + $(this).val() + '/' + 'live-tracking');
        }
    })
    $('.archived-events.sidebar-select').change(function() {
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
    $('.future-events.sidebar-select').change(function() {
        var url = window.location.href; // or window.location.toString()
        // var currentPath = (url.split("/")[5]);
        var getUrl = window.location;
        var currentPath = getUrl.pathname.split("/")[3];
        var baseUrl = getUrl.protocol + "//" + getUrl.host;

        if (currentPath && currentPath != "checkpoint" && currentPath != "live-tracking" && currentPath != "replay-tracking"){
            window.location.replace(baseUrl + '/event/' + $(this).val() + '/' + currentPath);
        } else {
            window.location.replace(baseUrl + '/event/' + $(this).val() + '/' + 'draw-route');
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
});
