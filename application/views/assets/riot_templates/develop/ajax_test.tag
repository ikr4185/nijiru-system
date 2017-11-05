<AjaxTest>

	<div>
		<span>{this.ui.log}</span>
		<button onClick="{this.ajaxTest}">{this.ui.button}</button>
	</div>

	<script>

		this.data = {
			a: 100,
			b: 200,
		}

		this.ui = {
			log: "loading...",
			button: "乱数を発生させる",
		};

		this.on("mount", function () {
			this.ajaxTest();
		});

		this.ajaxTest = function () {

			// https://developer.mozilla.org/ja/docs/Web/API/XMLHttpRequest/Synchronous_and_Asynchronous_Requests
			this.ui.log = 'loading...';
			this.ui.button = '数秒お待ち下さい';

			var xhr = new XMLHttpRequest();
			xhr.open("POST", 'http://njr-sys.net/develop/rand', true);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xhr.onload = function (e) {
				switch (xhr.readyState) {
					case 0:
						// 未初期化状態.
						this.ui.log = 'uninitialized!';
						break;
					case 1: // データ送信中.
						this.ui.log = 'loading...';
						break;
					case 2: // 応答待ち.
						this.ui.log = 'loaded.';
						break;
					case 3: // データ受信中.
						this.ui.log = 'interactive... ' + xhr.responseText.length + ' bytes.';
						break;
					case 4: // データ受信完了.
						if (xhr.status == 200 || xhr.status == 304) {
							var data = xhr.responseText; // responseXML もあり
							this.ui.log = 'COMPLETE! :' + data;
						} else {
							this.ui.log = 'Failed. HttpStatus: ' + xhr.statusText;
						}
						break;
				}
				this.ui.button = '乱数を発生させる';
				this.update();
			}.bind(this);
			xhr.onerror = function (e) {
				this.ui.log = 'Error: ' + xhr.statusText;
				this.update();
			}.bind(this);
			xhr.send(this.EncodeHTMLForm(this.data));
		};

		this.EncodeHTMLForm = function (data) {
			var params = [];

			for (var name in data) {
				var value = data[name];
				var param = encodeURIComponent(name) + '=' + encodeURIComponent(value);

				params.push(param);
			}

			return params.join('&').replace(/%20/g, '+');
		}


	</script>


</AjaxTest>