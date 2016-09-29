<div class="section">
	<div class="section__inner">
		<h3 class="subhead">
			{$result["record"]["name"]} を本当に削除しますか？
		</h3>
		<form method="POST" action="/admin/confirm/" class="flat_form">
			<input type="hidden" name="lowVotesNumber" value="{$result["record"]["low_votes_number"]}">
			<input type="hidden" name="confirm" value="confirm">
			<button type="submit" name="submit" class="flat_form-button--download">実行</button>
		</form>
	</div>
	<div class="section__inner">
		<a href="/admin/">戻る</a>
	</div>
</div>