{if !empty($result["msg"])}
	<div class="section">
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	</div>
{/if}

<div class="section">
	<h2 class="sub_title">SCP-JP Draft Threads</h2>
	<p>下書きスレッドを、「開始からの経過時刻」「最終コメントからの経過時刻」「スレッドの勢い」を付与して表示します。</p>
</div>

<div class="section scp-reader">

	{if !empty($result["items"]) }

		{if isset($result["items"]["error"]) }
			{$result["items"]["error"]}
		{else}

			{foreach from=$result["items"] item=item}

				<div class="section__head multi-box multi-box__bg">
					<div class="title">
						<a href="{$item['title-link']}">{$item['title']}</a>
					</div>
					<div class="b">
						by {$item['started-by']} / at {$item['started-time']}
					</div>
					<div class="multi-box__mini">
						Summary: {$item['description']}
					</div>
					<div class="multi-box__mini">
						Posts: {$item['posts']}
					</div>
					<div class="multi-box__mini">
						{if isset($item['hot'])}
							{$item['hot'][0] nofilter}<br>
							{$item['hot'][2] nofilter} by <span class="b">{$item['last-by']}</span><br>
							勢い(開始から/レス数): {$item['hot'][1] nofilter}
						{else}
							N/A
						{/if}
					</div>
				</div>
				<div class="section__inner multi-box multi-box__main">

				</div>


				{*last post by <span class="b">{$item['last-by']}</span><br>*}
				{*at <span class="b">{$item['last-time']}</span><br>*}

			{/foreach}

		{/if}
	{/if}

</div>