

/*function addToCart(pid){
	 var ajaxUrl = url('cart/cartpost');
	$.ajax( {
	  type : "POST",
	  url : ajaxUrl + encodeURI(pid) + "/" + encodeURI(1),
	  contentType : "application/txt; charset=utf-8",
	  dataType : "txt",
	  success : function(msg) {
		if (msg == -1) {
		  obj.val(oldqty);
		  alert('Goods shortage!');
		}
	  }
	});
}*/

function setClientMessage(message){
	
}

function checkCartShipping(){
	
	//check address information
	if($('input#delivery_first_name').val()=='' ){
		$('#client_message').text('Required field "First Name" should not be empty.');
		$('#client_message').css('display','block');
		$(window).scrollTop($('#client_message').position().top);
		$('input#delivery_first_name').focus();
		return false;
	}
	if($('input#delivery_last_name').val()==''){
		$('#client_message').text('Required field "Last Name" should not be empty.');
		$('#client_message').css('display','block');
		$(window).scrollTop($('#client_message').position().top);
		$('input#delivery_last_name').focus();
		return false;
	}
	if($('input#delivery_address').val()==''){
		$('#client_message').text('Required field "Delivery Address" should not be empty.');
		$('#client_message').css('display','block');
		$(window).scrollTop($('#client_message').position().top);
		$('input#delivery_address').focus();
		return false;
	}
	if($('input#delivery_city').val()==''){
		$('#client_message').text('Required field "City" should not be empty.');
		$('#client_message').css('display','block');
		$(window).scrollTop($('#client_message').position().top);
		$('input#delivery_city').focus();
		return false;
	}
	if($('input#delivery_postcode').val()==''){
		$('#client_message').text('Required field "ZIP/Postal Code" should not be empty.');
		$('#client_message').css('display','block');
		$(window).scrollTop($('#client_message').position().top);
		$('input#delivery_postcode').focus();
		return false;
	}
	if($('input#delivery_mobile').val()==''){
		$('#client_message').text('Required field "Phone Number" should not be empty.');
		$('#client_message').css('display','block');
		$(window).scrollTop($('#client_message').position().top);
		$('input#delivery_mobile').focus();
		return false;
	}
	
	//check delivery information
	var shipping_method = document.checkoutForm.shipping_method;
	var shipping_value;
	for(i=0;i<shipping_method.length;i++)
	{
		if(shipping_method[i].checked)
			shipping_value=shipping_method[i].value;
	}
	if (shipping_value == undefined) {
		$('#client_message').text('Please choose a shipping method');
		$('#client_message').css('display','block');
		$(window).scrollTop($('#client_message').position().top);
		return false;
	}

	//check payment information.
	var payment_method = document.checkoutForm.payment_method;
	var payment_value;
	for(i=0;i<payment_method.length;i++)
	{
		if(payment_method[i].checked)
			payment_value=payment_method[i].value;
	}
	if (payment_value == undefined) {
		$('#client_message').text('Please choose a payment method');
		$('#client_message').css('display','block');
		$(window).scrollTop($('#client_message').position().top);
		return false;
	}

	//will not handling more than one submit buttons for a form.
	var submits = $('input:submit', this);
	if(submits.length == 1){
       submits.attr("disabled",true);
       $('<input>').attr({
           type: 'hidden',
           name: submits.attr('name')}).appendTo(this);
	}
       return true;
	}

function checkRegister(){
	if(document.regForm.username.value.length<3){
		$('#client_message').text('Username should not less than 3 characters!');
		$('#client_message').css('display','block');
		$(window).scrollTop($('#client_message').position().top);
		return false;
	}else if(document.regForm.username.value.length>20){
		$('#client_message').text('Username should not more than 20 characters!');
		$('#client_message').css('display','block');
		$(window).scrollTop($('#client_message').position().top);
		return false;
	}
	if(!isCharNumber(document.regForm.username.value)){
		$('#client_message').text('Username should only contains numbers and letters!');
		$('#client_message').css('display','block');
		$(window).scrollTop($('#client_message').position().top);
		return false;
	}
	if(document.regForm.password.value.length<5){
		$('#client_message').text('password should not less than 5 characters!');
		$('#client_message').css('display','block');
		$(window).scrollTop($('#client_message').position().top);
		return false;
	}else if(document.regForm.password.value.length>20){
		$('#client_message').text('password should not be over 20 characters!');
		$('#client_message').css('display','block');
		$(window).scrollTop($('#client_message').position().top);
		return false;
	}
	if(document.regForm.confirm_password.value != document.regForm.password.value){
		$('#client_message').text('The two passwords are not match.');
		$('#client_message').css('display','block');
		$(window).scrollTop($('#client_message').position().top);
		return false;
	}
	return true;
}

function checkChangePwd(){
	  if(document.changePwdForm.newpwd.value.length<5){
	      alert('password should not less than 5 characters!');
	      return false;
	  }else if(document.changePwdForm.newpwd.value.length>20){
	     alert('password should not be over 20 characters!!');
	        return false;
	  }
	   if(document.changePwdForm.newpwd2.value != document.changePwdForm.newpwd.value){
	        alert('The two passwords are not match.');
	        return false;
	    }
	    return true;
}

function checkGuestBook(){
	if(document.guestBookForm.nickname.value.length==0){
		alert('nickname can\'t empty!');
		return false;
	}
	if(document.guestBookForm.subject.value.length==0){
		alert('subject can not be empty');
		return false;
	}
	if(document.guestBookForm.comment.value.length==0){
        alert('comment can not be empty');
        return false;
    }
    return true;
}

function isCharNumber(s){
    if( s.search(/(^[a-z0-9A-Z]{3,20})$/)==-1 ) {
     	return false;
    }
   return true; 
}