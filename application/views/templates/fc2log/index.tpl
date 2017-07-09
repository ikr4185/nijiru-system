{if $result["isStaff"] }
<div class="section">
	<p style="font-weight: bold">AUTHENTICATION ACCEPTED.</p>
</div>
<div class="section">
	<p>backup logs index</p>
	<p><a href="/fc2log/links/">Access</a></p>
</div>
<div class="section">
	<p>csv file</p>
	<p><a href="/fc2log/csv/">download</a></p>
</div>
{/if}


<div class="section">

	<h2 class="sub_title">Staff Authentication</h2>

	<div id="login_form">
		<form method="POST" action="/fc2log/auth" class="flat_form">
			<fieldset>
				<table class="flat_table">
					<tr>
						<th><label>password</label></th>
						<td class="input"><label><input type="password" name="staff_pass"></label></td>
					</tr>
					<tr>
						<th></th>
						<td class="input"><input type="submit" name="login" class="flat_form-button" value="login"></td>
					</tr>
				</table>
			</fieldset>
		</form>
	</div>
</div>