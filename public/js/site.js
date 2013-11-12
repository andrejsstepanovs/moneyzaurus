$(document).bind("mobileinit", function(){
//    $.extend($.mobile, {
//        ajaxEnabled: false
//    });
});

$(document).bind("pageinit", function(){
    var formElement = $("form.pie");

    var parameters = {"targetElement":"primaryPieChart"};
    var primaryChart = new PieChart(parameters);
    primaryChart.setFormElement(formElement).request();

    formElement.submit(function(){
        primaryChart.request();
        return false;
    });

});

