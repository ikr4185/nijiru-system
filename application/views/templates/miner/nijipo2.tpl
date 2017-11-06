<div class="secton1">
	<h2 class="sub_title">Njp/Monero Mining</h2>

	<div class="section__inner">
		<p>
			<a href="https://ja.wikipedia.org/wiki/Monero">仮想通貨 Monero</a> のマイニングを行い、<br>
			NjrSysサーバ維持費を小賢しく稼ぎつつ、Nijipoを増やします。<br>
		</p>
		<p class="red">
			スマホ/PCのCPUリソースを用いてマイニングを行うため、<br>
			バッテリー消費や電気代にご注意ください。
		</p>
	</div>

	<div class="section__inner">
		{if empty($view["id"]) }
			<div class="login-guide">
				<h3 class="login-guide__notice">ニジポ付与には、ログインが必要です</h3>
				<ul class="login-guide__guide">
					<li class="login-guide__li"><a href="/login/login/{$view["loginRedirect"]}">ログイン</a></li>
					<li class="login-guide__li"><a href="/login/register">新規登録する</a></li>
				</ul>
			</div>
		{/if}
	</div>

	<div class="section__inner">
		<button id="start" class="flat_form-button">採掘開始</button>
		<button id="stop" class="flat_form-button" style="display:none;">採掘停止</button>
		<span id="user-number" style="display: none;">{$result["userNum"]}</span>
	</div>

	<div class="section__inner">
		<h3 class="subhead">調整</h3>
		<p>スレッド数: <span id="threads">4</span></p>
		<button id="threads-up">増加</button>
		<button id="threads-down">減少</button>
	</div>

	<div class="section__inner">
		<p>hashesPerSecond: <span id="hashes-per-second"></span></p>
		<p>totalHashes: <span id="total-hashes"></span></p>
		<p>acceptedHashes: <span id="accepted-hashes"></span></p>
	</div>

	<div class="section__inner">
		<h3 class="subhead">成果</h3>
		<p>Total Payout: <span id="payout" class="b"></span> 円</p>
		<p>Njp Payout: <span id="njp-payout" class="b"></span> Njp</p>
		<p>※ Njp は、採掘停止時点で付与されます</p>
	</div>
</div>