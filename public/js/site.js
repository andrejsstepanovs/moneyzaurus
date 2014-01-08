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

    var listFormElement = $("form[name=list]");

    if (listFormElement.length) {
        var listParameters = {"targetElement":"listResults"};
        var TransactionList = new TransactionsList(listParameters);
        TransactionList.setFormElement(listFormElement).request();

        listFormElement.bind('submit', function() {
            TransactionList.resetData().request();
            return false;
        });

        var formElements = listFormElement.find(":input");
        formElements.each(
            function(){
                $(this).keyup(function () {
                    listFormElement.submit();
                });
            }
        );
    }
});

