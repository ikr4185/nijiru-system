// 実行
// casperjs /home/njr-sys/public_html/application/cli/casper/GetPm.js [引数]

// ========================================
// 設定
// ========================================

// module 呼び出し
var casper = require('casper').create({
	verbose: true,
	logLevel: "info"
});

//コマンドライン引数を受け取る
var word = casper.cli.args;

// コンフィグ
var config = {
	resultDir: 'result',
	loginFormSelector: "#html-body > div.container > div:nth-child(2) > div > div.login-paths > div.path.with-wikidot > form",
	loginParams: {
		login: "ikr_4185",
		password: word[0]
	},
	loginPageUrl: "https://www.wikidot.com/default--flow/login__LoginPopupScreen?originSiteId=709475&openerUri=http://sugoi-chirimenjako-pain.wikidot.com",

	targetPageUrl: 'https://www.wikidot.com/account/messages',
	targetSelector: "#messages-list > tbody > tr.message.new",
	messageSelector: "#message-view > div.pmessage > div.body"
};

// UAの設定
casper.userAgent('Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2725.0 Safari/537.36');

// ========================================
// ログイン
// ========================================

// ログイン画面を開く
casper.start(config.loginPageUrl, function () {
	this.viewport(1024, 768);
});

// ログインする
casper.then(function () {

	// ログイン
	this.wait(2000, function () {

		// フォーム入力後、submit
		this.fill(config.loginFormSelector, config.loginParams);

		this.evaluate(function () {
			document.querySelector("button[type='submit']").click();
		})
	});
});

// ログイン処理をまつ
casper.then(function () {

	// 遷移後少し待つ
	this.wait(5000, function () {
		// debug ////////////////////////////////////////
		// casper.capture('/home/njr-sys/public_html/test/casper_test_01.png');
	});
});

// ========================================
// PMを確認する
// ========================================

// メッセージ受信ボックスを開く
casper.thenOpen(config.targetPageUrl, function () {

	this.wait(1000, function () {

		if (this.exists(config.targetSelector)) {
			this.click(config.targetSelector);
		}else{
			casper.echo("error");
			exit();
		}

	}).wait(1000, function () {
		// debug ////////////////////////////////////////
		// casper.capture('/home/njr-sys/public_html/test/casper_test_02.png');
	});
});

// 本文を取得して出力する
casper.then(function () {

	this.wait(1000, function () {
		casper.echo(this.getHTML(config.messageSelector));
		// debug ////////////////////////////////////////
		// casper.capture('/home/njr-sys/public_html/test/casper_test_03.png');
	});
});

// ========================================
// 処理実行
// ========================================

// キャプチャチェックイベント
casper.on('capture.saved', function (file) {
});

// 実行
casper.run(function() { // 処理実行
	this.exit() ; //メッセージを出力して終了
});
