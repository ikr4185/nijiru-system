{if !empty($result["msg"])}
	<div class="section__inner">
		<div class="notice_text">
			<p>{$result["msg"]}</p>
		</div>
	</div>
{/if}

<div class="scp-reader section">

	<div class="section">
		<h2 class="sub_title">SCP-{$result["scpArray"]["scp_num"]}-JP - {$result["scpArray"]["title"] nofilter}</h2>
	</div>

	<div class="section">

		<h3 class="subhead">ItemNumber</h3>
		<div class="common_section">
			{$result["scpArray"]["item_number"]}
		</div>

		<h3 class="subhead">Object Class</h3>
		<div class="common_section">
			{$result["scpArray"]["class"] nofilter}
		</div>

		<h3 class="subhead">Protocol</h3>
		<div class="common_section">
			{$result["scpArray"]["protocol"] nofilter}
		</div>

		<h3 class="subhead">Description</h3>
		<div class="common_section">
			{$result["scpArray"]["description"] nofilter}
		</div>

		<h3 class="subhead">Vote</h3>
		<div class="common_section">
			{$result["scpArray"]["vote"]}
		</div>

		<h3 class="subhead">Created_by</h3>
		<div class="common_section">
			{$result["scpArray"]["created_by"]}
		</div>

		<h3 class="subhead">Created_at</h3>
		<div class="common_section">
			{$result["scpArray"]["created_at"]}
		</div>

		<h3 class="subhead">Tags</h3>
		<div class="common_section">
			{foreach from=$result["scpArray"]["tags"] item=tag }
				{$tag},
			{/foreach}
		</div>

		<h3 class="subhead">URL</h3>
		<div class="common_section">
			<a href="http://ja.scp-wiki.net/scp-{$result["scpArray"]["scp_num"]}-jp" target="_blank">
				http://ja.scp-wiki.net/scp-{$result["scpArray"]["scp_num"]}-jp
			</a>
		</div>

	</div>

</div>

<div id="favorite_form" class="section">

	<h2 class="sub_title">お気に入り登録</h2>

	{if !empty($view["id"]) }

	<form method="POST" action="" class="flat_form">
		<fieldset>
			<table class="flat_table">
				<tr>
					<td>
						<input type="radio" name="favorite-scp" id="favorite-scp-2" value="2" {if ($result["is_favorite"]) }checked="checked"{/if}>
						<label for="favorite-scp-2" class="enable flat_form-label">登録</label>
					</td>
					<td>
						<input type="radio" name="favorite-scp" id="favorite-scp-1" value="1" {if !($result["is_favorite"]) }checked="checked"{/if}>
						<label for="favorite-scp-1" class="disable flat_form-label">未登録</label>
					</td>
				</tr>
				<tr>
					<th>submit<br><span class="red">notice: without warning</span></th>
					<td class="input"><button type="submit" name="submit_favorite-scp" class="flat_form-button">Update</button></td>
				</tr>
			</table>
		</fieldset>
	</form>

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

<div class="section">

	<h2 class="sub_title">共有</h2>

	{literal}
	<a href="https://twitter.com/share" class="twitter-share-button"{count} data-lang="ja" data-size="large" data-hashtags="NijiruSystem">ツイート</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
	{/literal}
</div>
