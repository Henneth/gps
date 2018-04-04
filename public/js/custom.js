$(function() {
    $('.sidebar-select').change(function() {
        var getUrl = window.location;
        var baseUrl = getUrl.protocol + "//" + getUrl.host;
        window.location.replace(baseUrl + '/event/' + $(this).val());
    })
});
