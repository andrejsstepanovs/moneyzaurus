$(document).bind("mobileinit", function(){
    $.extend($.mobile, {
        ajaxEnabled: false
    });

    function pieChartSliceSelected(primary, secondary)
    {
        console.log(primary);
        console.log(secondary);
    }
});