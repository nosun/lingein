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
			var thumb_list = $("#thumblist a");
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
				$('#thumbnailsUp').addClass('disabled');
				if(thumb_list.length > 5){
					$("#thumbnailsDown").removeClass('disabled');
				}
			}
		});
	//}
	//if($("#thumbnailsDown a").hasClass('hasItem')){
		$("#thumbnailsDown a").click(function(){
			var thumb_list = $("#thumblist a");
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
				$('#thumbnailsDown').addClass('disabled');
				if(thumb_list.length > 5){
					$("#thumbnailsUp").removeClass('disabled');
				}
			}
		});
	//}
		$('ul.product-dimension li').click(function(){
			$('ul.product-dimension li').each(function(){
				$(this).removeClass('select');
				$('.product-overview div.show-item').removeClass('select');
			});
			$(this).addClass('select');
			$('.product-overview div.show-item.' + $(this).attr('id')).addClass('select');
		});
		ajaxGetInventory();
		$('.custom-select select').change(ajaxGetInventory);
		$('form#prOrder select.required').change(checkSelect);
		//check cart.
		$('form#prOrder').submit(formValidCheck);
		$('input.numcheck[name="qty"]').change(function(){
			var data_max = $(this).attr('data-max');
			if (typeof data_max !== 'undefined' && data_max !== false) {
				//has max attribute.
				if(parseInt(data_max) < parseInt($(this).val())){
					//max < obtained.
					$('input.add2Cart').attr('disabled','disabled').addClass('disabled');
					$('#stockInfo').addClass('errorInfo').text('No enough items.');
					//$('#stockInfo').removeClass().text('').addClass('errorInfo').text('Only '+ data_max +' items left in stock.');
				}else{
					//tell user the storage is ok.
					$('input.add2Cart').removeAttr('disabled').removeClass('disabled');
					$('#stockInfo').addClass('successInfo').text('In stock. Shipping in 24 hours.');
				}
			}
		});
		
});


function selectAttr(name,val){
    $("#attr_"+name).val(val);
  }
function checkSelect(){
    var isFormValid = true;
    if ($.trim($(this).val()).length == 0){
    	$('form#prOrder').addClass('focus');
    	$('p.error_notice', $(this).parent().parent()).css({'display':'inline-block'});
    	$('p.success_notice', $(this).parent().parent()).css({'display':'none'});
        isFormValid = false;
    } else {
    	$('p.error_notice', $(this).parent().parent()).css({'display':'none'});
    	$('p.success_notice', $(this).parent().parent()).css({'display':'inline-block'});
    }

    return isFormValid;
}

function formValidCheck(){
    var isFormValid = true;
    $('select.required, input.required', this).each(function(){
        if ($.trim($(this).val()).length == 0){
        	$('form#prOrder').addClass('focus');
        	$('p.error_notice', $(this).parent().parent()).css({'display':'inline-block'});
        	$('p.success_notice', $(this).parent().parent()).css({'display':'none'});
            isFormValid = false;
        } else {
        	$('p.error_notice', $(this).parent().parent()).css({'display':'none'});
        	$('p.success_notice', $(this).parent().parent()).css({'display':'inline-block'});
        }
    });
    return isFormValid;
}

function ajaxGetInventory(){
	$('#stockInfo').removeClass().text('');
	var vals = {};
	var has_empty_val = false;
	$('.custom-select select').each(function(){
		vals[this.name] = $(this).val();
		if(vals[this.name] == ''){
			has_empty_val = true;
		}
	});
	if(!has_empty_val){
		$.ajax({
			  url: url('inventory/ajaxgetinventory/'),
			  type: 'POST',
			  //dataType :'json',
			  data: {'sn':$('#p_sn').text(), 'values':vals}
			}).done(function (result) {
				$('input.numcheck[name="qty"]').attr('data-max', result);
				var data_max = $('input.numcheck[name="qty"]').attr('data-max');
				var value = $('input.numcheck[name="qty"]').val();
				if(result == '0'){
					//disable
					$('input.add2Cart').attr('disabled','disabled').addClass('disabled');
					var future = new Date();
					future.setDate(future.getDate() + 14);
					future = (future.getMonth()+1)+'/'+future.getDate()+'/'+future.getFullYear();
					$('#stockInfo').addClass('errorInfo').text('The item is out of stock. It will be in stock '+ future +'.');
				}else if(parseInt(data_max) < parseInt(value)){
					$('input.add2Cart').attr('disabled','disabled').addClass('disabled');
					$('#stockInfo').addClass('errorInfo').text('No enough items.');
					//$('#stockInfo').addClass('errorInfo').text('Only '+ data_max +' items left in stock.');
				}else{
					//tell user the storage is ok.
					$('input.add2Cart').removeAttr('disabled').removeClass('disabled');
					$('#stockInfo').addClass('successInfo').text('In stock. Ships in 24 hours.');
				}
			});
	}
}