function Page()
{
    this.transactionList = null;
    this.chart;
}

Page.prototype.mobileinit = function()
{
    this.useAjax(false);
}

Page.prototype.pageinit = function()
{
    if (!this.isOnline()) {
        this.initOfflineMode();
    } else {
        this.saveLocalTransactions();
    }

    var data = this.authenticatedLoadStorageData();
    if (!data
        || !data.timestamp
        || 60 < site.getTimestamp() - data.timestamp
        || !data.authenticated
    ) {
        this.isLoggedIn(this.authenticatedSaveToStorage);
    }
}

Page.prototype.saveLocalTransactions = function()
{
    var saving = localStorage.getItem("saving");
    if (saving) {
        return;
    }

    var transaction = new Transaction();
    var transactionsList = new TransactionsList();
    var listData = transactionsList.loadListDataFromStorage();
    if (listData && listData.data && listData.data.rows && listData.data.rows.length) {
        for (i in listData.data.rows) {
            var transactionObj = listData.data.rows[i];
            if (transactionObj && transactionObj.not_saved) {
                transaction.makeSaveRequest(transactionObj, i);
            }
        }
    }
}

Page.prototype.initOfflineMode = function()
{
    var menu = ["pie", "user"];
    for (i in menu) {
        var menuElement = $("#menu-nav-" + menu[i]);
        if (menuElement.length) {
            menuElement.parent().hide();
        }
    }

    var listTable = $("#transactions-list-table");
    if (listTable.length) {
        listTable.find("thead").hide();
    }
}

Page.prototype.authenticatedSaveToStorage = function(authenticated)
{
    var data = {
        "timestamp"    : site.getTimestamp(),
        "authenticated": authenticated
    };
    localStorage.setItem('authenticated', JSON.stringify(data));
    return this;
}

Page.prototype.authenticatedLoadStorageData = function()
{
    var data = false;
    var dataString = localStorage.getItem('authenticated');
    if (dataString) {
        data = $.parseJSON(dataString);
    }
    return data;
}

Page.prototype.showOfflineMessage = function()
{
    var message = "Looks like you're offline. ";
    message += "This functionality is not available in offline mode. ";
    message += "If you are sure that you have online, ";
    message += "please, refresh page.";
    site.popupMessage(message, 10000);
}

Page.prototype.isLoggedIn = function(callback)
{
    if (this.isOnline()) {
        $.post(
            "/authenticated",
            null,
            function(json, textStatus) {
                var authenticated = false;
                if (textStatus == "success" && json.success == true && json.authenticated == true) {
                    authenticated = true;
                }
                if (typeof(callback) == "function") {
                    callback(authenticated);
                }
                if (!authenticated && json.url) {
                    $.mobile.changePage(json.url);
                }
            },
            "json"
        );
    }
}

Page.prototype.getTransactionList = function()
{
    if (this.transactionList == null) {
        var listParameters = {"targetElement":"listResults"};
        this.transactionList = new TransactionsList(listParameters);
    }

    return this.transactionList;
}

Page.prototype.initListBindSubmit = function(listFormElement)
{
    var transactionList = this.getTransactionList();

    var formElements = listFormElement.find(":input");
    if (formElements.length) {
        formElements.each(
            function() {
                $(this).keyup(function () {
                    listFormElement.submit();
                });
            }
        );
    }

    transactionList.setFormElement(listFormElement).request(false);

    listFormElement.bind('submit', function() {
        transactionList.resetData().request(true);
        return false;
    });
}

Page.prototype.initListEditSubmit = function(listFormElement, editForm)
{
    var self = this;
    editForm.bind('submit', function() {
        var action = $(this).attr("action");
        var params = self.formToJson($(this));

        $.post(
            action,
            params,
            function(json, textStatus) {
                if (textStatus != "success") {
                    site.popupMessage("Request Failed: " + textStatus + ", " + error);
                }
                if (json.success) {
                    $("#editTransaction").popup("close");
                    self.getTransactionList().request(true);
                } else {
                    alert(json.error);
                }
            },
            "json"
        );

        return false;
    });
}

