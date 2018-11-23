
var Merlin = (function($){

    var t;

    // callbacks from form button clicks.
    var callbacks = {
		save_logo: function(btn){
			var logosave = new saveLogo(btn);
			logosave.init(btn);
		},
        install_child: function(btn) {
            var installer = new ChildTheme();
            installer.init(btn);
        },
        activate_license: function(btn) {
            var license = new ActivateLicense();
            license.init(btn);
        },
        install_plugins: function(btn){
            var plugins = new PluginManager();
            plugins.init(btn);
        },
        install_content: function(btn){
            var content = new ContentManager();
            content.init(btn);
        }
    };

    function window_loaded(){

    	var 
    	body 		= $('.merlin__body'),
    	body_loading 	= $('.merlin__body--loading'),
    	body_exiting 	= $('.merlin__body--exiting'),
    	drawer_trigger 	= $('#merlin__drawer-trigger'),
    	drawer_opening 	= 'merlin__drawer--opening';
    	drawer_opened 	= 'merlin__drawer--open';

    	setTimeout(function(){
	        body.addClass('loaded');
	    },100); 

    	drawer_trigger.on('click', function(){
        	body.toggleClass( drawer_opened );
        });

    	$('.merlin__button--proceed:not(.merlin__button--closer)').click(function (e) {
		    e.preventDefault();
		    var goTo = this.getAttribute("href");

		    body.addClass('exiting');

		    setTimeout(function(){
		        window.location = goTo;
		    },400);       
		});

        $(".merlin__button--closer").on('click', function(e){

        	body.removeClass( drawer_opened );

            e.preventDefault();
		    var goTo = this.getAttribute("href");

		    setTimeout(function(){
		        body.addClass('exiting');
		    },600);   
		    
		    setTimeout(function(){
		        window.location = goTo;
		    },1100);   
        });

        $(".button-next").on( "click", function(e) {            
            e.preventDefault();
            var loading_button = merlin_loading_button(this);
            if ( ! loading_button ) {
                return false;
            }
            var data_callback = $(this).data("callback");
            if( data_callback && typeof callbacks[data_callback] !== "undefined"){
                // We have to process a callback before continue with form submission.
                callbacks[data_callback](this);
                $(".saswp_branding").hide();
                return false;
            } else {                
                return true;
            }
        });
    }

    function saveLogo() {
    	var body 				= $('.merlin__body');
        var complete, notice 	= $("#child-theme-text");

        function ajax_callback(r) {
            
            if (typeof r.done !== "undefined") {
            	setTimeout(function(){
			        notice.addClass("lead");
			    },0); 
			    setTimeout(function(){
			        notice.addClass("success");
			        notice.html(r.message);
			    },600); 
			    
                
                complete();
            } else {
                notice.addClass("lead error");
                notice.html(r.error);
            }
        }

        function do_ajax() {
			var params = {
                action: "saswp_save_installer",
                wpnonce: saswp_install_params.wpnonce,
				}
			jQuery('ul.merlin__drawer--import-content').find('input, select').each(function(key, fields){
				
				switch(jQuery(this).attr('type')){
					case 'text':
					case 'hidden':
						params[jQuery(this).attr('name')] = jQuery(this).val();
					break;
					case 'checkbox':
						if(jQuery(this).prop('checked')==true){
							params[jQuery(this).attr('name')] = 1;
						}else{
							params[jQuery(this).attr('name')] = 0;
						}
					break;
					default: 
						params[jQuery(this).attr('name')] = jQuery(this).val();
					break;
				}
			});
            jQuery.post(saswp_install_params.ajaxurl, params, ajax_callback).fail(ajax_callback);
        }

        return {
            init: function(btn) {
                complete = function() {

                	setTimeout(function(){
							$(".merlin__body").addClass('js--finished');
						},1500);

                	body.removeClass( drawer_opened );

                	setTimeout(function(){
							$('.merlin__body').addClass('exiting');
						},3500);   

                    	setTimeout(function(){
							window.location.href=btn.href;
						},4000);
		    
                };
                do_ajax();
            }
        }
    }
	
	
	





    

    function merlin_loading_button( btn ){

        var $button = jQuery(btn);

        if ( $button.data( "done-loading" ) == "yes" ) {
        	return false;
        }

        var completed = false;

        var _modifier = $button.is("input") || $button.is("button") ? "val" : "text";
        
        $button.data("done-loading","yes");
        
        $button.addClass("merlin__button--loading");

        return {
            done: function(){
                completed = true;
                $button.attr("disabled",false);
            }
        }

    }

    return {
        init: function(){
            t = this;
            $(window_loaded);
        },
        callback: function(func){
            console.log(func);
            console.log(this);
        }
    }

})(jQuery);

Merlin.init();


jQuery(document).ready(function($) {
   $(".saswp-social-fields input[type=checkbox]").change(function(){       
        socialFields($(this));
   })
   $(".saswp-social-fields input[type=checkbox]").each(function(){
        socialFields($(this));
   }) 
   function socialFields(self){
        if(self.prop('checked')){
            var field_name = self.attr('name');
            field_name = field_name.replace("_checkbox",'');
            self.parent('.saswp-social-fields').find('input[type=text]').show();
        }else{
            self.parent('.saswp-social-fields').find('input[type=text]').val('').hide();
        }
   }


    $(".post-type-fields input[type=checkbox]").change(function(){
        var self = $(this);
        if(self.prop('checked')){
            var field_name = self.attr('name');
            field_name = field_name.replace("_checkbox",'');
            self.parent('.post-type-fields').find('select#schema_type').show();
        }else{
            self.parent('.post-type-fields').find('select#schema_type').val('').hide();
        }
   });
    $('.post-type-fields').each(function(){
        $(this).find('select#schema_type').val('').hide();
    });
});