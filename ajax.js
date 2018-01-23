function disableUser_byId(userid,isDisable){
    var url = ajaxurl;
    jQuery('#disable_by_id_spinner_loading_'+userid).show();
    jQuery.ajax({
        type: 'POST',
        url: url,
        data: isDisable ? {
            action: 'dwul_action_callback',
            user_id: userid,
        }:{
            action: 'dwul_enable_user_email',
            activateuserid: userid
        },

        success: function(response) {
            if(response == 1){
                jQuery('#wpbody-content .wrap .wp-header-end').after('<div id="notice-bloqueo-usuarios" class="updated notice"> Usuario deshabilitado con éxito</div>');
            }
            else{
                jQuery('#wpbody-content .wrap .wp-header-end').after('<div id="notice-bloqueo-usuarios" class="error notice"> Usuario no pudo ser deshabilitado!</div>');
            }
            setTimeout(function() {
                 window.location.reload();
              }, 2000);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        },
        done: function () {
            jQuery('#disable_by_id_spinner_loading_'+userid).hide();
        }
    });
}


jQuery(document).ready(function() {
                jQuery("#disableuser").click(function() {

                   
                    var userid = jQuery("#user_id").val();
                    if(userid == ''){
                        
                        jQuery('#user_id').fadeIn().delay(2000).fadeOut('slow');
                       
                        return false;
                    }
                    
                    var url = ajaxurl;
                    jQuery.ajax({
                        type: 'POST',
                        url: url,
                        data: {
                            action: 'dwul_action_callback',
                            user_id: userid,
                        },
                        beforeSend: function() {
                            
                            jQuery("#processimage").show();
                        
                        },
                        success: function(response) {
                            console.log("Resp: " + response);
                             if(response == 11){
                              jQuery("#adminroleerror").fadeIn().delay(2000).fadeOut('slow');  
                              jQuery("#processimage").hide();
                              return false;  
                            }
                           
                           if(response == 12){
                              
                              jQuery("#notexit").fadeIn().delay(2000).fadeOut('slow');  
                              jQuery("#processimage").hide();
                              return false;  
                            } 
                            if(response == 15){
                                
                              jQuery("#notinsert").fadeIn().delay(2000).fadeOut('slow');  
                              jQuery("#processimage").hide();
                              return false;  
                            }
                            
                            
                            if(response == 1){
                                location.reload();
                                jQuery("#disableerror").show();
                                jQuery("#user_id").val('');
                                jQuery("#processimage").hide();
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log(textStatus, errorThrown);
                        }
                    });
                    return false;
                });
                
                 jQuery(".customdisableuser td a").click(function() {

                 var acivateid = jQuery(this).attr('id');
                 
                    var url = ajaxurl;
                    jQuery.ajax({
                        type: 'POST',
                        url: url,
                        data: {
                            action: 'dwul_enable_user_email',
                            activateuserid: acivateid
                        },
                        beforeSend: function() {
                            

                        },
                        success: function(userresponse) {
                            console.log("Resp: " + userresponse);
                            if(userresponse == 1){
                                
                                 jQuery("#userid"+acivateid ).fadeOut();
                            }
                            
                        },
                        error: function(jqXHR, textStatus, errorThrown) {

                            console.log(textStatus, errorThrown);
                        }
                    });
                    return false;
                });
});
