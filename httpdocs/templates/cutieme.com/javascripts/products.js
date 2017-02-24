//check cart.
$('form#prOrder').submit(function(){
    var isFormValid = true;
    $('.prOrderOptions .custom-select', this).each(function(){
    	if($('button.pressed', this).length){

    		selectAttr($('dd', this).attr('class'), $('button.pressed', this).val());
    		//user has already pressed the button.
    		isFormValid = true;
    	}else{
    		isFormValid = false;
        	$('.error_notice', $(this)).css({'display':'inline'});
        	$('.success_notice', $(this)).css({'display':'none'});
        	return isFormValid;
    	}
    });
    return isFormValid;
});

$('form#prOrder .non-empty').change(function(){
    var isFormValid = true;
    if ($.trim($(this).val()).length == 0){
    	$('form#prOrder').addClass('focus');
    	$('p.error_notice', $(this).parent()).css({'display':'inline'});
    	$('p.success_notice', $(this).parent()).css({'display':'none'});
        isFormValid = false;
    } else {
    	$('p.error_notice', $(this).parent()).css({'display':'none'});
    	$('p.success_notice', $(this).parent()).css({'display':'inline'});
    }
    return isFormValid;
});


function change_main_pic(selector){
	$("#prodthumbnails a").removeClass('current');
	$(selector).addClass('current');
	$("#main-display-pic").attr("src", $(selector).attr('rev'));
	$("#w-featurePics").attr("href", $(selector).attr('rev'));
}

var rating_title = new Array(' ', 'Poor', 'Fair', 'Average', 'Good', 'Excellent'); 
var reviewText1 = 'Please only provide JPG files.';
var reviewText2 = 'Individual photo size cannot exceed 2MB.'; 

$.fn.checkCharLength=function(errContent){
	var $this=$(this);
	$this.change(function(event){
	var curLength = $this.val().length;
	var maxlength = $this.attr('maxlength');
	if(curLength > maxlength ){
	$this.val($.trim($this.val()).substr(0,maxlength)).trigger('change');
	return;
	}
	$(errContent).text(maxlength - curLength).parent().toggleClass('red', curLength > maxlength);
	}).keyup(function(){
	$this.trigger('change')
	});
	if($.trim($this.val())!=''){
	$this.trigger('change')
	}
	} 
$('#reviewContent').checkCharLength('#reContentChar');
$('#star-rating').mousemove(function(e){
var star = Math.floor((e.clientX - $(this).offset().left)/20+1),cl = $(this).attr('class');
$(this).attr({'class':cl.replace(/starH\d/,'starH'+star),'title':rating_title[parseInt(star)]});
$('#star-tip').text(' ( '+rating_title[parseInt(star)]+' ) ');
}).mouseout(function(e){
var cl = $(this).attr('class');
$(this).attr('class',cl.replace(/starH\d/,'starH0'));
$('#star-tip').text('');
}).click(function(e){
var star = Math.floor((e.clientX - $(this).offset().left)/20+1),cl = $(this).attr('class');
$('#rating').val(star);
$(this).attr({'class':cl.replace(/starB\d/,'starB'+star),'title':rating_title[parseInt(star)]});
$('#ratingErr').html('');
}); 
$('.commentsCount').bind('click',function(){
	$(this).parents('.w-review').find('.w-reviewReplys,.writeReply').toggle();
	return false;
	});
	$('.writeReply textarea').focus(function(){
	if($(this).val()=='Add your reply here...'){
	$(this).val('');
	}
	}).blur(function(){
	if($(this).val()==''){
	$(this).val('Add your reply here...');
	}
	}).click(function(){
	if($(this).parent().next().text().length>0){
	$(this).parent().next().text('');
	}
	});

