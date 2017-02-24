Object.keys = Object.keys || (function () {
    var hasOwnProperty = Object.prototype.hasOwnProperty,
        hasDontEnumBug = !{toString:null}.propertyIsEnumerable("toString"),
        DontEnums = [
            'toString',
            'toLocaleString',
            'valueOf',
            'hasOwnProperty',
            'isPrototypeOf',
            'propertyIsEnumerable',
            'constructor'
        ],
        DontEnumsLength = DontEnums.length;
 
    return function (o) {
        if (typeof o != "object" && typeof o != "function" || o === null)
            throw new TypeError("Object.keys called on a non-object");
 
        var result = [];
        for (var name in o) {
            if (hasOwnProperty.call(o, name))
                result.push(name);
        }
 
        if (hasDontEnumBug) {
            for (var i = 0; i < DontEnumsLength; i++) {
                if (hasOwnProperty.call(o, DontEnums[i]))
                    result.push(DontEnums[i]);
            }
        }
 
        return result;
    };
})();

$.toogleDiv = function(divContent, afterSelector)
{
        //create message and show it
        $(divContent)
          .insertAfter(afterSelector)  //<== where ever you want it to show
          .fadeIn('slow')
          .animate({opacity: 1.0})     //<== wait 3 sec before fading out
          .fadeOut('slow', function()
          {
                $(this).remove();
          });
};

function url(path) {
  var len = path.length;
  if (path !== '' && path.substr(len - 5) != '.html' && path.substr(len - 1) != '/') {
    path += '/';
  }
  return basePath + path;
}

function LTrim(str)
{
    var i;
    for(i=0;i<str.length;i++)
    {
        if(str.charAt(i)!=" "&&str.charAt(i)!=" ")break;
    }
    str=str.substring(i,str.length);
    return str;
}

function RTrim(str)
{
    var i;
    for(i=str.length-1;i>=0;i--)
    {
        if(str.charAt(i)!=" "&&str.charAt(i)!=" ")break;
    }
    str=str.substring(0,i+1);
    return str;
}

function Trim(str)
{
    return LTrim(RTrim(str));
}

function paymentc(obj, value)
{
 var path = url('paymentc/' + value);
 $.get(path, function(data){ 
    obj.html(data);
  });
}

