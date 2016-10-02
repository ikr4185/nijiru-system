<div class="section">
	<h2 class="sub_title">SiteMembers</h2>

	<p>
		サイトメンバー情報です。<br>
		集計は毎時1分～5分頃に行われます。<br>
	</p>

	{*{if !empty($result["msg"])}*}
	{*<div class="section__inner">*}
	{*<div class="notice_text">*}
	{*<p>{$result["msg"]}</p>*}
	{*</div>*}
	{*</div>*}
	{*{/if}*}
</div>

<div class="section">

	<div class="section__inner">
		<h3 class="subhead">アクティブメンバー</h3>

		<p>
			過去一ヶ月に活動のあったサイトメンバーです(2016/10/02～ 計測中)。<br>
			アクティブメンバー数は<span class="b">{count($result["activeMembers"])}</span>人です。
		</p>

		{*{var_dump( $result["siteMembers"] )}*}

		{if !empty($result["activeMembers"]) }

			<a class="toggle-switch">リストを開く/閉じる</a>
			<table class="flat_table flat_table--narrow zebra-table tight-table hidden">
				<thead>
				<tr>
					<th>
						名前
					</th>
					<th>
						活動種別
					</th>
					<th>
						活動日
					</th>
				</tr>
				</thead>
				<tbody>
				{foreach $result["activeMembers"] as $member }
					<tr>
						<td>{$member["name"]}</td>
						<td>{$member["type"]}</td>
						<td>{$member["recent_date"]}</td>
					</tr>
				{/foreach}
				</tbody>
			</table>

		{else}

			<p>nothing data</p>

		{/if}

	</div>

</div>


<div class="section">

	<div class="section__inner">
		<h3 class="subhead">メンバー一覧</h3>

		<p>
			現在の総メンバー数は<span class="b">{$result["count"]}</span>人です。
		</p>

		{if !empty($result["siteMembers"]) }

			<a class="toggle-switch">リストを開く/閉じる</a>
			<table class="flat_table flat_table--narrow zebra-table tight-table">
				<thead>
				<tr>
					<th>
						<a href="/sitemembers">
							{if ($result["sortBy"]=="")}▼{/if}登録番号
						</a>
					</th>
					<th>
						名前
					</th>
					<th>
						<a href="/sitemembers/index/?date=desc">
							{if ($result["sortBy"]=="date")}▼{/if}登録日
						</a>
					</th>
					<th>
						<a href="/sitemembers/index/?recent=desc">
							{if ($result["sortBy"]=="recent")}▼{/if}最近の活動
						</a>
					</th>
					<th>
						<a href="/sitemembers/index/?count=desc">
							{if ($result["sortBy"]=="count")}▼{/if}SCP記事数
						</a>
					</th>
					<th>
						<a href="/sitemembers/index/?max=desc">
							{if ($result["sortBy"]=="max")}▼{/if}最高評価
						</a>
					</th>
					<th>
						<a href="/sitemembers/index/?ave=desc">
							{if ($result["sortBy"]=="ave")}▼{/if}平均評価
						</a>
					</th>
				</tr>
				</thead>
				<tbody>
				{foreach $result["siteMembers"] as $member }
					<tr>
						<td>{$member["id"]}</td>
						<td>{$member["name"]}</td>
						<td>{$member["since"]}</td>
						<td>{$member["recentDate"]}</td>
						<td>{$member["articleCount"]}</td>
						<td>
							<a href="http://ja.scp-wiki.net/{$member["maxVoteArticle"]}">
								{$member["maxVoteArticle"]}
							</a><br>
							({$member["maxVote"]})
						</td>
						<td>{$member["averageVote"]}</td>
					</tr>
				{/foreach}
				</tbody>
			</table>

		{else}

			<p>nothing data</p>

		{/if}

	</div>

</div>
