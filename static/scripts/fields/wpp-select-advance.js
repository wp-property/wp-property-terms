jQuery(document).ready(function($) {
    var wraper = $(".rwmb-wpp-taxonomy-wrapper");
    wraper.each(function() {
        var $this = $(this), tagchecklist = $this.find(".tagchecklist"), datataxcounter = $this.attr("data-tax-counter"), attrName = $this.attr("data-name"), btnAdd = $this.find(".taxadd"), input = $this.find(".newtag"), template = $("#wpp-terms-taxnomy-template").html(), taxList = window["wpp_terms_available_options_" + datataxcounter];
        input.autocomplete({
            minLength: 0,
            source: taxList,
            focus: function(event, ui) {
                return input.val(ui.item.label), !1;
            },
            select: function(event, ui) {
                return input.val(ui.item.label), !1;
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            var exist = is_already_added(item.value, tagchecklist.children()), selected = exist ? "ui-state-selected" : "";
            return $("<li>").append("<a class='" + selected + "'>" + item.label + "</a>").appendTo(ul);
        }, input.autocomplete("widget").addClass("wpp-autocomplete"), input.on("focus", function() {
            wasOpen = input.autocomplete("widget").is(":visible"), wasOpen || input.autocomplete("search", "");
        }), btnAdd.on("click", function(e) {
            var tag = input.val(), taglistChild = tagchecklist.children();
            return "" == tag ? void input.focus() : (tag = tag.split(","), $.each(tag, function(index, item) {
                var item = item.trim(), label = item, exist = !1;
                if ($.each(taxList, function(i, tax) {
                    return item == tax.label ? (item = tax.value, label = tax.label, !1) : void 0;
                }), exist = is_already_added(item, taglistChild), 1 != exist) {
                    var tmpl = _.template(template, {
                        label: label,
                        val: item,
                        name: attrName
                    });
                    tagchecklist.append(tmpl);
                }
            }), void input.val(""));
        }), input.keypress(function(e) {
            return 13 == e.which ? (btnAdd.trigger("click"), e.preventDefault(), !1) : void 0;
        }), tagchecklist.on("click", ".ntdelbutton", function() {
            $(this).parent().remove();
        });
    });
    var is_already_added = function(value, tagList) {
        return exist = !1, $.each(tagList, function(i, tag) {
            var val = $(tag).find("input").val();
            return value == val ? (exist = !0, !1) : void 0;
        }), exist;
    };
});