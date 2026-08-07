<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

</main>

<footer class="lemonator-footer">
	<div class="lemonator-footer__grid">
		<div class="lemonator-footer__brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img src="<?php echo esc_url( lemonator_asset( 'Lemonator-Law.svg' ) ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="144" height="14">
			</a>
			<p>The Law Offices of Shahram Yermian helps California vehicle owners pursue lemon law claims against manufacturers.</p>
			<div class="lemonator-footer__social">
				<a href="#" aria-label="Facebook"><span>Facebook</span></a>
				<a href="#" aria-label="X"><span>X</span></a>
				<a href="#" aria-label="Yelp"><span>Yelp</span></a>
			</div>
		</div>

		<div class="lemonator-footer__col">
			<h3>Practice Areas</h3>
			<ul>
				<li><a href="#vehicles">Defective Vehicles</a></li>
				<li><a href="#vehicles">RV &amp; Motorhome Claims</a></li>
				<li><a href="#vehicles">Motorcycle Lemon Law</a></li>
				<li><a href="#vehicles">Aircraft &amp; Boat Claims</a></li>
				<li><a href="#process">Manufacturer Buybacks</a></li>
			</ul>
		</div>

		<div class="lemonator-footer__col">
			<h3>Areas We Serve</h3>
			<ul>
				<li><a href="#service-area">Santa Monica, CA 90404</a></li>
				<li><a href="#service-area">West Hills, CA 91307</a></li>
				<li><a href="#service-area">Commerce, CA 90022</a></li>
			</ul>
			<p class="lemonator-footer__note">&amp; across California</p>
		</div>

		<div class="lemonator-footer__col">
			<h3>Get In Touch</h3>
			<ul class="lemonator-footer__contact">
				<li>3200 Santa Monica Blvd. Santa Monica, CA 90404</li>
				<li><a href="tel:+18665053666">(866) 505-3666</a></li>
				<li>Mon–Fri, 9:00 AM – 5:00 PM</li>
			</ul>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
