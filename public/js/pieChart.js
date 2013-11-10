function PieChart(formElement, targetElement)
{
    this.formElement   = formElement;
    this.targetElement = targetElement;
}

PieChart.prototype.getData = function()
{
    var formData = this.formElement.serializeArray();

    var data = {"targetElement":this.targetElement};
    $.map(formData, function(n, i){
        data[n['name']] = n['value'];
    });

    return data;
}

PieChart.prototype.request = function()
{
    $.getJSON("pie/ajax", this.getData())
        .done (function(json) {
            if (json.success) {
                jQuery.globalEval(json.script);
            }
        })
        .fail (function(jqxhr, textStatus, error) {
            var err = textStatus + ", " + error;
            console.log("Request Failed: " + err);
        });
}
