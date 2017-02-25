<div class="section">

	<h2 class="sub_title">Discord Log</h2>

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
						<td><a href="http://njr-sys.net/discord/log/{$item[0]}">{$item[0]}</a></td>
						<td>{$item[2] nofilter}</td>
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