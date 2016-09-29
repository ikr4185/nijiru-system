$(document).ready(function(){
	marked.setOptions({
		langPrefix: '',
		highlight: function(code, lang) {
			return code;
		}
	});
	$('#content').html(marked($('#content_text').text()));
});