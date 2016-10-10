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

		// block quote
		str = str.replace(/> ((.|>)*?)$/gim,'njr-blockquote-start$1njr-blockquote-end');
		str = str.replace(/(njr-blockquote-end)\n(njr-blockquote-start)/gim,'<br>');
		str = str.replace(/(njr-blockquote-start)(.*?)(njr-blockquote-end)/gi,'<blockquote><p>$2</p></blockquote>');

		// bold
		str = str.replace(/\*\*(.*?)\*\*/gi,'<b>$1</b>');

		// italic
		str = str.replace(/\/\/(.*?)\/\//gi,'<em>$1</em>');

		// underline
		str = str.replace(/__(.*?)__/gi,'<span style="text-decoration: underline;">$1</span>');

		// line-through
		str = str.replace(/--(.*?)--/gi,'<span style="text-decoration: line-through;">$1</span>');

		// head h1~h5
		str = str.replace(/\+\+\+\+\+ (.*?)\n/gi,'<h5>$1</h5>\n');
		str = str.replace(/\+\+\+\+ (.*?)\n/gi,'<h4>$1</h4>\n');
		str = str.replace(/\+\+\+ (.*?)\n/gi,'<h3>$1</h3>\n');
		str = str.replace(/\+\+ (.*?)\n/gi,'<h2>$1</h2>\n');
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