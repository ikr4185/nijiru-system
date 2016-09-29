jQuery(function($) {
	var $view = $('#view'),
		$data = $('input[name="data"]');

	/**
	 * データ取得
	 */
	function getData() {
		$.post('/test/comet/?mode=view', {}, function(data) {
			$view.html(data);
			checkUpdate();
			countdown(10);
		});
	}

	/**
	 * 更新チェック
	 */
	function checkUpdate() {
		$.post('/test/comet/?mode=check', {}, function(data) {
			$view.html(data);
			checkUpdate();
		});
	}

	function countdown($count) {
		$('#count').css( "width", $count*10+"%");
		if ($count > 0) {
			setTimeout(function () {
				$count = $count - 1;
				countdown($count);
			}, 1000);
		}else{
			countdown(10);
		}
	}

	getData();
});