Page.prototype.initListEditDelete = function(listFormElement, deleteTransactionButton)
{
    var self = this;
    deleteTransactionButton.bind('click', function() {
        var action = $(this).attr("data-action");

        var transactionId = $("#editTransaction form :input[name=transaction_id]").val();
        var params = {transaction_id: transactionId};

        $.post(
            action,
            params,
            function(json, textStatus) {
                if (textStatus != "success") {
                    site.popupMessage("Dam! Something went wrong. Please refresh page and try again.");
                    return true;
                }
                if (json.success) {
                    $("#editTransaction").popup("close");
                    self.getTransactionList().request(true);
                } else {
                    alert(json.error);
                }
            },
            "json"
        );

        return false;
    });
}

Page.prototype.initList = function(listFormElement)
{
    if (listFormElement.length) {
        this.initListBindSubmit(listFormElement);
        this.initListEditSubmit(listFormElement, $("#editTransaction form"));
        this.initListEditDelete(listFormElement, $("#deleteTransactionButton"));
    }
}

Page.prototype.formatLoginForm = function(loginSubmitInputElement)
{
    loginSubmitInputElement.parent().addClass('ui-btn-active');
    loginSubmitInputElement.parent().css({'padding':'.785em 1em','margin-right':'0'});
}

Page.prototype.initTransaction = function(transactionForm)
{
    if (transactionForm.length) {
        transactionForm.find("input[name=item]").focus();
        transaction = new Transaction(transactionForm);
        transaction.start();
    }
}

Page.prototype.initLogin = function(loginFormElement)
{
    if (loginFormElement.length) {
        var Login = new LoginClass(loginFormElement);
        Login.start();
    }
}

Page.prototype.initPie = function(formElement)
{
    if (formElement.length) {
        var parameters = {"targetElement":"primaryPieChart", level:0};
        var primaryChart = new PieChart(parameters);
        primaryChart.setFormElement(formElement).start().request();
    }
}

Page.prototype.initChart = function(formElement)
{
    if (formElement.length) {
        this.chart = new Chart()
        this.chart.setFormElement(formElement).setMonthValue().request();
    }
}

Page.prototype.pageshow = function()
{
    if (!this.isOnline()) {
        $("#offline-mode-message").show();
    }
    this.initPie($("form.pie"));
    this.initChart($("form.chart"));
    this.initLogin($("form[name=login-form]"));
    this.initList($("form[name=list]"));
    this.initTransaction($("#transactionForm"));
    this.formatLoginForm($("#login-submit"));
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

Page.prototype.popupMessage = function(message, timeout)
{
    if (message.length) {
        var popup = $("#popup");
        if (popup.length) {
            popup.html("<p>" + message + "</p>").popup("open");
        }

        var popupMessage = setTimeout(
            function() {
                popup.popup("close");
            },
            timeout
        );
    }
}

Page.prototype.loadingOpen = function(msgText)
{
    $.mobile.loading(
        "show",
        {
            text:        msgText,
            textVisible: true,
            theme:       "b"
        }
    );
}

Page.prototype.loadingClose = function()
{
    $.mobile.loading("hide");
}

Page.prototype.getTimestamp = function()
{
    return Math.round(new Date().getTime() / 1000)
}

Page.prototype.getFormattedDate = function()
{
    var date = new Date();
    var year  = date.getFullYear();
    var month = date.getMonth() + 1;
    var day   = date.getDate();

    var output = "";
    output += year;
    output += '-';
    output += month < 10 ? "0" + month : month;
    output += '-';
    output += day < 10 ? "0" + day : day;

    return output;
}

Page.prototype.array_unshift = function(array)
{
    var i = arguments.length;

    while (--i !== 0) {
        arguments[0].unshift(arguments[i]);
    }

    return arguments[0].length;
}