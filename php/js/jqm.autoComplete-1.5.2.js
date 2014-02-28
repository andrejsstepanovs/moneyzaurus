/*
 Name: autoComplete
 Authors:
 Andy Matthews: @commadelimited
 Raymond Camden: @cfjedimaster

 Website: http://andyMatthews.net
 Version: 1.5.2
 GA: Add data: {} and change data: {}, to data: settings.data, so can pass in variables.
 : data-icon="none" >> data-icon="false" jqm 1.4
 */
(function ($) {

    "use strict";

    var defaults = {
        method: 'GET',
        icon: 'arrow-r',
        cancelRequests: false,
        target: $(),
        source: null,
        callback: null,
        link: null,
        data: {},
        minLength: 0,
        transition: 'fade',
        matchFromStart: true,
        labelHTML: function (value) {
            return value;
        },
        termParam: 'term',
        loadingHtml: '<li data-icon="false"><a href="#">Searching...</a></li>',
        interval: 0,
        builder: null,
        dataHandler: null
    },
    buildItems = function ($this, data, settings) {
        var str,
            vclass = '';
        if (settings.builder) {
            str = settings.builder.apply($this.eq(0), [data, settings]);
        } else {
            str = [];
            if (data) {
                if (settings.dataHandler) {
                    data = settings.dataHandler(data);
                }
                $.each(data, function (index, value) {
                    // are we working with objects or strings?
                    if ($.isPlainObject(value)) {
                        var hrefValue = settings.link.replace("%s", value.label);
                        str.push('<li ' + vclass + ' data-icon=' + settings.icon + '><a href="javascript:void(0)" onclick="' + hrefValue + '" data-transition="' + settings.transition + '" data-autocomplete=\'' + JSON.stringify(value).replace(/'/g, "&#39;") + '\'>' + settings.labelHTML(value.label) + '</a></li>');
                    }
                });
            }
        }
        if ($.isArray(str)) {
            str = str.join('');
        }
        $(settings.target).html(str).listview("refresh");

        // is there a callback?
        if (settings.callback !== null && $.isFunction(settings.callback)) {
            attachCallback(settings);
        }

        if (str.length > 0) {
            $this.trigger("targetUpdated.autocomplete");
        } else {
            $this.trigger("targetCleared.autocomplete");
        }
    },
    attachCallback = function (settings) {
        $('li a', $(settings.target)).bind('click.autocomplete', function (e) {
            e.stopPropagation();
            e.preventDefault();
            settings.callback(e);
        });
    },
    clearTarget = function ($this, $target) {
        $target.html('').listview('refresh').closest("fieldset").removeClass("ui-search-active");
        $this.trigger("targetCleared.autocomplete");
    },
    handleInputKeyDown = function (e) {
        var $this = $(this),
            settings = $this.jqmData("autocomplete");

        // Fix For IE8 and earlier versions.
        if (!Date.now) {
            Date.now = function () {
                return new Date().valueOf();
            };
        }

        if (e && (e.keyCode === 40 || e.keyCode === 13 || e.keyCode === 38)) {
            var predictionEl = $(settings.target);
            var currentActiveEl = predictionEl.find('.ui-btn-active');

            if (e.keyCode === 40) { // down
                if (!currentActiveEl.length) {
                    predictionEl.find('.ui-btn:first').addClass('ui-btn-active');
                } else {
                    currentActiveEl.removeClass('ui-btn-active');
                    currentActiveEl.parent().nextAll('li:eq(0)').find("a").addClass('ui-btn-active');
                }
            } else if (e.keyCode === 13) { // enter
                if (currentActiveEl.length) {
                    currentActiveEl.click();
                } else {
                    return false;
                }
            } else if (e.keyCode === 38) { // up
                if (!currentActiveEl.length) {
                    predictionEl.find('.ui-btn:first').addClass('ui-btn-active');
                } else {
                    currentActiveEl.removeClass('ui-btn-active');
                    currentActiveEl.parent().prevAll('li:eq(0)').find("a").addClass('ui-btn-active');
                }
            }
        }
    },
    handleInputKeyUp = function (e) {
        var $this = $(this),
            id = $this.attr("id"),
            text,
            data,
            settings = $this.jqmData("autocomplete"),
            element_text,
            re;

        if (!settings) {
            return;
        }

        // get the current text of the input field
        text = $this.val();

        // check if it's the same as the last one
        if (settings._lastText === text) {
            return;
        }

        // store last text
        settings._lastText = text;

        // reset the timeout...
        if (settings._retryTimeout) {
            window.clearTimeout(settings._retryTimeout);
            settings._retryTimeout = null;
        }

        // dont change the result the user is browsing...
        if (e && (e.keyCode === 13 || e.keyCode === 38 || e.keyCode === 40)) {
            return;
        }

        // if we don't have enough text zero out the target
        if (text.length < settings.minLength) {
            clearTarget($this, $(settings.target));
        } else {
            // are we looking at a source array or remote data?
            if ($.isArray(settings.source)) {
                // this function allows meta characters like +, to be searched for.
                // Example would be C++
                var escape = function (value) {
                    return value.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&");
                };
                data = settings.source.sort().filter(function (element) {
                    // matching from start, or anywhere in the string?
                    if (settings.matchFromStart) {
                        // from start
                        element_text, re = new RegExp('^' + escape(text), 'i');
                    } else {
                        // anywhere
                        element_text, re = new RegExp(escape(text), 'i');
                    }
                    if ($.isPlainObject(element)) {
                        element_text = element.label;
                    } else {
                        element_text = element;
                    }
                    return re.test(element_text);
                });
                buildItems($this, data, settings);
            }
        }
    },
    methods = {
        init: function (options) {
            var el = this;
            el.jqmData("autocomplete", $.extend({}, defaults, options));
            var settings = el.jqmData("autocomplete");
            return el.unbind("keydown.autocomplete")
                .bind("keydown.autocomplete", handleInputKeyDown)
                .bind("keyup.autocomplete", handleInputKeyUp)
                .next('.ui-input-clear')
                .bind('click', function () {
                    clearTarget(el, $(settings.target));
                });
        }
    };

    // construct
    $.fn.autocomplete = function (method) {
        return methods.init.apply(this, arguments);
    };

})(jQuery);