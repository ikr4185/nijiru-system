<div id="sidebar">
	<aside>

		<h2 class="sub_title">user</h2>
		<div class="side_section">
			{if !empty($view["id"]) }
				User: <span class="b">{$view["user_name"]}</span>({$view["id"]})<br>
				{if !empty($view["tera_point"])}
					Nijipo: <span class="b">{number_format($view["tera_point"])}T{number_format($view["point"])} Njp</span><br>
				{else}
					Nijipo: <span class="b">{number_format($view["point"])}</span><br>
				{/if}
			{else}
				User: <span class="b">guest</span>
			{/if}
		</div>

		<h2 class="sub_title">User Settings</h2>
		<div class="side_section">
			<a href="http://{$view["serverName"]}/login/login">Login/Logout</a><br>
			<a href="http://{$view["serverName"]}/login/register">Register</a><br>
			{if !empty($view["id"]) }
				<a href="http://{$view["serverName"]}/login/update">Update</a><br>
				<a href="http://{$view["serverName"]}/point/give">Give Someone Nijipo</a><br>
			{/if}
		</div>

		<h2 class="sub_title">contents</h2>
		<div class="side_section">
			<h3 class="mb10">IRCbot "KASHIMA-EXE"</h3>
			<div class="mb10 pl10">
				<a href="http://{$view["serverName"]}/irc">IRC Reader #scp-jp</a><br>
				<a href="http://{$view["serverName"]}/irc/logs81">IRC Reader #site8181</a><br>
				<a href="http://{$view["serverName"]}/irc/draftreserve/" class="side_section__sub">下書き批評予約</a><br>
				<a href="http://{$view["serverName"]}/irc/search/" class="side_section__sub">#scp-jp 検索</a><br>
			</div>

			<h3 class="mb10">SCP記事データベース</h3>
			<div class="mb10 pl10">
				<a href="http://{$view["serverName"]}/scpReader">SCP-JP Reader</a><br>
				<a href="http://{$view["serverName"]}/scpReader/log" class="side_section__sub">SCP-JP Reading Log</a><br>
				<a href="http://{$view["serverName"]}/scpReader/favorites" class="side_section__sub">SCP-JP My Favorite SCP</a><br>
				<a href="http://{$view["serverName"]}/scpReader/search" class="side_section__sub">SCP-JP 曖昧検索</a><br>
			</div>

			<h3 class="mb10">ランダムSCP</h3>
			<div class="mb10 pl10">
				<a href="http://{$view["serverName"]}/randScp" target="_blank">EN記事 ランダムSCP (別窓)</a><br>
				<a href="http://{$view["serverName"]}/randScp/jp" target="_blank">JP記事 ランダムSCP (別窓)</a><br>
			</div>

			<h3 class="mb10">投稿者名を除外した新着記事一覧</h3>
			<div class="mb10 pl10">
				<a href="http://{$view["serverName"]}/newSCP">最近作成された記事</a><br>
				<a href="http://{$view["serverName"]}/newScp/jp">最近作成されたJP記事</a><br>
			</div>

			<h3 class="mb10">サイト統計</h3>
			<div class="mb10 pl10">
				<a href="http://{$view["serverName"]}/sitemembers/">サイトメンバー一覧</a> <span class="red">new</span><br>
				<a href="http://{$view["serverName"]}/sitemembers/memberHistory" class="side_section__sub">SiteMember History</a> <span class="red">new</span><br>
				<a href="http://{$view["serverName"]}/sitemembers/voteHistory" class="side_section__sub">Vote History</a> <span class="red">new</span><br>
			</div>

			<h3 class="mb10">フォーラム</h3>
			<div class="mb10 pl10">
				<a href="http://{$view["serverName"]}/forum">フォーラム新着</a><br>
				<a href="http://{$view["serverName"]}/forum/drafts" class="side_section__sub">下書きカテゴリ一覧</a><br>
				<a href="http://{$view["serverName"]}/forum/draftsThread" class="side_section__sub">下書きスレ新着</a><br>
			</div>

			<h3 class="mb10">ユーティリティ</h3>
			<div class="mb10 pl10">
				<a href="http://{$view["serverName"]}/idGenerator">SCP財団IDジェネレーター</a><br>
				<a href="http://{$view["serverName"]}/webapps/scpsearch">SCP-Search</a><br>
				<a href="http://{$view["serverName"]}/download/">Nijiru Downloader</a><br>
				<a href="http://{$view["serverName"]}/storage/">Nijiru Storage</a><br>
			</div>

		</div>

		<h2 class="sub_title">contact</h2>
		<div class="side_section">
			<a href="http://{$view["serverName"]}/contact/">お問い合わせ</a><br>
		</div>

		<h2 class="sub_title">links</h2>
		<div class="side_section">
			<a href="http://ja.scp-wiki.net/forum:recent-posts">日本支部フォーラム：最近の投稿</a><br>
			<a href="http://ja.scp-wiki.net/author:ikr-4185">エージェント・育良の人事ファイル</a><br>
			<a href="https://twitter.com/ikr_4185">Twitter@ikr_4158</a><br>
		</div>

		<h2 class="sub_title">System</h2>
		<div class="side_section">
			<a href="http://{$view["serverName"]}/admin/login">Nijiru Administrator</a><br>
			<a href="https://github.com/ikr4185/nijiru-system">GitHub ikr4185/nijiru-system</a><br>
		</div>
	</aside>
</div>