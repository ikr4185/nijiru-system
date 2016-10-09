<div class="section">

	<h2 class="sub_title">#scp-jp {date( 'Y-m-d (D)', strtotime($result["date"]))}</h2>

	<div class="section__inner text__wrap">
		{if !empty($result["before_date"])}<a href="{$result["logsLink"]}/log/{$result["before_date"]}" class="text__item">&lt; {$result["before_date"]}</a>{/if}
		<a href="{$result["logsLink"]}/" class="text__item">一覧へ</a>
		{if !empty($result["after_date"])}<a href="{$result["logsLink"]}/log/{$result["after_date"]}" class="text__item">{$result["after_date"]} &gt;</a>{/if}
	</div>

	<div class="section__inner">

		<table class="irc-table zebra-table">
			<tbody>
			{$result["html"] nofilter}
			</tbody>
		</table>

	</div>

	<div class="section__inner text__wrap">
		{if !empty($result["before_date"])}<a href="{$result["logsLink"]}/log/{$result["before_date"]}" class="text__item">&lt; {$result["before_date"]}</a>{/if}
		<a href="{$result["logsLink"]}/" class="text__item">一覧へ</a>
		{if !empty($result["after_date"])}<a href="{$result["logsLink"]}/log/{$result["after_date"]}" class="text__item">{$result["after_date"]} &gt;</a>{/if}
	</div>

</div>