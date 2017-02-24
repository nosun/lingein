function url(path) {
  var len = path.length;
  if (path !== '' && path.substr(len - 5) != '.html' && path.substr(len - 1) != '/') {
    path += '/';
  }
  return basePath + path;
}

function JsonToStr(o) {
  var arr = [];
  var fmt = function(s) {
  if (typeof s == 'object' && s != null) return JsonToStr(s);
  return /^(string|number)$/.test(typeof s) ? "'" + s + "'" : s;
  }
  for (var i in o) arr.push("'" + i + "':" + fmt(o[i]));
  return '{' + arr.join(',') + '}';
}
var JSON = {
		  encode : function(input) {
		  	if (!input)
		    return 'null'
		  	switch (input.constructor) {
		  	case String:
		    return '"' + input + '"'
		  	case Number:
		    return input.toString()
		  	case Boolean:
		    return input.toString()
		  	case Array:
		    var buf = []
		    for (i in input)
		    	buf.push(JSON.encode(input[i]))
		    return '[' + buf.join(', ') + ']'
		  	case Object:
		    var buf = []
		    for (k in input)
		    	buf.push(k + ' : ' + JSON.encode(input[k]))
		    return '{ ' + buf.join(', ') + '} '
		  	default:
		    return 'null'
		  	}
		  }
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
  initialize: function() {
	$('.hidden').hide();
    $('a.btn_confirm').click(function(e) {
      if (!window.confirm(this.rel)) {
        e.preventDefault();
      }
    });
    $('a.btn_cancel, input.btn_cancel').click(function(e) {
      history.go(-1);
      e.preventDefault();
    });
    $('div#tabs').tabs({show: function(event, ui) {
    	BOLING.tabsResetbody();
    }});
    $('#getswfupload').click(function(e){
      var i = 0
      $(this).parent('form').find('.non-empty').each(function(){
        var v = $(this).val();
        var r = $(this).attr('title');
        v = Trim(v);
        $(this).val(v);
        if (!v){
          alert(r);
          i++;
          return false;
        }
      });
      if (i !=0) {
        return false;
      }
      $(this).hide();
      $('.hidden').show();
      BOLING.swfuplod.initialize($(this));
      e.preventDefault();
    });
    $('.ispost').change(function(){
      window.location.href = url('admin/site/mailsetting/' + $(this).val());
    });
    if ($.xheditor) {
      $('textarea.normalXheditor').xheditor({forcePtag:false, width:'85%',height:'140px',html5Upload:false,upImgUrl:url('admin/file/editorupload/product'),showBlocktag:true});
    }
    $('.datetimepicker').click(function(){
      WdatePicker({skin:'whyGreen',dateFmt:'yyyy-MM-dd HH:mm:ss'});
    });
  },
  checkAllPage : function(el) {
    el.click(function(e) {
      var aaa = $('#isall').val();
      if($('#isall').val() == "0"){
        BOLING.onCheckAllPage();
      }
    });
  },
  onCheckAllPage : function () {
    $('#check_top_title').html('<a href="javascript:;" id="checkAllPage" onclick="BOLING.cancelCheckAllPage();">点此取消选择所有</a>');
    $('#isall').val('1');
    $('#checkAll').attr('checked', true);
    $('input.checkItem').attr('checked', true);
    
  },
  cancelCheckAllPage : function () {
    var count = $('#count').val();
    $('#isall').val('0');
    $('#checkAll').attr('checked', false);
    $('input.checkItem').attr('checked', false);
    $('#check_top_title').html('<a href="javascript:;" id="checkAllPage" onclick="BOLING.onCheckAllPage();">点此选择所有的  '+count+' 条记录');
    $('#check_top_title').hide();
    
  },
  checkboxButton : function(el) {
    el.click(function(e) {
      if ($('#checkAll').attr('checked')) {
        $('input.checkItem').attr('checked', true);
        $('#check_top_title').show();
      } else {
        $('input.checkItem').attr('checked', false);
      }
    });
  },
  pagevariable_xheditor : function(el) {
      var dXheditor = $('textarea.pagevariable_xheditor');
      //dXheditor.removeClass('pagevariable_xheditor').addClass('xheditor').xheditor({width:'95%',height:'120px',sourceMode:true});
      dXheditor.removeClass('pagevariable_xheditor').addClass('xheditor').xheditor({forcePtag:false, width:'85%', height:'100px',upImgUrl:url('admin/file/editorupload/product')});
  },
  getcurrencylist: function(el){
    var select = document.getElementById('currencylist');
    var currencyArray = ["人民币|CNY|人民币元","美元|USD|美元","奥币|ATS|奥地利先令","澳元|AUD|澳元","比法郎|BEF|比利时法郎","巴西币|BRL|巴西里尔","加元|CAD|加元","瑞郎|CHF|瑞郎","智利币|CLP|智利比索","捷克币|CZK|捷克克朗","马克|DEM|德国马克","丹麦币|DKK|丹麦克朗","西币|ESP|西班牙比塞塔","欧元|EUR|欧元","芬兰币|FIM|芬兰马克","法法郎|FRF|法国法郎","英镑|GBP|英镑","港币|HKD|港币","匈牙利币|HUF|匈牙利福林","印尼盾|IDR|印度尼西亚盾","印度币|INR|印度卢比","义里拉|ITL|意大利里拉","日元|JPY|日元","韩元|KER|韩元","墨披索|MXN|墨西哥比索","马币|MYR|马来西亚元","荷兰币|NLG|荷兰盾","挪威币|NOK|挪威克朗","纽币|NZD|新西兰元","菲披索|PHP|菲律宾比索","波兰币|PLN|波兰兹罗提","葡币|PTE|葡萄牙埃斯库多","卢布|RUB|俄罗斯卢布","沙乌地|SAR|沙特里亚尔","瑞典币|SEK|瑞典克朗","新加坡元|SGD|新加坡元","泰铢|THB|泰铢","台币|TWD|台币","委内拉|VEF|强势玻利瓦尔","越南盾|VND|越南盾","南非币|ZAR|南非兰特"];
    var defaultvalue = $('#currencylist').attr("title");
    for (var i=0,len = currencyArray.length;i<len;i++){
      var items = currencyArray[i].split("|");
      var opt1 = document.createElement("option");
      opt1.value = items[1];
      opt1.innerHTML = items[1]+" "+items[2];
      if (items[1] == defaultvalue) opt1.selected = "true";
      select.appendChild(opt1);
    }
  },
  inputsubmit : function (el) {
    el.live("click", function(e) {
      var name = $('#name').val();
      var key = $('#key').val();
      var calculateway = $(this).val();
      var ajaxUrl = 'admin/site/ajaxgetshippingareaform/';
      $.ajax( {
        type : "GET",
        url : url(ajaxUrl + encodeURI(name) + "/" + encodeURI(calculateway) + "/" + encodeURI(key)),
        contentType : "application/txt; charset=utf-8",
        dataType : "txt",
        success : function(msg) {
          $('#dataform').html(msg);
        }
      });
    });
  },
  getshippingmoney : function (el) {
    el.change(function(){
      var ajaxUrl = 'cart/getshippingmoney/';
      var shipping_name = $(this).val(); 
      var goods_weight = $('#goods_weight').val();  
      var goods_amount = $('#goods_amount').val(); 
      var goods_number = $('#goods_number').val();
      var countryid = $('#cid').val();
      var provinceid = $('#pid').val();
      if (!shipping_name) {
        $('#shipping_money').html('');
        $('#input_shipping_money').val(0);
        return;
      };
      $.ajax( {
        type : "GET",
        url : url(ajaxUrl + encodeURI(shipping_name) + "/" + encodeURI(goods_weight) + "/" + encodeURI(goods_amount) + "/" + encodeURI(goods_number) + "/" + encodeURI(countryid) + "/" + encodeURI(provinceid)),
        contentType : "application/txt; charset=utf-8",
        dataType : "txt",
        success : function(msg) {
          if (msg >= 0) {
            $('#shipping_money').val(msg);
          } else if(msg == -1){
            alert('can not shipping');
          }else {
            alert('STYLE ERROR');
          }
        }
      });
    });
  },
  resetbody : function (el) {
    var leftElement = $('div.body_left');
    var rightElement = $('div.body_right');
    
    var leftHeight = leftElement.height();
    var rightHeight = rightElement.height();
    
    BOLING.doResetbody(leftHeight, rightHeight, leftElement, rightElement);
    $(window).resize(function() {
    	BOLING.doResetbody(leftHeight, rightHeight, leftElement, rightElement);
    });
  },
  tabsResetbody : function () {
	var rightElement = $('div.body_right');
	var rightHeight = rightElement.height();  
    
	//alert(rightHeight);
  },
  doResetbody : function (leftHeight, rightHeight, leftElement, rightElement) {

    var screenHeight = document.documentElement.clientHeight - 103;
	var showLeftHeight = leftHeight;
	var showRightHeight = rightHeight;
	    
	leftHeight < screenHeight ? showLeftHeight = screenHeight : showLeftHeight = leftHeight;
	rightHeight < screenHeight ? showRightHeight = screenHeight + 1 : showRightHeight = rightHeight;
	leftElement.height(showLeftHeight);
	//rightElement.height(showRightHeight);
  },
  postcheck : function(el){
    el.click(function(){
      var i = 0;
      var arr = new Array(); 
      $('.non-empty').each(function(){
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
            if (cid != 0 && provinceArray!='') {
              var opta = document.createElement("option");
              opta.value = 0;
              opta.innerHTML = 'all';
              select.appendChild(opta);
            }
            for (var i in provinceArray){
              var opt1 = document.createElement("option");
              opt1.value = i;
              opt1.innerHTML = provinceArray[i];
              select.appendChild(opt1);
            }
          }
        }
      });
    })
  },
  shippingarea : function (el) {
    var spid_json = $('#spid_json').val();
    var spid = eval('(' + spid_json + ')');;
    $('#addprovince').click(function(){
      var cid = $('#select_countries').val();
      if (!cid) {
        return false;
      }
      var cname = $("#select_countries").find("option:selected").text(); 
      var pid = $('#select_provinces').val();
      var pname = $("#select_provinces").find("option:selected").text(); 
      if (cid + pid in spid){
        alert('This area have exist!'); return;
      } else {
        spid[cid + pid] = cid + pid;
      }
      var str = '<div>';
      str += '<input type="checkbox" class="del" title="'+ cid +'" checked value="' + pid + '" name="area['+cid+'][]">';
      str += cname +' '+ pname;
      str += '</div>';
      var html = $('#txt_provinces').html();
      $('#txt_provinces').html(html + str)
    });
    $('.del').live("click", function(){
      var cid = $(this).attr('title');
      var pid = $(this).val();
      $(this).closest('div').remove();
      delete(spid[cid + pid]);
    });
  },
  
  getpvThemeData : function (el) {
    el.ready(function(){
      var theme = $('#pvTheme').val();
      if (theme) {
        $('.pv').each(function(ell){
          $(this).attr('disabled', true);
          $(this).xheditor(false);
        });
      }
    })
    el.change(function(){
      var theme = $('#pvTheme').val();
      var key = $('#pvTheme').find("option:selected").text();
      var type = $('#pvThemeType').val();
      var dataid = $('#dataid').val();
      var title = $("[name='title']");
      var meta_keywords = $("[name='meta_keywords']");
      var meta_description = $("[name='meta_description']");
      var var1 = $("[name='var1']");
      var var2 = $("[name='var2']");
      var var3 = $("[name='var3']");
      var var4 = $("[name='var4']");
      var var5 = $("[name='var5']");
      var var6 = $("[name='var6']");
      if (theme) {
        var ajaxUrl = 'admin/site/ajaxGetPageVariablesByKey/';
        if (!dataid) {
          $('#pvbody').css('visibility', 'hidden');
        } else {
          $('#pvbody').css('visibility', 'inherit');
          $.ajax( {
            type : "GET",
            url : url(ajaxUrl + encodeURI(key) + "/" + encodeURI(type) + "/" + encodeURI(dataid)),
            contentType : "application/txt; charset=utf-8",
            dataType : "txt",
            success : function(msg) {
              var pv = eval('(' + msg + ')');
              title.val(pv['title']);
              meta_keywords.val(pv['meta_keywords']);
              meta_description.val(pv['meta_description']);
              var1.val(pv['var1']);
              var2.val(pv['var2']);
              var3.val(pv['var3']);
              var4.val(pv['var4']);
              var5.val(pv['var5']);
              var6.val(pv['var6']);
            }
          });
          $('.pv').each(function(ell){
            $(this).attr('disabled', true);
            $(this).xheditor(false);
          });
        }
      } else {
    	$('#pvbody').css('visibility', 'inherit');
        $('.pv').each(function(ell){
          $(this).attr('disabled', false);
          $(this).xheditor({forcePtag:false, width:'85%', height:'120px',upImgUrl:url('admin/file/editorupload/product')});
        });
      }
    })
  },
  downtermsedit : function() {
    $('.downtermsedit').click(function(){
      var id = $(this).attr('id');
      var value = $('#text_' + id).html();
      $('#text_' + id).html('<form action="'+url('admin/site/widgetedit/downloads/updateterms')+'" method="post"><input value="'+value+'" name="name['+id+']"> <input type="submit" class="btn" value="保存"></form>');
      $(this).html('');
    });
  }
};

