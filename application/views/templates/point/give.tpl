<div class="secton1">

	{if !empty($view["id"]) }

		{if !empty($result["msg"])}
			<div class="section__inner">
				<div class="notice_text">
					<p>{$result["msg"]}</p>
				</div>
			</div>
		{/if}
		<h2 class="sub_title">Give Neighbors the Nijipo !</h2>
		<div class="section__inner">
			<p>ニジポを隣人に喜捨しましょう。</p>
			<p><s>慈悲深い、50%のニジポ還元が得られることでしょう。</s></p>
			<p>※デバッグ用に一瞬停止中</p>
		</div>
		<div id="login_form">

			<form method="POST" action="" class="flat_form">
				<fieldset>
					<table class="flat_table">
						<tr>
							<th><label>To [ id ]</label></th>
							<td class="input"><input type="text" name="to"></td>
						</tr>
						<tr>
							{if !empty($view["tera_point"])}
								<th>
									<label>Nijipo Value<br>(your: {number_format($view["tera_point"])}T{number_format($view["point"])} Njp)</label>
								</th>
							{else}
								<th><label>Nijipo Value<br>(your: {number_format($view["point"])} Njp)</label></th>
							{/if}
							<td class="input"><input type="number" name="point"></td>
						</tr>
						<tr>
							<th>submit<br><span class="red">notice: without warning</span></th>
							<td class="input">
								<button type="submit" name="give" class="flat_form-button">Give</button>
							</td>
						</tr>
					</table>

				</fieldset>
			</form>
		</div>
		<h2 class="sub_title">Published Users</h2>
		<div class="section">
			<table class="flat_table--narrow">

				{if  !is_string($result["allUsers"]) }

					{foreach from=$result["allUsers"] item=item}
						<tr>
							<td class="b">
								id: {$item['id']}
							</td>
							<td>
								Name: {$item['user_name']}
							</td>
							<td>
								{if !empty($item["tera_point"])}
									{$item["tera_point"]}T {number_format($item['point'])} Njp
								{else}
									{number_format($item['point'])} Njp
								{/if}
							</td>
						</tr>
					{/foreach}

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
