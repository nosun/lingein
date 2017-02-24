$(function(){
	if($('.extend').text().trim().length > 0){
		$('.cat-summary').append('<a class="cat-more">&nbsp;&nbsp; Read More >> </a>');
		$('.extend').append('<a class="cat-less">&nbsp;&nbsp; << Read Less </a>');
		$('.extend').hide();
		$('a.cat-more').click(function(){
			$('.extend').show();
			$(this).hide();
		});
		$('a.cat-less').click(function(){
			$('.extend').hide();
			$('a.cat-more').show();
		});
	}
});