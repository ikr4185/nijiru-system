$(function(){

	console.log("scp_editor.js loaded");
	addStr("preview");

	// 文字入力ファンクション
	function addStr(id){

		var obj = $("#"+id);
		var str = wikidotParser($("#textarea").text());

		obj.empty();
		obj.append(str);

		console.log(str);

		$("#debug").empty();
		$("#debug").append(str);
	}
	
	function wikidotParser(str){

		str = str.replace(/\*\*(.*?)\*\*/gi,'<b>$1</b>');
		str = str.replace(/\+ (.*?)\n/gi,'<h1>$1</h1>\n');

		str = str.replace(/\n*((.|\n|\/)+?)(\n\n|$)/g, "\n\n<p>$1</p>\n\n");
		
		str = str.replace(/\n+/g, '\n');
		// str = str.replace(/\n/g, '');

		return str;
	}


	$("#textarea")
	// フォーカス時の設定
		.focus(function(){
			window.document.onkeydown = function(e){

				// TABキーの動作
				if(e.keyCode === 9) {
					document.execCommand('insertHTML', false, '&#009');
					e.preventDefault(); // デフォルト動作停止
				}
				// Enterキーの動作
				if(e.keyCode === 13) {
					document.execCommand('insertHTML', false, '\n');
					e.preventDefault();
				}
			}
		})
		// フォーカスが外れた時の設定
		.blur(function(){
			// 通常の動作を行うように再設定
			window.document.onkeydown = function(e){
				return true;
			}
		})
		.on("input",function(){
			addStr("preview");
		});
});