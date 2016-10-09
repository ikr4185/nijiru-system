<div class="section">

	<h2 class="sub_title">#site8181 {date( 'Y-m-d (D)', strtotime($result["date"]))}</h2>

	<div class="section__inner">
		<div class="flat_form--search">
			<input class="flat_form--search__input" type="text" id="search" placeholder="search" value="">
			<input class="flat_form--search__button" type="button" value="Search">
		</div>
	</div>


	<div class="section__inner text__wrap">
		{if !empty($result["before_date"])}<a href="/irc/log81/{$result["before_date"]}" class="text__item">&lt; {$result["before_date"]}</a>{/if}
		<a href="{$result["logsLink"]}/" class="text__item">一覧へ</a>
		{if !empty($result["after_date"])}<a href="/irc/log81/{$result["after_date"]}" class="text__item">{$result["after_date"]} &gt;</a>{/if}
	</div>

	<div class="section__inner">

		<table class="irc-table zebra-table">
			<tbody>
			{foreach from=$result["logs"] item=item key=key}
				<tr id="js_irc_log_{$key}" class="js_irc_log {$item["specialColor"]}">
					<td class="nowrap">
						{$item["datetime"]}
					</td>
					<td class="nowrap">
						<span class="b {$item["color"]}">&lt;{$item["nick"]}&gt;</span>
					</td>
					<td class="wrap irc-table__message">{$item["message"]}<br>
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>

	</div>

	<div class="section__inner text__wrap">
		{if !empty($result["before_date"])}<a href="/irc/log81/{$result["before_date"]}" class="text__item">&lt; {$result["before_date"]}</a>{/if}
		<a href="{$result["logsLink"]}/" class="text__item">一覧へ</a>
		{if !empty($result["after_date"])}<a href="/irc/log81/{$result["after_date"]}" class="text__item">{$result["after_date"]} &gt;</a>{/if}
	</div>

</div>