{if !empty($result["msg"])}
	<div class="section__inner">
		<div class="notice_text">
			<p>{$result["msg"]}</p>
		</div>
	</div>
{/if}

<div class="section">

	<h2 class="sub_title">SCP-JP Reading Log</h2>

	<p>SCP-JP-Readerの閲覧履歴を表示できます。</p>

</div>

<div class="section">

	{if !empty($view["id"]) }

		<table class="flat_table zebra-table log-table">

			<tr>
				<th>Article</th>
				<th>Date</th>
			</tr>

			{if !empty($result["records"])}

				{foreach from=$result["records"] item=log}
					<tr>
						<td><a href="/scpReader/scp/{$log["url"]}">SCP-{$log["url"]}-JP</a></td>
						<td>{$log["created_date"]}</td>
					</tr>
				{/foreach}

			{else}
				<tr><td colspan='2' class=\"center\">Nothing Available Data</td></tr>
			{/if}

		</table>

	{else}

		<div class="login-guide">
			<h3 class="login-guide__notice">ご利用いただくには、ログインが必要です</h3>
			<ul class="login-guide__guide">
				<li class="login-guide__li"><a href="/login/login/{$view["loginRedirect"]}">ログイン</a></li>
				<li class="login-guide__li"><a href="/login/register">新規登録する</a></li>
			</ul>
		</div>

	{/if}

</div>