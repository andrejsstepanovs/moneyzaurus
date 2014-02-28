function Transaction(formElement)
{
    this.formElement = formElement;

    this.data       = {};
    this.formData   = {};
    this.parameters = {};

    this.itemElement      = null;
    this.groupElement     = null;
    this.priceElement     = null;
    this.dateElement      = null;
    this.submitElement    = null;
    this.minInputLength   = 3;
    this.ajaxExist        = null;
}

Transaction.prototype.setData = function(key, value)
{
    this.data[key] = value;
    return this;
}

Transaction.prototype.getData = function()
{
    return this.data;
}

Transaction.prototype.getFormData = function()
{
    if (null === this.formData) {
        var data = {};
        if (this.getFormElement() != null) {
            data["item"] = this.getItemElement().val();
            data["group"] = this.getGroupElement().val();
            data["price"] = this.getPriceElement().val();
            data["date"] = this.getDateElement().val();
        }

        this.formData = data;
    }

    return this.formData;
}

Transaction.prototype.resetData = function()
{
    this.data = {};
    return this;
}

Transaction.prototype.getFormElement = function()
{
    return this.formElement;
}

Transaction.prototype.getItemElement = function()
{
    if (this.itemElement == null) {
        this.itemElement = this.getFormElement().find("#item");
    }
    return this.itemElement;
}

Transaction.prototype.getGroupElement = function()
{
    if (this.groupElement == null) {
        this.groupElement = this.getFormElement().find("#group");
    }
    return this.groupElement;
}

Transaction.prototype.getItemElement = function()
{
    if (this.itemElement == null) {
        this.itemElement = this.getFormElement().find("#item");
    }
    return this.itemElement;
}

Transaction.prototype.getPriceElement = function()
{
    if (this.priceElement == null) {
        this.priceElement = this.getFormElement().find("#price");
    }
    return this.priceElement;
}

Transaction.prototype.getDateElement = function()
{
    if (this.dateElement == null) {
        this.dateElement = this.getFormElement().find("#date");
    }
    return this.dateElement;
}

Transaction.prototype.getSubmitElement = function()
{
    if (this.submitElement == null) {
        this.submitElement = this.getFormElement().find("#submit");
    }
    return this.submitElement;
}

Transaction.prototype.getPredictionId = function(key)
{
    return "predict-" + key;
}

Transaction.prototype.getPredictionElement = function(key)
{
    return $("#" + this.getPredictionId(key));
}

Transaction.prototype.start = function()
{
    this.getItemElement().focus();
    this.getDateElement().val(site.getFormattedDate());

    this.bindSubmit();

    var self = this;
    this.getItemElement().bind("input keyup", function() {
        var value = self.getItemElement().val();
        if (value == "") {
            self.getPredictionElement("group").hide();
            self.getPredictionElement("price").hide();
        } else if (value.length >= self.minInputLength) {
            self.setData("item", $(this).val());
            self.fetchPrediction(self.getGroupElement(), "group");
        }
    });

    this.getGroupElement().bind("input keyup change", function() {
        var value = self.getGroupElement().val();
        if (value == "") {
            self.getPredictionElement("price").hide();
        } else if (value.length >= self.minInputLength) {
            self.setData("item", self.getItemElement().val());
            self.setData("group", $(this).val());
            self.fetchPrediction(self.getPriceElement(), "price");
        }
    });

    this.getPriceElement().bind("input keyup change focus", function() {
        self.transactionExist();
    });
    this.getDateElement().bind("input keyup change focus", function() {
        self.transactionExist();
    });

    this.autocompleteStart();
}

Transaction.prototype.bindSubmit = function()
{
    var self = this;
    this.getFormElement().bind('submit', function() {
        if ($.active) {
            self.ajaxExist.abort();
        }
        self.save();
        return false;
    });
}

Transaction.prototype.disableFormElements = function()
{
    this.getItemElement().attr("disabled", "disabled");
    this.getGroupElement().attr("disabled", "disabled");
    this.getPriceElement().attr("disabled", "disabled");
    this.getDateElement().attr("disabled", "disabled");
    this.getSubmitElement().attr("disabled", "disabled");
    return this;
}

