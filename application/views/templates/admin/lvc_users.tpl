<div class="section">
	<h2 class="sub_title">「消すやつ」メール配信設定</h2>

	{if !empty($result["msg"])}
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	{/if}

	<div class="section__inner">
		<h3 class="subhead">配信先リスト</h3>
		<table class="flat_table zebra-table log-table">

			<tr>
				<th>ユーザ名</th>
				<th>アドレス</th>
				<th>リストから削除</th>
			</tr>

			{if !empty($result["LvcUsers"])}

				{foreach $result["LvcUsers"] as $info }
					<tr>
						<td>{$info["name"]}</td>
						<td>{substr($info["mail"],0,2)}[編集済]{substr($info["mail"],-4,4)}</td>
						<td>
							<form method="POST" action="" class="flat_form">
								<input type="hidden" name="id" value="{$info["id"]}">
								<button type="submit" name="delLvc" class="flat_form-button--download">削除</button>
							</form>
						</td>
					</tr>
				{/foreach}

			{else}
				<tr>
					<td colspan="2">not exists.</td>
				</tr>
			{/if}

		</table>
	</div>

	<div class="section__inner">
		<h3 class="subhead">配信先追加</h3>
		<form method="POST" action="" class="flat_form">
			<fieldset>
				<table class="flat_table">
					<tr>
						<th><label>wikidot ID</label></th>
						<td class="input"><label><input type="text" name="name"></label></td>
					</tr>
					<tr>
						<th><label>Email</label></th>
						<td class="input"><label><input type="email" name="mail"></label></td>
					</tr>
					<tr>
						<th>submit<br><span class="red">notice: 確認無しで実行します</span></th>
						<td class="input"><button type="submit" name="submit" class="flat_form-button">Register</button></td>
					</tr>
				</table>
				<input type="hidden" name="registerLvc" value="1">
			</fieldset>
		</form>
	</div>

	<div class="section__inner">
		<a href="/admin">戻る</a>
	</div>

</div>