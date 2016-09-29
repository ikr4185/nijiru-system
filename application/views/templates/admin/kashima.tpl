{if !empty($result["msg"])}
	<div class="section">
		<div class="section__inner">
			<div class="notice_text">
				<p>{$result["msg"]}</p>
			</div>
		</div>
	</div>
{/if}

<div class="section">
	<h2 class="sub_title">KASHIMA-EXE Controller Panel</h2>

	<div class="section__inner">
		<h3 class="subhead">注意</h3>
		<p>※警告なしで実行されます　十分に注意して運用してください</p>
		<p>※開発中の機能です　十分に注意して運用してください</p>
		<p>※十分に注意して運用してください</p>
	</div>

	<div class="section__inner">
		<h3 class="subhead">KASHIMAシリーズ実行状況</h3>
		<table class="flat_table--narrow zebra-table">
			<tr>
				<td class="b">KASHIMA-EXE</td>
				<td>{$result["kashimaStatus"][0] nofilter}</td>
			</tr>
			<tr>
				<td class="b">KASHIMA-EXE-81</td>
				<td>{$result["kashimaStatus"][1] nofilter}</td>
			</tr>
		</table>
	</div>

	<div class="section__inner">
		<h3 class="subhead">KASHIMAメモリ使用状況</h3>
		<p>/home/njr-sys/public_html/cli/logs/KASHIMA_memory_used.log</p>
		<pre>
			{$result["memoryUsed"] nofilter}
		</pre>
	</div>

	<div class="section__inner">
		<h3 class="subhead">運用コマンド</h3>
		<table class="flat_table--narrow zebra-table">
			<tr>
				<td class="b">.quit <i>[下記停止パス]</i></td>
				<td colspan="2">Kashima-exeの緊急停止コマンド。停止後は、毎時1,16,31,46分に再起動。</td>
			</tr>
		</table>
	</div>

	<div class="section__inner">
		<h3 class="subhead">停止パス</h3>
		<p>{$result["pass"]}</p>
	</div>

	<div class="section__inner">
		<a href="http://njr-sys.net/admin/">戻る</a>
	</div>

	{*<form method="POST" action="/admin/kashima/" class="flat_form">*}
	{*<label><input type="radio" name="order" value="start" checked>start</label>*}
	{*<label><input type="radio" name="order" value="stop">stop</label>*}
	{*<label><input type="radio" name="order" value="reboot">reboot</label>*}
	{*<<button type="submit" name="submit" class="flat_form-button--download">実行</button>*}
	{*</form>*}
</div>
