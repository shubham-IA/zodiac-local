/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require([
    'Magento_Sales/order/view/post-wrapper'
]);

require(['jquery', 'jquery/ui'], function($){ 

	$(document).on('change', '#history_status', function() {
		if(jQuery( "#history_status" ).val()=='requested_cancellation'){ 
			jQuery('#history_notify').prop('checked', false);
			jQuery('.order-history-comments-options').hide();
		}else{
			jQuery('.order-history-comments-options').show();
		}	
	});
});