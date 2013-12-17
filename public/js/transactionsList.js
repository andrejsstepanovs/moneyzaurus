function TransactionsList(parameters)
{
    this.parameters = parameters ? parameters : {};
    this.formElement = null;
    this.iterator = ["a", "b", "c", "d", "e"];
    this.i = 0;
    this.resetData();
}

TransactionsList.prototype.setFormElement = function(formElement)
{
    this.formElement = formElement;
    return this;
}

TransactionsList.prototype.getTargetElement = function()
{
    return this.parameters.targetElement;
}

TransactionsList.prototype.resetData = function()
{
    this.data = null;
    return this;
}

TransactionsList.prototype.getData = function()
{
    if (null === this.data) {
        var data = this.parameters ? this.parameters : {};

        if (this.formElement != null) {
            var formData = this.formElement.serializeArray();

            $.map(formData, function(n, i){
                data[n.name] = n.value;
            });
        }

        this.data = data;
    }

    return this.data;
}

TransactionsList.prototype.getGridVal = function()
{
    var j = this.i;

    this.i++;
    if (this.i >= 5) {
        this.i = 0;
    }

    return this.iterator[j];
}

TransactionsList.prototype.getRowHtml = function(row, columns)
{
    var html = "";
    for (var i in columns) {
        if (columns.hasOwnProperty(i)) {
            var key = columns[i];

            html += "<div class=\"ui-block-" + this.getGridVal() + "\">";
            html += row[key];
            html += "</div>";
        }
    }

    return html;
}

TransactionsList.prototype.buildTable = function(data)
{
    var target = this.getTargetElement();

    var el = $("#" + target);

    var html = "";

    var rows = data.rows;
    for (var i in rows) {
        if (rows.hasOwnProperty(i)) {
            html += this.getRowHtml(rows[i], data.columns);
        }
    }

    el.html(html);
}

TransactionsList.prototype.request = function()
{
    var self = this;
    $.getJSON("/list/ajax", this.getData())
    .done (function(json) {
        if (json.success) {
            if (json.script) {
                jQuery.globalEval(json.script);
            }
            self.buildTable(json.data);
        }
    })
    .fail (function(jqxhr, textStatus, error) {
        var err = textStatus + ", " + error;
        console.log("Request Failed: " + err);
    });
}