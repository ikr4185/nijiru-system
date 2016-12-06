$(function () {

	// ----------------------------------------
	// 描画処理
	// ----------------------------------------

	var offset = 5;

	var fromX;
	var fromY;
	var drawFlag = false;
	var canvas = $("canvas");
	var context = canvas.get(0).getContext('2d');

	var token = $("#fwb-token").val();

	// 初期ペン設定
	var lineWidth = 1;
	var lineColor = "#000";
	var lineAlpha = 1.0;

	// 線の設定(固定)
	context.lineJoin = "round";
	context.lineCap = "round";

	// パスを開始する
	canvas.mousedown(function (e) {

		drawFlag = true;
		fromX = e.pageX - $(this).offset().left - offset;
		fromY = e.pageY - $(this).offset().top - offset;

		return false;  // for chrome
	});

	// パスを追加する
	canvas.mousemove(function (e) {
		if (drawFlag) {
			draw(e);
		}
		return false;  // for chrome
	});

	// パスを閉じる
	canvas.on('mouseup', function () {
		drawFlag = false;
		context.closePath();
		return false;  // for chrome
	});
	canvas.on('mouseleave', function () {
		drawFlag = false;
		context.closePath();
		return false;  // for chrome
	});

	$('.controllers__pen-color').click(function (e) {
		e.preventDefault();

		// 色設定の変更
		lineColor= $(this).attr('data-pen-color');

		// 選択されたツール以外のボーダーをリセット
		$('.controllers__pen-color').css("border", "1px solid #687b8f");
		// 選択されたツールのボーダーを緑にする
		$(this).css("border", "1px solid #30ff30");
	});

	$('.controllers__pen-size').click(function (e) {
		e.preventDefault();

		// ペンサイズの変更
		lineWidth = $(this).attr('data-pen-size');

		// 選択されたツール以外のボーダーをリセット
		$('.controllers__pen-size').css("border", "1px solid #687b8f");
		$('.controllers__pen-size').css("background", "#ddd");
		// 選択されたツールのボーダーを緑にする
		$(this).css("border", "1px solid #30ff30");
		$(this).css("background", "#90ff90")
	});
	
	$('.controllers__pen-alpha').click(function (e) {
		e.preventDefault();

		// 透明度の変更
		lineAlpha = $(this).attr('data-pen-alpha');

		// 選択されたツール以外のボーダーをリセット
		$('.controllers__pen-alpha').css("border", "1px solid #687b8f");
		$('.controllers__pen-alpha').css("background", "#ddd");
		// 選択されたツールのボーダーを緑にする
		$(this).css("border", "1px solid #30ff30");
		$(this).css("background", "#90ff90");
	});

	$('#clear').click(function (e) {
		e.preventDefault();
		context.clearRect(0, 0, canvas.width(), canvas.height());
	});

	/**
	 * 描画処理
	 * @param e
	 */
	function draw(e) {
		var toX = e.pageX - canvas.offset().left - offset;
		var toY = e.pageY - canvas.offset().top - offset;

		// 先の太さ
		// TODO パスを毎回閉じないと無理
		// var a = fromX - toX;
		// var b = fromY - toY;
		// var lineLength = Math.sqrt(Math.pow(a,2) + Math.pow(b,2));
		// context.lineWidth = lineWidth - Math.floor(lineLength/3) + 3;
		// console.log(context.lineWidth);

		// 描画処理: パスの開始
		context.beginPath();
		context.moveTo(fromX, fromY);

		// 線の設定(可変)
		context.lineWidth = lineWidth;
		context.strokeStyle = lineColor;
		context.globalAlpha = lineAlpha;

		// ストローク
		context.lineTo(toX, toY);
		context.stroke();

		fromX = toX;
		fromY = toY;
	}

	/**
	 * データ読み込み
	 * @returns {boolean}
	 */
	function load() {

		$.ajax({
			type: 'GET',
			url: 'http://njr-sys.net/api/loadWhiteBoard/' + token,
			dataType: 'json',
			success: function (data, dataType) {

				// debug ////////////////////////////////////////
				// console.log("load / token : " + token);

				// レコードが存在しない
				if (!data) {
					return false;
				}

				// 画像の表示
				var image = new Image();
				image.src = data[0].data;
				image.onload = function () {
					context.clearRect(0, 0, canvas.width(), canvas.height());
					context.globalAlpha = 1.0; // 読み込み時に薄くなるのを防止
					context.drawImage(image, 0, 0);
				};

			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				alert(errorThrown);
			},
			complete: function () {
			}
		});
		return false;
	}

	// 初回ロード時に読み込み実行
	load();

	/**
	 * 読み込みボタンによる読み込み
	 */
	$('#load').click(function (e) {
		e.preventDefault();
		load();
	});

	/**
	 * データ保存
	 */
	$('#save').click(function () {
		var d = $("canvas")[0].toDataURL("image/png");
		var pass = $("#fwb-pass").val();

		var data = {
			token: token,
			data: d,
			pass: pass
		};
		$.ajax({
			type: 'POST',
			url: 'http://njr-sys.net/api/saveWhiteBoard',
			data: data,
			dataType: 'json',
			success: function (data, dataType) {
				if (data === "ok") {
					alert("saved - njr-sys.net");
				} else {
					alert(data);
				}
				// console.log(data);
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				alert(errorThrown);
			},
			complete: function () {
			}
		});
		return false;
	});
});