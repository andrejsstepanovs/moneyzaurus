function Page()
{

}

Page.prototype.mobileinit = function()
{
    this.useAjax(true);
}

Page.prototype.pageinit = function()
{
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

    var loginFormElement = $("form[name=login-form]");
    if (loginFormElement.length) {
        var Login = new LoginClass(loginFormElement);
        Login.start();
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

        var self = this;
        editForm.bind('submit', function() {
            var action = $(this).attr("action");
            var params = self.formToJson($(this));

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
    loginSubmitInputElement.parent().css({'padding':'.785em 1em','margin-right':'0'});
}

Page.prototype.pageshow = function()
{
    var transactionForm = $("#transactionForm");
    if (transactionForm.length) {
        transactionForm.find("input[name=item]").focus();
    }
}

Page.prototype.init = function(key, value)
{
    this.data[key] = value;
    return this;
}

Page.prototype.useAjax = function(enabled)
{
    if (!enabled) {
        $.extend($.mobile, {ajaxEnabled: enabled});
    }
    return this;
}

Page.prototype.formToJson = function(selector)
{
    var ary = $(selector).serializeArray();
    var obj = {};
    for (var a = 0; a < ary.length; a++) obj[ary[a].name] = ary[a].value;
    return obj;
}
