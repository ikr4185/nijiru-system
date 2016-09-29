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
	<h2 class="sub_title">SCP-JP Drafts Forum</h2>
	<p>日本支部フォーラムの下書き批評カテゴリを、最新順で表示します。</p>
</div>

<div class="section scp-reader">

{if !empty($result["items"]) }

	{foreach from=$result["items"] item=item}


			<div class="section__head multi-box multi-box__bg">
				<div>
					<a href="{$item['link']}">
						<span class="title">{$item['title']}</span>
					</a>
				</div>
				<div class="b">
					by {$item['user']} : {$item['date']}
				</div>
				<div class="multi-box__mini">
				</div>
			</div>
			<div class="section__inner multi-box multi-box__main">
				{$item['contents'] nofilter}
			</div>

	{/foreach}

{else}
	<p>Unknown Error</p>
{/if}


</div>

