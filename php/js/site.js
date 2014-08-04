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