Transaction.prototype.enableFormElements = function()
{
    this.getItemElement().removeAttr("disabled");
    this.getGroupElement().removeAttr("disabled");
    this.getPriceElement().removeAttr("disabled");
    this.getDateElement().removeAttr("disabled");
    this.getSubmitElement().removeAttr("disabled");
    return this;
}

Transaction.prototype.resetFormData = function()
{
    this.getItemElement().val("");
    this.getGroupElement().val("");
    this.getPriceElement().val("");

    $("#" + this.getPredictionId("item")).remove();
    $("#" + this.getPredictionId("group")).remove();
    $("#" + this.getPredictionId("price")).remove();
    return this;
}

Transaction.prototype.save = function()
{
    this.formData = null;
    var formData = this.getFormData();
    if (site.isOnline()) {
        this.saveRequest(formData, this.addTransactionToStorage, true);
    } else {
        this.addTransactionToStorage(formData, true);
    }
}

Transaction.prototype.addTransactionToStorage = function(transactionData, notSaved)
{
    var self = this;
    this.disableFormElements();
    site.loadingOpen("Saving locally...");

    var transactionsList = new TransactionsList();
    listData = transactionsList.loadListDataFromStorage();
    if (notSaved) {
        transactionData.not_saved     = notSaved;
        transactionData.item_name     = transactionData.item;
        transactionData.group_name    = transactionData.group;
        transactionData.currency_html = "";
    }
    site.array_unshift(listData.data.rows, transactionData)

    transactionsList.listDataSaveToStorage(listData.data);

    var enableForm = setTimeout(
        function() {
            self.enableFormElements();
            self.resetFormData();
            site.loadingClose();
            self.getItemElement().focus();
        }, 1500
    );
}

Transaction.prototype.saveRequest = function(transactionData, callback)
{
    this.formData = null;
    this.disableFormElements();
    site.loadingOpen("Saving...");
    var self = this;

    $.post(
        "/transaction/save",
        transactionData,
        function(json, textStatus) {
            site.loadingClose();
            self.enableFormElements();

            if (textStatus == "success") {
                if (json.success) {
                    self.resetFormData();
                    if (typeof(callback) == "function") {
                        callback(json.transaction, false);
                    }
                    site.popupMessage(json.message, 2000);
                } else {
                    site.popupMessage("Failed to save. " + json.message, 5000);
                }

            }
        },
        "json"
    );
}

Transaction.prototype.makeSaveRequest = function(transactionData, rowId)
{
    localStorage.setItem("saving", 1);
    var self = this;
    $.post(
        "/transaction/save",
        transactionData,
        function(json, textStatus) {
            if (textStatus == "success" && json.success) {
                self.updateListData(json.transaction, rowId);
            }
            localStorage.removeItem("saving");
        },
        "json"
    );
}

Transaction.prototype.updateListData = function(rowData, rowId)
{
    var transactionsList = new TransactionsList();
    var listData = transactionsList.loadListDataFromStorage();
    if (listData && listData.data && listData.data.rows && listData.data.rows.length) {
        listData.data.rows[rowId] = rowData;
        transactionsList.listDataSaveToStorage(listData.data);
    }
}

Transaction.prototype.autocompleteFetchData = function()
{
    var self = this;
    $.post(
        "/transaction/data",
        this.getFormData(),
        function(json, textStatus) {
            if (textStatus == "success") {
                self.autocompleteSaveData(json.item, json.group)
                    .autocompleteStart();
            }
        },
        "json"
    );
}

Transaction.prototype.autocompleteStart = function()
{
    var data = this.autocompleteLoadStorageData();
    if (site.isOnline()) {
        if (!data || !data.timestamp || 60 < site.getTimestamp() - data.timestamp) {
            this.autocompleteFetchData();
        }
    }

    if (data) {
        this.autocompleteInput(
            $("#item"),
            $('#suggestions-item'),
            data.item,
            $('#group')
        );

        this.autocompleteInput(
            $("#group"),
            $('#suggestions-group'),
            data.group,
            $('#price')
        );
    }

    return this;
}

