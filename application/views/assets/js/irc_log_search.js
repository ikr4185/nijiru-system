$(window).load(function(){

	// get_DOM
	var
		lists = document.querySelectorAll('.js_irc_log'),
		search_input = $('#search');

	var searchUsersList = function(event){

		var
			i,
			id,
			contents_text,
			input_text = search_input.val(),
			regex;

		for (i = 0; i < lists.length; i++){

			// id_information
			id = lists[i].id;

			// DOM_contents
			contents_text = lists[i].innerText;
			// Firefox fixed
			if (null == contents_text) {
				contents_text = lists[i].textContent;
			}

			// search_regex
			regex = new RegExp(input_text, "i");

			// console.log(regex);

			// search_run
			if ( -1 == contents_text.search(regex)){
				$('#' + id).addClass('hidden');
			} else {
				$('#' + id).removeClass('hidden');
			}
		}
	};
	search_input.on('focusout', searchUsersList);
	search_input.keypress(function(e) {
		if (e.which == 13) {
			searchUsersList();
		}
	})

});