BOLING.taxonomy = {
  terms_json : $('#terms_json').val(),
  sid1 : $('#sid1').val(),
  sid2 : $('#sid2').val(),
  sid3 : $('#sid3').val(),
  divname : ['sc1', 'sc2', 'sc3'],
  selectname : ['sclass1', 'sclass2', 'sclass3'],
  start : function(el) {
    $('input#termName').keyup(function(event) {
      BOLING.taxonomy.getalias(event);
    });
    $('.termsselect').live("change", function(){
      BOLING.taxonomy.getalias(el);
    });
    terms_j = eval('(' + BOLING.taxonomy.terms_json + ')');
    BOLING.taxonomy.creatSelect(0, terms_j, BOLING.taxonomy.divname, BOLING.taxonomy.selectname, BOLING.taxonomy.sid1);
    if(BOLING.taxonomy.sid2!=0){
      terms_j = eval('(' + BOLING.taxonomy.terms_json + ')')[BOLING.taxonomy.sid1]['sub'];
      BOLING.taxonomy.creatSelect(1, terms_j, BOLING.taxonomy.divname, BOLING.taxonomy.selectname, BOLING.taxonomy.sid2);
    }
    if(BOLING.taxonomy.sid2!=0){
      terms_j = eval('(' + BOLING.taxonomy.terms_json + ')')[BOLING.taxonomy.sid1]['sub'][BOLING.taxonomy.sid2]['sub'];
      BOLING.taxonomy.creatSelect(2, terms_j, BOLING.taxonomy.divname, BOLING.taxonomy.selectname, BOLING.taxonomy.sid3);
    }
  },
  getalias : function (event){
    var keyCode = event.keyCode || event.which || event.charCode;
    var termName = $('input#termName').val() || 'default';
    var stype = $('input#stype').val();
    var ajaxUrl = $('input#ajaxurl').val();
    var class1 = $('select#sclass1').find("option:selected").text() || '';
    var class2 = $('select#sclass2').find("option:selected").text() || '';
    var class3 = $('select#sclass3').find("option:selected").text() || '';
    termName = termName.replace(/[^0-9A-Za-z-\u4e00-\u9fa5]+/g," ");;
    class1 = class1.replace(/[^0-9A-Za-z-\u4e00-\u9fa5]+/g," ");;
    class2 = class2.replace(/[^0-9A-Za-z-\u4e00-\u9fa5]+/g," ");;
    class3 = class3.replace(/[^0-9A-Za-z-\u4e00-\u9fa5]+/g," ");;
    $.ajax( {
      type : "GET",
      url : url(ajaxUrl + encodeURI(termName) + "/" + encodeURI(stype) + "/" + encodeURI(class1) + "/" + encodeURI(class2)+ "/" + encodeURI(class3)),
      contentType : "application/txt; charset=utf-8",
      dataType : "txt",
      success : function(msg) {
        $('input#path_alias').val(msg);
      }
    });
  },
  creatSelect : function(step, terms, divname, selectname, sid) {
    if (step > divname.length-1) {
      return;
    }
    var nextStep = step + 1;
    if(nextStep == 1){
    	$('#sc2').html('');
    	$('#sc3').html('');
    }
    if(nextStep == 2){
    	$('#sc3').html('');
    }
    var str;
    str = '<select name="' + selectname[step] + '" id="' + selectname[step] + '" class="select termsselect" ><option value="0"></option>';
    for ( var i in terms) {
      str += '<option value="' + terms[i]['tid'] + '" ';
      if (terms[i]['tid'] == sid) {
        str += 'selected';
      }
      str += '>' + terms[i]['name'] + '</option>';
    }
    str += '</select>';
    $('#' + divname[step]).html(str);
    $('#' + selectname[step]).change(function(){
      if(terms[this.value]){
        var nextTerms = terms[this.value]['sub'];
        BOLING.taxonomy.creatSelect(nextStep, nextTerms, divname, selectname, 0);
      }
    });
  }
};

