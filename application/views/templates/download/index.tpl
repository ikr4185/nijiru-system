<div class="section">

	<h2 class="sub_title">Nijiru Downloader</h2>

	<div class="section__inner">
		<p>ニジポを消費して特典コンテンツをダウンロードできます。</p>
	</div>

	{if !empty($result["msg"])}
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	{/if}

	{if !empty($view["id"]) }

		{foreach from=$result["data"] item=data}
			<div class="section__inner">
				<h3 class="subhead">{$data["download_id"]}. {$data["description"]}</h3>
				<form method="POST" action="" class="flat_form">

					<div class="download-img">
						<img src="http://njr-sys.net/download/{$data["sample_path"]}" class="download-img__img">
						<input type="hidden" name="dl[{$data["download_id"]}]" id="dl[{$data["download_id"]}]">
					</div>

					<h3 class="subhead">Download</h3>
					<button type="submit" name="submit" class="flat_form-button--download">Confirm</button>

				</form>
			</div>
		{/foreach}

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