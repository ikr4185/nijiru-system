<h2 class="sub_title">最近作成された記事</h2>

<div class="section">

	{foreach from=$result["data"] key=key item=items name=logs}

	<h3 class="subhead">ページ{{$smarty.foreach.logs.iteration}}</h3>
	<div class="common_section">
		<table class="flat_table--narrow zebra-table">

			{foreach from=$items key=key item=item name=log}

			<tr class=\"article-list__row\">
				<td class="article-list__title article-list__data">
					<a href = "http://ja.scp-wiki.net/{$item['url']}">{$item['title']}</a>
				</td>
				<td class="article-list_post__date article-list__data">
					{$item['postDate']}
				</td>
			</tr>

			{/foreach}

		</table>
	</div>

	{/foreach}

</div>