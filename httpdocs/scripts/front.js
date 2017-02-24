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

var BOLING = {
  initialize : function (el){
    $('div#tabs').tabs();
  },  
  getshippingmoney : function (el) {
    el.change(function(){
      var ajaxUrl = 'cart/getshippingmoney/';
      var shipping_name = $(this).val(); 
      var goods_weight = $('#goods_weight').val();  
      var goods_amount = $('#goods_amount').val(); 
      var goods_number = $('#goods_number').val();
      var countryid = $('#select_countries').val();
      var provinceid = $('#select_provinces').val();
      if (!shipping_name) {
        $('#shipping_money').html('');
        $('#input_shipping_money').val(0);
        $('#select_countries').attr('disabled', false);
        $('#select_provinces').attr('disabled', false);
        return;
      };
      $.ajax( {
        type : "GET",
        url : url(ajaxUrl + encodeURI(shipping_name) + "/" + encodeURI(goods_weight) + "/" + encodeURI(goods_amount) + "/" + encodeURI(goods_number) + "/" + encodeURI(countryid) + "/" + encodeURI(provinceid)),
        contentType : "application/txt; charset=utf-8",
        dataType : "txt",
        success : function(msg) {
          if (msg >= 0) {
            $('#shipping_money').html(msg);
            $('#input_shipping_money').val(msg);
            $('#delivery_cid').val(countryid);
            $('#delivery_pid').val(provinceid);
            $('#select_countries').attr('disabled', true);
            $('#select_provinces').attr('disabled', true);
          } else if(msg == -1){
            alert('can not shipping');
          }else {
            alert('STYLE ERROR');
          }
        }
      });
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
      var cid = obj.val();
      var ajaxUrl = 'admin/site/ajaxgetprovince/';
      $.ajax( {
        type : "GET",
        url : url(ajaxUrl + encodeURI(cid)),
        contentType : "application/txt; charset=utf-8",
        dataType : "txt",
        success : function(msg) {
          if (msg == -1) {
            alert('获取省份错误');
          } else {
            provinceArray = msg;
            provinceArray = eval('(' + provinceArray + ')');
            var select = document.getElementById('select_provinces');
            select.innerHTML = '';
            var opt1 = document.createElement("option");
            select.appendChild(opt1);
            for (var i in provinceArray){
              var opt1 = document.createElement("option");
              opt1.value = i;
              opt1.innerHTML = provinceArray[i];
              select.appendChild(opt1);
            }
          }
        }
      });
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
        var qty = obj.val();
        var ajaxUrl = 'cart/updateOrderProductQty/';
        $.ajax( {
          type : "GET",
          url : url(ajaxUrl + encodeURI(pid) + "/" + encodeURI(qty)),
          contentType : "application/txt; charset=utf-8",
          dataType : "txt",
          success : function(msg) {
            if (msg == -1) {
              obj.val(oldqty);
              alert('Goods shortage!');
            }
          }
        });
      }  
    });
  }
};
(function() {
  var doWhileExist = function(sModuleId, oFunction) {
    var el = $(sModuleId);
    if (el.length) oFunction(el);
  };
  BOLING.initialize();
  doWhileExist('select#shipping_method', BOLING.getshippingmoney);
  doWhileExist('div#cart', BOLING.cart.initialize);
  doWhileExist('a.tr_hidden', BOLING.hiddentr);
  doWhileExist('select#getaddressselect', BOLING.getaddress);
  doWhileExist('input[type="submit"]', BOLING.postcheck);
  doWhileExist('input.numcheck', BOLING.numcheck);
  doWhileExist('form#form_order', BOLING.order.initialize);
  doWhileExist('select#select_countries', BOLING.ajaxgetprovince);
})();
