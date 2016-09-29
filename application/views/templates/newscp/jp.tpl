<h2 class="sub_title">最近作成されたJP記事</h2>


<div class="section">
	<div class="section__inner">
		<p>日本支部に投稿された最新記事の一覧です。</p>
		<p>SCP記事(-J除く)は SCP-Reader をご利用いただけます。</p>
	</div>
</div>

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

						{*もしTaleなら、colspanを2にする*}
						<td class="article-list_post__date article-list__data" {if ( !$item['isScpArticle'] )}colspan="2"{/if}>
							{$item['postDate']}
						</td>

						{*もしSCP記事なら、SCP-Readerへのリンクを貼る*}
						{if ( $item['isScpArticle'] )}
						<td class="article-list__scp-reader article-list__data"><a href="http://njr-sys.net/scpReader/scp/{$item['itenNumber']}">
								> Read in SCP Reader
						</td>
						{/if}

					</tr>

				{/foreach}

			</table>
		</div>

	{/foreach}

</div>