<div class="secton1">

	{if !empty($view["id"]) }

		{if !empty($result["msg"])}
			<div class="section__inner">
				<div class="notice_text">
					<p>{$result["msg"]}</p>
				</div>
			</div>
		{/if}

	<h2 class="sub_title">Change Name</h2>

	<div id="login_form">

		<div class="notice_text red">
			※ガチな個人情報は育良がたまげてしまうので、適当なやつにしといてください
		</div>

		<form method="POST" action="" class="flat_form">
			<fieldset>
				<table class="flat_table">
					<tr>
						<th><label>user id</label></th>
						<td class="input"><span class="bold">{$view["id"]}</span></td>
					</tr>
					<tr>
						<th><label>user name</label></th>
						<td class="input"><span class="bold">{$view["user_name"]}</span></td>
					</tr>
					<tr>
						<th><label>new user name</label></th>
						<td class="input"><input type="text" name="newName"></td>
					</tr>
					<tr>
						<th><label>check password</label></th>
						<td class="input"><input type="password" name="pass"></td>
					</tr>
					<tr>
						<th>submit<br><span class="red">notice: without warning</span></th>
						<td class="input"><button type="submit" name="submit_user_data" class="flat_form-button">Update</button></td>
					</tr>
				</table>
			</fieldset>
		</form>

	</div>

	<div id="login_form">

		<h2 class="sub_title">Change Publication</h2>

		<form method="POST" action="" class="flat_form">
			<fieldset>
				<table class="flat_table">
					<tr>
						<td>
							<input type="radio" name="publication" id="publication-2" value="2" {if $result["is_public"]}checked="checked"{/if}>
							<label for="publication-2" class="public flat_form-label">公開</label>
						</td>
						<td>
							<input type="radio" name="publication" id="publication-1" value="1" {if !$result["is_public"]}checked="checked"{/if}>
							<label for="publication-1" class="private flat_form-label">非公開</label>
						</td>
					</tr>
					<tr>
						<th><label>check password</label></th>
						<td class="input"><input type="password" name="pass"></td>
					</tr>
					<tr>
						<th>submit<br><span class="red">notice: without warning</span></th>
						<td class="input"><button type="submit" name="submit_publication" class="flat_form-button">Update</button></td>
					</tr>
				</table>
			</fieldset>
		</form>
	</div>

	{else}

		<div class="login-guide">
			<h3 class="login-guide__notice">ご利用いただくには、ログインが必要です</h3>
			<ul class="login-guide__guide">
				<li class="login-guide__li"><a href="/login/login/{$view["loginRedirect"]}">ログイン</a></li>
				<li class="login-guide__li"><a href="/login/register">新規登録する</a></li>
			</ul>
		</div>

	{/if}


</div>