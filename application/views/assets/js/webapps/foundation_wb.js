$(function () {
	var offset = 5;
	var fromX;
	var fromY;
	var drawFlag = false;
	var context = $("canvas").get(0).getContext('2d');

	// 初期ペンサイズ
	context.lineWidth = 1;
//		var socket = io.connect('http://localhost');

//		// サーバからメッセージ受信
//		socket.on('send user', function (msg) {
//			context.strokeStyle = msg.color;
//			context.lineWidth = 2;
//			context.beginPath();
//			context.moveTo(msg.fx, msg.fy);
//			context.lineTo(msg.tx, msg.ty);
//			context.stroke();
//			context.closePath();
//		});
//
//		socket.on('clear user', function () {
//			context.clearRect(0, 0, $('canvas').width(), $('canvas').height());
//		});

	$('#load').click(function (e) {
		e.preventDefault();

		var img = new Image();
		var timestamp = new Date().getTime();

		img.src = "http:///njr-sys.net/node_application/foundation_wb/test.png?" + timestamp;
		img.onload = function () {
			context.clearRect(0, 0, $('canvas').width(), $('canvas').height());
			context.drawImage(img, 0, 0);
		};
	});

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
		context.strokeStyle = $(this).css('background-color');
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

	function draw(e) {
		var toX = e.pageX - $('canvas').offset().left - offset;
		var toY = e.pageY - $('canvas').offset().top - offset;

		context.beginPath();
		context.moveTo(fromX, fromY);
		context.lineTo(toX, toY);
		context.stroke();
		context.closePath();

		// ３．サーバへのデータ送信方法
//			// サーバへメッセージ送信
//			socket.emit('server send', { fx:fromX, fy:fromY, tx:toX, ty:toY, color:context.strokeStyle });
		fromX = toX;
		fromY = toY;
	}

	$('#save').click(function () {
		var d = $("canvas")[0].toDataURL("image/png");

		var data = {
			id: '1',
			data: d
		};
		$.ajax({
			type: 'POST',
			url: 'http://njr-sys.net/api/saveWhiteBoard',
			data: data,
//				scriptCharset: 'utf-8',
			success: function (data, dataType) {
				alert("saved - njr-sys.net");
				console.log(data);
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				alert("error - njr-sys.net");
				console.log('通信失敗 ' + errorThrown);
			},
			complete: function () {
				console.log('処理終了');
			}
		});
		return false;
//
//			//スクリプトタグ生成
//			var sc = document.createElement("script");
//			sc.type = 'text/javascript';
//			//アクセス先を指定
//			sc.src = "//example.com/api/getData.php?key=xxx&callback=callbackFunc";
//			//生成したスクリプトタグを追加、実行
//			var parent = document.getElementsByTagName("script")[0];
//			parent.parentNode.insertBefore(sc,parent);
	});
});