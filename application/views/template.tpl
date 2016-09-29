<!DOCTYPE html>
<html>
{include 'file:/home/njr-sys/public_html/application/views/layouts/header.tpl'}
<body>

<!-- #container -->
<div id="container">

	<!--   main_visual   -->
	<section class="main_visual">
	</section>
	<!-- / .main_visual -->

	<div id="wrapper">

		<!-- #main_contents -->
		<div id="main_contents">
			<main>
				<article>
					{include file="$template"}
				</article>
			</main>
		</div>
		<!-- / #main_contents -->

		<!-- sidebar -->
		{include 'file:/home/njr-sys/public_html/application/views/layouts/sidebar.tpl'}
		<!-- / #sidebar -->

	</div>
	<!-- / #wrapper -->

</div>
<!-- / #container -->

<!-- footer -->
{include 'file:/home/njr-sys/public_html/application/views/layouts/footer.tpl'}
<!-- / footer -->

</body>
</html>