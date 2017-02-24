function CheckIsNull(targetId, notifyId) {
	if ($.trim($('#' + targetId).val()).length == 0) {
		$('#' + notifyId).css("display", "inline");
		return false;
	} else {
		$('#' + notifyId).css("display", "none");
		return true;
	}
}

function FormatName(target) {
	$(target).val($.trim($(target).val()));
}

function CheckPhone(targetId, notifyId) {
	var phoneRegExp = /^((\+)?[1-9]{1,2})?([-\s\.])?((\(\d{1,4}\))|\d{1,4})(([-\s\.])?[0-9]{1,12}){1,2}$/;
	var phoneVal = $.trim($('#' + targetId).val());
	var numbers = phoneVal.split("").length;
	var valid = false;
	if (5 <= numbers && numbers <= 20 && phoneRegExp.test(phoneVal)) {
		$('#' + notifyId).css('display', 'none');
		valid = true;
	} else {
		$('#' + notifyId).css('display', 'block');
		valid = false;
	}
	SetPhone();
	return valid;
}

function FocusPhone()
{
    var str = jQuery('#delivery_mobile').val();
    if (str == 'example:+1 215-555-2527')
    {
        jQuery('#delivery_mobile').val('');
        jQuery('#delivery_mobile').css('color', '#333');
    }
}

function SetPhone() {
    var str = jQuery('#delivery_mobile').val();
    if (str == '') {
        jQuery('#delivery_mobile').val('example:+1 215-555-2527');
        jQuery('#delivery_mobile').css('color', '#aaa');
    }
    else {
        if (str == 'example:+1 215-555-2527') { jQuery('#telNum').css('color', '#aaa'); }
        else { jQuery('#delivery_mobile').css('color', '#333'); }
    }
}
SetPhone();

function CheckEmail(targetId, notifyId) {
	var emailRegExp = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
	var emailValue = $.trim($('#' + targetId).val());
	if (emailRegExp.test(emailValue)) {
		$('#' + notifyId).css('display', 'none');
		return true;
	} else {
		$('#' + notifyId).css('display', 'inline');
		return false;
	}
}

function CheckParam(rid) {
	var id = "";
	if (rid != "0") {
		id = "_" + rid;
	}
	if (!CheckIsNull('delivery_first_name' + id, 'sp_fname' + id)) {
		return false;
	}
	if (!CheckIsNull('delivery_last_name' + id, 'sp_lname' + id)) {
		return false;
	}
	if (!CheckIsNull('delivery_address' + id, 'sp_address1' + id)) {
		return false;
	}
	if (!CheckIsNull('delivery_city' + id, 'sp_city' + id)) {
		return false;
	}
	if (!$('#delivery_pid_select' + id).is(':visible')) {
		if (!CheckIsNull('delivery_province' + id, 'sp_province' + id)) {
			return false;
		}
	}
	if (!CheckIsNull('delivery_postcode' + id, 'sp_code' + id)) {
		return false;
	}
	if (!CheckPhone('delivery_mobile' + id, 'sp_tel' + id)) {
		return false;
	}
	return true;
}
function AddAddress() {
	if (!CheckParam("0")) {
		return false;
	}
	$('#rid').val(0);
	return true;
}

