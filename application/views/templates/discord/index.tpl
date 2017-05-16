<div class="section">

	<h2 class="sub_title">Discord Logs</h2>


	{if !empty($result["channels"]) }

		{foreach from=$result["channels"] item=item key=channelName}

			{if !$item["isPrivate"]}
				<div class="section__inner">

					<h2 class="subhead">#{$channelName}</h2>

					<table class="flat_table zebra-table">

						<thead>
						<tr style="background:#f9f9f9;;">
							<th>日付</th>
							<th>発言数</th>
						</tr>
						</thead>

						<tbody>

						{foreach from=$item["logs"] item=logs}
							<tr style="height:30px;">
								<td><a href="http://njr-sys.net/discord/log/{$channelName}/?date={$logs[0]}">{$logs[0]}</a></td>
								<td>{$logs[2] nofilter}</td>
							</tr>
						{/foreach}

						</tbody>
					</table>

				</div>
			{/if}

		{/foreach}

	{else}
		Unknown Error
	{/if}
</div>


<div class="section">

	<h2 class="sub_title">Staff Authentication</h2>

	<div id="login_form">
		<form method="POST" action="/discord/auth" class="flat_form">
			<fieldset>
				<table class="flat_table">
					<tr>
						<th><label>password</label></th>
						<td class="input"><label><input type="password" name="staff_pass"></label></td>
					</tr>
					<tr>
						<th></th>
						<td class="input"><input type="submit" name="login" class="flat_form-button" value="login"></td>
					</tr>
				</table>
			</fieldset>
		</form>
	</div>
</div>