BOLING.product = {
  pid: null,
  type: null,
  dExtra: null,
  pagevariables : null,
  initialize: function(el) {
    var self = BOLING.product;
    self.pid = el.find('#pid').val();
    self.type = el.find('#type').val();
    if ($.xheditor) {
      $('textarea.txt_xheditor').xheditor({forcePtag:false, width:'80%',height:'200px',html5Upload:false,upImgUrl:url('admin/file/editorupload/product') });
    }
    self.bindDirecotyrListener(el);
    self.bindBatchEdit();
    self.dExtra = el.find('#extra');
    self.pagevariables = el.find('#pagevariables');
    el.find('select[name="type"]').change(self.getfields);
    el.find('#btn_continue').click(function(e) {
      el.find('#continue').val(1);
      el.submit();
      e.preventDefault();
    });
    self.bindExtraListener();
    $('a.btn_del_file').click(function(e){
      BOLING.FileManage.delData(e, this);
      e.preventDefault();
    });
    $('#btn_list').click(function(){
      $('#list').show();
      $('#add').hide();
    });
    $('#btn_add').click(function(){
      $('#list').hide();
      $('#add').show();
      $('#ifr_add').attr('src', url('admin/productrelated/addlist'));
    });
    $('#btn_select').click(function(){
      $('#list').hide();
      $('#add').show();
      $('#ifr_add').attr('src', url('admin/productrelated/select'));
    });
    $('#sell_price').keyup(function(){
      var markprice = $('#list_price_percentage').val();
      var sell_price = $('#sell_price').val();
      var old_list_price = $('#list_price').val();
      if (old_list_price != '0.00' || old_list_price != '0') {
        var list_price = sell_price * markprice / 100;
        $('#list_price').val(list_price);
      }
      $('.rank_price').find('.checkboxrank').each(function(){
        var sell_price = $('#sell_price').val();
        var rank = $(this).attr('title');
        var rank_price = sell_price * rank / 100;
        $(this).nextAll('input').val(rank_price);
      });
    });
  },
  bindRankPriceListener: function(el) {
    el.find('input:checkbox').change(function(e) {
      if (this.checked) {
        $(this).nextAll('input').attr('disabled', '').focus();
      } else {
        $(this).nextAll('input').attr('disabled', 'disabled');
      }
    });
  },
  bindListListener: function(el) {
    var dCurrent;
    var dList = el.find('tr:first td');
    var edit = false;
    var dl = eval('(' + $('#directory_tree_list').val() + ')');
    var bl = eval('(' + $('#brand_list').val() + ')');
    var sl = eval('(' + $('#status_list').val() + ')');
    el.click(function(e) {
      var target = e.target;
      var dTarget = $(target);
      var dInput, tagName, oldText, oldValue, field, html;
      if (target.tagName == 'TD' && !edit) {
        if (dCurrent == target && target.className.substr(0, 2) == 'ce') {
          pid = dTarget.parent().attr('class').substr(1);
          if (pid) {
            field = target.className.substr(3);
            html = [];
            if (field.substr(0, 2) == 'dt') {
              oldValue = field.substr(3);
              field = 'dt';
              html.push('<select style="width:90%"><option value="0"></option>');
              
              for (var i in dl) {
                html.push('<option value="' + i + '">' + dl[i] + '</option>');
              }
              html.push('</select>');
              tagName = 'SELECT';
            } else if (field.substr(0, 2) == 'bt') {
              oldValue = field.substr(3);
              field = 'bt';
              html.push('<select style="width:90%"><option value="0"></option>');
              for (var i in bl) {
                html.push('<option value="' + i + '">' + bl[i] + '</option>');
              }
              html.push('</select>');
              tagName = 'SELECT';
            } else if (field.substr(0, 2) == 'sa') {
              oldValue = field.substr(3);
              field = 'sa';
              html.push('<select style="width:90%"><option value="0"></option>');
              for (var i in sl) {
                html.push('<option value="' + i + '">' + sl[i] + '</option>');
              }
              html.push('</select>');
              tagName = 'SELECT';
            } else {
              html.push('<input type="text" style="width:90%"></input>');
              tagName = 'INPUT';
            }
            dInput = $(html.join());
            oldText = dTarget.text();
            if (tagName == 'INPUT') {
              dInput.val(oldText);
            } else if (tagName == 'SELECT') {
              dInput.children('[value="' + oldValue + '"]').attr('selected', 'selected');
            }
            dInput.keypress(function(e) {
              if (13 === e.keyCode) {
                dInput.blur();
              } else if (27 === e.keyCode) {
                $(dCurrent).text(oldText);
                edit = false;
              }
            });
            dInput.blur(function(e) {
              var value = dInput.val();
              tagName = dInput.get(0).tagName;
              if ((tagName == 'INPUT' && value != oldText) || (tagName == 'SELECT' && value != oldValue && value != 0)) {
            	  $.post(url('admin/product/editfield/' + pid + '/' + field), 'value=' + value, function(data) {
                  if (0 === data.error) {
                    if (tagName == 'SELECT') {
                      dCurrent.className = dCurrent.className.substr(0, 6) + value;
                      if (0 == value) {
                        data.value = '-';
                      }
                    }
                    $(dCurrent).text(data.value);
                  } else {
                    $(dCurrent).text(oldText);
                    alert(data.msg);
                  }
                  edit = false;
                  dCurrent = null;
                }, 'json');
              } else {
                $(dCurrent).text(oldText);
                edit = false;
                dCurrent = null;
              }
            });
            dTarget.html(dInput);
            dInput.focus();
            edit = true;
          }
        } else {
          dCurrent = target;
        }
      }
    });
  },
  bindDirecotyrListener: function(el) {
    //var dl = $.parseJSON($('#directory_list').val());
    var dl = eval('(' + $('#directory_list').val() + ')');
    var dTid1 = el.find('select[name="directory_tid1"]');
    var dTid2 = el.find('select[name="directory_tid2"]');
    var dTid3 = el.find('select[name="directory_tid3"]');
    var dTid4 = el.find('select[name="directory_tid4"]');
    var tid1 = dTid1.val(), tid2 = dTid2.val(), tid3 = dTid3.val(), tid4 = dTid4.val();
    var ds, html;
    dTid1.change(function(e) {
      tid1 = dTid1.val();
      dTid2.children(':gt(0)').remove();
      dTid3.children(':gt(0)').remove();
      dTid4.children(':gt(0)').remove();
      if (dl[tid1]) {
        ds = dl[tid1];
        if (ds.sub) {
          html = [];
          for (var i in ds.sub) {
            html.push('<option value="' + i + '">' + ds.sub[i].name + '</option>');
          }
          dTid2.append(html.join(''));
        }
      }
    });
    dTid2.change(function(e) {
      tid2 = dTid2.val();
      dTid3.children(':gt(0)').remove();
      dTid4.children(':gt(0)').remove();
      if (dl[tid1].sub[tid2]) {
        ds = dl[tid1].sub[tid2];
        if (ds.sub) {
          html = [];
          for (var i in ds.sub) {
            html.push('<option value="' + i + '">' + ds.sub[i].name + '</option>');
          }
          dTid3.append(html.join(''));
        }
      }
    });
    dTid3.change(function(e) {
    	
        tid3 = dTid3.val();
        dTid4.children(':gt(0)').remove();
        if (dl[tid1].sub[tid2].sub[tid3]) {
          ds = dl[tid1].sub[tid2].sub[tid3];
          if (ds.sub) {
            html = [];
            for (var i in ds.sub) {
              html.push('<option value="' + i + '">' + ds.sub[i].name + '</option>');
            }
            dTid4.append(html.join(''));
          }
        }
      });
  },
  bindBatchEdit: function() {
	/**
	 * 商品批量编辑, 点击按钮事件；或促销活动的商品设置
	 * added by 55feng (2010-10-14)
	 */
	var isInBatchedit = $('#is_in_batchedit').val();

	if (isInBatchedit!=1) {
	  return false;
	}
    //var dl = $.parseJSON($('#directory_list').val());
    var dl = eval('(' + $('#directory_list').val() + ')');
    var dTid1 = $('#batchDirectoryTid1');
    var dTid2 = $('#batchDirectoryTid2');
    var dTid3 = $('#batchDirectoryTid3');
    var dTid4 = $('#batchDirectoryTid4');
    var tid = 0, tid1 = dTid1.val(), tid2 = dTid2.val(), tid3 = dTid3.val(), tid4 = dTid4.val();
	
    var srcSelect = $('#src_select');
    var destSelect = $('#dest_select');
    
    
    var selectAll = $('#batchedit_select_all');
    var add = $('#batchedit_add');
    var remove = $('#batchedit_remove');
    var cleanAll = $('#batchedit_clean_all');    
    
    var batchDelete = $('#batcheditDeleteSelected');
    var setPromotion = $('#setPromotionProducts');
    
    var ds, html;
    dTid1.change(function(e) {
      tid1 = dTid1.val();
      dTid2.children(':gt(0)').remove();
      dTid3.children(':gt(0)').remove();
      dTid4.children(':gt(0)').remove();
      if (dl[tid1]) {
        ds = dl[tid1];
        if (ds.sub) {
          html = [];
          for (var i in ds.sub) {
            html.push('<option value="' + i + '">' + ds.sub[i].name + '</option>');
          }
          dTid2.append(html.join(''));
        }
      }
    });
    dTid2.change(function(e) {
      tid1 = dTid1.val();
      tid2 = dTid2.val();
      dTid3.children(':gt(0)').remove();
      dTid4.children(':gt(0)').remove();
      if (dl[tid1].sub[tid2]) {
        ds = dl[tid1].sub[tid2];
        if (ds.sub) {
          html = [];
          for (var i in ds.sub) {
            html.push('<option value="' + i + '">' + ds.sub[i].name + '</option>');
          }
          dTid3.append(html.join(''));
        }
      }
    });
    dTid3.change(function(e) {
      tid2 = dTid2.val();    
      tid3 = dTid3.val();
      dTid4.children(':gt(0)').remove();
      if (dl[tid1].sub[tid2].sub[tid3]) {
        ds = dl[tid1].sub[tid2].sub[tid3];
        if (ds.sub) {
          html = [];
          for (var i in ds.sub) {
            html.push('<option value="' + i + '">' + ds.sub[i].name + '</option>');
          }
          dTid4.append(html.join(''));
        }
      }
    });
    
    selectAll.click(function(e) {
      html = [];
      srcSelect.children('option').each(function(){
        html.push('<option value="' + $(this).val() + '">' + $(this).text() + '</option>');
      });
      destSelect.append(html.join(''));
      srcSelect.children().remove();
      BOLING.product.batchEditSetPidList(destSelect);
    });
    
    add.click(function(e) {
      html = [];
      srcSelect.children('option:selected').each(function(){
        html.push('<option value="' + $(this).val() + '">' + $(this).text() + '</option>');
        $(this).remove();
      });    
      destSelect.append(html.join(''));
      BOLING.product.batchEditSetPidList(destSelect);
    });
    
    remove.click(function(e) {
      html = [];
      destSelect.children('option:selected').each(function(){
        html.push('<option value="' + $(this).val() + '">' + $(this).text() + '</option>');
        $(this).remove();
      });    
      srcSelect.append(html.join(''));
      BOLING.product.batchEditSetPidList(destSelect);
    });
    
    cleanAll.click(function(e) {
      html = [];
      destSelect.children('option').each(function(){
        html.push('<option value="' + $(this).val() + '">' + $(this).text() + '</option>');
      });
      srcSelect.append(html.join(''));
      destSelect.children().remove(); 
      BOLING.product.batchEditSetPidList(destSelect);
    });
    batchDelete.click(function(e) {
      BOLING.product.batchEditSetPidList(destSelect);
      
      var pidListValue = $('#BatchPidList').val();
      var path = 'admin/batchjobs/batchDeleteProducts';
      if(pidListValue==''){
        alert('请在上面列表选择您要删除的商品. ');
      }else{
    	if(confirm('确认要删除吗(不可恢复)? ')){ 
    	  $.post(url(path), { pidlist: pidListValue },function(data){ 
    		if(data=='OK'){
    		  alert('商品已删除!'); 
              destSelect.children().remove(); 
              BOLING.product.batchEditSetPidList(destSelect);
    	    }else{
    	      alert('已删除' + data + '个商品。');
    	    }
    	  });
    	}
      }
    });	
    setPromotion.click(function(e) {
      BOLING.product.batchEditSetPidList(destSelect);
    	
      var pidListValue = $('#BatchPidList').val();
      var form = $('#form_productinfo');
      //if(pidListValue==''){
      //  alert('请在上面列表选择您要设置的商品. ');
      //}else{
      	form.submit();
      //}
    });	
    $('input.batchEditFilterButton').click(function(e) {
      var filter = new Array();
      tid1 = dTid1.val();
      tid2 = dTid2.val();
      tid3 = dTid3.val();
      tid4 = dTid4.val();
      filter['tid'] = tid4!=0 ? tid4 : (tid3!=0 ? tid3 : (tid2!=0 ? tid2 : tid1));
      filter['name'] = $('#batchProductName').val();
      filter['sn'] = $('#batchProductSN').val();
      filter['pid'] = $('#batchProductID').val();
      filter['number'] = $('#batchProductNumber').val();
      filter['brand_tid'] = $('#batchProductBrandTID').val();
      filter['status'] = $('#batchProductStatus').val();
      filter['lowPrice'] = $('#batchProductLowprice').val();
      filter['highPrice'] = $('#batchProductHighprice').val();
      BOLING.product.getBatchEditProductList(filter);
    });	
  },
  getBatchEditProductList: function(filter) {

	/**
	 * 商品批量编辑, 批量编辑选择商品时在下面选择框列出商品列表
	 * added by 55feng (2010-10-14)
	 */
	var isInBatchedit = $('#is_in_batchedit').val();	
	if (isInBatchedit!=1) {
	  return false;
	}
	var srcSelect = $('#src_select');
	var destSelect = $('#dest_select');    
	var path = 'admin/batchjobs/productsJsonList';
	var html;
	$('#batchProductLoading').css('display', 'block');
	$.getJSON(url(path), { tid: filter['tid'], name: filter['name'], 
		sn: filter['sn'], pid: filter['pid'], 
		number: filter['number'], brand_tid: filter['brand_tid'], 
		status: filter['status'], lowPrice: filter['lowPrice'], 
		highPrice: filter['highPrice'] }, function(json){ 
	
	  ///var count = 0;
	  ///for (var k in json) count++;
	  
	  srcSelect.children().remove();
	  //destSelect.children().remove();		
	  html = [];
	  $.each(json, function(pid, name){ 
	    html.push('<option value="' + pid + '">' + name + '</option>');
	  });
	  srcSelect.append(html.join(''));
	  $('#batchProductLoading').css('display', 'none');
	});
	
  },  
  batchEditSetPidList: function(destSelect) {
	/**
	 * 商品批量编辑, 设置选中的商品ID列表
	 * added by 55feng (2010-10-14)
	 */
	var pidListValue = '[';
	var i = 0;
	var pidList = $('#BatchPidList');
    destSelect.children("option").each(function(){
      i>0 ? pidListValue += ','+$(this).val():pidListValue += $(this).val();
      i++;
    });
    pidListValue += ']';
    if(i==0){
      pidListValue = '';
    }
    pidList.val( pidListValue );
  },
  getfields: function(e) {
    var self = BOLING.product;
    var type = e ? this.value : self.type;
    if (type == '') {
      return;
    } else {
      var u = 'admin/product/getfields/' + type + '/';
      if (self.pid != '') {
        u += self.pid + '/';
      }
      $.get(url(u), null, function(data) {
        self.dExtra.html(data);
        self.bindExtraWidget();
      });
    }
  },
  bindExtraWidget: function() {
    var self = BOLING.product;
    var dXheditor = self.dExtra.find('textarea.field_xheditor');
    if (dXheditor.xheditor) {
      dXheditor.removeClass('field_xheditor').addClass('xheditor').xheditor({forcePtag:false, width:'80%',height:'200px',upImgUrl:url('admin/file/editorupload/product')});
    }
    var dDatepicker = self.dExtra.find('input.field_datepicker');
    dDatepicker.removeClass('field_datepicker').addClass('datepicker').datepicker({dateFormat:'yy-mm-dd'});
  },
  bindExtraListener: function() {
    var self = BOLING.product;
    self.dExtra.click(function(e) {
      if (e.target.tagName == 'A') {
        var dTarget = $(e.target);
        if (dTarget.hasClass('btn_field_add')) {
          var td = dTarget.closest('tr').next().children();
          var dNew = td.children('p:last').clone();
          var dObj = dNew.children(':first');
          if (dObj.hasClass('xheditor')) {
            dObj.removeClass('xheditor').addClass('field_xheditor');
            dObj.siblings(':lt(2)').remove().show();
          } else if (dObj.hasClass('datepicker')) {
            dObj.attr('id', '').removeClass('hasDatepicker').removeClass('datepicker').addClass('field_datepicker');
          }
          dNew.appendTo(td);
          self.bindExtraWidget();
          e.preventDefault();
        } else if (dTarget.hasClass('btn_field_remove')) {
          var dP = dTarget.closest('p');
          if (dP.siblings().length) {
            dP.remove();
          }
          e.preventDefault();
        }
      }
    });
    self.bindExtraWidget();
  }
};

