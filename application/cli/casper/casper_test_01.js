// 実行
// casperjs /home/njr-sys/public_html/application/cli/casper/casper_test_01.js [引数]

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
	forumPageUrl: 'http://sugoi-chirimenjako-pain.wikidot.com/forum/t-1995290/test-2016-11-15-casper',
	postFormSelector: "#new-post-form",
	postParams: {
		title: 'テスト投稿',
		source: '**テスト投稿**'
	}
};

// UAの設定
casper.userAgent('Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2725.0 Safari/537.36');


// ログイン画面を開く
casper.start(config.loginPageUrl, function () {
	this.echo(this.getTitle());
	this.viewport(1024, 768);
});

// ログインする
casper.then(function () {

	casper.echo("waiting login");

	// ログイン
	this.wait(2000, function () {

		casper.echo("login");

		// フォーム入力後、submit
		this.fill(config.loginFormSelector, config.loginParams);
		this.capture('casper_test_00.png');

		this.evaluate(function () {
			document.querySelector("button[type='submit']").click();
		})
	});
});

// ログイン処理をまつ
casper.then(function () {

	casper.echo("waiting redirect");

	// 遷移後にキャプチャを取る
	this.wait(5000, function () {
		casper.echo("redirected");
		casper.capture('casper_test_01.png');
	});
});

// コメント投稿のあれを開く
casper.thenOpen(config.forumPageUrl, function () {

	this.echo(this.getTitle());

	this.wait(1000, function () {

		// コメント投稿ボタンをクリックする
		casper.echo("click new post button");
		this.click('#new-post-button')

	}).wait(1000, function () {

		// キャプチャをとる
		casper.echo("opened");
		casper.capture('casper_test_02.png');
	});
});

// 投稿する
casper.then(function () {

	casper.echo("posting");

	// 投稿処理
	this.fill(config.postFormSelector, config.postParams);
	casper.capture('casper_test_03.png');

	this.evaluate(function () {
		document.querySelector('#np-post').click();
	});

	casper.wait(10000, function () {
		// キャプチャをとる
		casper.echo("posted");
		casper.capture('casper_test_04.png');
	});
});

// キャプチャチェックイベント
casper.on('capture.saved', function (file) {
	casper.echo('[CAPTURE] ' + file);
});

// 実行
casper.run();