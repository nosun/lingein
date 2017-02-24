
$(function () {
    $('a.checkoutBtn').on("click", function (e) {
    	if($(this).hasClass('disabled')){
    		e.preventDefault();
    	}
    });
    /*
	$('input.ajax_qty').change(function(){
		ajaxCheckInventory($(this).prop('id'));
	});
	*/
    
});

