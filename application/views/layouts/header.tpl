<head lang="jp">
	<meta charset="utf-8">
	{if !empty($view["page_title"])}
		<title>{$view["page_title"]} | Nijiru System</title>
	{else}
		<title>Nijiru System</title>
	{/if}
	<meta name="keywords" content="nijiru,scp">
	<meta name="description" content="">

	<meta name="viewport" content="width=device-width,user-scalable=0">
	<link rel="stylesheet" type="text/css" href="http://{$view["serverName"]}/{$view["css"]}">
	<link rel="shortcut icon" href="http://{$view["serverName"]}/{$view["icon"]}">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	<script src="/application/views/assets/js/smooth_scroll.js"></script>

	<!-- Google Analytics -->
	{literal}
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

		ga('create', 'UA-84647256-1', 'auto');
		ga('send', 'pageview');

	</script>
	{/literal}

</head>