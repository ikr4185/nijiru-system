<!--global header-->
<header>
	<div class="heading">
		<h2 class="heading__logo"><a href="http://njr-sys.net/"><img src="http://{$view["serverName"]}/{$view["imgDir"]}common/NIJIRU-logo.png" alt="Nijiru System"/></a></h2>
		<p class="heading__description">Nijiru System</p>
		<a class="heading__menu-link" href="#sidebar" alt="menu"></a>
	</div>
	<!-- / .inner1 -->
</header>

<!--   global footer   -->
<footer>
	<small>
		Unless otherwise stated, the content of this pageis licensed under <a href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution-ShareAlike 3.0 License</a>,
		<br>&copy; 2015 育良
	</small>
</footer>

{if !empty($view["jsPathArray"])}
	{foreach $view["jsPathArray"] as $jsPath}
		<script type="text/javascript" src="{$jsPath}"></script>
	{/foreach}
{/if}
