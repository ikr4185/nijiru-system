<div class="secton1">

	<h2 class="sub_title">決済完了: Download</h2>

	<div class="section__inner">
		<p>ニジポ決済が完了しました。下記ボタンからダウンロードが可能です。</p>
		<div class="notice_text ">
			<p>ダウンロードは一度しかできません。ダウンロード中にキャンセルした場合のニジポ返還は承りかねます。</p>
		</div>
	</div>

	{if !empty($result["msg"])}
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	{/if}

	<div class="section__inner">
		<form method="POST" action="" class="flat_form">

			<input type="hidden" name="dl[{$result["fileID"]}]" id="dl[{$result["fileID"]}]">

			<h3 class="subhead">Download</h3>
			<button type="submit" name="submit" class="flat_form-button--download">Download</button>

		</form>
	</div>

	<div>
		<a href="/download/">ダウンロードページへ戻る</a>
	</div>

</div>