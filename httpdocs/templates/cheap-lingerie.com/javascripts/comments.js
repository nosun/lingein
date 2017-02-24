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
	$this.trigger('change');
	});
	if($.trim($this.val())!=''){
	$this.trigger('change');
	}
	};
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
$('.writeReply .button').bind('click',function(){
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