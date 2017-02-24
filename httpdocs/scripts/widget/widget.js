(function() {
  $('#initial').click(function(){
    $('#productcompare').removeClass('productcompareInitial').addClass('productcompareNomarl');
    $('#initial').css('display', 'none');
    $('#normal').css('display', 'block');
  });
  $('#normal').find('.title').click(function(){
    $('#productcompare').removeClass('productcompareNomarl').addClass('productcompareInitial'); 
    $('#normal').css('display', 'none');
    $('#initial').css('display', 'block');
  });
  $('.comparedel').live('click',function(){
	 $(this).closest('li').remove();
	 var pid = $(this).attr('rel');
	 var rand = Math.random();
	 var ajaxUrl = 'widget/productcompare/delsession/' + pid + '/' + rand ;
	 $.ajax( {
	    type : "GET",
	    url : url(ajaxUrl + pid),
	    contentType : "application/txt; charset=utf-8",
	    dataType : "txt",
	    success : function(msg) {
	    }
	  });
  });
  $('#clearcompare').click(function(){
	  $('#compareul').html('');
	 var ajaxUrl = 'widget/productcompare/clearsession/';
	 $.ajax( {
	    type : "GET",
	    url : url(ajaxUrl),
	    contentType : "application/txt; charset=utf-8",
	    dataType : "txt",
	    success : function(msg) {
	     
	    }
	  });
  });
  $('.comparePruductButton').click(function(){
	  var pid = $(this).attr('id');
	  var name = $(this).attr('title');
	  $('#productcompare').removeClass('productcompareInitial').addClass('productcompareNomarl');
      $('#initial').css('display', 'none');
      $('#normal').css('display', 'block');
      var rand = Math.random();
      var ajaxUrl = 'widget/productcompare/addtosession/' + pid + '/' + name + '/' + rand ;
      $.ajax( {
  	    type : "GET",
  	    url : url(ajaxUrl),
  	    contentType : "application/txt; charset=utf-8",
  	    dataType : "txt",
  	    success : function(msg) {
    	  ret = eval('(' + msg + ')');
  	      if (ret['status'] != '1') {
  	        alert(ret['msg']);
  	      } else {
  	    	str = $('#compareul').html();
  	    	if (str == '<li>Please choose goods</li>') {
  	    	  str = '';
  	    	}
  	    	newstr = '<li id="li_' + pid + '" class="listproduct"><span class="spantxt">' + name + '</span><span class="spanimg"><img class="comparedel" rel="' + pid + '" src="/images/widget/delete.gif"></span></li>';
  	    	$('#compareul').html(str + newstr);
  	      }
  	    }
  	  });
  });
  $('#startcompare').click(function(){
	  if($('#compareul >li').size()>1){
		  window.open('/widget/productcompare/comparestart'); 
	  }
  });
})();