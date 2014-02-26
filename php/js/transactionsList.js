function TransactionsList(parameters)
{
    this.parameters = parameters ? parameters : {};
    this.formElement = null;
    this.ajax        = null;
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
    if (!row) {
        return "";
    }

    var transactionId = row.hasOwnProperty("transaction_id") ? row["transaction_id"] : 0;
    var dataId = "data-id=\"" + transactionId + "\"";
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

TransactionsList.prototype.listDataSaveToStorage = function(listData)
{
    var data = {
        "timestamp" : site.getTimestamp(),
        "data"      : listData
    };
    localStorage.setItem('list_data', JSON.stringify(data));
    return this;
}

TransactionsList.prototype.loadListDataFromStorage = function()
{
    var listData = false;
    var dataString = localStorage.getItem('list_data');
    if (dataString) {
        listData = $.parseJSON(dataString);
    }
    return listData;
}

TransactionsList.prototype.request = function()
{
    var dataList = this.loadListDataFromStorage();
    if (dataList && dataList.data) {
        this.buildTable(dataList.data);
    }

    if (site.isOnline()
        && (!dataList || !dataList.timestamp || 60 < site.getTimestamp() - dataList.timestamp)
    ) {
        this.makeRequest(this.listDataSaveToStorage);
    }
}

TransactionsList.prototype.makeRequest = function(callback)
{
    var self = this;

    if (this.ajax) {
        this.ajax.abort();
    }

    site.loadingOpen("Loading...");
    $.post(
        "/list/ajax",
        this.getData(),
        function(json, textStatus) {
            if (textStatus != "success") {
                site.popupMessage("Request Failed: " + textStatus + ", " + error);
            }
            if (json.success) {
                if (json.script) {
                    jQuery.globalEval(json.script);
                }
                self.buildTable(json.data);

                if (typeof(callback) == "function") {
                    callback(json.data);
                }
            }
            site.loadingClose();
        },
        "json"
    );
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