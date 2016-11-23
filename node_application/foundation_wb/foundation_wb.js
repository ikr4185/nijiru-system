/**
 * HTML5とNode.jsとSocket.ioで絵チャ的な事をする為のやつ
 * @see https://twitter.com/NanimonoKashira/status/801249345014116353
 * @see http://www.koikikukan.com/archives/2012/02/13-000300.php
 * @see http://www.cosketch.com/API
 */

var app = require('express').createServer()
	, io = require('socket.io').listen(app);
app.listen(8124);

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