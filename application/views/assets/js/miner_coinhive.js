var dom_hashesPerSecond = document.getElementById("hashes-per-second");
var dom_totalHashes = document.getElementById("total-hashes");
var dom_acceptedHashes = document.getElementById("accepted-hashes");
var dom_payout = document.getElementById("payout");
var dom_start = document.getElementById("start");
var dom_heavyStart = document.getElementById("heavy-start");
var dom_stop = document.getElementById("stop");

// クリックして開始
dom_start.addEventListener('click', lightMiner, false);
dom_heavyStart.addEventListener('click', HeavyMiner, false);
dom_stop.addEventListener('click', stop, false);

var miner = {};
var payout = 0;
var lastPayout = 0;
var miningInterval = null;

function lightMiner() {

	miner = new CoinHive.Anonymous('ovUHdxKQYmoqZOLns0qOY4v7kbmg5KIk',{
		threads: 2,
		autoThreads: false,
		throttle: 0.8,
		forceASMJS: false
	});

	miner.start();

	// Listen on events
	miner.on('authed', function(params) {
		console.log('Token name is: ', miner.getToken());
	});

    // Update stats once per second
	miningInterval = setInterval(function() {

		// 統計値の取得
		var hashesPerSecond = miner.getHashesPerSecond();
		var totalHashes = miner.getTotalHashes();
		var acceptedHashes = miner.getAcceptedHashes();

		// DOM更新
		dom_hashesPerSecond.textContent = hashesPerSecond;
		dom_totalHashes.textContent= totalHashes;
		dom_acceptedHashes.textContent = acceptedHashes;

		// 日本円換算
		var xmr = (totalHashes/30084069116) * 6.43 * 0.7;
		payout = xmr * 10800;

		// 最新の payout を lastPayout に加算してグローバルに格納
		lastPayout = lastPayout + payout;

		// lastPayout を表示
		dom_payout.textContent =lastPayout.toFixed(20);

	}, 1000);

	dom_start.style.display = "none";
	dom_heavyStart.style.display = "none";
	dom_stop.style.display = "block";
}

function HeavyMiner() {

	miner = new CoinHive.Anonymous('ovUHdxKQYmoqZOLns0qOY4v7kbmg5KIk',{
		threads: 4,
		autoThreads: false,
		throttle: 0,
		forceASMJS: false
	});

	miner.start();

	// Listen on events
	miner.on('authed', function(params) {
		console.log('Token name is: ', miner.getToken());
	});

	// Update stats once per second
	miningInterval = setInterval(function() {
		var hashesPerSecond = miner.getHashesPerSecond();
		var totalHashes = miner.getTotalHashes();
		var acceptedHashes = miner.getAcceptedHashes();

		dom_hashesPerSecond.textContent = hashesPerSecond;
		dom_totalHashes.textContent= totalHashes;
		dom_acceptedHashes.textContent = acceptedHashes;

		var payout = (totalHashes/30084069116) * 6.43 * 0.7;
		payout = payout * 10800;

		lastPayout = lastPayout + payout;

		dom_payout.textContent =lastPayout.toFixed(20);
	}, 1000);

	dom_start.style.display = "none";
	dom_heavyStart.style.display = "none";
	dom_stop.style.display = "block";

}

function stop() {

	miner.stop();
	clearInterval(miningInterval);

	dom_start.style.display = "block";
	dom_heavyStart.style.display = "block";
	dom_stop.style.display = "none";
}

