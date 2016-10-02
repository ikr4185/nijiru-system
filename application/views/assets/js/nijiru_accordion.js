$(function(){
	$(".toggle-switch").on("click", function() {
		$(this).next().slideToggle();
	});
});