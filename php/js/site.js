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