var BOLING = {
  initialize : function (el){
    $('div#tabs').tabs();
  },
  ajaxAddToCart : function (){
	  $(".buynowdirectly").on("submit", function(event){
		  //stop the default submit action  
		  event.preventDefault();
		  //ajax submit.
	      var postUrl = 'cart/ajaxAddProduct';
	      var data = {};
	      $('input', this).each(function(){
	    	  curItem = $(this);
	    	  if(!curItem.is(':disabled')){
	    		  data[curItem.attr('name')] = curItem.val();
	    	  }
	      });
	      var submitElement = $('input[type=submit]', this);
	      /* Send the data using post and put the results in a div */
	      
	      $.post( url(postUrl), data,
	        function( response ) {
	            var success = response['success'];
	            if(success){
		            $.toogleDiv('<div class="dynamicMessage"><em></em><div>Added</div></div>', submitElement);
		            //change shopping chart amount
		            $('#shoppingCart em.cart_item_number').text(response['goodsInCart']);
		            //create a new anchor and open it in the new window.   
		            //window.open(url('cart'), 'new_window');
	            }else{
		            $.toogleDiv('<div class="dynamicMessage"><em></em><div>'+response['message']+'</div></div>', submitElement);
	            }
	        }, "json");
	        
		});
  },

  caucluatePayments : function()
  {
      var ajaxUrl = 'cart/ajaxgetfees/';
      /*
      var ciid_elements = $('input[name="cart_item_ids[]"]');
      var ciidList = [];
      for(var i=0; i < ciid_elements.length; i++){
    	  ciidList[i] = $(ciid_elements[i]).val();
      }
      var ciids = ciidList.join(',');
      */
      var ciids = $('input[name=pids]').val();
      var shipping_name = $(':radio[name=shipping_method][checked]').val(); 
      if(shipping_name == undefined) shipping_name = 'not_set';
      var payment_name = $(':radio[name=payment_method][checked]').val();
      if(payment_name == undefined) payment_name = 'not_set';
      var countryid = $('#select_countries').val();
      var provinceid = $('#select_provinces').val();
      if(provinceid == null && $('input[name="delivery_or_province"]').val() == '' && shipping_name != 'not_set'){
    	  alert("Please complete your state/province/region information first.");
    	  $(':radio[name=shipping_method]').attr('checked', false);
     	 return;
      }
      if(provinceid == null){
    	  provinceid = 0;
      }
      if (shipping_name == 'not_set') {
    	paymentc($('#shipping_money'), 0.0);
        $('#select_countries').attr('disabled', false);
        $('#select_provinces').attr('disabled', false);
        $('input[name="delivery_or_province"]').attr('disabled', false);
      };
      $.ajax( {
        type : "GET",
        url : url(ajaxUrl + encodeURI(ciids) + '/' + encodeURI(shipping_name + ',' + payment_name) + '/' + encodeURI(countryid) + "/" + encodeURI(provinceid)),
        contentType : "application/txt; charset=utf-8",
        dataType : "json",
        success : function(msg) {
          if ((typeof msg['shipping_fee'] != "undefined") && msg['shipping_fee'] >= 0) {
        	paymentc($('#shipping_money'), msg['shipping_fee']);
        	paymentc($('#shipping-count-'+shipping_name), msg['shipping_fee']);
        	//Tempororily setting.
            $('#delivery_cid').val(countryid);
            $('#delivery_pid').val(provinceid);
            //$('#select_countries').attr('disabled', true);
            //$('#select_provinces').attr('disabled', true);
            //$('input[name="delivery_or_province"]').attr('readonly', true);
          }else if(shipping_name != 'not_set' && (msg['shipping_fee'] && msg['shipping_fee'] == -1)){
        	  alert('This shipping method is not supported in your location currently.');
        	  $(':radio[name=shipping_method]').attr('checked', false);
              $('#select_countries').attr('disabled', false);
              $('#select_provinces').attr('disabled', false);
              $('input[name="delivery_or_province"]').attr('readonly', false);
        	  paymentc($('#shipping_money'), 0.0);
        	  $('#shipping-count-'+shipping_name).text('Select To Count');
          }else{
        	  paymentc($('#shipping_money'), 0.0);
        	  $('#shipping-count-'+shipping_name).text('Select To Count');
          }
          if (msg['bank_fee'] && msg['bank_fee'] >= 0) {
        	paymentc($('#payment_money'), msg['bank_fee']);
          }else{
        	  paymentc($('#payment_money'), 0.0);
          }
          if (msg['payment_amount'] && msg['payment_amount'] >= 0){
        	paymentc($('#order_amount'), msg['payment_amount']);//总费用
          }
        }
      });
  },

  hiddentr : function (el) {
    el.click(function(){
      $(this).parents('tr').remove();
    });
  },
    getaddress : function (el) {
    el.change(function(){
      var rid = $(this).val();
      var ajaxUrl = 'user/ajaxgetaddress/';
      $.ajax( {
        type : "GET",
        url : url(ajaxUrl + encodeURI(rid)),
        contentType : "application/txt; charset=utf-8",
        dataType : "txt",
        success : function(msg) {
          if (msg) {
            var address = eval('(' + msg + ')');
            $('#delivery_name').val('');
            $('#delivery_postcode').val('');
            $('#delivery_mobile').val('');
            $('#delivery_area').val('');
            $('#delivery_phone').val('');
            $('#delivery_address').val('');
            $('#delivery_email').val('');
            $('#delivery_name').val(address['delivery_name']);
            $('#delivery_postcode').val(address['delivery_postcode']);
            $('#delivery_mobile').val(address['delivery_mobile']);
            $('#delivery_area').val(address['delivery_area']);
            $('#delivery_phone').val(address['delivery_phone']);
            $('#delivery_address').val(address['delivery_address']);
            $('#delivery_email').val(address['delivery_email']);
            $('#delivery_first_name').val(address['delivery_first_name']);
            $('#delivery_last_name').val(address['delivery_last_name']);
            $('#delivery_city').val(address['delivery_city']);
            $('#delivery_time').val(address['delivery_time']);
            var countryId = address['delivery_cid'];
            var provinceId = address['delivery_pid'];
              $("#select_countries option[value='"+countryId+"']").attr('selected',true);
              BOLING.ajaxgetprovince_($("#select_countries"),provinceId);
             return;
          } else {
            alert('STYLE ERROR');
          }
        }
      });
    });
  },
  postcheck : function(el){
    el.click(function(){
        var i = 0;
        var arr = new Array();
        var form = $(this).closest(form);
        $('.non-empty', form).each(function(){
        var v = $(this).val();
        var r = $(this).attr('title');
        var t = $(this).attr('alt');
        v = Trim(v);
        if (t) {
          altarr = t.split('_');
          len = altarr[1];
          if (!arr[t]) {
            arr[t] = new Array(); 
          }
          if (!arr[t]['empty']) {
            if (!v) {
              if (!arr[t]['num']) {
                arr[t]['num'] = 0;
              }
              arr[t]['num']++;
              if (arr[t]['num'] == len){
                alert(r);
                i++;
                return false;
              }
            } else {
              arr[t]['empty'] = 1;
            }  
          }
        } else {
          $(this).val(v);
          if (!v){
            alert(r);
            i++;
            return false;
          }
        }
      });
      if (i !=0) {
        return false;
      }
    });
  },
  numcheck : function (el) {
    el.blur(function(){
      BOLING.numcheck_($(this));
    });
  },
  numcheck_ : function (obj){
    var v = obj.val();
    var numstr = obj.attr('title');
    var numarr = new Array();
    numarr = numstr.split(',');
    numarr[0] = Math.ceil(numarr[0]);
    numarr[1] = Math.ceil(numarr[1]);
    if (v < 1 || v != Math.ceil(v)) {
      if (numarr[0]) {
        obj.val(numarr[0]);
      } else {
        obj.val(1);
      }
      alert('Number format is not correct');
    } else if (v < numarr[0]){
      obj.val(numarr[0]);
      alert('Less than the minimum purchase');
      return false;
    } else if (numarr[1]>0 && v > numarr[1]){
      obj.val(numarr[1]);
      alert('Larger than the maximum number of purchase');
      return false;
    }
  },
  ajaxgetprovince : function (el) {
    el.change(function(){
      var obj = $(this);
      BOLING.ajaxgetprovince_(obj);
     
    });
  },
  ajaxgetprovince_ : function(obj , pid) {
	  $('#delivery_pid_select').attr('disabled', 'disabled');
	  $('#sp_province').css('display', 'none');
      var cid = obj.val();
      var ajaxUrl = 'cart/ajaxgetprovince/';
      $.ajax( {
        type : "GET",
        url : url(ajaxUrl + encodeURI(cid)),
        contentType : "application/txt; charset=utf-8",
        dataType : "text",
        success : function(msg) {
    	  $('#delivery_pid_select').removeAttr('disabled');
          if (msg == -1) {
            alert('Error for getting province information');
          } else {
            provinceArray = msg;
            provinceArray = eval('(' + provinceArray + ')');

            var select = document.getElementById('delivery_pid_select');
            select.innerHTML = '';
            $('input[name="delivery_province"]').val('');
            //if the province Array don't have keys.
            if(Object.keys(provinceArray).length < 1){
            	$('#delivery_province').css('display', 'block');
            	$('#delivery_pid_select').parent().css('display', 'none');
            }else{
            	$('#delivery_province').css('display', 'none');
            	$('#delivery_pid_select').parent().css('display', 'block');
	            for (var i in provinceArray){
	              var opt1 = document.createElement("option");
	              opt1.value = i;
	              opt1.innerHTML = provinceArray[i];
	              select.appendChild(opt1);
	            }
	            if(pid != 'undefined'){
	               $("#delivery_pid_select option[value='"+pid+"']").attr('selected',true);
	            }
            }
          }
        }
      });
  },
  init_shipping_and_payment: function(el){
	  el.change(function(){
	  BOLING.caucluatePayments();
	});
  },
  filter_product : function (el) {
	     var pageCount = parseInt($('#pageCount').val());
	     var page = parseInt($('#page').val());
	     if(page>pageCount){
	    	 page = pageCount;
	     }
	    $('#list_mode_p').click(function(){
	      $('#listMode').val('photo');
	      $(el).submit(); 
	    });
	    $('#list_mode_l').click(function(){
	      $('#listMode').val('list');
	      $(el).submit(); 
	    });
	    $('#orderby').change(function(){
	      $(el).submit(); 
	    });
	   $('#pageRows').change(function(){
	     $(el).submit(); 
	    });
	   $('#pageFirst').click(function(){
		     $('#page').val(1);
		     $(el).submit(); 
		});
	   $('#pagePrve').click(function(){
	     var prevpage = page-1;
	     if(prevpage<=0){
	    	 prevpage = page;
	     }
	     $('#page').val(prevpage);
	     $(el).submit(); 
	   });

	   $('#pageNext').click(function(){
		   	 var nextpage = page + 1;
		   	 if(nextpage>pageCount){
		   		 nextpage = pageCount;
		   	 }
		     $('#page').val(nextpage);
		     $(el).submit(); 
		});
	   $('#pageLast').click(function(){
		     $('#page').val(pageCount);
		     $(el).submit(); 
	   });
	   $('#page').keydown(function(el){
		   if(el.which==13){
			   $('#page').val(page);
			   $(el).submit();
		   }
	   });
	},
	/*changed by nemo*/
	  filter_search : function (el) {
		  $('.orderby').change(function(){
			 this.form.submit(); 
		  });
		  $('.pageRows').change(function(){
			  this.form.submit(); 
		  });
		},
	
	
	/**currencySelect : function () {
	    $('select.currencySelect').change(function(){
	      var path = url('default/changecurrency/'+$(this).val());
	      redirect(path);
	    });
	  }**/
	currencySelect : function () {
		$('#cur_selector ul li a.flag').click(function(){
			var path = url('default/changecurrency/' + $(this).text());
			redirect(path);
		});
	}
};

