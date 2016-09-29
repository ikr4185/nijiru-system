<div class="section">

	<h2 class="sub_title">内容確認</h2>

	<div class="section__inner">
		<div class="notice_text">
			<p>お問い合わせ内容をご確認ください</p>
		</div>
	</div>

	<div class="section__inner">
		<form method="POST" action="" class="flat_form">
			<fieldset>
				<table class="flat_table">
					<tr>
						<th><label>お名前</label></th>
						<td class="input">
							{if isset($result["contact"]["name"])}{$result["contact"]["name"]}{/if}
							<input type="hidden" name="contact[$this->name]" id="contact[$this->name]">
						</td>
					</tr>
					<tr>
						<th><label>メールアドレス</label></th>
						<td class="input">
							{if isset($result["contact"]["mail"])}{$result["contact"]["mail"]}{/if}
							<input type="hidden" name="contact[$this->mail]" id="contact[$this->mail]">
						</td>
					</tr>
					<tr>
						<th><label>タイトル</label></th>
						<td class="input">
							{if isset($result["contact"]["subject"])}{$result["contact"]["subject"]}{/if}
							<input type="hidden" name="contact[$this->subject]" id="contact[$this->subject]">
						</td>
					</tr>
					<tr>
						<th><label>本文</label></th>
						<td class="input">
							{if isset($result["contact"]["text"])}{$result["contact"]["text"]}{/if}
							<input type="hidden" name="contact[$this->text]" id="contact[$this->text]">
						</td>
					</tr>
					<tr>
						<th></th>
						<td class="input">
							<input type="submit" name="submit" class="flat_form-button" value="送信">
						</td>
					</tr>
				</table>
			</fieldset>
		</form>
	</div>

</div>