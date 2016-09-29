<div class="section">
	<h2 class="sub_title">Vote History</h2>

	<p>
		過去のVoteの統計的な分析をします。
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
		<h3 class="subhead">全体</h3>
		<p>SCP-JP記事総数: <span class="b">{$result["totalCount"]}</span></p>
		<p>全体平均Vote: <span class="b">{$result["totalAverageVote"]}</span></p>
	</div>

	<div class="section__inner">
		<h3 class="subhead">Vote分析</h3>

		{if !empty($result["voteHistory"]) }

			<div id="chart" style="width: 100%; height: 100%; border: 1px solid #aaa;"></div>

			<table class="flat_table flat_table--narrow zebra-table tight-table">
				<thead>
				<tr>
					<th>月日</th>
					<th>記事数</th>
					<th>平均値</th>
					<th>中央値</th>
				</tr>
				</thead>
				<tbody>
				{foreach $result["voteHistory"] as $item }
					<tr>
						<td class="j_dates">{$item["date"]}</td>
						<td class="j_count">{$item["count"]}</td>
						<td class="j_avgs">{$item["avg"]}</td>
						<td class="j_meds">{$item["med"]}</td>
					</tr>
				{/foreach}
				</tbody>
			</table>

		{else}

			<p>nothing data</p>

		{/if}

	</div>

</div>