Transaction.prototype.autocompleteSaveData = function(item, group)
{
    var data = {
        "timestamp": site.getTimestamp(),
        "item"     : item,
        "group"    : group
    };
    localStorage.setItem('autocomplete_data', JSON.stringify(data));
    return this;
}

Transaction.prototype.autocompleteLoadStorageData = function()
{
    var data = false;
    var dataString = localStorage.getItem('autocomplete_data');
    if (dataString) {
        data = $.parseJSON(dataString);
    }
    return data;
}

Transaction.prototype.transactionExist = function()
{
    if (!site.isOnline()
        || !this.getPriceElement().val()
        || !this.getDateElement().val()
        || !this.getGroupElement().val()
    ) {
        return;
    }

    this.formData = null;

    if (this.ajaxExist) {
        this.ajaxExist.abort();
    }

    this.ajaxExist = $.post(
        "/transaction/exist",
        this.getFormData(),
        function(json, textStatus) {
            if (textStatus == "success" && json.success && json.exist) {
                var message = "<h3>" + json.message + "</h3>";
                message += "<ul>";
                for (i in json.transactions) {
                    var transaction = json.transactions[i];
                    message += "<li>";
                    message += transaction.date_created + "  --  " + transaction.email;
                    message += "</li>";
                }
                message += "</ul>";

                site.popupMessage(message, 3000);
            }
        },
        "json"
    );
}

Transaction.prototype.fetchPrediction = function(element, key)
{
    if (!site.isOnline()) {
        return;
    }

    var self = this;
    this.setData("predict", key);

    $.post(
        "/transaction/predict",
        this.getData(),
        function(json, textStatus) {
            if (textStatus == "success" && json.success) {
                self.buildPredictedButtons(json.data, element, key);
            }
        },
        "json"
    );
}

Transaction.prototype.buildPredictedButtons = function(data, element, key)
{
    var html = "";
    var predictId = this.getPredictionId(key);
    var groupData = data[key];

    for (i in groupData) {
        if (groupData.hasOwnProperty(i)) {
            html += "<a href=\"javascript:void(0);\" ";
            html += "data-predict=\"group\" ";
            html += "class=\"predict ui-btn ui-btn-inline ui-corner-all ui-shadow\"";
            html += ">";
            html += groupData[i];
            html += "</a>";
        }
    }

    if (html.length) {
        var predictEl = $("#" + predictId);
        if (predictEl.length) {
            predictEl.innerHTML = html;
        } else {
            var htmlFull = "<div style=\"margin-left:10px;display:none;\" id=\"" + predictId + "\" >";
            htmlFull += html;
            htmlFull += "</div>";
        }

        element.parent().prepend(htmlFull);
    }

    var self = this;
    element.parent().find("a.predict").bind('click', function() {
        var predictElement = $("#" + predictId);
        predictElement.remove();

        if (key == "group") {
            self.getGroupElement().val($(this)[0].innerHTML);
            self.getPriceElement().focus();
            self.fetchPrediction(self.getPriceElement(), "price");
        } else if (key == "price") {
            self.getPriceElement().val($(this)[0].innerHTML);
            self.getDateElement().focus();
        }
    });

    $("#" + predictId).show();
}

Transaction.prototype.autocompleteInput = function(element, target, data, nextElement)
{
    var elementId = element.attr('id');
    var targetId  = target.attr('id');
    var nextElementId = nextElement.attr('id');
    element.autocomplete({
        target: target,
        source: data,
        link: '$(\'#' + elementId + '\').val(\'%s\');'
            + '$(\'#' + elementId + '\').trigger(\'input\');'
            + '$(\'#' + targetId + '\')[0].innerHTML=\'\';'
            + '$(\'#' + nextElementId + '\').focus();'
    });
}