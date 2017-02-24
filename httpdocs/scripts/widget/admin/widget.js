(function() {
  $('#filterproduct').click(function(){
    $('#productList').attr('disabled', true);
    var directory_tid1 = $('select[name="directory_tid1"]').val();
    var directory_tid2 = $('select[name="directory_tid2"]').val();
    var directory_tid3 = $('select[name="directory_tid3"]').val();
    var directory_tid4 = $('select[name="directory_tid4"]').val();
    var pname = $('input[name="pname"]').val();
    var ajaxUrl = '/admin/site/widgetedit/promotion/ajaxGetFilterProducts/';
    $.ajax( {
      type : "GET",
      url : ajaxUrl + encodeURI(directory_tid1) + "/" + encodeURI(directory_tid2) + "/" + encodeURI(directory_tid3)+ "/" + encodeURI(directory_tid4)+ "/" + encodeURI(pname),
      contentType : "application/txt; charset=utf-8",
      dataType : "txt",
      success : function(msg) {
        $('#productList').attr('disabled', false);
        var ret = eval('(' + msg + ')');
        if (ret['status'] == '1') {
          var products = ret['data'];
          var select = document.getElementById('productList');
          $("#productList option").remove();
          for(var i in products) {
            var opt = document.createElement("option");
            opt.value = products[i]['pid'];
            opt.innerHTML = products[i]['name'];
            select.appendChild(opt);
          }
        } else if (ret['status'] == '-1')  {
          $("#productList option").remove();
          var select = document.getElementById('productList');
          var opt = document.createElement("option");
          opt.value = '';
          opt.innerHTML = '';
          select.appendChild(opt);
          alert('找不到符合条件的商品！');
        }
      }
    });
  });
  $('.tpsetting').click(function(){
    
    var name = $('#productList').find("option:selected").text();
    var pid = $('#productList').val();
    if (!name) {
      alert('请先选择商品');
    } else {
      var str;
      str = '商品名称：' + name + '<br> 商品数量：<input name="nums[' + pid + ']" value="1"/>';
      $(this).closest('tr').find('div').html(str);
      $(this).closest('tr').find('.input_pids').val(pid);
    }
  });
  $('.tpreset').click(function(){
    $(this).closest('tr').find('div').html('');
    $(this).closest('tr').find('input').val('');
  });
})();
  