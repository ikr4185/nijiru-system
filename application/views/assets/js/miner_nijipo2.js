/**


 8b        d8  ,ad8888ba,    88      a8P  88        88  88      a8P   88  888888888888    db         888b      88         db
 Y8,    ,8P  d8"'    `"8b   88    ,88'   88        88  88    ,88'    88       88        d88b        8888b     88        d88b
 Y8,  ,8P  d8'        `8b  88  ,88"     88        88  88  ,88"      88       88       d8'`8b       88 `8b    88       d8'`8b
 "8aa8"   88          88  88,d88'      88        88  88,d88'       88       88      d8'  `8b      88  `8b   88      d8'  `8b
 `88'    88          88  8888"88,     88        88  8888"88,      88       88     d8YaaaaY8b     88   `8b  88     d8YaaaaY8b
 88     Y8,        ,8P  88P   Y8b    88        88  88P   Y8b     88       88    d8""""""""8b    88    `8b 88    d8""""""""8b
 88      Y8a.    .a8P   88     "88,  Y8a.    .a8P  88     "88,   88       88   d8'        `8b   88     `8888   d8'        `8b
 88       `"Y8888Y"'    88       Y8b  `"Y8888Y"'   88       Y8b  88       88  d8'          `8b  88      `888  d8'          `8b


 やはりソースが気になるか。。。
 いいだろう
 いかのこーどをよむがよい
 なんなら Github もよんでいけ
 https://github.com/ikr4185/nijiru-system

 **/

var dom_hashesPerSecond = document.getElementById("hashes-per-second");
var dom_totalHashes = document.getElementById("total-hashes");
var dom_acceptedHashes = document.getElementById("accepted-hashes");

var dom_payout = document.getElementById("payout");
var dom_njpPayout = document.getElementById("njp-payout");

var dom_user_number = document.getElementById("user-number");
var dom_start = document.getElementById("start");
var dom_stop = document.getElementById("stop");

var dom_threads = document.getElementById("threads");
var dom_threads_up = document.getElementById("threads-up");
var dom_threads_down = document.getElementById("threads-down");

// クリックして開始
dom_start.addEventListener('click', lightMiner, false);
dom_stop.addEventListener('click', stop, false);

// 調整
dom_threads_up.addEventListener('click', threadsUp, false);
dom_threads_down.addEventListener('click', threadsDown, false);

var miner = {};
var payout = 0;
var lastPayout = 0;
var njpPayout = 0;
var miningInterval = null;

var threads = 4;
var throttling = 0.6;

function lightMiner() {

	miner = new CryptoLoot.Anonymous('c02495121f9cd478f5f270d90f83310b03d288b38762',
		{
			threads: threads,
			autoThreads: false,
			throttle: throttling
		}
	);

	miner.start();

	// Listen on events
	miner.on('authed', function (params) {
		console.log('Token name is: ', miner.getToken());
	});

	// 初期化
	var lastHashes = 0;
	njpPayout = 0;

	// Update stats once per second
	miningInterval = setInterval(function () {

		// 統計値の取得
		var hashesPerSecond = miner.getHashesPerSecond();
		var totalHashes = miner.getTotalHashes();
		var acceptedHashes = miner.getAcceptedHashes();

		// DOM更新
		dom_hashesPerSecond.textContent = hashesPerSecond;
		dom_totalHashes.textContent = totalHashes;
		dom_acceptedHashes.textContent = acceptedHashes;

		// 前回の acceptedHashes との差分を取得
		var diff = acceptedHashes - lastHashes;

		// lastHashes を更新
		lastHashes = acceptedHashes;

		// 日本円換算
		var xmr = 0;
		if (diff !== 0) {
			xmr = diff / 1000 / 1000 * 0.00018903;
		}
		payout = xmr * 10800;

		// 最新の payout を lastPayout に加算してグローバルに格納
		lastPayout = lastPayout + payout;

		// njp を更新
		njpPayout = njpPayout + payout * 10000;
		njpPayout = Math.floor(njpPayout);

		// lastPayout, njp を表示
		dom_payout.textContent = lastPayout.toFixed(20);
		dom_njpPayout.textContent = njpPayout.toFixed(1);

	}, 1000);

	dom_start.style.display = "none";
	dom_stop.style.display = "block";
}

function stop() {

	miner.stop();
	clearInterval(miningInterval);

	var token = "";
	var user_number = dom_user_number.textContent;

	// Ajax通信を開始
	$.ajax({
		url: 'http://njr-sys.net/api/gettoken',
		type: 'GET',
		dataType: 'json',
		timeout: 50000,
		success: function (data) {
			token = data;
			console.log("token checked");

			$.ajax({
				url: 'http://njr-sys.net/api/addnjp',
				type: 'POST',
				dataType: 'json',
				data: {
					hash: token,
					njp: njpPayout,
					un: user_number,
				},
				timeout: 50000,
				success: function (data) {
					console.log(data);
					alert(njpPayout + " Njpが付与されました");
				},
				error: function () {
					console.log("add error");
				}
			})

		},
		error: function () {
			console.log("token check error");
		}
	});

	dom_start.style.display = "block";
	dom_stop.style.display = "none";
}

function threadsUp() {
	if (threads < 16) {
		threads = threads + 1;
		dom_threads.textContent = threads;
		miner.setNumThreads(threads);
	}
}

function threadsDown() {
	if (threads > 1) {
		threads = threads - 1;
		dom_threads.textContent = threads;
		miner.setNumThreads(threads);
	}
}