BOLING.order = {
    initialize: function(el) {
      el.find('input.datepicker').datepicker({
        dateFormat:'yy-mm-dd'
       });
    }
  };


BOLING.cart = {
	      initialize : function () {
	        $('.ajax_qty').blur(function(){
	          var obj = $(this);
	          if (!BOLING.numcheck_(obj)) {
	            var oldqty = obj.attr('alt');
	            var pid = obj.attr('id');
	            var price = obj.attr('rel');
	            var qty = obj.val();
	            var cartInfo = new Array();
	            var ajaxUrl = 'cart/updateOrderProductQty/';
	               

	            
	            $.ajax( {
	              type : "GET",
	              url : url(ajaxUrl + encodeURI(pid) + "/" + encodeURI(qty)),
	              contentType : "application/txt; charset=utf-8",
	              dataType : "json",
	              success : function(msg) {
	            	  
	            	  if(msg['error'] != undefined){
	            		  //abnormal states, need display the error.
	            		  $('div.messages_box').each(function(){
	            			  $(this).addClass('error');
	            			  $('p', this).text(msg['error']);
	            		  })
	            	  }else{

	            		  if(pid == msg['modified_item']['cart_item_id']){
	            			  //change the value for this product.
	            			  $('tr#'+ pid + ' .prQuant input[name="qtys[]"]').val(msg['modified_item']['quantity']);
	            			  $('tr#'+ pid + ' .prAmount .payPrice .numVal').text(msg['modified_item']['subtotal']);
	            		  }
		            	  $('tfoot .scTotalSaving .numVal').text(msg['saving_amount']);
		            	  $('tfoot .totalPrice .numVal').text(msg['amount']);
	            		  
	            	  }
	            	  
	              }
	            })
	            .done(function(msg, status) { 

	            	
	            	});
	          }  
	        });
	        $('#cart .delivery_address_list').click(function(){
	          $('#delivery_address_input_id').val($('#' + $(this).val()).text());
	        });
	        //BOLING.cart.getcouponform();
	      },
	      getcouponform : function () {
	        var price = $('#CommodityPrice').val();
	      var path = url('widget/coupon/couponform/' + price);    
	      var couponform = $('#CouponForm');
	        $.get(path, function(data){ 
	          couponform.html(data);
	          $('#SubmitCouponButton').click(function(){
	            BOLING.cart.submitCouponCode();
	          });
	        });
	      },
	      submitCouponCode : function () {
	        var code = $('#CouponCodeNumber').val();
	        if (code=='') {
	          alert('Invalid code, correct it please.'); 
	          return false;
	        }
	       BOLING.cart._submitCouponCode(code);
	      },
	      _submitCouponCode : function(code){
	      	var price = $('#goods_amount').val();
	        var goods_number = $('#goods_number').val();
	        var shippingPrice = $('#input_shipping_money').val();
	        var procedure_fee = $('#input_procedure_fee').val();  //交易费
	        var insurance_premium = parseFloat(goods_number * 3.5); //保险费
	        var payment_method = $('input[name=payment_method]:checked').val();
	        var couponPrice = 0;
	        var goodsAmount = price;
	      	var path = url('widget/coupon/validate/' + code + '/' + price);
	        $.getJSON(path, function(json){ 
	          if (parseInt(json['error']) != 0) {
	            alert(json['codeInfo']);
	            $('#CouponCodeNumber').val('');
	          } else {
	            var couponType = parseInt(json['codeInfo']['type']);
	            var couponValue = parseFloat(json['codeInfo']['value']);
	            var couponPrice = json['codeInfo']['couponPrice'];
	            couponPrice = couponPrice.replace(/[^\d.]/g, "");
	            if (couponType == 1) {
	              var html = 'Coupon: <strong>-' + json['codeInfo']['couponShowValue'] + '</strong> ';
	            } else {
	              var html = 'Discount: <strong>' + couponValue / 10 + '</strong>';
	            }
	            goodsAmount = parseFloat(couponPrice);
	          /*  if('western' == payment_method){
	            procedure_fee = 0;
	          }else{
	            procedure_fee = parseFloat((parseFloat(goodsAmount) + parseFloat(shippingPrice) +  + parseFloat(insurance_premium))*8/100);
	          }
	         
	          var totalPayment = parseFloat(goodsAmount) + parseFloat(shippingPrice) + parseFloat(procedure_fee) + parseFloat(insurance_premium);
	           */
	          var totalPayment = parseFloat(goodsAmount) + parseFloat(shippingPrice);
	          paymentc($('#order_amount'), totalPayment);//总费用
	            $('#goods_amount').val(goodsAmount);
	            $('#CouponForm').html('&nbsp;');
	            $('#CouponPriceInfo').html(html);
	            $('#CouponCodeNum').val(code);
	          }
	        });
	      }
	    };


