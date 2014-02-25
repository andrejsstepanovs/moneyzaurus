var site = new Page();

$(document).bind("mobileinit", function(){
    site.mobileinit();
});

$(document).bind("pageinit", function(){
    site.pageinit();
});

$(document).on('pageshow', function(){
    site.pageshow();
});

function popupMessage(message)
{
    if (message.length) {
        var popup = $("#popup");
        if (popup.length) {
            popup.html("<p>" + message + "</p>").popup("open");
        }

        var timeout = 2000;
        var popupMessage = setTimeout(
            function() {
                popup.popup("close");
            },
            timeout
        );
    }
}

function loadingOpen(msgText)
{
    $.mobile.loading(
        "show",
        {
            text:        msgText,
            textVisible: true,
            theme:       "b"
        }
    );
}

function loadingClose()
{
    $.mobile.loading("hide");
}