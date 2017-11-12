<div class="section">

	{if !empty($view["id"]) }

		{if !empty($result["msg"])}
			<div class="section__inner">
				<div class="notice_text">
					<p>{$result["msg"]}</p>
				</div>
			</div>
		{/if}
		<h2 class="sub_title">Send NJC To WikidotID</h2>
		<div class="section__inner">
			<p>WikidotID宛に、ニジルコインを送金します。</p>
		</div>
		<div class="section__inner">
			<div class="multi-box multi-box__bg">
				<h3 class="subhead">あなたのウォレットデータ</h3>
				<div class="multi-box__mini">
					<span class="b">ウォレットアドレス:</span><br>
					{$result["fromAddress"]}
				</div>
				{if $result["totalAmount"]!==false}
					<div class="multi-box__mini"><span class="b">保有:</span> {number_format($result["totalAmount"])} NJC
					</div>
				{else}
					<div class="multi-box__mini"><span class="b">保有:</span> まだ NJC を保有していません</div>
				{/if}
			</div>
		</div>
		<div class="section__inner">

			<form method="POST" action="" class="flat_form">
				<fieldset>
					<table class="flat_table">
						<tr>
							<th><label>送金先 WikidotID</label></th>
							<td class="input"><input type="text" name="wikidotId""></td>
						</tr>
						<tr>
							<th><label>送金額</label></th>
							<td class="input"><input type="number" name="amount" value="0"></td>
						</tr>
						<tr>
							<th>submit<br><span class="red">注意: 確認無しで送金します。</span></th>
							<td class="input">
								<button type="submit" name="send" class="flat_form-button">送金</button>
							</td>
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
