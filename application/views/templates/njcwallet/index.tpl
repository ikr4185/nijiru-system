<div class="section">

	<h2 class="sub_title">NjcWallet</h2>
	<div class="section__inner">
		<p>"仮想"仮想通貨「ニジルコイン」のWEBウォレット。</p>
		<p>価値を持たない通貨で安心の取引を。</p>
	</div>

	<div class="section__inner">
		<h3 class="subhead">FAQ</h3>
		<p class="mb10">Q. 仮想通貨なの？<br>A. いいえ、ただのDBレコードです。ブロックチェーンでも電子マネーでもありません。</p>
		<p class="mb10">Q. ニジポとどう違うの？<br>A. ニジルアカウントではなくアドレスに紐づく点が大きな違いです。これにより、システム上は、アドレスを保有する人なら誰にでも送る事が出来ます。</p>
		<p class="mb10">Q. でもお高いんでしょう？<br>A. そんなことはありません。と言うか仮想通貨と日本円交換とか有価ポイント発行みたいなのは、景品表示法とか改正資金決済法に引っかかるので無理です。</p>
		<p class="mb10">Q. 何に使うの？<br>A. しらん</p>
	</div>

	{if !empty($view["id"]) }

		{if !empty($result["msg"])}
			<div class="section__inner">
				<div class="notice_text">
					<p>{$result["msg"]}</p>
				</div>
			</div>
		{/if}
		<div class="section__inner">
			<div class="multi-box multi-box__bg">
				<h3 class="subhead">あなたのウォレットデータ</h3>
				<div class="multi-box__mini">
					<p class="b">メインアドレス:</p>
					<p style="word-break: break-all">{$result["address"]}</p>
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
			<div class="multi-box multi-box__bg">
				<h3 class="subhead">入金</h3>
				<div class="multi-box__mini">
					<p style="word-break: break-all"><span class="b">入金URL:</span>
						http://njr-sys.net/njcwallet/send/{$result["address"]}</p>
				</div>
				<div class="multi-box__mini">
					<p><a href="/njcwallet/qr">入金用QRコードの生成</a></p>
				</div>
				<div class="multi-box__mini">
					<p>入金URLをツイートしてみる</p>
					<a class="twitter-share-button" href="https://twitter.com/share" data-text="[わたしのNJCアドレス] " data-url="http://njr-sys.net/njcwallet/send/{$result["address"]}" data-hashtags="NijiruSystem">
						ツイート
					</a>
					{literal}
						<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
					{/literal}
				</div>
			</div>
		</div>
		<div class="section__inner">
			<div class="multi-box multi-box__bg">
				<h3 class="subhead">送金</h3>
				<div class="multi-box__mini">
					<p><a href="/njcwallet/send/">アドレス指定送金</a></p>
				</div>
				<div class="multi-box__mini">
					<p><a href="/njcwallet/send2wkid">WikidotID指定送金</a></p>
				</div>
			</div>
		</div>
		<h2 class="sub_title">履歴（最新50件）</h2>
		<div class="section__inner">

			<table class="tight-table zebra-table ">
				<tr>
					<th class="">相手</th>
					<th class="">移転額</th>
					<th class="">日時</th>
				</tr>
				{if (!empty($result["transactions"]))}
					{foreach from=$result["transactions"] item=item key=key}
						<tr>
							<td class="">{$item["from_address"]}</td>
							<td class="">{number_format($item["amount"])}</td>
							<td class="">{$item["created_at"]}</td>
						</tr>
					{/foreach}
				{else}
					<tr>
						<td class="" colspan="3">取引履歴がありません</td>
					</tr>
				{/if}
			</table>

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
