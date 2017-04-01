<div class="section">

	<h2 class="sub_title">Discord Logs</h2>


	{if !empty($result["channels"]) }

		{foreach from=$result["channels"] item=item key=channelName}
			<div class="section__inner">

				<h2 class="subhead">#{$channelName}</h2>

				{foreach from=$item item=logs}
					<table class="flat_table zebra-table">

						<thead>
						<tr style="background:#f9f9f9;;">
							<th>日付</th>
							<th>発言数</th>
						</tr>
						</thead>

						<tbody>


						<tr style="height:30px;">
							<td><a href="http://njr-sys.net/discord/log/{$channelName}/?date={$logs[0]}">{$logs[0]}</a></td>
							<td>{$logs[2] nofilter}</td>
						</tr>

						</tbody>
					</table>
				{/foreach}

			</div>
		{/foreach}

	{else}
		Unknown Error
	{/if}
</div>