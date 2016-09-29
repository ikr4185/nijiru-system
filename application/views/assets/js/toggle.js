$(function(){

	/*

	汎用Wikidot折りたたみ構文再現スクリプト
	作: 育良啓一郎 @ikr_4185

	 */

	$('.collapsible-block .collapsible-block-content').hide();

	$(".collapsible-block .collapsible-block-link").on("click", function() {

		var
			folded,
			unfolded,
			content,
			parent,
			parentClass;

		/*
		 collapsible-block
		 	collapsible-block-folded
		 		collapsible-block-link　開く
		 	collapsible-block-unfolded
		 		collapsible-block-unfolded-link
		 			collapsible-block-link　閉じる
		 		collapsible-block-content　中身
		 */

		// 開く/閉じるの判別
		parent = $(this).parent();
		parentClass = parent[0].className;

		// + 開く
		if(parentClass == "collapsible-block-folded"){
			console.log("open");
			folded = parent;
			unfolded = folded.next();
			// unfolded = $(this).closest(".collapsible-block-unfolded");
			content = unfolded.children('.collapsible-block-content');
			// console.log(unfolded);
		}

		// - 閉じる
		if(parentClass == "collapsible-block-unfolded-link"){
			console.log("close");
			unfolded = parent.parent();
			folded = unfolded.prev();
			content = unfolded.children('.collapsible-block-content');
		}

		// .collapsible-block-folded
		folded.toggle();

		// .collapsible-block-unfolded
		unfolded.toggle();

		// .collapsible-block-content
		content.fadeToggle(300);

	});

});