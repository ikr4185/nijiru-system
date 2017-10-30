<div class="section">

	{if !empty($result["msg"])}
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	{/if}

	<h2 class="sub_title">Create Hash</h2>

	<div id="login_form">
		<form method="POST" action="" class="flat_form">
			<input type="hidden" name="redirectTo" value="{$result["redirectTo"]}">
			<fieldset>
				<table class="flat_table">
					<tr>
						<th><label>seed</label></th>
						<td class="input"><input type="text" name="seed" value=""></td>
					</tr>
					<tr>
						<th></th>
						<td class="input"><button type="submit" name="submit" class="flat_form-button">login</button></td>
					</tr>
				</table>
			</fieldset>
		</form>
	</div>

	{if !empty($result["hash"])}
		<div class="section__inner">
			<div class="system_msg">
				<p>Hash:</p>
				<p>{$result["hash"]}</p>
			</div>
		</div>
	{/if}

</div>