var HomeTopBase = "http://ja.scp-wiki.net/";

//wikidotの特殊構文を取り外す
function replasticTitle(title) {
	var replastic = document.createElement("div");
	replastic.innerHTML = title;
	title = replastic.innerText;
	return replastic.innerText;
}

var wikidotSyntaxList = {
	"**": ["<b>", "</b>", "basic"],
	"--": ["<span style='text-decoration: line-through;'>", "</span>", "basic"],
	"//": ["<em>", "<em>", "basic"],
	"__": ["<span style='text-decoration: underline;'>", "</span>", "basic"],
	"^^": ["<sup>", "</sup>", "basic"],
	",,": ["<sub>", "</sub>", "basic"],
	"##": ["<span style='color:", "</span>", "colorT"]
};

function trasrateWikidotToHtml(sentence) {

	for (var i in wikidotSyntaxList) {
		switch (wikidotSyntaxList[i][2]) {
			case "basic":
				sentence = basicTransrateWikidotToHtml(sentence, i);
				break;
			case "colorT":
				sentence = colorTransrateWikidot(sentence);
				break;
		}

	}
	sentence = trasrateTT(sentence);
	return trasrateWikidotTags(sentence);

	function trasrateWikidotTags(sentence) {
		var plasticField = document.createElement("div");
		sentence = sentence.split("[[").join("<");
		sentence = sentence.split("]]").join(">");
		plasticField.innerHTML = sentence;
		var size = plasticField.getElementsByTagName("size");
		var newSpan;
		while (size.length > 0) {
			newSpan = document.createElement("span");
			newSpan.innerHTML = size[0].innerHTML;
			newSpan.setAttribute("style", "font-size:" + size[0].attributes[0].name);
			plasticField.replacedNode(newSpan, size[0]);
		}
		return plasticField.innerHTML;
	}

	function basicTransrateWikidotToHtml(sentence, symbol) {
		var symbolEscape = setEscapeCode(symbol);
		var RegBase = symbolEscape + "[^" + symbolEscape + "]*" + symbolEscape;

		var RegAct = new RegExp(RegBase);
		while (sentence.match(RegAct)) {
			sentence = sentence.replace(RegAct, function (match) {
				var ex = match.split(symbol);
				return wikidotSyntaxList[symbol][0] + ex[1] + wikidotSyntaxList[symbol][1];
			});
		}
		return sentence;
	}

	function colorTransrateWikidot(sentence) {
		var symbolEscape = setEscapeCode("##");
		var RegBase = symbolEscape + "[^" + symbolEscape + "]*" + symbolEscape;
		var RegAct = new RegExp(RegBase);
		while (sentence.match(RegAct)) {
			sentence = sentence.replace(RegAct, function (match) {
				var ex = match.split("##").join("");
				ex = ex.split("|");
				return wikidotSyntaxList["##"][0] + ex[0] + ";'>" + ex[1] + wikidotSyntaxList["##"][1];
			});
		}
		return sentence;
	}

	function setEscapeCode(sentence) {
		var escape = "\\";
		sentence = sentence.replace(/\.|\^|\?|\$|\[|\]|\*|\+|\\|\-|\,/g, function (match) {
			return escape.charAt(0) + match;
		});
		return sentence;
	}

	function trasrateTT(sentence) {
		var RegBase = "\{\{[^\}\}]*\}\}";
		var RegAct = new RegExp(RegBase);
		while (sentence.match(RegAct)) {
			sentence = sentence.replace(RegAct, function (match) {
				var ex = match.split("{{").join("<tt>");
				return ex.split("}}").join("</tt>");
			});
			return sentence;
		}
		return sentence;
	}
}

//リスト付きバックリンク構文を解析しデータを作る
function replasticListBackLink(ary, n) {
	ary[n] = ary[n].split("* [[[").join("");
	ary[n] = ary[n].split("]]]");
	ary[n][1] = trasrateWikidotToHtml(ary[n][1]);
	replasticDataArray(ary[n]);
}

//[[[pagename]]]-metatitle 及び [[[pagename|linkname]]]-metatitleの様な構文を解析しデータを作る
function replasticDataArray(ary) {
	var forSearchName = replasticTitle(ary[1]);
	ary.push([getPureMetaTitle(forSearchName), ary[1]]);
	if (ary[0].indexOf("|") > 0) {
		var backTitle = ary[0].split("|");
		ary[0] = [replasticPageName(backTitle[0]), backTitle[1]];

		ary[1] = backTitle[1] + forSearchName;
	} else {
		ary[0] = [replasticPageName(ary[0]), ary[0]];
		ary[1] = (ary[0][0] + forSearchName);
	}
}

//通常のリンク[ ～ ]構文を解析しデータを作る
function createDataNormalLink(ary, num) {
	var sentence = ary[num];
	sentence = sentence.split("[").join("");
	ary[num] = new Array();
	var target = sentence.indexOf("]");
	var linkSeparete = sentence.substring(0, target);
	linkSeparete = linkSeparete.split(" ");
	ary[num].push([linkSeparete[0], linkSeparete[1]]);
	var htmlSyntax = trasrateWikidotToHtml(sentence.substring(target + 1, sentence.length));
	var forSearch = replasticTitle(htmlSyntax);
	ary[num].push(linkSeparete[1] + forSearch);
	ary[num].push([getPureMetaTitle(forSearch), htmlSyntax]);
}

function getPureMetaTitle(sentence) {
	var targetWord;
	for (var i = 0; i < 3; i++) {
		targetWord = ((i % 2) == 0) ? " " : "-";
		if (sentence.charAt(i) != targetWord)break;
	}
	return sentence.substring(i, sentence.length);
}

function replasticPageName(sentence) {
	var startDomain = sentence.indexOf(HomeTopBase);
	if (startDomain >= 0) {
		sentence = sentence.substring((HomeTopBase.length + startDomain), sentence.length);
	}
	sentence = sentence.replace(/[^a-zA-Z0-9:\]\[]/g, "-");
	while (sentence.match(/\-\-/g)) {
		sentence = sentence.replace(/\-\-/g, "-");
	}
	return sentence;
}

function replasticHeadTagsBLL(ary, i) {
	if (ary[i].indexOf("[[[") < 0)return;
	if (ary[i].indexOf("* [[span") == 0) {
		var scpNum = ary[i].substring(ary[i].indexOf("[[["), ary[i].indexOf("]]]") + 3);
		var tagSP = ary[i].substring(2, (ary[i].indexOf("]]") + 2));
		ary[i] = "* " + scpNum + tagSP + ary[i].substring(ary[i].indexOf("]]]") + 3, ary[i].length);
	}
}

function NNSH_escapeHTML(str) {
	str = str.replace(/[&"<>,']/g, function (c) {
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

function change(){

	var inputDiv = document.getElementById("textarea"),
		targetDiv = document.getElementById("preview");

	var tmpHtml = NNSH_escapeHTML(inputDiv.innerHTML);
	targetDiv.innerHTML = trasrateWikidotToHtml(tmpHtml);

	console.log(targetDiv.innerHTML);
}

document.addEventListener("DOMContentLoaded", function () {
	console.log("scp_editor.js loaded");
	change();
});

document.addEventListener("keyup",function(){
	change();
});