function AjaxEditAdress($requestUrl) {
	if (!CheckParam("0")) {
		return false;
	}
	$('#editAddressTb').css('border', '1px solid #ccc');
	$('#rid').val(0);
	var checkOutForm = $('form[name="checkoutForm"]');
	checkOutForm.mask("Waiting...");
	$.post($requestUrl,
		   {
			pids : $.trim($('#pids').val()),
			delivery_rid : $.trim($('#delivery_rid').val()),
			delivery_first_name : $.trim($('#delivery_first_name').val()),
			delivery_last_name : $.trim($('#delivery_last_name').val()),
			delivery_address : $.trim($('#delivery_address').val()),
			delivery_city : $.trim($('#delivery_city').val()),
			delivery_province : $('#delivery_province').is(':visible') ? $.trim($('#delivery_province').val()) : '',
			delivery_cid: $.trim($('#delivery_cid_select').val()),
			delivery_pid: $('#delivery_pid_select').is(':visible') ? $.trim($('#delivery_pid_select').val()) : 0,
			delivery_postcode: $.trim($('#delivery_postcode').val()),
			delivery_mobile: $.trim($('#delivery_mobile').val()),
			'default': $.trim($('#default').val())
		   },
		   function(response){
			   ret = eval('(' + response + ')');
			   if (ret.success) {
				   $('#checkout_shipaddr_panel').empty();
				   for (var i = 0; i < ret.data.length; i++) {
					   var $content = "<li rid='" + ret.data[i].rid + "' onclick='chooseAddress(this);' >";
					   var $checked="";
					   if (ret.data[i]['default'] == "1" || i == 0) {
						   $checked = "checked";
					   }
					   $content += "<input type='radio' " + $checked + " class='address_sel_radio' />";
					   $content += "<label><strong>(" +
								   "<span title='delivery_first_name'>" + ret.data[i].delivery_first_name + "</span>" +
								   "<span title='delivery_last_name' style='margin-right:5px'>" + ret.data[i].delivery_last_name + "</span>" + ")</strong>" +
								   "<span title='delivery_address'>" + ret.data[i].delivery_address + "</span>" +
								   "<span title='delivery_city'>" + ret.data[i].delivery_city + "</span>" +
								   "<span title='delivery_province'>" + ret.data[i].delivery_province + "</span>" +
								   "<span title='delivery_country'>" + ret.data[i].delivery_country + "</span>" +
								   "<span title='delivery_postcode'>" + ret.data[i].delivery_postcode + "</span>" + '&nbsp;Tel:' +
								   "<span title='delivery_mobile'>" + ret.data[i].delivery_mobile + "</span>" +
								   "<span title='delivery_cid' style='display:none'>" + ret.data[i].delivery_cid + "</span>" +
								   "<span title='delivery_pid' style='display:none'>" + ret.data[i].delivery_pid + "</span>" +
								   "<span title='delivery_default' style='display:none'>" + ret.data[i]['default'] + "</span>" +
								   "<a href='javascript:void(0)' style='font-weight:bold; margin-left:10px; text-decoration:underline;' onclick='editAddress(this, " + ret.data[i].rid + ");' >Edit</a></label>";
			           $content += "</li>";
					   $('#checkout_shipaddr_panel').append($content);
					   if ($checked == 'checked') {
						   $('#delivery_rid').val(ret.data[i].rid);
					   }
				   }
				   $('.tb-shippingMethod tbody tr').remove();
				   var i = 0;
				   for (var shippingMethodName in ret.shippingMethodList) {
					   var shippingMethod = ret.shippingMethodList[shippingMethodName];
					   var checked = '';
					   if (i == 0) {
						   checked = ' checked';
						   var shippingMoney = shippingMethod.shippingFee;
						   var subtotal = parseFloat($('#ot_subtotal').html().substr(1));
						   var shippingFee = parseFloat(shippingMoney.substr(1));
						   var totalPay = subtotal + shippingFee;
						   var unit = shippingMoney.substr(0,1);
						   $('#shipping_money').html(shippingMoney);
						   $('#order_amount').html(unit + totalPay);
						   i++;
					   }
					   var content = ['<tr><th valign="top" class="nowrap">',
					                  '<input type="radio" onclick="changeShipping(this.value)" name="shipping_method" value="' + shippingMethod.name + '" id="radio-' + shippingMethod.name + '"' + checked +'>',
					                  '<strong>' + shippingMethod.displayName + '</strong></th>',
					                  '<td>' + shippingMethod.description + '</td>',
					                  '<td valign="top" class="nowrap">',
					                  '<span id="shipping-count-' + shippingMethod.name + '" class="chargeFee red">' + shippingMethod.shippingFee + '</span>',
					                  '</td>',
					                  '</tr>'].join('');
					   $('.tb-shippingMethod tbody').append(content);
				   }
				   $("#client_message").css('display', 'none');
			       $("#editAddressTb").css('display', 'none');
			   } else {
				   alert(ret.data);
			   }
			   checkOutForm.unmask();
		   });
}

function cancelEditAddress() {
	var length = $('#checkout_shipaddr_panel li').length;
	if (length > 0) {
		$('#editAddressTb').css({'display':'none','border':'1px solid #ccc'});
	}
}
function showPanel(hide, display) {
	$('#' + hide).css('display', 'none');
	$('#' + display).css('display', 'block');
	return false;
}

function updateAddress(rid) {
	if (!CheckParam(rid)) {
		return false;
	}
	return true;
}

