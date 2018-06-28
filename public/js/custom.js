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
});
