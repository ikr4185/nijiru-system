<div class="wrap" style="padding: 20px; background:white;">
	<div class="section__inner">
		<h3 class="subhead" style="color:#333">あなたのQRコード</h3>
		<img src="http://chart.apis.google.com/chart?cht=qr&chs=300x300&chl=http://njr-sys.net/njcwallet/send/{$result["address"]}" style="max-width: 300px;width: 100%;">
		<p style="color:#333">このQRコードを、あなた宛てに送金する相手に教えてください。</p>
		<p style="color:#333">または下記URLを教えてください。</p>
		<p style="color:#333;word-break: break-all">読み込まれるURL: <a href="/njcwallet/send/{$result["address"]}">http://njr-sys.net/njcwallet/send/{$result["address"]}</a>
		</p>
	</div>
</div>

