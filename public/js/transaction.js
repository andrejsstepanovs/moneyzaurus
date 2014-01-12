function Transaction(parameters)
{
    this.parameters = parameters ? parameters : {};
    this.formElement = null;
    this.data = {};
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

Transaction.prototype.getDataValue = function(key)
{
    return this.data[key];
}

Transaction.prototype.resetData = function()
{
    this.data = {};
    return this;
}

Transaction.prototype.start = function()
{
    var form = this.getFormElement();
    var groupEl = form.find("#group");
    var priceEl = form.find("#price");
    var dateEl = form.find("#date");

    var self = this;
    form.find("#item").bind('input', function() {
        self.setData("item", $(this).val());
        self.predictGroup(groupEl, priceEl, dateEl);
    });
}

Transaction.prototype.predictPrice = function(priceEl, dateEl)
{
    var self = this;
    this.setData("predict", "price");
    $.getJSON("/transaction/predict", this.getData())
        .done (function(json) {
            if (json.success) {
                self.buildPricePrediction(json.data, priceEl, dateEl);
            }
        });
}

Transaction.prototype.predictGroup = function(groupEl, priceEl, dateEl)
{
    var self = this;
    this.setData("predict", "group");
    $.getJSON("/transaction/predict", this.getData())
        .done (function(json) {
            if (json.success) {
                self.buildGroupPrediction(json.data, groupEl, priceEl, dateEl);
            }
        });
}

Transaction.prototype.buildGroupPrediction = function(data, groupEl, priceEl, dateEl)
{
    var html = "";
    var groupData = data["group"];
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
        groupEl.parent().append("<div style=\"margin-left:10px;display:none;\" id=\"predict-group\" >" + html + "</div>");
    }

    var self = this;
    groupEl.parent().find("a.predict").bind('click', function(el) {
        $("#predict-group").val("");
        $("#predict-group").hide();
        groupEl.val($(this)[0].innerHTML);
        priceEl.focus();
        self.predictPrice(priceEl, dateEl);
    });

    $("#predict-group").show();
}


Transaction.prototype.buildPricePrediction = function(data, priceEl, dateEl)
{
    var html = "";
    var priceData = data["price"];
    for (i in priceData) {
        if (priceData.hasOwnProperty(i)) {
            html += "<a href=\"javascript:void(0);\" ";
            html += "data-predict=\"group\" ";
            html += "class=\"predict ui-btn ui-btn-inline ui-corner-all ui-shadow\"";
            html += ">";
            html += priceData[i];
            html += "</a>";
        }
    }

    if (html.length) {
        priceEl.parent().append("<div style=\"margin-left:10px;display:none;\" id=\"predict-price\" >" + html + "</div>");
    }

    priceEl.parent().find("a.predict").bind('click', function() {
        $("#predict-price").val("");
        $("#predict-price").hide();
        priceEl.val($(this)[0].innerHTML);
        dateEl.focus();
    });

    $("#predict-price").show();
}


Transaction.prototype.setFormElement = function(formElement)
{
    this.formElement = formElement;
    return this;
}

Transaction.prototype.getFormElement = function()
{
    return this.formElement;
}
