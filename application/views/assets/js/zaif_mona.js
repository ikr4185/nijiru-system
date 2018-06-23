$(function () {

	// {"last": 1225.0, "high": 1280.0, "low": 895.0, "vwap": 1067.9557, "volume": 1669473.0, "bid": 1225.0, "ask": 1241.0}

	var chartdata = {}, counter = 0, isInit = true, isConnecting = false;
	var last = 0, high = 0, low = 0, volume = 0, bid = 0, ask = 0;
	var askArray = [], askTitle = 0;

	$("#status").html("Loading.");

	var getMona = function () {

		counter = counter -1;

		$("#status").html("waiting...");
		console.log("count up");

		if (!isConnecting){

			isConnecting = !isConnecting;

			$.ajax('http://njr-sys.net/api/mona', {
					type: 'get',
					dataType: 'json'
				})
				.done(function (data) {

					console.log("API done");

					// データの変数格納
					last = data.last;
					high = data.high;
					low = data.low;
					volume = data.volume;
					bid = data.bid;
					ask = data.ask;

					// 初期化処理
					if (isInit){

						$("#status").html("System Init.");

						// bidArray = ["買値",bid,bid,bid,bid,bid,bid,bid,bid,bid,bid];
						askArray = ["売値",ask,ask,ask,ask,ask,ask,ask,ask,ask,ask];
						isInit = false;
					}

					// カウンタ上限経過で更新
					if (counter < 1){

						// チャートデータセット
						chartdata = {
							"config": {
								"title": "MONA/JPY",
								"subTitle": "",
								"type": "line",
								"lineWidth": 2,
								"colorSet":
									["red"],
								"bgGradient": {
									"direction":"vertical",
									"from":"#555",
									"to":"#222"
								},
								"useMarker": "css-ring",
								"markerWidth": 6,
							},
							"data": [
								["count","-10","-09","-08","-07","-06","-05","-04","-03","-02","-01",""],
								// bidArray,
								askArray
							]
						};

						// グラフ更新
						// bidArray.push(bid);
						// bidArray.splice(1, 1);
						askArray.push(ask);
						askArray.splice(1, 1);
						ccchart.init('chart', chartdata);

						console.log("Chart Update.");

						// カウンタリセット
						counter = 30;
					}

					// 表示の更新
					$("#last").html(last + " 円");
					$("#high").html(high + " 円");
					$("#low").html(low + " 円");
					$("#volume").html(volume + " MONA");
					$("#bid").html(bid + " 円");
					$("#ask").html(ask + " 円");

					askTitle = ask.toString();
					askTitle = askTitle.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');

					$("#ask-title").html(askTitle);
					$("#counter").html(counter);

					$("#status").html("<span style='color: #3f3'>API Success.</span>");
					isConnecting = false;
				})
				.fail(function () {
					console.log("API Fail");
					$("#status").html("<span style='color: #f33'>[!] API Fail.</span>");
					isConnecting = false;
				});
		}
	};

	getMona();
		
	setInterval(function(){
		getMona();
	},2000);

});

