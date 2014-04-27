function Chart(parameters)
{
    this.parameters = parameters ? parameters : {};
    this.formElement  = null;
    this.monthElement = null;
}

Chart.prototype.setFormElement = function(formElement)
{
    this.formElement = formElement;
    return this;
}

Chart.prototype.getFormElement = function()
{
    return this.formElement;
}

Chart.prototype.getMonthElement = function()
{
    if (null === this.monthElement) {
        this.monthElement = this.getFormElement().find("input[name=month]");
    }
    return this.monthElement;
}

Chart.prototype.setMonthValue = function()
{
    var date = site.getFormattedDate().substring(0, 7);
    this.getMonthElement().val(date);
    this.getMonthElement().attr("max", date);

    var self = this;
    this.getMonthElement().bind("keyup, input", function() {
        self.request();
        return false;
    });

    return this;
}

Chart.prototype.request = function()
{
    this.closeElement();

    if (!site.isOnline()) {
        site.showOfflineMessage();
        return false;
    }

    swfobject.removeSWF();
    swfobject.embedSWF(
        "/flash/open-flash-chart.swf",
        "group_pie",
        "100%",
        "600",
        "9.0.0",
        "expressInstall.swf",
        {"data-file":"/chart/ajax?month=" + this.getMonthElement().val()}
    );
}

Chart.prototype.getSubPie = function(chart_id, index)
{
    $("#sub_group_pie_close").html('<a href="javascript:void(0)" onclick="site.chart.closeElement();" >close</a><br />');

    var url = "/chart/ajax-group?data=" + chart_id + "|" + this.getMonthElement().val();
    swfobject.removeSWF();
    swfobject.embedSWF(
        "/flash/open-flash-chart.swf",
        "sub_group_pie",
        "50%",
        "300",
        "9.0.0",
        "expressInstall.swf",
        {
            "data-file":url
        }
    );
    $("#sub_group").css("display", "block");

    this.singleGroupHistory(chart_id);
}

Chart.prototype.singleGroupHistory = function(chart_id)
{
    var url = "/chart/ajax-group-history?data=" + chart_id + "|" + this.getMonthElement().val();
    $.getJSON(url)
        .done (function(json) {

        var html = "<table>";
        html += "<caption>History</caption>";
        if (json.success) {
            var data = json.data;
            for (var i in data) {
                if (data.hasOwnProperty(i)) {
                    var row = data[i];
                    html += "<tr>";
                    html += "<td>" + row["month"] + "</td>";
                    html += "<td style=\"padding-left:5px;\">EUR " + row["price"] + "</td>";
                    html += "</tr>";
                }
            }
        }
        html += "</table>";

        $("#sub_group_pie_history").html(html);
    });
}

Chart.prototype.closeElement = function()
{
    $("#sub_group").css("display", "none");
}

