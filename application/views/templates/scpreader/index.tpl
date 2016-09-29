<div class="section">
	<h2 class="sub_title">SCP-JP一覧</h2>

	<div class="flat_form">
		<input type="text" id="search" placeholder="search" value="">
	</div>
</div>

<div class="section">

	{if !empty($result["ArticleArray"])}

		{foreach from=$result["ArticleArray"] item=item}

			{* 5つおきに改行 *}
			{if ( 0 === ($item["item_number"] % 5) )}
				<br>
			{/if}

			{* 100おきに題字 *}
			{if ( 0 === ($item["item_number"] % 100) )}
				<br>
				<h3 class="subhead">{$item["item_number"]}～</h3>
			{/if}

			<ul>
				<li id="scp-{$item["item_number"]}-jp" class="scp-reader_li">
					<a href="http://njr-sys.net/scpReader/scp/{$item["item_number"]}">
						{$item["name"]}
					</a>
					- {$item["nickname"] nofilter}
				</li>
			</ul>
		{/foreach}

	{else}
		Nothing Available Data
	{/if}
</div>


