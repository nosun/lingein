$(function(){
	if($('.extend').text().trim().length > 0){
		$('.cat-summary').append('<a class="cat-more">More </a>');
		$('.extend').append('<a class="cat-less">Less</a>');
		$('.extend').hide();
		$('a.cat-more').click(function(){
			$('.extend').fadeIn();
			$(this).hide();
		});
		$('a.cat-less').click(function(){
			$('.extend').fadeOut();
			$('a.cat-more').show();
		});
	}
});