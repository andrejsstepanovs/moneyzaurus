function TransactionsList(parameters)
{
    this.parameters = parameters ? parameters : {};
    this.formElement = null;
    this.i = 0;
    this.resetData();
    this.rowClass = "transaction-row";
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

TransactionsList.prototype.getRowHtml = function(row, columns)
{
    var dataId = "data-id=\"" + row["transaction_id"] + "\"";
    var html = "<tr class=\"" + this.rowClass + "\" " + dataId + ">";
    for (var i in columns) {
        if (columns.hasOwnProperty(i)) {
            var key = columns[i];

            html += "<td>";
            html += row[key];
            if (key == "price") {
                html += " " + row["currency_html"];
            }
            html += "</td>";
        }
    }
    html += "</tr>";

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

    this.bindRowClick(rows);
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

TransactionsList.prototype.bindRowClick = function(rows)
{
    $("tr." + this.rowClass).each(function(){
        $(this).click(function(){
            var transactionId = $(this).attr("data-id");

            for (var i in rows) {
                if (rows.hasOwnProperty(i)) {
                    var data = false;
                    $.each(rows[i], function(key, value) {
                        if (key == "transaction_id" && value == transactionId) {

                            data = rows[i];
                            return false; //break;
                        }
                    });

                    if (data != false) {
                        $("#editTransaction form :input[name=item]").val(data["item_name"]);
                        $("#editTransaction form :input[name=group]").val(data["group_name"]);
                        $("#editTransaction form :input[name=price]").val(data["price"]);
                        $("#editTransaction form :input[name=date]").val(data["date"]);
                        $("#editTransaction form :input[name=currency]").find("option[value=" + data["id_currency"] + "]").attr('selected','selected');
                        $("#editTransaction form :input[name=transaction_id]").val(data["transaction_id"]);

                        $("#editTransaction form :input[name=submit]").parent().addClass('ui-btn-active');

                        $("#editTransaction").popup("open");
                        break;
                    }
                }
            }
        });
    });
}