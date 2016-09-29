<div class="section">

	<h2 class="sub_title">#site8181 {$result["logs"][0]["date"]}</h2>

	<div class="section__inner">

		<table class="irc-table zebra-table">
			<tbody>
			{foreach from=$result["logs"] item=item}
				<tr class="{$item["specialColor"]}">
					<td class="nowrap">
						{$item["datetime"]}
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

	<div class="section__inner">
		<a href="{$result["logsLink"]}">戻る</a>
	</div>

</div>