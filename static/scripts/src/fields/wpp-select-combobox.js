jQuery(document).ready(function($){
    var wraper = $('.wpp-taxonomy-select-combobox');
    wraper.each(function(){
        var $this = $(this);
        var taxonomy = $this.attr('data-taxonomy');
        var input = $this.find('.ui-autocomplete-input');
        var btntoggle  = $this.find('.select-combobox-toggle');

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
                  var exist = (item.label == input.val());
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
                    input.autocomplete( "search", input.val() );
                });
                input.attr('data-autocomplete-loaded', true);
                if(input.is(':focus'))
                    input.autocomplete( "search", '');
            }); 
        });
        var wasOpen;
        btntoggle.on('click', function(e){
            console.log(input.attr('data-autocomplete-loaded'));
            if(!input.attr('data-autocomplete-loaded'))
                return input.focus();
            if ( wasOpen ) {
                btntoggle.blur();
                return;
            }
            input.focus();
            input.autocomplete( "search", '');
        })
        .mousedown(function() {
            wasOpen = input.autocomplete( "widget" ).is( ":visible" );
        });

    });

});