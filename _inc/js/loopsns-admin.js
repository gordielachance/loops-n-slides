jQuery(document).ready(function($) {

    //Json Viewer
    $( ".loopsns-json-container" ).each(function( index ) {
        
        var container = $(this);
        var textarea = $(this).find("textarea");
        var active_tab_idx = 0;
        

        textarea.on('change keyup paste', function() {
            
            var json = null;
            var data = textarea.val();
            data = data.trim();
            var render_el = container.find('.loopsns-json-display-read');

            if (data){
                try {
                    json = JSON.parse(data);
                    container.removeClass('loopsns-error loopsns-json-error');
                } catch(e) {
                    container.addClass('loopsns-error loopsns-json-error');
                    console.log("unable to parse JSON");
                }
            }else{
                active_tab_idx = 1;
            }
            
            if (json){
                render_el.jsonViewer(json);
            }else{
                render_el.html('');
            }
            

        });
        
        textarea.trigger('paste'); //init
        $(this).tabs({ active: active_tab_idx });
        
    });
    

});
