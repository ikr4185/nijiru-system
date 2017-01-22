<div class="secton">

	<h2 class="sub_title">下書き批評予約 {date( 'Y-m-d (D)', strtotime($result["date"]))}</h2>

	{if !empty($result["msg"])}
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	{/if}

	<div class="section__inner">

		<table class="irc-table zebra-table">
			<tbody>
			{foreach from=$result["reserve"] item=item key=key}
				<tr>
					<td class="wrap irc-table__message">{str_replace($result["date"],"",$item[0])}</td>
					<td class="wrap irc-table__message">{$item[1]}</td>
					<td class="wrap irc-table__message">{$item[2]}</td>
					<td class="wrap irc-table__message"><a href="{$item[3]}" target="_blank">{$item[3]}</a></td>
				</tr>
			{/foreach}
			</tbody>
		</table>

	</div>

	<form method="POST" action="" class="flat_form">

		<div class="section__inner">
			<table class="flat_table">
				<tr>
					<th>Name: </th>
					<td><input type="text" name="name" Value="{$result["data"]["name"]}"></td>
				</tr>
				<tr>
					<th>Title: </th>
					<td><input type="text" name="title" Value="{$result["data"]["title"]}"></td>
				</tr>
				<tr>
					<th>URL: </th>
					<td><input type="text" name="url" Value="{$result["data"]["url"]}"></td>
				</tr>
			</table>
		</div>

		<div class="section__inner">
			<h3 class="subhead">Save</h3>
				<button type="submit" name="submit" class="flat_form-button" value="1">Save</button>
		</div>

	</form>

</div>