BOLING.mailSubscribe = {
  initialize : function () {
    $('#submitMailAddress').click(function(){
      var pattern=/^([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+/;
      var mailAddress =  $('#mailAddress').val();
      if (mailAddress=='') {
        alert('Invalid email address, correct it please.'); 
        return false;
      }
      if(pattern.test(mailAddress)){         
  		var path = url('widget/mailsubscribe/saveemailaddress/' + mailAddress);
  		$.getJSON(path, function(json){ 
          alert(json['MSG']);
        });
      }else{   
  		alert('Invalid email address, correct it please.'); 
      }
    });
  },
  mailContentCheck : function(){
	 var mail_val;
    $('#mailAddress').focus(function(){
    	  mail_val = $(this).val();
    	   $(this).val('');
    }).blur(function(){
    	  if($.trim($(this).val())==''){
    	    $(this).val(mail_val);
    	  }
    }); 	 
  }
};	

BOLING.searchBox = {
	initialize : function (el) {
		el.click(function(){
			var keyword = $.trim($('#search_keyword').val());
			if(keyword == 'What would you like to save on today'){
				return false;
			}
			if(keyword.length==0){
				location.href='/browse/allgoods.html';
			}else{
				location.href='/search/'+keyword;
			}
		});
	},
	searchContentCheck : function(){
		 var mail_val;
	    $('#search_keyword').focus(function(){
	    	  mail_val = $(this).val();
	    	   $(this).val('');
	    }).blur(function(){
	    	  if($.trim($(this).val())==''){
	    	    $(this).val(mail_val);
	    	  }
	    }); 	 
	  }
};

function redirect(url) {
//	  var theLink = '';
//	  if (document.getElementById) {
//	    theLink = document.getElementById('redirect_link');
//	    if (!theLink) {
//	      theLink = document.createElement('a');
//	      theLink.style.display = 'none';
//	      theLink.id = 'redirect_link';
//	      document.body.appendChild(theLink);
//	    }
//	    if (url) theLink.href = url;
//	  }
//	  if ((theLink) && (theLink.click)) window.location = theLink.href;
//	  else location.href = url;
	window.location = url;
	}


(function() {
  var doWhileExist = function(sModuleId, oFunction) {
    var el = $(sModuleId);
    if (el.length) oFunction(el);
  };
  BOLING.initialize();
  //BOLING.selectMenu.initialize();
 // doWhileExist('select#shipping_method', BOLING.getshippingmoney);
  doWhileExist('div#cart', BOLING.cart.initialize);
  doWhileExist('a.tr_hidden', BOLING.hiddentr);
  doWhileExist('select#getaddressselect', BOLING.getaddress);
  doWhileExist('input[type="submit"]', BOLING.postcheck);
  doWhileExist('input.numcheck', BOLING.numcheck);
  doWhileExist('form#form_order', BOLING.order.initialize);
  doWhileExist('select#delivery_cid_select', BOLING.ajaxgetprovince);
  doWhileExist('form#form_filter_product', BOLING.filter_product);
  
  doWhileExist('form#product_filter', BOLING.filter_search);
  doWhileExist('form#search_filter', BOLING.filter_search);
  doWhileExist('form#search_filter2', BOLING.filter_search);
  doWhileExist('input#header_search_keyword', BOLING.searchBox.initialize);
  doWhileExist('input#search_keyword', BOLING.searchBox.searchContentCheck);
  doWhileExist('button#submitMailAddress', BOLING.mailSubscribe.initialize);
  doWhileExist('input#mailAddress', BOLING.mailSubscribe.mailContentCheck);
//  doWhileExist('select.currencySelect', BOLING.currencySelect);
  doWhileExist('#cur_selector ul li a.flag', BOLING.currencySelect);
  doWhileExist('.buynowdirectly', BOLING.ajaxAddToCart);
})();

function changeShipping(sname){
	BOLING.caucluatePayments();
}
function changePayment(sname){
	$('.tb-paymentMethod').css('border', 'none');
	BOLING.caucluatePayments();
}
function bookmark(){ 
	var title=document.title;
	var url=document.location.href; 
	if (window.sidebar) window.sidebar.addPanel(title, url,""); 
	else if( window.opera && window.print ){ 
	var mbm = document.createElement('a'); 
	mbm.setAttribute('rel','sidebar'); 
	mbm.setAttribute('href',url); 
	mbm.setAttribute('title',title); 
	mbm.click();} 
	else if( document.all ) window.external.AddFavorite( url, title); 
	}