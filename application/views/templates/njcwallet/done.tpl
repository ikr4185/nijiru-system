<div class="section">

	{if !empty($view["id"]) }

		{if !empty($result["msg"])}
			<div class="section__inner">
				<div class="notice_text">
					<p>{$result["msg"]}</p>
				</div>
			</div>
		{/if}

		{if $result["isValid"] }
			<h2 class="sub_title">Complete</h2>
			<div class="section__inner">
				<p>送金が完了しました。</p>
			</div>
		{/if}
		<div class="section__inner">
			<table class="tight-table zebra-table ">
				<tr>
					<th class="">相手</th>
					<th class="">送金額</th>
				</tr>
				<tr>
					<td class="">{$result["toAddress"]}</td>
					<td class="">{$result["amount"]}</td>
				</tr>
			</table>
		</div>
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
