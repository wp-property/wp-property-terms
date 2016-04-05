jQuery(document).ready(function($) {
    var wraper = $(".wpp-taxonomy-select-combobox");
    wraper.each(function() {
        var $this = $(this), taxonomy = $this.attr("data-taxonomy"), input = $this.find(".ui-autocomplete-input"), btntoggle = $this.find(".select-combobox-toggle"), query = {
            action: "term_autocomplete",
            taxonomy: taxonomy
        }, url = ajaxurl + "?" + jQuery.param(query);
        input.one("focus", function() {
            input.addClass("ui-autocomplete-loading"), $.ajax(url).done(function(data) {
                input.removeClass("ui-autocomplete-loading"), input.autocomplete({
                    minLength: 0,
                    source: data,
                    focus: function(event, ui) {
                        return input.val(ui.item.label), !1;
                    },
                    select: function(event, ui) {
                        return input.val(ui.item.label), !1;
                    }
                }).autocomplete("instance")._renderItem = function(ul, item) {
                    var exist = item.label == input.val(), selected = exist ? "ui-state-selected" : "";
                    return $("<li>").append("<a class='" + selected + "'>" + item.label + "</a>").appendTo(ul);
                }, input.autocomplete("widget").addClass("wpp-autocomplete"), input.on("focus", function() {
                    wasOpen = input.autocomplete("widget").is(":visible"), wasOpen || input.autocomplete("search", input.val());
                }), input.attr("data-autocomplete-loaded", !0), input.is(":focus") && input.autocomplete("search", "");
            });
        });
        var wasOpen;
        btntoggle.on("click", function(e) {
            return console.log(input.attr("data-autocomplete-loaded")), input.attr("data-autocomplete-loaded") ? wasOpen ? void btntoggle.blur() : (input.focus(), 
            void input.autocomplete("search", "")) : input.focus();
        }).mousedown(function() {
            wasOpen = input.autocomplete("widget").is(":visible");
        });
    });
});