BOLING.productrelated = {
  pids : {},  
  len : 0,
  pid : null,
  initialize : function (el) {
    BOLING.product.bindDirecotyrListener(el);
  },
  relatedinit : function (el) {
    $('.isbothway').live('click', function(){
      var pid = $(this).attr("title");
      var status = $(this).val();
      var pids_string = $('#related_info').val();
      var pids = eval('(' + pids_string + ')');
      pids[pid]['isbothway'] = status;
      var new_string = JsonToStr(pids);
      $('#related_info').val(new_string);
    });
    $('.related_cancel').live("click",function(){
      var pid = $(this).attr("rel");
      BOLING.productrelated.del_row(this, pid);
    });
  },
  relatedaddlistinit : function (el) {
    $('#prosceniumaddrelated').click(function(){
      var self = BOLING.productrelated;
      var pids_string = $(window.parent.document).find('#related_info').val();
      var pids = eval('(' + pids_string + ')');
      var pids_add = {};
      var status = 0;
      $("[name='checkItem']:checked").each(function(){
          status = 0;
          var pid = $(this).val();
          for( var i in pids){
            if(pid == i){
              status = 1;
            }
          }
          pids[pid] = new Array(); 
          pids[pid]['pid'] = $(this).val();
          pids[pid]['name'] = $(this).attr("title");
          pids[pid]['isbothway'] = 1;
         if (status == 0) {
            pids_add[pid] = new Array(); 
            pids_add[pid]['pid'] = $(this).val();
            pids_add[pid]['name'] = $(this).attr("title");
            pids_add[pid]['isbothway'] = 1;
          }
      });
      if ($(window.parent.document).find('#IsInTagsInfoEdit').val() == 'YES') {
        var new_string = JSON.encode(pids);
      } else {
      	var new_string = JsonToStr(pids);
      }
      $(window.parent.document).find('#related_info').val(new_string);
      self.getviewaction(pids_add);
   });
  },
  getviewaction : function (pids){
    var self = BOLING.productrelated;
    $(window.parent.document).find("#add").hide();
    $(window.parent.document).find("#list").show();
    var listDataObject = parent.document.getElementById('datalist');
    $('datalist').find('tr').hide();
    self.add_row(listDataObject, pids);
    $('#related_info').val(String(pids));
  },
  add_row : function (obj, pids){
    var self = BOLING.productrelated;
    var isbothway = $(window.parent.document).find("#isbothway").val();
    for(var i in pids){
      if (self.len == 0) {
        var len = $(window.parent.document).find("#datalist").find("tr").prevAll().length + 1; 
      } else {
        var len = self.len + 1; 
      }
      self.len = len;
      var tr = obj.insertRow(1);
      var td;
      td = tr.insertCell(0);   
      td.innerHTML = '<a href="javascript:;" id="a_cancel_'+pids[i]['pid']+'" class="related_cancel" rel="'+pids[i]['pid']+'" >&nbsp;&nbsp;&nbsp;&nbsp;</a>';
      td = tr.insertCell(1);  
      td.innerHTML = pids[i]['name']
      if(isbothway != 'no') {
        td = tr.insertCell(2);  
        td.innerHTML = '<input class="isbothway" id="el-c9631-e855c" type="radio" name="isall_'+pids[i]['pid']+'" title="'+pids[i]['pid']+'" value="0" /> 单向关联&nbsp;<input class="isbothway" id="el-c9631-e855b" title="'+pids[i]['pid']+'" type="radio" name="isall_'+pids[i]['pid']+'" value="1" checked/> 双向关联';
      }
    }
  },   
  del_row : function (obj, pid){
    BOLING.productrelated.len--; 
    $(obj).closest('tr').remove();
    BOLING.productrelated.del_pids(pid);
  },
  del_pids : function (pid){
    var self = BOLING.productrelated;
    var pids_string = $('#related_info').val();
    var pids = eval('(' + pids_string + ')');
    var new_pids = {};
    for(var i in pids){
      if(i != pid){
        new_pids[i] = {};
        new_pids[i]['pid'] = pids[i]['pid'];    
        new_pids[i]['name'] = pids[i]['name'];
        new_pids[i]['isbothway'] = pids[i]['isbothway'];
      }
    }
    var new_string = JsonToStr(new_pids);
    $('#related_info').val(new_string);
  },
  puttoinput : function (pids) {
    var self = BOLING.productrelated;
    var str = "";
    for(var i in pids){
      if (str != "") {
        str += ",";
      }
      str += pids[i]['pid'];
    }
    return str;
  }
};

