<div class="secton1">

	<h2 class="sub_title">Confirm</h2>

	{if !empty($view["id"]) }

		{if !empty($result["msg"])}
			<div class="section__inner">
				<div class="notice_text">
					<p>{$result["msg"]}</p>
				</div>
			</div>
		{/if}

		<div class="section__inner">
			<p>下記画像をダウンロードしますか？</p>
			<div class="notice_text">
				<p>必要ニジポ: {$result["data"]["price"]}ニジポ</p>
			</div>
		</div>

		<div class="section__inner">

			<form method="POST" action="" class="flat_form">

				<div class="download_img">
					<img src="http://njr-sys.net/download/{$result["data"]["sample_path"]}">
					<input type="hidden" name="dl[{$result["fileId"]}]" id="dl[{$result["fileId"]}]">
				</div>

				<h3 class="subhead">Purchase</h3>
				<button type="submit" name="download" class="flat_form-button--download">Purchase</button>

			</form>
		</div>

	{else}
		<div class="login-guide">
			<h3 class="login-guide__notice">ご利用いただくには、ログインが必要です</h3>
			<ul class="login-guide__guide">
				<li class="login-guide__li"><a href="/login/login/{$view["loginRedirect"]}">ログイン</a></li>
				<li class="login-guide__li"><a href="/login/register">新規登録する</a></li>
			</ul>
		</div>
	{/if}

</div>