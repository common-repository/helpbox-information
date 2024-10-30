jQuery(function($){
	$(document).ready(function(){
		$('.hb_question:first-child p').addClass('active').css('display', 'block');
		$('.hb_question').click(function () {
		    $(this).find('p.answer').slideToggle('200').addClass('active');
		    $(this).siblings().find('.active').each(function () {
		        $(this).slideUp('300').removeClass('active');
		    });
		}); 	
	});
});