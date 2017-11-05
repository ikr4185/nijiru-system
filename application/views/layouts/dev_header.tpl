<head lang="jp">
	<meta charset="utf-8">
	{if !empty($view["page_title"])}
		<title>{$view["page_title"]} | Nijiru System</title>
	{else}
		<title>Nijiru System</title>
	{/if}
	<meta name="keywords" content="nijiru,scp">
	<meta name="description" content="">

	<link rel="stylesheet" type="text/css" href="http://{$view["serverName"]}/{$view["css"]}">
	<meta name="viewport" content="width=device-width,user-scalable=0">

	{if !$view["noJquery"]}
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	{/if}

	{if !empty($view["jsPathArray"])}
		{foreach $view["jsPathArray"] as $jsPath}
			<script type="text/javascript" src="{$jsPath}"></script>
		{/foreach}
	{/if}

	{if !empty($view["header"])}
		{$view["header"] nofilter}
	{/if}

</head>