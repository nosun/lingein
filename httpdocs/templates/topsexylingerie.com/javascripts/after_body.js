
$(function(){
	$('#userActionPanel #UAP-help dl').mouseover(function(){
        if($(this).hasClass('over')){return false;}
        else{$(this).addClass('over');}});
	$('#userActionPanel #UAP-help dl').mouseout(function(){
        if($(this).hasClass('over')){$(this).removeClass('over');}});
	$('#userActionPanel #UAP-livechat dl dt').mouseover(function(){
        if($(this).hasClass('over')){return false;}
        else{$(this).addClass('over');}});
	$('#userActionPanel #UAP-livechat dl dt').mouseout(function(){
        if($(this).hasClass('over')){$(this).removeClass('over');}});
	$('#cur_selector').mouseover(function(){
        if($(this).hasClass('over')){return false;}
        else{$(this).addClass('over');}});
	$('#cur_selector').mouseout(function(){
        if($(this).hasClass('over')){$(this).removeClass('over');}
	});
	$('.navigation #litb-nav dl').mouseover(function(){
        if($(this).hasClass('current')){return false;}
        else if($('.navItem', this).text() == "Others"){return false;}
        else{
			$(this).addClass('current');
			$(this).find('dd.sub-nav-container').each(function(){
				var as = $(this).find('div div p a');
				var maxWidth = 0;
				for(var i = 0; i < as.length; i++){
					var fontSize = parseInt($(as[i]).css("font-size"))
					var realLength = fontSize * $(as[i]).text().length
					if(maxWidth < realLength)
						maxWidth = realLength;
				}
				$(this).css({width: maxWidth + "px"});
				var clientWidth = $(this).find('div div p')[0].clientWidth;
				if(clientWidth == 0){
					$(this).css({width: ''});
				}else{
					$(this).css({width: clientWidth + 'px'});
				}});
        }
	});
	$('.navigation #litb-nav dl').mouseout(function(){
        if($(this).hasClass('current')){$(this).removeClass('current');}
	});
    $('#prodcutTab >ul.tab > li').mouseover(function(){
      if($(this).hasClass('clik')){return false;}
      else{
        $('#prodcutTab >ul.tab > li').removeClass('clik');
        $(this).addClass('clik');
        var thisNode = $('#prodcutTab >ul.tab > li').index(this);
        $('#prodcutTab > ul.product_hot').css('display','none');
        $('#prodcutTab > ul.product_hot').eq(thisNode).css('display','block');
      }});
    $('.product_hot >ul.tab >li').mouseover(function(){
          if($(this).hasClass('clik')){return false;}
          else{
            $('.product_hot >ul.tab > li').removeClass('clik');
            $(this).addClass('clik');
            $('.product_hot > a.more').attr('href',$(this).children('a').attr('href'));
            var thisNode = $('.tabcontent >ul.tab > li').index(this);
            $('.TabCategory_content').css('display','none');
            $('.TabCategory_content').eq(thisNode).css('display','block');
          }});
    /*
    $('#resultSortBar .showType select').change(function(){
    	$('#browse_filter input[name="pageRows"]').val($("#resultSortBar .showType select option:selected").val());
    	$('#browse_filter').submit();
    });*/
    $('.c_viewoptions .showType ul li a.select').click(changeViewOptions);
    $('.search_viewoptions .showType ul li a.select').click(changeSearchViewOptions);
});

function changeSearchViewOptions(ev){
	ev.preventDefault();

    $.ajax( {
        type : "POST",
        url : url('sphinx/ajaxshowproducts'),
        data : {'pageRows':$(this).text()},
        dataType : "html",
        success : function(htmlfrags) {
        	if(htmlfrags){
        		$('div#productlist').empty();
        		$('div#productlist').append(htmlfrags);
        		$('.search_viewoptions .showType ul li a.select').click(changeSearchViewOptions);
        	}
    	}
    });
}

function changeViewOptions(ev){
	ev.preventDefault();
    $.ajax( {
        type : "POST",
        url : url('product/ajaxshowproducts'),
        data : {'pageRows':$(this).text()},
        dataType : "html",
        success : function(htmlfrags) {
        	if(htmlfrags){
        		$('div#productlist').empty();
        		$('div#productlist').append(htmlfrags);
        		$('.c_viewoptions .showType ul li a.select').click(changeViewOptions);
        	}
    	}
    });
}
function submit_shopping_cart(name){
	$('#itemsForm input#cart_submit').attr('name', name);
	$('#itemsForm').submit();
}
function filter_option(name, condition){
	$('#browse_filter input[name="'+ name + '"]').val(condition);
	$('#browse_filter').submit();
}