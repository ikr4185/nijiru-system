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

<div class="section">

	<h2 class="sub_title">共有</h2>

	{literal}
		<a href="https://twitter.com/share" class="twitter-share-button"{count} data-lang="ja" data-size="large" data-hashtags="NijiruSystem">ツイート</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
	{/literal}
</div>

