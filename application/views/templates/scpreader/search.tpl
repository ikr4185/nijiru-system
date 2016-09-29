<div class="section">
	<h2 class="sub_title">SCP-JP あいまい検索</h2>
	<p>
		「なんかあんな感じの…なんだっけ…」という疑問にお応えします。<br>
		※ 検索結果が多い場合、アイテムナンバー上位100件までを表示します
	</p>
</div>

<div class="section">
	<div class="flat_form">
		<form method="POST" action="" class="flat_form">
			<fieldset>
				<table class="flat_table">
					<tr>
						<th><label>キーワード</label></th>
						<td class="input"><input type="text" name="search" id="search" placeholder="search" value="{if !empty($result["search"])}{$result["search"]}{/if}"></td>
					</tr>
					<tr>
						<th></th>
						<td class="input"><button type="submit" name="submit" class="flat_form-button">search</button></td>
					</tr>
				</table>
			</fieldset>
		</form>
	</div>
</div>

<div class="section">

	{if !empty($result["msg"])}
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	{/if}

</div>

{if !empty($result["records"])}


	{$i=0}

	{foreach from=$result["records"] item=record}

		{if $i==0}
			<h2 class="sub_title">[タイトル]</h2>
		{elseif $i==1}
			<h2 class="sub_title">[特別収容プロトコル]</h2>
		{elseif $i==2 }
			<h2 class="sub_title">[説明]</h2>
		{elseif $i==3 }
			<h2 class="sub_title">[作者]</h2>
		{elseif $i==4 }
			<h2 class="sub_title">[タグ]</h2>
		{/if}
		{$i=$i+1}


		{if !empty($record)}

			<div class="section">

				{foreach from=$record item=item}

					{*5つおきに改行*}
					{if ( 0 === ($item["scp_num"] % 5) )}
						<br>
					{/if}

					{*100おきに題字*}
					{if ( 0 === ($item["scp_num"] % 100) )}
						<br>
						<h3 class="subhead">{$item["item_number"]}～</h3>
					{/if}

					<ul>
						<li id="scp-{sprintf('%03d', $item["scp_num"])}-jp" class="scp-reader_li">
							<a href="http://ja.scp-wiki.net/scp-{sprintf('%03d', $item["scp_num"])}-jp">
								{$item["item_number"]}
							</a>
							- {$item["title"] nofilter}
						</li>
					</ul>
				{/foreach}

			</div>

		{else}
			<div class="section">
				Nothing Available Data
			</div>
		{/if}


	{/foreach}

{else}
	<div class="section">
		Nothing Available Data
	</div>
{/if}



