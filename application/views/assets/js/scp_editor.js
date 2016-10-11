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
		$("#debug").append(NNSH_escapeHTML(str));
	}
	
	function wikidotParser(str){

		// ==========================================================================================
		// BASIC wikidot

		// block quote
		str = str.replace(/> ((.|>)*?)$/gim,'njr-blockquote-start$1njr-blockquote-end');
		str = str.replace(/(njr-blockquote-end)\n(njr-blockquote-start)/gim,'<br>');
		str = str.replace(/(njr-blockquote-start)(.*?)(njr-blockquote-end)/gi,'<blockquote><p>$2</p></blockquote>');

		// bold
		str = str.replace(/\*\*(.*?)\*\*/gi,'<b>$1</b>');

		// custom code
		str = str.replace(/##((.*?)\|(.*?))?##/gi,'<span style="color: \#\$2">$3</span>');

		// ul list
		str = str.replace(/ \* ((.|\*)*?)$/gim,'njr-ul-list-child-start$1njr-ul-list-child-end');
		str = str.replace(/\* ((.|\*)*?)$/gim,'njr-ul-list-start$1njr-ul-list-end');

		str = str.replace(/(njr-ul-list-child-end)\n(njr-ul-list-child-start)/gi,'</li><li>');
		str = str.replace(/(njr-ul-list-end)\n(njr-ul-list-child-start)/gim,'njr-ul-list-child-start');
		str = str.replace(/(njr-ul-list-child-end)\n(njr-ul-list-start)/gim,'njr-ul-list-child-end<li>');
		str = str.replace(/(njr-ul-list-end)\n(njr-ul-list-start)/gim,'</li><li>');

		str = str.replace(/(njr-ul-list-child-start)(.*?)(njr-ul-list-child-end)/gi,'<ul><li>$2</li></ul>');
		str = str.replace(/(njr-ul-list-start)(.*?)(njr-ul-list-end)/gi,'<ul><li>$2</li></ul>');
		str = str.replace(/(njr-ul-list-start)(.*?<\/li><\/ul>)/gi,'<ul><li>$2</li></ul>');

		// ol list
		str = str.replace(/ # ((.|\*)*?)$/gim,'njr-ol-list-child-start$1njr-ol-list-child-end');
		str = str.replace(/# ((.|\*)*?)$/gim,'njr-ol-list-start$1njr-ol-list-end');

		str = str.replace(/(njr-ol-list-child-end)\n(njr-ol-list-child-start)/gi,'</li><li>');
		str = str.replace(/(njr-ol-list-end)\n(njr-ol-list-child-start)/gim,'njr-ol-list-child-start');
		str = str.replace(/(njr-ol-list-child-end)\n(njr-ol-list-start)/gim,'njr-ol-list-child-end<li>');
		str = str.replace(/(njr-ol-list-end)\n(njr-ol-list-start)/gim,'</li><li>');

		str = str.replace(/(njr-ol-list-child-start)(.*?)(njr-ol-list-child-end)/gi,'<ol><li>$2</li></ol>');
		str = str.replace(/(njr-ol-list-start)(.*?)(njr-ol-list-end)/gi,'<ol><li>$2</li></ol>');
		str = str.replace(/(njr-ol-list-start)(.*?<\/li><\/ol>)/gi,'<ol><li>$2</li></ol>');

		// italic
		str = str.replace(/\/\/(.*?)\/\//gi,'<em>$1</em>');

		// underline
		str = str.replace(/__(.*?)__/gi,'<span style="text-decoration: underline;">$1</span>');

		// hr
		str = str.replace(/\n(------)\n/gi,'\n<hr>\n');

		// line-through
		str = str.replace(/--(.*?)--/gi,'<span style="text-decoration: line-through;">$1</span>');

		// teletype
		str = str.replace(/{{(.*?)}}/gi,'<tt>$1</tt>');

		// superscript, subscript
		str = str.replace(/\^\^(.*?)\^\^/gi,'<sup>$1</sup>');
		str = str.replace(/,,(.*?),,/gi,'<sub>$1</sub>');

		// escape
		str = str.replace(/@@(.*?)@@/gi,'<span style="white-space: pre-wrap;">$1</span>');

		// head h1~h5
		str = str.replace(/\+\+\+\+\+ (.*?)\n/gi,'<h5><span>$1</span></h5>\n');
		str = str.replace(/\+\+\+\+ (.*?)\n/gi,'<h4><span>$1</span></h4>\n');
		str = str.replace(/\+\+\+ (.*?)\n/gi,'<h3><span>$1</span></h3>\n');
		str = str.replace(/\+\+ (.*?)\n/gi,'<h2><span>$1</span></h2>\n');
		str = str.replace(/\+ (.*?)\n/gi,'<h1><span>$1</span></h1>\n');

		// ----------------------------------------
		// [[****]]

		// div
		str = str.replace(/\[\[DIV(.*?)]]((.|\n)*?)\[\[\/DIV]]/gim,'<div$1>\n\n$2\n\n</div>');

		// span
		str = str.replace(/\[\[SPAN(.*?)]]((.|\n)*?)\[\[\/SPAN]]/gim,'<span$1>$2</span>');

		// text-align
		str = str.replace(/\[\[>]]((.|\n)*?)\[\[\/>]]/gim,'<div style="text-align: right;">$1</div>');
		str = str.replace(/\[\[=]]((.|\n)*?)\[\[\/=]]/gim,'<div style="text-align: center;">$1</div>');
		str = str.replace(/\[\[<]]((.|\n)*?)\[\[\/<]]/gim,'<div style="text-align: left;">$1</div>');

		// module
		str = str = str.replace(/\[\[module Rate]]/gim,'\n\n<div class="page-rate-widget-box"><span class="rate-points">評価:&nbsp;<span class="number prw54353">+5</span></span><span class="rateup btn btn-default"><a title="+投票をする">+</a></span><span class="ratedown btn btn-default"><a title="-投票をする">–</a></span><span class="cancel btn btn-default"><a title="投票を取り消し">x</a></span></div>\n\n');
		str = str = str.replace(/\[\[module(.*?)]]((.|\n)*?)(\[\[\/module]])*?/gim,'\n\nMODULE $1 $2 /MODULE\n\n');
		str = str = str.replace(/\[\[module(.*?)]]((.|\n)*?)(\[\[\/module]])*?/gim,'\n\nMODULE $1 $2 /MODULE\n\n');

		// link
		str = str.replace(/\[\[\[\*(.*?)]]]/gim,'<a href="http://ja.scp-wiki.net/wiki-syntax" target="_blank">$1</a>');
		str = str.replace(/\[\*(.*?)( )*?\|( )*?(.*?)]/gim,'<a href="$1" target="_blank">$4</a>');
		str = str.replace(/\[\*(.*?) (.*?)]/gim,'<a href="$1" target="_blank">$2</a>');

		str = str.replace(/\[\[\[(.*?)]]]/gim,'<a href="http://ja.scp-wiki.net/wiki-syntax">$1</a>');
		str = str.replace(/\[(.*?)( )*?\|( )*?(.*?)]/gim,'<a href="$1">$4</a>');
		str = str.replace(/\[(.*?) (.*?)]/gim,'<a href="$1">$2</a>');

		// ==========================================================================================
		// p/br

		// p
		str = str.replace(/\n*((.|\n|\/)+?)(\n\n|$)/g, "\n\n<p>$1</p>\n\n");

		// br
		str = str.replace(/\n+/g, '\n');
		str = str.replace(/\n/g, '<br>');

		// ==========================================================================================
		// 整理

		// del first br
		str = str.replace(/^(<br>)/g, '');

		// del p+br
		str = str.replace(/(<\/p><br>)/g, '</p>');

		// del div/span p
		str = str.replace(/<p><(\/?(div)|(span))(.*?)><\/p>/g, '<$1$4>');

		// del block quote/ul/ol/h1-5 p
		str = str.replace(/<p><(blockquote|ul|ol|h(\d))>/g, '<$1>');
		str = str.replace(/<\/(blockquote|ul|ol|h(\d))><\/p>/g, '</$1>');

		// del h1-h5 br
		str = str.replace(/<\/h(\d)><br>/g, '</h$1>');



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

	function NNSH_escapeHTML(str) {
		str =  str.replace(/[&"<>,']/g, function (c) {
			return {
				'&': '&amp;',
				'"': '&quot;',
				'<': '&lt;',
				'>': '&gt;',
				',': '&#044;',
				'\'': '&#039;'
			}[c];
		});
		str = str.replace(/\n/g, '<br>');
		str = str.replace(/&lt;br&gt;/g, '&lt;br&gt;<br>');
		str = str.replace(/&lt;(\/)?p&gt;/g, '&lt;$1p&gt;<br>');
		return str;
	}


});