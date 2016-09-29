$(window).load(function(){

	// get_DOM
	var scp_lists = document.querySelectorAll('.scp-reader_li');

	var searchUsersList = function(event){

		var
			i,
			id,
			contents_text,
			input_text = $('#search').val(),
			regex;

		for (i = 0; i < scp_lists.length; i++){

			// id_information
			id = scp_lists[i].id;

			// DOM_contents
			contents_text = scp_lists[i].innerText;
			// Firefox fixed
			if (null == contents_text) {
				contents_text = scp_lists[i].textContent;
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
	$('#search').on('input', searchUsersList);

});