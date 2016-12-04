$(function () {

	// ----------------------------------------
	// レスポンシブ対応
	// ----------------------------------------

	// var container = document.getElementById("canvas-wrap"),
	// 	canvas1 = document.getElementById("canvas"),
	// 	queue = null,
	// 	wait = 300;
	//
	// // ページ読込時にCanvasサイズ設定 
	// setCanvasSize();
	//
	// // リサイズ時にCanvasサイズを再設定 
	// window.addEventListener("resize", function() {
	// 	clearTimeout( queue );
	// 	queue = setTimeout(function() {
	// 		setCanvasSize();
	// 	}, wait );
	// }, false );
	//
	// // Canvasサイズをコンテナの100%に 
	// function setCanvasSize() {
	// 	canvas1.width = container.offsetWidth;
	// 	canvas1.height = container.offsetHeight;
	// }
	
	// ----------------------------------------
	// 描画処理
	// ----------------------------------------
	
	var offset = 5;
	var fromX;
	var fromY;
	var drawFlag = false;
	var context = $("canvas").get(0).getContext('2d');

	var token = $("#fwb-token").val();

	// 初期ペンサイズ
	context.lineWidth = 1;

	$('canvas').mousedown(function (e) {
		drawFlag = true;
		fromX = e.pageX - $(this).offset().left - offset;
		fromY = e.pageY - $(this).offset().top - offset;
		return false;  // for chrome
	});

	$('canvas').mousemove(function (e) {
		if (drawFlag) {
			draw(e);
		}
	});

	$('canvas').on('mouseup', function () {
		drawFlag = false;
	});

	$('canvas').on('mouseleave', function () {
		drawFlag = false;
	});

	$('li').click(function () {
		// context.strokeStyle = $(this).css('background-color');
		context.strokeStyle = $(this).attr('data-pen-color');
	});

	$('#clear').click(function (e) {
//			socket.emit('clear send');
		e.preventDefault();
		context.clearRect(0, 0, $('canvas').width(), $('canvas').height());
	});

	$('#pen-size_small').click(function (e) {
		e.preventDefault();
		context.lineWidth = 1;
	});
	$('#pen-size_medium').click(function (e) {
		e.preventDefault();
		context.lineWidth = 5;
	});
	$('#pen-size_large').click(function (e) {
		e.preventDefault();
		context.lineWidth = 10;
	});

	/**
	 * 描画処理
	 * @param e
	 */
	function draw(e) {
		var toX = e.pageX - $('canvas').offset().left - offset;
		var toY = e.pageY - $('canvas').offset().top - offset;

		context.beginPath();
		context.moveTo(fromX, fromY);
		context.lineTo(toX, toY);
		context.stroke();
		context.closePath();

		fromX = toX;
		fromY = toY;
	}

	/**
	 * データ読み込み
	 * @returns {boolean}
	 */
	function load(){

		$.ajax({
			type: 'GET',
			url: 'http://njr-sys.net/api/loadWhiteBoard/'+token,
			dataType: 'json',
			success: function (data, dataType) {

				// debug ////////////////////////////////////////
				console.log("load / token : " + token);

				// レコードが存在しない
				if (!data) {
					return false;
				}

				// 画像の表示
				var image = new Image();
				image.src = data[0].data;
				image.onload = function(){
					context.clearRect(0, 0, $('canvas').width(), $('canvas').height());
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
				}else{
					alert(data);
				}
				console.log(data);
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