$(document).bind("mobileinit", function(){
//    $.extend($.mobile, {
//        ajaxEnabled: false
//    });
});

$(document).bind("pageinit", function(){
    var formElement = $("form.pie");
    if (formElement.length) {
        var parameters = {"targetElement":"primaryPieChart",level:0};
        var primaryChart = new PieChart(parameters);
        primaryChart.setFormElement(formElement).request();

        formElement.submit(function(){
            primaryChart.resetData().request();
            return false;
        });
    }
});

