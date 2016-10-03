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
		<h3 class="subhead">統計</h3>

		<table class="flat_table flat_table--narrow zebra-table tight-table">
			<tbody>
				<tr>
					<th>現在の総メンバー</th>
					<td>{$result["count"]} 人</td>
				</tr>
				<tr>
					<th>直近1ヶ月のアクティブメンバー</th>
					<td>{count($result["activeMembers"])} 人</td>
				</tr>
				<tr>
					<th>直近1週間のアクティブメンバー</th>
					<td>{count($result["activeMembersWeek"])} 人</td>
				</tr>
			</tbody>
		</table>
		<p>※ アクティブメンバー統計は、2016/10/02 より計測中です。</p>

	</div>

	<div class="section__inner">
		<h3 class="subhead">メンバー一覧</h3>

		{if !empty($result["siteMembers"]) }

			{*<div id="chart" style="width: 100%; height: 100%; border: 1px solid #aaa;"></div>*}

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
