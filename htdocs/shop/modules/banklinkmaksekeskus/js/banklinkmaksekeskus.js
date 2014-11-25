/* 
 * 
 *  Copyright 2013 Aktsiamaailm OÜ
 *  Litsentsitingimused on saadaval http://www.e-abi.ee/litsents.
 *  

 */



/**
 * <p>Adds global-value checkbox for setting global value under multistore environment</p>
 * @param {jQuery} $
 * @param {type} undefined
 * @returns {undefined}
 */
(function($, undefined ) {
    var dataKey = 'maksekeskus_storescope';
    $.maksekeskus_storescope = {
        conf: {
        }
    };
    /**
     * 
     * @param {object} conf
     * @param {dom} template
     */
    function initTemplate(conf, template) {
        var left = template.parent().prev();
        //init procedure
        applyInputWithGlobalCheckbox(left, template);
        
        //click procedures
        template.change(function(event) {
            if (template.attr('checked')) {
                left.val(template.val());
            } else {
                left.val('');
            }
            applyInputWithGlobalCheckbox(left, template);
        });
    }
    /**
     * <p>Disables left input, when right box is checked</p>
     * @param {dom} leftInput any single html input,select,textarea
     * @param {dom} rightCheckbox input type checkbox
     * @returns {undefined}
     */
    function applyInputWithGlobalCheckbox(leftInput, rightCheckbox) {
        if (rightCheckbox.val() && leftInput.val() === rightCheckbox.val()) {
            //matching values
            //disable left
            //set template name html attribute
            leftInput.attr('disabled', 'disabled');
            rightCheckbox.attr('checked', 'checked');
            rightCheckbox.attr('name', leftInput.attr('name'));
        } else {
            //non matching values
            leftInput.removeAttr('disabled');
            rightCheckbox.removeAttr('name');
            rightCheckbox.removeAttr('checked');
        }
        
    }
    
    /**
     * <p>Initialization function</p>
     * @param {object} conf
     * @returns {true}
     */
    $.fn.maksekeskus_storescope = function(conf) {
        conf = $.extend(true, {}, $.maksekeskus_storescope.conf, conf),
                $that = $(this);
        //avoid adding event handlers twice
        if (!$that.data(dataKey)) {
            initTemplate(conf, $that);
            
            
            $that.data(dataKey, true);
        }
        return $that.data(dataKey);
    };

}( jQuery ));
