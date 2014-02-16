function Transaction(formElement)
{
    this.formElement = formElement;
    this.data = {};

    this.itemElement  = null;
    this.groupElement = null;
    this.priceElement = null;
    this.dateElement  = null;
    this.minInputLength = 3;
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
}

Transaction.prototype.fetchPrediction = function(element, key)
{
    var self = this;
    this.setData("predict", key);
    $.getJSON("/transaction/predict", this.getData())
        .done (function(json) {
            if (json.success) {
                self.buildPredictedButtons(json.data, element, key);
            }
        });
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