<div class="section">
	<h2 class="sub_title">Nijiru Storage</h2>

	<p>
		画像をアップできます。<br>
		※NijiruSystemアカウントが必要なのは、荒らし対策です。
	</p>

	{if !empty($result["msg"])}
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	{/if}
</div>

<div class="section">

	<div class="section__inner">
		<h3 class="subhead">ファイルアップロード</h3>

		{if !empty($view["id"]) }

			<form method="POST" action="" class="flat_form" enctype="multipart/form-data">
				<fieldset>
					<table class="flat_table">
						<tr>
							<th><label>ファイル</label></th>
							<td class="input"><label><input type="file" name="upload" size="30"></label></td>
						</tr>
						<tr>
							<th><label>クレジット名</label></th>
							<td class="input"><label><input type="text" name="credit"></label></td>
						</tr>
						<tr>
							<th>submit<br><span class="red">notice: 確認無しで実行します</span></th>
							<td class="input"><button type="submit" name="submit" class="flat_form-button">Upload</button></td>
						</tr>
					</table>
					<input type="hidden" name="registerLvc" value="1">
				</fieldset>
			</form>

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

	<div class="section__inner">
		<h3 class="subhead">ファイル一覧</h3>

		<div id="storage-container" class="free-wall">
			{if !empty($result["fileArray"])}

				{foreach $result["fileArray"] as $file }

					<div class="storage-brick">

						<div class="storage-brick__inner">
							<div class="storage-brick__img">
								<img src="/storage/outimg/?url={urlencode($file["file_path"])}&width=300" class="storage-img">
							</div>
							<ul class="storage-brick__ul">
								<li><a href="{$file["url"]}" class="download_text">{$file["file_name"]}</a></li>
								<li>credit: {$file["credit"]}</li>
								<li>upload: {$file["user_name"]}</li>
								<li>{floor( $file["size"] / 1000 )} KB</li>
								{if !empty($view["id"]) }<li><a href="/storage/del/{$file["id"]}">delete this</a></li>{/if}
							</ul>
						</div>

					</div>

				{/foreach}
			{else}
				not exists.
			{/if}
		</div>

	</div>

</div>


