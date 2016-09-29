<div class="section">
	<h2 class="sub_title">Site Member History</h2>

	<p>
		サイトメンバーの登録推移です。
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
		<h3 class="subhead">Member分析</h3>

		{if !empty($result["memberHistory"]) }

			<div id="chart" style="width: 100%; height: 100%; border: 1px solid #aaa;"></div>

			<table class="flat_table flat_table--narrow zebra-table tight-table">
				<thead>
				<tr>
					<th>月日</th>
					<th>月間登録数</th>
					<th>累計登録数</th>
				</tr>
				</thead>
				<tbody>
				{foreach $result["memberHistory"] as $item }
					<tr>
						<td class="j_dates">{$item["date"]}</td>
						<td class="j_count">
							{$item["count"]}
							<div class="j_newbies hidden">{$item["newbies"]}</div>
						</td>
						<td class="j_allMemberCount">{$item["allMemberCount"]}</td>
					</tr>
				{/foreach}
				</tbody>
			</table>

		{else}

			<p>nothing data</p>

		{/if}

	</div>

</div>


