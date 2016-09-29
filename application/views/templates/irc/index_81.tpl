<div class="section">

	<h2 class="sub_title">IRC Reader #site8181</h2>

	<div class="section__inner">

		<table class="flat_table zebra-table">

			<thead>
			<tr style="background:#f9f9f9;;">
				<th>日付</th>
				<th>発言数</th>
			</tr>
			</thead>

			<tbody>
			{if !empty($result["logs"]) }

				{foreach from=$result["logs"] item=item}
					<tr style="height:30px;">
						<td><a href="http://njr-sys.net/irc/log81/{$item[0]}">{$item[0]}</a></td>
						{*<td>{$item[1]}</td>*}
						<td><div style="background:#333;width:{$item[2]}%;color:#888;padding:0 5px;">{$item[1]}</div></td>
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