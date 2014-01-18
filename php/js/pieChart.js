function PieChart(parameters)
{
    this.parameters = parameters ? parameters : {};
    this.formElement = null;
    this.resetData();
}

PieChart.prototype.setFormElement = function(formElement)
{
    this.formElement = formElement;
    return this;
}

PieChart.prototype.resetData = function()
{
    this.data = null;
    return this;
}

PieChart.prototype.getData = function()
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