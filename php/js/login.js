function LoginClass(formElement)
{
    this.formElement = formElement;
    this.data = {};
    this.formData = {};
    this.parameters = {};

    this.usernameElement = null;
    this.passwordElement = null;
    this.submitElement   = null;
    this.ajaxExist       = null
}

LoginClass.prototype.getFormData = function()
{
    if (null === this.formData) {
        var data = {};
        if (this.getFormElement() != null) {
            data["email"]    = this.getUsernameElement().val();
            data["password"] = this.getPasswordElement().val();
            data["submit"]   = this.getSubmitElement().val();
        }

        this.formData = data;
    }

    return this.formData;
}

LoginClass.prototype.resetData = function()
{
    this.data = {};
    return this;
}

LoginClass.prototype.getFormElement = function()
{
    return this.formElement;
}

LoginClass.prototype.getUsernameElement = function()
{
    if (this.usernameElement == null) {
        this.usernameElement = this.getFormElement().find("input[name=email]");
    }
    return this.usernameElement;
}

LoginClass.prototype.getPasswordElement = function()
{
    if (this.passwordElement == null) {
        this.passwordElement = this.getFormElement().find("input[name=password]");
    }
    return this.passwordElement;
}

LoginClass.prototype.getSubmitElement = function()
{
    if (this.submitElement == null) {
        this.submitElement = this.getFormElement().find("submit");
    }
    return this.submitElement;
}

LoginClass.prototype.start = function()
{
    this.getUsernameElement().focus();

    this.bindSubmit();
}

LoginClass.prototype.bindSubmit = function()
{
    var self = this;
    this.getFormElement().bind('submit', function() {
        if ($.active) {
            self.ajaxExist.abort();
        }
        self.login();
        return false;
    });
}

LoginClass.prototype.disableFormElements = function()
{
    this.getUsernameElement().attr("disabled", "disabled");
    this.getPasswordElement().attr("disabled", "disabled");
    this.getSubmitElement().attr("disabled", "disabled");
    return this;
}

LoginClass.prototype.enableFormElements = function()
{
    this.getUsernameElement().removeAttr("disabled");
    this.getPasswordElement().removeAttr("disabled");
    this.getSubmitElement().removeAttr("disabled");
    return this;
}

LoginClass.prototype.resetFormData = function()
{
    this.getUsernameElement().val("");
    this.getPasswordElement().val("");
    return this;
}

LoginClass.prototype.login = function()
{
    this.formData = null;
    this.disableFormElements();
    loadingOpen("Loggin in...");
    var self = this;

    console.log(this.getFormData());

    $.post(
        "/login/authenticate",
        this.getFormData(),
        function(json, textStatus) {
            loadingClose();
            self.enableFormElements();
            self.resetFormData();

            if (textStatus != "success") {
                alert("Uh, dam! Something went wrong. Please refresh and try again. Sorry!");
            } else {
                if (json.message) {
                    site.popupMessage(json.message, 2000);
                }

                if (json.success && json.url) {
                    var redirect = setTimeout(
                        function() {
                            $.mobile.changePage(json.url);
                        }, 2100
                    );
                }
            }
        },
        "json"
    );
}
