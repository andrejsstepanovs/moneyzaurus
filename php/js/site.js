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

        var monthElement = formElement.find("input[name=month]");
        monthElement.bind("input keyup change", function() {
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

        var editForm = $("#editTransaction form");

        editForm.bind('submit', function() {
            var action = $(this).attr("action");
            var params = formToJson($(this));

            $.getJSON(action, params)
                .done (function(json) {
                if (json.success) {
                    $("#editTransaction").popup("close");
                    listFormElement.submit();
                } else {
                    alert(json.error);
                }
            });

            return false;
        });

        $("#deleteTransactionButton").bind('click', function() {
            var action = $(this).attr("data-action");

            var transactionId = $("#editTransaction form :input[name=transaction_id]").val();
            var params = {transaction_id: transactionId};

            $.getJSON(action, params)
                .done (function(json) {
                if (json.success) {
                    $("#editTransaction").popup("close");
                    listFormElement.submit();
                } else {
                    alert(json.error);
                }
            });

            return false;
        });
    }

    var transactionForm = $("#transactionForm");
    if (transactionForm.length) {
        transaction = new Transaction(transactionForm);
        transaction.start();
    }

    var loginSubmitInputElement = $("#login-submit");
    loginSubmitInputElement.parent().addClass('ui-btn-active');
});

$(document).on('pageshow', function(event){
    var transactionForm = $("#transactionForm");
    if (transactionForm.length) {
        transactionForm.find("input[name=item]").focus();
    }
});

function formToJson (selector) {
    var ary = $(selector).serializeArray();
    var obj = {};
    for (var a = 0; a < ary.length; a++) obj[ary[a].name] = ary[a].value;
    return obj;
}
