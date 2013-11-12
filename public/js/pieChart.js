function PieChart(parameters)
{
    if (parameters) {
        this.parameters = parameters;
    } else {
        this.parameters = {};
    }
    this.formElement = null;
    this.data = null;
}

PieChart.prototype.setFormElement = function(formElement)
{
    this.formElement = formElement;
    return this;
}

PieChart.prototype.getData = function()
{
    if (null === this.data) {
        if (this.parameters) {
            var data = this.parameters;
        } else {
            var data = {};
        }

        if (this.formElement != null) {
            var formData = this.formElement.serializeArray();

            $.map(formData, function(n, i){
                if (n.name) {
                    data[n.name] = n.value;
                }
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
