<div class="section">
	<h2 class="sub_title">下書き批評予約</h2>
	<p>
		IRCチャット定例会等で行われる、下書き批評の順番を管理するツールです。
	</p>
</div>

<div class="section">

	<div class="section__inner">

		<table class="flat_table zebra-table">

			<thead>
			<tr style="background:#f9f9f9;;">
				<th>日付</th>
				<th>予約数</th>
			</tr>
			</thead>

			<tbody>
			{if !empty($result["logs"]) }

				{foreach from=$result["logs"] item=item}
					<tr style="height:30px;">
						<td><a href="http://njr-sys.net/irc/draftReserve/{$item[0]}">{$item[0]}</a></td>
						<td>{$item[1] nofilter}</td>
					</tr>
				{/foreach}

			{else}
				<tr style="height:30px;">
					<td>Unknown Error</td>
				</tr>
			{/if}
			</tbody>

		</table>

	</div>
</div>