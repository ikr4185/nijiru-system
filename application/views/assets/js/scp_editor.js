$(function(){

	console.log("scp_editor.js loaded");

	// 文字入力ファンクション
	function addStr(id){
		var obj = $("#"+id); // オブジェクト取得
		obj.empty();
		obj.append($("#textarea").text());
	}

	// TABキーの動作
	$("#textarea")
	// フォーカス時の設定
		.focus(function(){
			window.document.onkeydown = function(e){
				if(e.keyCode === 9) {   // 9 = Tab

					console.log("tab key down");
					document.execCommand('insertHTML', false, '&#009');
					e.preventDefault(); // デフォルト動作停止
					// addStr(this.activeElement.id, "\t");　// \t = タブ
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