function chooseAddress($obj) {
	var $address = $($obj);
	$address.siblings().find(':radio').attr('checked', false);
	$address.find(':radio').attr('checked', true);
	$("#delivery_rid").val($address.attr('rid'));
	var countryid = $address.find('span[title="delivery_cid"]').text();
    var provinceid = $address.find('span[title="delivery_pid"]').text();
    $("#delivery_cid").val(countryid);
    $("#delivery_cid").val(provinceid); 
}

function addAddress() {
	$('#delivery_first_name').val('');
	$('#delivery_last_name').val('');
	$('#delivery_address').val('');
	$('#delivery_city').val('');
	$('#delivery_province').val('');
	$('#delivery_cid_select').get(0).selectedIndex=0;
	BOLING.ajaxgetprovince_($('#delivery_cid_select'), $('#delivery_cid_select').val());
	$('#delivery_postcode').val('');
	$('#delivery_mobile').val('');
	$('#delivery_default_cb').attr('checked', false);
	$('#default').val(0);
	
	SetPhone();
	$('#addAddressBtn').css('display', 'inline');
	$('#updateAddressBtn').css('display', 'none');
	$('#editAddressTb').css('display', 'block');
}

function editAddress($obj, $rid) {
	$infoLabel = $($obj).parent();
	$('#delivery_first_name').val($infoLabel.find('span[title="delivery_first_name"]').text());
	$('#delivery_last_name').val($infoLabel.find('span[title="delivery_last_name"]').text());
	$('#delivery_address').val($infoLabel.find('span[title="delivery_address"]').text());
	$('#delivery_city').val($infoLabel.find('span[title="delivery_city"]').text());
	$('#delivery_province').val($infoLabel.find('span[title="delivery_province"]').text());
	$('#delivery_cid_select').val($infoLabel.find('span[title="delivery_cid"]').text());
	$('#delivery_postcode').val($infoLabel.find('span[title="delivery_postcode"]').text());
	$('#delivery_mobile').val($infoLabel.find('span[title="delivery_mobile"]').text());
	$('#delivery_email').val($infoLabel.find('span[title="delivery_email"]').text());
	
	var delivery_pid = $infoLabel.find('span[title="delivery_pid"]').text();
	if (delivery_pid == "0") {
		$('#delivery_pid_select').parent().css('display', 'none');
		$('#delivery_province').css('display', 'block');
	} else {
		$('#delivery_pid_select').parent().css('display', 'block');
		BOLING.ajaxgetprovince_($('#delivery_cid_select'), delivery_pid);
		$('#delivery_province').css('display', 'none');
	}
	var isDefault = $infoLabel.find('span[title="delivery_default"]').text();
	if (isDefault == "1") {
		$('#delivery_default_cb').attr('checked', true);
	} else {
		$('#delivery_default_cb').attr('checked', false);
	}
	$('#default').val(isDefault);
	
	SetPhone();
	$('#updateAddressBtn').css('display', 'inline');
	$('#addAddressBtn').css('display', 'none');
	$('#editAddressTb').css('display', 'block');
}

function onCountryChange(obj, rid) {
	$('#delivery_pid_select_' + rid).attr('disabled', 'disabled');
	var cid = $(obj).val();
	var ajaxUrl = 'cart/ajaxgetprovince/';
	$.ajax( {
		type : "GET",
		url : url(ajaxUrl + encodeURI(cid)),
		contentType : "application/txt; charset=utf-8",
		dataType : "text",
		success : function(msg) {
		$('#delivery_pid_select_' + rid).removeAttr('disabled');
		if (msg == -1) {
			alert('Error for getting province information');
		} else {
			provinceArray = msg;
			provinceArray = eval('(' + provinceArray + ')');
			var select = document.getElementById('delivery_pid_select_' + rid);
			select.innerHTML = '';
			//if the province Array don't have keys.
			if(Object.keys(provinceArray).length < 1){
				$('#delivery_province_' + rid).css('display', 'inline');
				$('#delivery_pid_select_' + rid).css('display', 'none');
			}else{
				$('#delivery_province_' + rid).css('display', 'none');
				$('#delivery_pid_select_' + rid).css('display', 'inline');
				for (var i in provinceArray){
					var opt1 = document.createElement("option");
					opt1.value = i;
					opt1.innerHTML = provinceArray[i];
					select.appendChild(opt1);
				}
			}
		}	
	}
	});
}