BOLING.swfuplod = {
  swfu : null,
  set : null,
  numFilesUploaded : 0,
  initialize : function (el){
  	$('.hidden').show();
    var self = BOLING.swfuplod;
    var swfupload_id = $('#swfupload_id').val();
    if (swfupload_id == 'batchuploadproduct') {
      if ($("[name='free_shipping']").attr('checked') == true) {
    	  free_shipping = 1;
      } else {
    	  free_shipping = 0;
      }
      if ($("[name='status']").attr('checked')  == true) {
    	  statu = 1;
      } else {
    	  statu = 0;
      }
      if ($("[name='shippable']").attr('checked')  == true) {
    	  shippable = 1;
      } else {
    	  shippable = 0;
      }
      var set = {
        flash_url : url('scripts/swfupload-jquery/swfupload/') + 'swfupload.swf',
        upload_url: url('admin/product/uploadProduct'),
        post_params: {"PHPSESSID" : $('#session_id').val(), 
          "num" : $("[name='num']").val(),
          "directory_tid1" : $("[name='directory_tid1']").val(),
          "directory_tid2" : $("[name='directory_tid2']").val(),
          "directory_tid3" : $("[name='directory_tid3']").val(),
          "directory_tid4" : $("[name='directory_tid4']").val(),
          "brand_tid" : $("[name='brand_tid']").val(),
          "sn" : $("[name='sn']").val(),
          "name" : $("[name='name']").val(),
          "sell_price" : $("[name='sell_price']").val(),
          "list_price" : $("[name='list_price']").val(),
          "wt" : $("[name='wt']").val(),
          "stock" : $("[name='stock']").val(),
          "sell_max" : $("[name='sell_max']").val(),
          "sell_min" : $("[name='sell_min']").val(),
          "summary" : $("[name='summary']").val(),
          "description" : $("[name='description']").val(),
          "template" : $("[name='template']").val(),
          "type" : $("[name='type']").val(),
          "free_shipping" : free_shipping,
          "status" : statu,
          "shippable" : shippable
        },
        file_post_name : 'filedata',
        file_size_limit : "8192",
        file_types : "*.jpg;*.gif;*.png",
        file_types_description : "Images",
        file_upload_limit : 0,
        file_queue_limit : 0,
        custom_settings : {
          progressTarget : "fsUploadProgress",
          cancelButtonId : "btnCancel"
        },
        debug: false,
        button_image_url : url('scripts/swfupload-jquery/images/') + 'upload-btn.png',
        button_width : 61,
        button_height : 22,
        button_placeholder : $('#button')[0]
      };
    } else if (swfupload_id == 'productalbum') {
      var set = {
        flash_url : url('scripts/swfupload-jquery/swfupload/') + 'swfupload.swf',
        upload_url: url('admin/file/savefile'),
        post_params: {"PHPSESSID" : $('#session_id').val()},
        file_post_name : 'filedata',
        file_size_limit : "8192",
        file_types : "*.jpg;*.gif;*.png",
        file_types_description : "Images",
        file_upload_limit : 0,
        file_queue_limit : 0,
        custom_settings : {
          progressTarget : "fsUploadProgress",
          cancelButtonId : "btnCancel"
        },
        debug: false,
        button_image_url : url('scripts/swfupload-jquery/images/') + 'upload-btn.png',
        button_width : 61,
        button_height : 22,
        button_placeholder : $('#button')[0]
      };
    }
    swfu = el.swfupload(set);
    self.swfu = swfu;
    self.set = set;
    $('#btnstartupload').click(function(event){
      swfu.swfupload('startUpload');
    });
    swfu.bind('fileDialogStart', self.fileDialogStart)
      .bind('fileQueued', self.fileQueued)
      .bind('fileQueueError', self.fileQueueError)
      .bind('uploadStart', self.uploadStart)
      .bind('uploadProgress', self.uploadProgress)
      .bind('uploadSuccess', self.uploadSuccess)
      .bind('uploadComplete', self.uploadComplete)
      .bind('uploadError', self.uploadError);
    
  },
  fileDialogStart : function(){
    BOLING.swfuplod.numFilesUploaded = 0;
    BOLING.FileManage.changeUplodNums(0);
  },
  fileQueued : function(event, file){
    BOLING.FileProgress.FileProgress(file, BOLING.swfuplod.set.custom_settings.progressTarget);
    BOLING.FileProgress.setStatus(file.id, 'pending');
    $('#' + file.id).find('a.progressCancel').live('click',function(){
      BOLING.FileProgress.cancel(self.swfu, file.id);
    });
  },
  fileQueueError : function(event, file, errorCode, message){
    switch (errorCode) {
    case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
      alert("You have attempted to queue too many files.\n" + (message === 0 ? "You have reached the upload limit." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
      return;
    case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
      BOLING.FileProgress.FileProgress(file, BOLING.swfuplod.set.custom_settings.progressTarget);
      $('#' + file.id).find('.progressContainer').addClass('red');
      $('#' + file.id).find('.progressCancel').css('visibility','hidden');
      BOLING.FileProgress.setStatus(file.id, 'Error Code: File too big');
      setTimeout(function(){$('#'+file.id).hide('slow')}, 5000);
      break;
    case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
      BOLING.FileProgress.FileProgress(file, BOLING.swfuplod.set.custom_settings.progressTarget);
      $('#' + file.id).find('.progressContainer').addClass('red');
      $('#' + file.id).find('.progressCancel').css('visibility','hidden');
      BOLING.FileProgress.setStatus(file.id, 'Error Code: Zero byte file');
      setTimeout(function(){$('#'+file.id).hide('slow')}, 5000);
      break;
    case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
      BOLING.FileProgress.FileProgress(file, BOLING.swfuplod.set.custom_settings.progressTarget);
      $('#' + file.id).find('.progressContainer').addClass('red');
      $('#' + file.id).find('.progressCancel').css('visibility','hidden');
      BOLING.FileProgress.setStatus(file.id, 'Invalid File Type');
      setTimeout(function(){$('#'+file.id).hide('slow')}, 5000);
      break;
    default:
      if (file !== null) {
        BOLING.FileProgress.setStatus(file.id, 'Unhandled Error');
      }
      break;
    }
  },
  uploadStart : function(event, file){
    BOLING.FileProgress.setStatus(file.id, 'starting');
  },
  uploadProgress : function(event, file, bytesLoaded, bytesTotal){
    var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
    $('#' + file.id).find('.progressContainer').addClass('green');
    $('#' + file.id).find('.progressBarInProgress').css('width',percent + "%");
  },
  uploadSuccess : function(event, file, serverData){
    var swfupload_id = $('#swfupload_id').val();
    if (swfupload_id == 'batchuploadproduct') {
      BOLING.swfuplod.batchuploadproductSuccess(serverData, file);
    } else if (swfupload_id == 'productalbum') {
      BOLING.swfuplod.productalbumSuccess(serverData, file);
    }
    
  },
  batchuploadproductSuccess : function (serverData, file) {
    if (serverData == '0') {
      $('#' + file.id).find('.progressContainer').addClass('red');
      BOLING.FileProgress.setStatus(file.id, 'error');
      $('#' + file.id).find('.progressBarInProgress').css('width',"0");
      $('#' + file.id).find('.progressCancel').css('visibility','hidden');
      alert(file.name+' 图片上传失败');
    } else if (serverData == '-2') {
      $('#' + file.id).find('.progressContainer').addClass('red');
      BOLING.FileProgress.setStatus(file.id, 'error');
      $('#' + file.id).find('.progressBarInProgress').css('width',"0");
      $('#' + file.id).find('.progressCancel').css('visibility','hidden');
      alert('请商品相关信息');
    } else {
      $('#' + file.id).find('.progressContainer').addClass('blue');
      BOLING.FileProgress.setStatus(file.id, 'complete');
      $('#' + file.id).find('.progressBarInProgress').css('width',"0");
      $('#' + file.id).find('.progressCancel').css('visibility','hidden');
      numFilesUploaded = BOLING.swfuplod.numFilesUploaded + 1;
      BOLING.swfuplod.numFilesUploaded = numFilesUploaded;
      BOLING.FileManage.changeUplodNums(numFilesUploaded);
    }
  },
  productalbumSuccess : function (serverData, file) {
    if (serverData && serverData != '-1'){
      $('#' + file.id).find('.progressContainer').addClass('blue');
      BOLING.FileProgress.setStatus(file.id, 'complete');
      $('#' + file.id).find('.progressBarInProgress').css('width',"0");
      $('#' + file.id).find('.progressCancel').css('visibility','hidden');
      numFilesUploaded = BOLING.swfuplod.numFilesUploaded + 1;
      BOLING.swfuplod.numFilesUploaded = numFilesUploaded;
      BOLING.FileManage.changeUplodNums(numFilesUploaded);
      serverData = $.parseJSON(serverData);
      if(serverData.fid){
        BOLING.FileManage.view(serverData);
      }
    } else {
      $('#' + file.id).find('.progressContainer').addClass('red');
      BOLING.FileProgress.setStatus(file.id, 'error');
      $('#' + file.id).find('.progressBarInProgress').css('width',"0");
      $('#' + file.id).find('.progressCancel').css('visibility','hidden');
    }
  },
  uploadComplete : function(event, file){
    setTimeout(function(){$('#'+file.id).hide('slow')}, 5000);
    $(this).swfupload('startUpload');
  },
  uploadError : function(event, file, errorCode, message){
    $('#' + file.id).find('.progressContainer').addClass('red');
    BOLING.FileProgress.setStatus(file.id, message);
  }
};

BOLING.FileProgress = {
  FileProgress : function(file, targetID) { 
    fileProgressID = file.id;
    var shtml = $('#'+targetID).html();
    var html = '';
    html = '<div id="'+file.id+'" class="progressWrapper">';
    html += '<div class="progressContainer">';
    html += '<a class="progressCancel" href="###" style="visibility: visible;"> </a>';
    html += '<div class="progressName">'+file.name+'</div>';
    html += '<div class="progressBarStatus">Pending...</div>';
    html += '<div class="progressBarInProgress"></div>';
    html += '</div>';
    html += '</div>';
    $('#'+targetID).html(shtml+html);
  },
  setStatus : function (fileID, msg){
    $('#' + fileID).find('.progressBarStatus').html(msg);
  },
  cancel : function(swfUploadInstance, fileID) {
    swfUploadInstance.swfupload('cancelUpload', fileID);
    $('#' + fileID).find('.progressContainer').addClass('red');
    BOLING.FileProgress.setStatus(fileID, 'cencel');
    $('#'+fileID).hide('slow');
  }
};

BOLING.FileManage = {
  len : 0,
  filesjson : eval('(' + $('#files').val() + ')'),
  init : function(){
    $('.file_list_txt').live("keyup", function(){
      var fid = $(this).attr('title');
      var fidArray = BOLING.FileManage.filesjson;
      fidArray[fid]['alt'] = $(this).val();
      $('#files').val(JsonToStr(fidArray));
    });
    $('.file_list_weight_txt').live("keyup", function(){
      var fid = $(this).attr('title');
      var fidArray = BOLING.FileManage.filesjson;
      fidArray[fid]['weight'] = $(this).val();
      BOLING.FileManage.filesjson = fidArray;
      $('#files').val(JsonToStr(fidArray));
    });
  },
  changeUplodNums : function(num){
    var htmlarr = $('#divStatus').html().split(' ');
    htmlarr[0] = num;
    var html = '';
    for($i=0; $i<htmlarr.length; $i++){
      html += htmlarr[$i]+' ';
    }
    $('#divStatus').html(html);
  },
  delData : function (e ,obj){
    var fid = $(obj).attr('rel'); 
    BOLING.FileManage.del_row(obj, fid);
  },
  view : function (file){
    var cell = [
                  '<a href="/images'+file.filepath+'" target="_blank"><img src="/images/cache/admin_product_album'+file.filepath+'" class="file_list_img" /></a>',
                  file.filename,
//                  file.filename,
                  '<input class="file_list_txt" value="" name="alt['+file.fid+']" title="'+file.fid+'" />',
                  '<input class="file_list_weight_txt" value="0" name="weights['+file.fid+']" title="'+file.fid+'" />',
                  '<a href="###" id="btn'+file.fid+'" class="btndel">删除</a>'
                ];
    var listDataObject = document.getElementById('listData');
    BOLING.FileManage.add_row(listDataObject, cell, file.fid);
    BOLING.FileManage.add_fids(file.fid);
    $('#btn'+file.fid).click(function(){
      BOLING.FileManage.del_row(this, file.fid);
    });
  },
  add_row : function (obj, cell ,id){
    if (BOLING.FileManage.len == 0) {
      var len = $('#listData').find("tr").prevAll().length + 1; 
    } else {
      var len = BOLING.FileManage.len + 1; 
    }
    BOLING.FileManage.len = len;
    var tr = obj.insertRow(0); 
    var td;
    var j = 0;
    for(var i=0;i<cell.length;i++){
      td = tr.insertCell(j);   
      td.innerHTML = cell[i];
      ++j;
    }   
  },   
  del_row : function (obj, fid){
    BOLING.FileManage.len--; 
    $(obj).closest('tr').remove();
    BOLING.FileManage.del_fids(fid);
  },
  add_fids : function (fid){
    BOLING.FileManage.filesjson[fid] = {}; 
    BOLING.FileManage.filesjson[fid]['fid'] = fid;
    BOLING.FileManage.filesjson[fid]['alt'] = '';
    BOLING.FileManage.filesjson[fid]['weight'] = 0;
    $('#files').val(JsonToStr(BOLING.FileManage.filesjson));
  },
  del_fids : function (fid){
    var fidArray = BOLING.FileManage.filesjson;
    var new_fids = new Array();
    for(var i in fidArray){
      if(fidArray[i]['fid'] != fid){
        new_fids[i] = {};
        new_fids[i]['fid'] = fidArray[i]['fid'];    
        new_fids[i]['alt'] = fidArray[i]['alt'];
        new_fids[i]['weight'] = fidArray[i]['weight'];
      }
    }
    BOLING.FileManage.filesjson = new_fids;
    $('#files').val(JsonToStr(new_fids));
  }      
};

BOLING.articleRelated = {
  len : 0,
  init : function (){
    $('#btn_list1').click(function(){
      $('#list1').show();
      $('#add1').hide();
    });
    $('#btn_add1').click(function(){
      $('#list1').hide();
      $('#add1').show();
      $('#ifr_add1').attr('src', url('admin/content/getAricleRelatedList'));
    });
    $('#btn_select1').click(function(){
      $('#list1').hide();
      $('#add1').show();
      $('#ifr_add1').attr('src', url('admin/content/getAricleRelatedSelect'));
    }); 
    $('#articleaddrelated').click(function(){
      var self = BOLING.articleRelated;
      var articleRelated =  eval('(' + $(window.parent.document).find('#articleRelatedData').val() + ')');
      var status = 0;
      var aid;
      $("[name='checkItem']:checked").each(function(){
          aid = $(this).val();
          if(aid) {
            articleRelated[aid] = new Array();
            articleRelated[aid]['aid'] = $(this).val();
            articleRelated[aid]['title'] = $(this).attr('title');
          }
      });
      var new_string = JsonToStr(articleRelated);
      $(window.parent.document).find('#articleRelatedData').val(new_string);
      self.getviewaction();
    });
    $('.related_cancel').live("click",function(){
      var aid = $(this).attr("rel");
      BOLING.articleRelated.del_row(this, aid);
    });
  },
  getviewaction : function (){
    var self = BOLING.articleRelated;
    $(window.parent.document).find("#add1").hide();
    $(window.parent.document).find("#list1").show();
    var listDataObject = parent.document.getElementById('datalist1');
    self.show(listDataObject);
  },
  show : function (obj){
    $(obj).find('tr').remove();
    var articleRelated =  eval('(' + $(window.parent.document).find('#articleRelatedData').val() + ')');
    for(var i in articleRelated){
      if (self.len == 0) {
        var len = $(window.parent.document).find("#datalist1").find("tr").prevAll().length + 1; 
      } else {
        var len = self.len + 1; 
      }
      self.len = len;
      var tr = obj.insertRow(0);
      var td;
      td = tr.insertCell(0);   
      td.innerHTML = '<a href="javascript:;" id="a_cancel_'+articleRelated[i]['aid']+'" name="aaaa_cancel" class="related_cancel" rel="'+articleRelated[i]['aid']+'" >&nbsp;&nbsp;&nbsp;&nbsp;</a>';
      td = tr.insertCell(1);  
      td.innerHTML = articleRelated[i]['title'];
    }
  },   
  del_row : function (obj, aid){
    BOLING.articleRelated.len--; 
    $(obj).closest('tr').remove();
    BOLING.articleRelated.del_pids(aid);
  },
  del_pids : function (aid){
    var self = BOLING.articleRelated;
    var articleRelated = eval('(' + $('#articleRelatedData').val() + ')');
    var new_pids = new Array();
    for(var i in articleRelated){
      if(i != aid){
        new_pids[i] = new Array();
        new_pids[i]['aid'] = articleRelated[i]['aid'];    
        new_pids[i]['title'] = articleRelated[i]['title'];
      }
    }
    var new_string = JsonToStr(new_pids);
    $('#articleRelatedData').val(new_string);
  }

};

BOLING.articleProduct = {
  len : 0,
  init : function (){
    $('#btn_list').click(function(){
      $('#list').show();
      $('#add').hide();
    });
    $('#btn_add').click(function(){
      $('#list').hide();
      $('#add').show();
      $('#ifr_add').attr('src', url('admin/productrelated/addlist'));
    });
    $('#btn_select').click(function(){
      $('#list').hide();
      $('#add').show();
      $('#ifr_add').attr('src', url('admin/productrelated/select'));
    }); 
    $('.related_cancel').live("click",function(){
      var pid = $(this).attr("rel");
      BOLING.productrelated.del_row(this, pid);
    });
  }
};

BOLING.content = {
	pageStart: function(el){
		$('input#page_title').keyup(function(event) {
			BOLING.content.getPageAlias(event);
		});
	},
	articleTypeStart: function(el){
		$('input#page_title').keyup(function(event) {
			BOLING.content.getarticleTypeAlias(event);
		});
	},
	getPageAlias: function (event){
	    var keyCode = event.keyCode || event.which || event.charCode;
		var page_title = $('input#page_title').val();
		page_title = page_title.replace(/[^0-9A-Za-z-\u4e00-\u9fa5]+/g," ");
		if($.trim(page_title) != ''){
			$.get(url('admin/content/getpagealias/' + encodeURI(page_title)), function(msg){
				$('#path_alias').val(msg);
				$('#article_type_id').val(msg);
			});
		}
	},
	getarticleTypeAlias: function (event){
	    var keyCode = event.keyCode || event.which || event.charCode;
		var page_title = $('input#page_title').val();
		page_title = page_title.replace(/[^0-9A-Za-z-\u4e00-\u9fa5]+/g," ");
		var article_category = $('select#article_category').attr('value');
		var term_json = eval('(' + $('#terms_json').val() + ')');
		var cid = $('#article_category').val();
		if(cid > 0){
			article_category = term_json[cid]['allname'];
		}else{
			article_category = '';
		}
		if($.trim(page_title) != ''){
			$.getJSON(url('admin/content/gettypealias/' + encodeURI(page_title) + '/' + encodeURI(article_category)), function(msg){
				$('#article_type_id').val(msg['title']);
				if(msg['type'] != ''){
					$('#path_alias').val(msg['type'] + '-' + msg['title']);
				}else{
					$('#path_alias').val(msg['title']);
				}
			});
		}
	}
};

BOLING.promotion = {
  initialize: function(el) {
    el.find('input.datepicker').datepicker({dateFormat:'yy-mm-dd'});
  }
};

BOLING.order = {
    initialize: function(el) {
      el.find('input.datepicker').datepicker({dateFormat:'yy-mm-dd'});
    }
};

BOLING.widget = {
  initialize: function(el) {
    el.find('input.datepicker').datepicker({dateFormat:'yy-mm-dd'});
  }
};

BOLING.template = {
  dFileSelector: null,
  dTextarea: null,
  busying: false,
  initialize: function(el) {
  	var self = BOLING.template;
  	self.dTextarea = el.find('textarea:first');
  	var dSelector = el.find('#filename');
  	self.dFileSelector = dSelector;
  	dSelector.change(function(e) {
  	  self.loadFileContent(this.value);
  	});
  	el.find('#tplfile_reload').click(function(e) {
  	  BOLING.template.loadFileContent(dSelector.val());
  	  e.preventDefault();
  	});
  },
  loadFileContent: function(file, loadDefault){
    if (typeof loadDefault == 'undefined') {
      loadDefault = false;
    }
    var self = BOLING.template;
    if (self.busying) {
      return;
    }
    self.busying = true;
    self.dTextarea.attr('disabled', true);
    $.getJSON(url('admin/site/ajaxgettemplatecontent/' + file + '/' + (loadDefault ? '1' : '0')), null, self.showFileContent);
  },
  showFileContent: function(result) {
    var self = BOLING.template;
    if (result.error) {
      alert(result.msg);
    } else {
      self.dTextarea.val(result.content);
    }
    self.dTextarea.attr('disabled', false);
    self.busying = false;
  }
};


(function() {
  var doWhileExist = function(sModuleId, oFunction) {
    var el = $(sModuleId);
    if (el.length) oFunction(el);
  };

  BOLING.initialize();
  doWhileExist('input#checkAll', BOLING.checkboxButton);
  doWhileExist('div#taxonomy', BOLING.taxonomy.start);
  doWhileExist('a#checkAllPage', BOLING.checkAllPage);
  doWhileExist('form#form_productinfo', BOLING.product.initialize);
  doWhileExist('form#form_productinfo .rank_price', BOLING.product.bindRankPriceListener);
  doWhileExist('div#swfupload-control', BOLING.swfuplod.initialize);
  doWhileExist('div#productrelated', BOLING.productrelated.initialize);
  doWhileExist('div#related', BOLING.productrelated.relatedinit);
  doWhileExist('div#relatedaddlist', BOLING.productrelated.relatedaddlistinit);
  doWhileExist('table#products_list', BOLING.product.bindListListener);
  doWhileExist('div#album', BOLING.FileManage.init);
  doWhileExist('div#articleRelated', BOLING.articleRelated.init);
  doWhileExist('div#articleProduct', BOLING.articleProduct.init);
  doWhileExist('textarea.pagevariable_xheditor', BOLING.pagevariable_xheditor);
  doWhileExist('form#form_promotioninfo', BOLING.promotion.initialize);
  doWhileExist('select#currencylist', BOLING.getcurrencylist);
  doWhileExist('input.inputsubmit', BOLING.inputsubmit);
  doWhileExist('select#shipping_method', BOLING.getshippingmoney);
  doWhileExist('div.body_left', BOLING.resetbody);
  doWhileExist('form#form_order', BOLING.order.initialize);
  doWhileExist("input[type='submit'][title!='nocheck']" , BOLING.postcheck);
  doWhileExist('select#select_countries', BOLING.ajaxgetprovince);
  doWhileExist('#shippingarea', BOLING.shippingarea);
  doWhileExist('select#pvTheme', BOLING.getpvThemeData);
  doWhileExist('form#form_templateedit', BOLING.template.initialize);
  doWhileExist('.downtermsedit', BOLING.downtermsedit);
  doWhileExist('.form_widget', BOLING.widget.initialize);
  doWhileExist('div#article_page', BOLING.content.pageStart);
  doWhileExist('div#article_type', BOLING.content.articleTypeStart);
})();
