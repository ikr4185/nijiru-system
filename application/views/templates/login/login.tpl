<div class="section">

	{if !empty($result["msg"])}
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	{/if}

	<h2 class="sub_title">login</h2>

	<div id="login_form">
		<form method="POST" action="" class="flat_form">
			<input type="hidden" name="redirectTo" value="{$result["redirectTo"]}">
			<fieldset>
				<table class="flat_table">
					<tr>
						<th><label>user id</label></th>
						<td class="input"><input type="text" name="id" value="{$result["sessId"]}"></td>
					</tr>
					<tr>
						<th><label>password</label></th>
						<td class="input"><input type="password" name="pass"></td>
					</tr>
					<tr>
						<th></th>
						<td class="input"><button type="submit" name="login" class="flat_form-button">login</button></td>
					</tr>
				</table>
			</fieldset>
		</form>
	</div>

	<h2 class="sub_title">logout</h2>

	<div id="login_form">
		<form method="POST" action="" class="flat_form">
			<fieldset>
				<table class="flat_table">
					<tr>
						<th></th>
						<td class="input"><button type="submit" name="logout" class="flat_form-button">logout</button></td>
					</tr>
				</table>
			</fieldset>
		</form>
	</div>

</div>