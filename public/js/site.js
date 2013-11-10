$(document).bind("mobileinit", function(){
//    $.extend($.mobile, {
//        ajaxEnabled: false
//    });
});

$(document).bind("pageinit", function(){
    var formElelemt = $("form.pie");

    var primaryChart = new PieChart(formElelemt, "primaryPieChart");
    primaryChart.request();

    formElelemt.submit(function(){
        primaryChart.request();
        return false;
    });
});