$('.toogleWriteReview').bind('click', function(){
	var writeReview = $('#write-a-review');
	if( writeReview.length == 0){
		redirect(url('user/login/'));
	}else{
		if( $(writeReview).css('display') == 'none'){
			$(writeReview).css({'display':'block'});
		} 
	}
});
$('#cancelWriteReview').bind('click', function(){
	var writeReview = $('#write-a-review');
	if( $(writeReview).css('display') == 'block'){
		$(writeReview).css({'display':'none'});
	}
	$('#reviewContent').text();
});
$('.writeReply .textbtn').bind('click',function(){
	var _self=$(this);
	$(_self).prev('.errors').text('');
	var postData=$.trim(_self.parents('form').find('textarea').val());
	if(!postData||postData=='Add your reply here...'||postData.replace(/(\s|　)/g,'')==''){
	$(_self).prev('.errors').text('Please input your reply.');
	return false;
	}
	if(postData.length>3000){
	$(_self).prev('.errors').text('Is your Review Content correct? Our system requires a maximum of 3000 characters.');
	return false;
	}
	/*
	_self.parents('form').find('input[type=hidden]').each(function(){
	postData+='&'+$(this).attr('name')+'='+$.trim($(this).val());
	});
	*/
	$(_self).attr('disabled',true);
	$.ajax({
	url:url('comment/addReply'),
	//data:'review_comment='+postData,
	data:_self.parents('form').find('input[type=hidden],textarea'),
	type:'post',
	dataType:'json',
	complete:function(xhr, status){
	$(_self).attr('disabled',false);
	},
	success:function(result){
	if(result&&result.ok){
	_self.parents('form').find('textarea').val('Add your reply here...');
	if(_self.parents('.writeReply').prev().hasClass('w-reviewReplys')){
	$(
	'<div class="reviewReply">'+
	'<div>'+
	'<p>'+result.comment.replace(/\n/g,"<br/>")+'</p>'+
	'<p><span class="lightGray">By</span> <cite class="'+((result.nickname == 'Administrator')?'Dlink litb':'replier')+'">'+result.nickname+'</cite> <cite class="time">'+result.time+'</cite></p>'+
	'</div>'+
	'</div>'
	).appendTo(_self.parents('.writeReply').prev());
	}else{
	$(
	'<div class="w-reviewReplys" style="display: block;">'+
	'<span class="arrow"></span>'+
	'<div class="reviewReply">'+
	'<div>'+
	'<p>'+result.comment.replace(/\n/g,"<br/>")+'</p>'+
	'<p><span class="lightGray">By</span> <cite class="'+((result.nickname == 'Administrator')?'Dlink litb':'replier')+'">'+result.nickname+'</cite> <cite class="time">'+result.time+'</cite></p>'+
	'</div>'+
	'</div>'+
	'</div>'
	).insertAfter(_self.parents('.writeReply').prev());
	}
	var commentsCount=_self.parents('.w-review').find('.commentsCount');
	var counts=parseInt(commentsCount.attr('reply'),10)+1;
	commentsCount.text('Reply ('+counts+')');
	commentsCount.attr('reply',counts);
	}
	else {
	$(_self).prev('.errors').text('Please refresh this page and try again.');
	}
	}
	}
	);
	return false;
}); 

$(function(){
	//first make the image change when click the thumbnail.
	$("#prodthumbnails a").click(function(){
		change_main_pic(this);
	});
	$('ul.product-dimension li').click(function(){
		$('ul.product-dimension li').each(function(){
			$(this).removeClass('select');
			$('.product-overview div.show-item').removeClass('select');
		});
		$(this).addClass('select');
		$('.product-overview div.show-item.' + $(this).attr('id')).addClass('select');
	});
	ajaxGetInventory();
	$('#product-abstract .custom-select button').click(function(ev){
		ev.preventDefault();
		$('button', $(this).parent()).each(function(){
			$(this).removeClass('pressed');
		});
		$('.error_notice.error', $(this).parent().parent()).hide();
		$(this).addClass('pressed');
		ajaxGetInventory();
	});
	$('input.numcheck[name="qty"]').change(function(){
		var data_max = $(this).attr('data-max');
		if (data_max !== undefined && data_max !== false) {
			if(parseInt(data_max) < parseInt($(this).val())){
				$('button.add2Cart').attr('disabled','disabled').addClass('disabled');
				$('#stockInfo').removeClass('successInfo').addClass('errorInfo').text('Only ' + data_max + ' Items Left.');
			}else{
				$('button.add2Cart').removeAttr('disabled').removeClass('disabled');
				$('#stockInfo').removeClass('errorInfo').addClass('successInfo').text('In stock. Ships in 24 hours.');
			}
		}
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

function ajaxGetInventory(){
	$('#stockInfo').removeClass().text('');
	var vals = {};
	var has_empty_val = false;
	var fields_num = 0;
	$('.custom-select button.pressed').each(function(){
		var propName = $(this).parent().attr('class');
		vals[propName] = $(this).val();
		++fields_num;
	});
	var required_fields_num = $('.custom-select .required').length;
	if (fields_num < required_fields_num) {
		has_empty_val = true;
	}
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
					$('button.add2Cart').addClass('disabled');
					var future = new Date();
					future.setDate(future.getDate() + 14);
					future = (future.getMonth()+1)+'/'+future.getDate()+'/'+future.getFullYear();
					$('#stockInfo').addClass('errorInfo').text('The item is out of stock. It will be in stock '+ future +'.');
				}else if(parseInt(data_max) < parseInt(value)){
					$('button.add2Cart').addClass('disabled');
					$('#stockInfo').addClass('errorInfo').text('Only ' + result + ' Items Left.');
					//$('#stockInfo').addClass('errorInfo').text('Only '+ data_max +' items left in stock.');
				}else{
					//tell user the storage is ok.
					$('button.add2Cart').removeAttr('disabled').removeClass('disabled');
					$('#stockInfo').addClass('successInfo').text('In stock. Ships in 24 hours.');
				}
			});
	}
}