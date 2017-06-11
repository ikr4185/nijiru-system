{if $result["isStaff"] }
<div class="section">
	<p>welcome personal.</p>
	<p>if you want to view page logs, access the url directly as shown below.</p>
	<p>ex) http://njr-sys.net/fc2log/view/20170611_230657</p>
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