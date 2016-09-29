<div class="section">
	<h2 class="sub_title">削除基準以下の記事一覧</h2>

	{if !empty($result["msg"])}
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	{/if}

	<div class="section__inner">
		<table class="flat_table zebra-table log-table">

			<tr>
				<th>記事名</th>
				<th>勧告済み</th>
				<th>投稿日</th>
				<th>基準超え</th>
				<th>猶予期限</th>
				<th>リストから削除</th>
			</tr>

			{if !empty($result["LowVotes"])}

				{foreach $result["LowVotes"] as $info }
					<tr>
						<td><a href="{$info["url"]}">{$info["name"]}</a></td>
						<td>
							{if !$info["is_notified"]}
								<span class="red b">未勧告</span>
							{else}
								<span>勧告済み</span>
							{/if}
						</td>
						<td class="sp-word-wrap">{$info["post_date"]}</td>
						<td class="sp-word-wrap">{$info["fall_date"]}</td>
						<td class="sp-word-wrap">{$info["del_date"]}</td>
						<td>
							<form method="POST" action="/admin/confirm/" class="flat_form">
								<input type="hidden" name="lowVotesNumber" value="{$info["low_votes_number"]}">
								<button type="submit" name="submit" class="flat_form-button--download">削除</button>
							</form>
						</td>
					</tr>
				{/foreach}

			{else}
				<tr>
					<td colspan="6">not exists.</td>
				</tr>
			{/if}

		</table>
	</div>

	<div class="section__inner">
		<h3 class="subhead">付記</h3>
		<p><a href="http://ja.scp-wiki.net/lowest-rated-pages">評価の低い記事(日本支部サイト)</a></p>
		<p><a href="/admin/lvcUsers">メール配信設定</a></p>
	</div>

</div>


<div class="section">

	<h2 class="sub_title">KASHIMA Controller Panel</h2>
	<div class="section__inner">
		<a href="/admin/kashima/">管理画面</a>
	</div>


</div>