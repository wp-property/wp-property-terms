jQuery(document).ready(function($){
    var wraper = $('.rwmb-wpp-taxonomy-wrapper');
    wraper.each(function(){
        var $this = $(this);
        var tagchecklist = $this.find('.tagchecklist');
        var datataxcounter = $this.attr('data-tax-counter');
        var attrName = $this.attr('data-name');
        var taxonomy = $this.attr('data-taxonomy');
        var btnAdd = $this.find('.taxadd');
        var input  = $this.find('.newtag');
        var template = $('#wpp-terms-taxnomy-template').html();
        var taxList = {};

        var query = {
            action: 'term_autocomplete',
            taxonomy: taxonomy,
        };

        var url = ajaxurl + '?' + jQuery.param(query);
        input.one('focus', function(){
            input.addClass('ui-autocomplete-loading');
            $.ajax(url)
             .done(function(data){
                input.removeClass('ui-autocomplete-loading');
                taxList = data;
                input.autocomplete({
                    minLength: 0,
                    source: data,
                    focus: function( event, ui ) {
                        input.val( ui.item.label );
                        return false;
                    },
                    select: function( event, ui ) {
                        input.val( ui.item.label );
                        return false;
                    }

                })
                .autocomplete( "instance" )._renderItem = function( ul, item ) {
                  var exist = is_already_added(item.value, tagchecklist.children());
                  var selected = exist?'ui-state-selected':'';
                  return $( "<li>" )
                    .append( "<a class='"+selected+"'>" + item.label + "</a>" )
                    .appendTo( ul );
                };

                input.autocomplete( "widget" ).addClass('wpp-autocomplete');

                input.on('focus', function(){
                    wasOpen = input.autocomplete( "widget" ).is( ":visible" );
                    if ( wasOpen ) {
                      return;
                    }
         
                    // Pass empty string as value to search for, displaying all results
                    input.autocomplete( "search", "" );
                });

                if(input.is(':focus'))
                    input.autocomplete( "search", input.val() );
            }); 
        });

        btnAdd.on('click', function(e){
            var tag = input.val();
            var taglistChild = tagchecklist.children();
            if(tag == ''){
                input.focus();
                return;
            }
            tag = tag.split(",");

            $.each(tag, function(index, item){
                var item = item.trim();
                var label = item;
                var exist = false;

                // serching if it's available in taxlist, if then use tax id.
                $.each(taxList, function(i, tax){
                    if(item == tax.label){
                        item = tax.value;
                        label = tax.label;
                        return false;
                    }
                });

                // If already added
                exist = is_already_added(item, taglistChild);

                if(exist != true){
                    var tmpl = _.template( template, {label: label, val: item, name: attrName});
                    tagchecklist.append(tmpl);
                }
            });
            
            input.val('');
            
        });

        // Hook for enter key.
        input.keypress(function(e){
            if ( e.which == 13 ){
                btnAdd.trigger('click');
                e.preventDefault();
                return false;
            }
        });

        // Remove tag button.
        tagchecklist.on('click', '.ntdelbutton', function(){
            $(this).parent().remove();
        });

    });
    var is_already_added = function(value, tagList){
        exist = false;
        $.each(tagList, function(i, tag){
            var val = $(tag).find('input').val();
            if (value == val) {
                exist = true;
                return false;
            }
        });
        return exist;
    }
});