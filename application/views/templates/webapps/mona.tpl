<div>

	<h1>MONA/JPY</h1>

	<p>今だいたい <span id="ask-title" style="font-weight: bold; font-size: 5em; color: #40c4e5"></span> 円</p>

	<div style="display: flex">

		<div style="margin-right: 50px;">
			<h2>チャート(だいたい30秒更新)</h2>
			<canvas id="chart"></canvas>
		</div>

		<div>
			<h2>詳細</h2>
			<table class="mb20">
				<tr>
					<th>買気配値</th>
					<td id="bid"></td>
				</tr>
				<tr>
					<th>売気配値</th>
					<td id="ask"></td>
				</tr>
				<tr>
					<th>終値</th>
					<td id="last"></td>
				</tr>
				<tr>
					<th>過去24時間の高値</th>
					<td id="high"></td>
				</tr>
				<tr>
					<th>過去24時間の安値</th>
					<td id="low"></td>
				</tr>
				<tr>
					<th>過去24時間の出来高</th>
					<td id="volume"></td>
				</tr>
			</table>

			<p class="mb20">
				・チャート更新まであと <span id="counter">0</span> カウント<br>
				・システム: <span id="status"></span><br>
			</p>

			<p>
				data from <a href="https://zaif.jp/trade_mona_jpy" style="color: #40c4e5">Zaif</a>.
			</p>
		</div>
	</div>
</div>