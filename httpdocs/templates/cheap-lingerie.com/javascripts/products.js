//check cart.
$('form#prOrder').submit(function(){
    var isFormValid = true;
    $('.required', this).each(function(){
        if ($.trim($(this).val()).length == 0){
        	$('form#prOrder').addClass('focus');
        	$('p.error_notice', $(this).parent().parent()).css({'display':'inline'});
        	$('p.success_notice', $(this).parent().parent()).css({'display':'none'});
            isFormValid = false;
        } else {
        	$('p.error_notice', $(this).parent().parent()).css({'display':'none'});
        	$('p.success_notice', $(this).parent().parent()).css({'display':'inline'});
        }
    });
    return isFormValid;
});

$('form#prOrder .required').change(function(){
    var isFormValid = true;

    if ($.trim($(this).val()).length == 0){
    	$('form#prOrder').addClass('focus');
    	$('p.error_notice', $(this).parent().parent()).css({'display':'inline'});
    	$('p.success_notice', $(this).parent().parent()).css({'display':'none'});
        isFormValid = false;
    } else {
    	$('p.error_notice', $(this).parent().parent()).css({'display':'none'});
    	$('p.success_notice', $(this).parent().parent()).css({'display':'inline'});
    }

    return isFormValid;
});


function change_main_pic(selector){
	$("#prodthumbnails a").removeClass('current');
	$(selector).addClass('current');
	$("#main-display-pic").attr("src", $(selector).attr('rev'));
	$("#w-featurePics").attr("href", $(selector).attr('rev'));
}


$(function(){
	//first make the image change when click the thumbnail.
	$("#prodthumbnails a").click(function(){
		change_main_pic(this);
	});
	//then apply up and down row feature when click the pre and next button.

	//if the previous hasItem attr is set.
	//if($("#thumbnailsUp a").hasClass('hasItem')){
		$("#thumbnailsUp a").click(function(){
			var thumb_list = $("#prodthumbnails a");
			for(var i=0;i < thumb_list.length; i++){
				//Find the first item without display:none.
				if($(thumb_list[i]).css('display')!= 'none') break;
			}
			//get it's previous display none item.
			if(i > 0){
				$(thumb_list[--i]).css({'display':'block'});
				if(thumb_list.length - i > 5){
					//this is the one that just dissapeared.
					$(thumb_list[i+5]).css({'display':'none'});
					//if the dissapeared one is the perviously selected, need to make change selection to the nearest one.
					if($(thumb_list[i+5]).hasClass('current')){
						change_main_pic(thumb_list[i+4]);
					}
				}
				if(i == 0){
					$(this).removeClass('hasItem');
					$("#thumbnailsDown a").addClass('hasItem');
				}
			}
			else{
				//the button should be gray.
				$(this).removeClass('hasItem');
				if(thumb_list.length > 5)
					$("#thumbnailsDown a").addClass('hasItem');
			}
		});
	//}
	//if($("#thumbnailsDown a").hasClass('hasItem')){
		$("#thumbnailsDown a").click(function(){
			var thumb_list = $("#prodthumbnails a");
			for(var i=thumb_list.length - 1;i >=0 ; i--){
				//Find the first item without display:none.
				if($(thumb_list[i]).css('display')!= 'none') break;
			}
			//get it's next display none item.
			if(i < thumb_list.length - 1){
				$(thumb_list[++i]).css({'display':'block'});
				if(i > 4){
					$(thumb_list[i-5]).css({'display':'none'});

					//if the dissapeared one is the perviously selected, need to make change selection to the nearest one.
					if($(thumb_list[i-5]).hasClass('current')){
						change_main_pic(thumb_list[i-4]);
					}
					
				}
				if(i == thumb_list.length - 1){
					$(this).removeClass('hasItem');
					$("#thumbnailsUp a").addClass('hasItem');
				}
			}
			else{
				//the button should be gray.
				$(this).removeClass('hasItem');
				if(thumb_list.length > 5)
					$("#thumbnailsUp a").addClass('hasItem');
			}
		});
	//}

});
function selectAttr(name,val){
    $("#attr_"+name).val(val);
  }