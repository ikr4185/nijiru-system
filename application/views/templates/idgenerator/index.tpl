<div class="secton">

	<h2 class="sub_title">ID Generator</h2>

	{if !empty($result["msg"])}
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	{/if}

	<div class="section__inner">
		<div class="id_gen_img">
			<img src="{$result["filePath"]}">
		</div>
	</div>

	<form method="POST" action="" class="flat_form">

		<div class="section__inner">
			<table class="flat_table">
				<tr>
					<th>Name: </th>
					<td><input type="text" name="name" Value="{$result["id_data"]["name"]}"></td>
				</tr>
				<tr>
					<th>Position: </th>
					<td><input type="text" name="staff" Value="{$result["id_data"]["staff"]}"><br>Field Agent, Researcher or Containment Specialist, etc</td>
				</tr>
				<tr>
					<th>ID-No.: </th>
					<td><input type="text" name="idnum" Value="{$result["id_data"]["idnum"]}"></td>
				</tr>
				<tr>
					<th>Security Clearance Level: </th>
					<td><input type="text" name="scl"  Value="{$result["id_data"]["scl"]}"></td>
				</tr>
				<tr>
					<th>Duty: </th>
					<td><input type="text" name="duty" Value="{$result["id_data"]["duty"]}"><br>諜報:Intelligence 実地評価:Field Evaluation 現地回収:Field Recovery</td>
				</tr>

				<tr>
					<th>locate: </th>
					<td><input type="text" name="locate" Value="{$result["id_data"]["locate"]}"><br>ex) ContainmentSite-19, ArmedArea-02</td>
				</tr>
				<!--顔写真（あれば。サイズは正方形にしてください。）：<input type="file" name="upfile" size="30"><br>-->
			</table>
		</div>


		<div class="section__inner">
			<h3 class="subhead">Generate</h3>

			<button type="submit" name="generate" class="flat_form-button--idgen">Generate</button>

			<div class="notice_text">
				<p>※あまりにバグが多いため、半角英数字のみの対応になっています。- 育良</p>
			</div>
		</div>

		<div class="section__inner">
			<h3 class="subhead">Form Data Save</h3>

			{if !empty($view["id"]) }
				<button type="submit" name="save" class="flat_form-button--idgen">Save</button>
			{else}
				<button type="button" class="flat_form-button--idgen-disable mb30">Save</button>

				<div class="login-guide">
					<h3 class="login-guide__notice">保存機能のご利用には、ログインが必要です</h3>
					<ul class="login-guide__guide">
						<li class="login-guide__li"><a href="/login/login/{$view["loginRedirect"]}">ログイン</a></li>
						<li class="login-guide__li"><a href="/login/register">新規登録する</a></li>
					</ul>
				</div>
			{/if}
		</div>

	</form>

</div>