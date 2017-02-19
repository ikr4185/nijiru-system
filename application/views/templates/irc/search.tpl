<div class="section">
	<h2 class="sub_title">#scp-jp 検索</h2>
	<p>
		IRC #scp-jp ログからワード検索します<br>
		※ 検索結果が多い場合、最新{$result["searchLimit"]}件までを表示します
	</p>
</div>

<div class="section">
	<div class="flat_form">
		<form method="POST" action="" class="flat_form">
			<fieldset>
				<table class="flat_table">
					<tr>
						<th><label>キーワード</label></th>
						<td class="input"><input type="text" name="search" id="search" placeholder="search" value="{if !empty($result["search"])}{$result["search"]}{/if}"></td>
					</tr>
					<tr>
						<th></th>
						<td class="input"><button type="submit" name="submit" class="flat_form-button">search</button></td>
					</tr>
				</table>
			</fieldset>
		</form>
	</div>
</div>

<div class="section">

	{if !empty($result["msg"])}
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	{/if}

	<div class="section__inner">

		<table class="irc-table zebra-table">
			<tbody>
			{foreach from=$result["searchResult"] item=item key=key}
				<tr class="js_irc_log {$item["specialColor"]}">
					<td class="nowrap">
						<a href="http://njr-sys.net/irc/log/{$item["datetime"]}">{$item["datetime"]}</a>
					</td>
					<td class="nowrap">
						<span class="b {$item["color"]}">&lt;{$item["nick"]}&gt;</span>
					</td>
					<td class="wrap irc-table__message">{$item["message"]}<br>
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>

	</div>

</div>