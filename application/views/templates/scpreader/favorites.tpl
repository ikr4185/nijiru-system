{if !empty($result["msg"])}
	<div class="section__inner">
		<div class="notice_text">
			<p>{$result["msg"]}</p>
		</div>
	</div>
{/if}

<div class="section">

	<h2 class="sub_title">SCP-JP My Favorite SCP</h2>

	<p>SCP-JP-Readerのお気に入り記事一覧を閲覧できます。</p>

</div>

<div class="section">

	{if !empty($view["id"]) }

	<table class="flat_table zebra-table log-table">

		<tr>
			<th>Article</th>
			<th>Add Date</th>
		</tr>

		{foreach from=$result["records"] item=fav}

			<tr>
			<td><a href="http://njr-sys.net/scp_reader/scp/{$fav["item_number"]}">SCP-{$fav["item_number"]}-JP</a></td>
			<td>{$fav["modified_date"]}</td>
			</tr>

		{/foreach}

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
