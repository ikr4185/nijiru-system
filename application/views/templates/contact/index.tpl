<div class="section">

	{if !empty($result["msg"])}
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	{/if}

	<h2 class="sub_title">お問い合わせ</h2>

	<div class="section__inner">
		<div class="notice_text">
			<p>お問い合わせ内容は、ikr_4185へ直接届きます。<br>入力頂いたアドレス宛てに返信いたします。</p>
		</div>
	</div>

	<div class="section__inner">
		<form method="POST" action="" class="flat_form">
			<fieldset>
				<table class="flat_table">
					<tr>
						<th><label>お名前</label></th>
						<td class="input"><input type="text" name="name" required value="{if isset($result["contact"]["name"])}{$result["contact"]["name"]}{/if}"</td>
					</tr>
					<tr>
						<th><label>メールアドレス(返信先)</label></th>
						<td class="input"><input type="email" name="mail" required value="{if isset($result["contact"]["mail"])}{$result["contact"]["mail"]}{/if}"></td>
					</tr>
					<tr>
						<th><label>タイトル</label></th>
						<td class="input"><input type="text" name="subject" required value="{if isset($result["contact"]["subject"])}{$result["contact"]["subject"]}{/if}"></td>
					</tr>
					<tr>
						<th><label>本文</label></th>
						<td class="input">
							<textarea id="text" name="text" cols="40" rows="10" placeholder="" required>{if isset($result["contact"]["text"])}{$result["contact"]["text"]}{/if}</textarea>
						</td>
					</tr>
					<tr>
						<th></th>
						<td class="input">
							<input type="submit" name="submit" class="flat_form-button" value="確認">
						</td>
					</tr>
				</table>
			</fieldset>
		</form>
	</div>

</div>