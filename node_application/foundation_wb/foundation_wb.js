/**
 * HTML5とNode.jsとSocket.ioで絵チャ的な事をする為のやつ
 * @see https://twitter.com/NanimonoKashira/status/801249345014116353
 * @see http://www.koikikukan.com/archives/2012/02/13-000300.php
 * @see http://www.cosketch.com/API
 */

var express = require('express'),
	app = express(),
    http = require('http').Server(app),
	io = require('socket.io')(http);

// サーバーをポート8124番で起動
http.listen(8124, function(){
	console.log('listening on *:8124');
});

// GETを受けたらindex.htmlを返す
app.get('/', function (req, res) {
	res.sendfile(__dirname + '/index.html');
});


// ４．クライアントへの送信方法
io.sockets.on('connection', function (socket) {

	// クライアントからメッセージ受信
	socket.on('clear send', function () {

		// 自分以外の全員に送る
		socket.broadcast.emit('clear user');
	});

	// クライアントからメッセージ受信
	socket.on('server send', function (msg) {

		// 自分以外の全員に送る
		socket.broadcast.emit('send user', msg);
	});

	// 切断
	socket.on('disconnect', function () {
		io.sockets.emit('user disconnected');
	});
});