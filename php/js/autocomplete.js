/**
 * author: @commadelimited, @cfjedimaster
 */
(function ($) {

    "use strict";

    var defaults = {
        icon: 'arrow-r',
        target: $(),
        source: null,
        link: null,
        data: {},
        minLength: 1,
        transition: 'fade',
        matchFromStart: true
    },
    buildItems = function ($this, data, settings) {
        var str = [];
        if (data) {
            $.each(data, function (index, value) {
                if ($.isPlainObject(value)) {
                    var hrefValue = settings.link.replace("%s", value.label);
                    str.push(
                        '<li data-icon="' + settings.icon + '">' +
                            '<a href="javascript:void(0)" ' +
                                'onclick="' + hrefValue + '" ' +
                                'data-transition="' + settings.transition + '" ' +
                                'data-autocomplete=\'' + JSON.stringify(value).replace(/'/g, "&#39;") + '\'>' +
                                value.label +
                            "</a>" +
                        "</li>"
                    );
                }
            });
        }

        if ($.isArray(str)) {
            str = str.join("");
        }

        $(settings.target).html(str).listview("refresh");

        if (str.length > 0) {
            $this.trigger("targetUpdated.autocomplete");
        } else {
            $this.trigger("targetCleared.autocomplete");
        }
    },
    clearTarget = function ($this, $target) {
        $target.html('').listview('refresh').closest("fieldset").removeClass("ui-search-active");
        $this.trigger("targetCleared.autocomplete");
    },
    handleInputKeyDown = function (e) {
        var $this = $(this),
            settings = $this.jqmData("autocomplete");

        if (e && (e.keyCode === 40 || e.keyCode === 13 || e.keyCode === 38)) {
            var predictionEl = $(settings.target);
            var currentActiveEl = predictionEl.find(".ui-btn-active");

            if (e.keyCode === 40) { // down
                if (!currentActiveEl.length) {
                    predictionEl.find(".ui-btn:first").addClass("ui-btn-active");
                } else {
                    currentActiveEl.removeClass("ui-btn-active");
                    currentActiveEl.parent().nextAll("li:eq(0)").find("a").addClass("ui-btn-active");
                }
            } else if (e.keyCode === 13) { // enter
                if (currentActiveEl.length) {
                    currentActiveEl.click();
                }
            } else if (e.keyCode === 38) { // up
                if (!currentActiveEl.length) {
                    predictionEl.find(".ui-btn:first").addClass("ui-btn-active");
                } else {
                    currentActiveEl.removeClass("ui-btn-active");
                    currentActiveEl.parent().prevAll("li:eq(0)").find("a").addClass("ui-btn-active");
                }
            }
            return false;
        }
    },
    handleInputKeyUp = function (e) {
        var $this = $(this),
            settings = $this.jqmData("autocomplete"),
            element_text,
            re;

        if (!settings) {
            return;
        }

        // get the current text of the input field
        var text = $this.val();

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

                var data = settings.source.sort().filter(function (element) {
                    // matching from start, or anywhere in the string?
                    if (settings.matchFromStart) {
                        // from start
                        element_text, re = new RegExp('^' + escape(text), "i");
                    } else {
                        // anywhere
                        element_text, re = new RegExp(escape(text), "i");
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

            // select first element
            var predictionEl = $(settings.target);
            var currentActiveEl = predictionEl.find(".ui-btn-active");
            if (!currentActiveEl.length) {
                predictionEl.find(".ui-btn:first").addClass("ui-btn-active");
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
                .next(".ui-input-clear")
                .bind("click", function () {
                    clearTarget(el, $(settings.target));
                });
        }
    };

    // construct
    $.fn.autocomplete = function (method) {
        return methods.init.apply(this, arguments);
    };

})(jQuery);