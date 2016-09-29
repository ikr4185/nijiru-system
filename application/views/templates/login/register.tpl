<div class="secton1">

	{if !empty($result["msg"])}
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	{/if}

	<h2 class="sub_title">register</h2>

	<div id="login_form">

		<div class="notice_text">
			【ニジポプレゼントキャンペーン】<br>
			今なら新規登録で100ニジポをプレゼント！<br>
			この機会に是非ご登録ください<br>
			<span class="red">※ガチな個人情報は育良がたまげてしまうので、適当なやつにしといてください</span><br>
		</div>

		<form method="POST" action="" class="flat_form">
			<fieldset>
				<table class="flat_table">
					<tr>
						<th><label>user id<br>(ex. ikr_4185)</label></th>
						<td class="input"><input type="text" name="id"></td>
					</tr>
					<tr>
						<th><label>user name<br>(ex. いくら)</label></th>
						<td class="input"><input type="text" name="name"></td>
					</tr>
					<tr>
						<th><label>password</label></th>
						<td class="input"><input type="password" name="pass"></td>
					</tr>
					<tr>
						<th><label>possword<br>(reconfirm)</label></th>
						<td class="input"><input type="password" name="checkPass"></td>
					</tr>
					<tr>
						<th>submit<br><span class="red">notice: without warning</span></th>
						<td class="input"><button type="submit" name="submit" class="flat_form-button">Register</button></td>
					</tr>
				</table>

			</fieldset>
		</form>
	</div>

</div>