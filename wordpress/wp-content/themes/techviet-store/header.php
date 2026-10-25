<?php
/**
 * Header.
 *
 * @package TechVietStore
 */

$tv_phone   = techviet_mod( 'techviet_phone' );
$tv_email   = techviet_mod( 'techviet_email' );
$tv_address = techviet_mod( 'techviet_address' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="tv-skip-link" href="#tv-main">Bỏ qua điều hướng, đến nội dung chính</a>

<div class="tv-topbar">
	<div class="tv-container tv-topbar__inner">
		<p class="tv-topbar__notice">
			<span class="tv-topbar__dot" aria-hidden="true"></span>
			<?php echo esc_html( techviet_mod( 'techviet_announcement' ) ); ?>
		</p>
		<div class="tv-topbar__meta">
			<a href="tel:<?php echo esc_attr( techviet_tel( $tv_phone ) ); ?>">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.2.2 2.4.6 3.6.1.4 0 .8-.2 1l-2.3 2.2z" fill="currentColor"/></svg>
				<?php echo esc_html( $tv_phone ); ?>
			</a>
			<a class="tv-topbar__email" href="mailto:<?php echo esc_attr( $tv_email ); ?>">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 5h18v14H3V5zm2 2v.4l7 4.7 7-4.7V7H5zm14 10V9.9l-7 4.6-7-4.6V17h14z" fill="currentColor"/></svg>
				<?php echo esc_html( $tv_email ); ?>
			</a>
		</div>
	</div>
</div>

<header class="tv-header" id="tv-header">
	<div class="tv-container tv-header__inner">
		<a class="tv-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="TechViet Store — Trang chủ">
			<img class="tv-logo__mark" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-mark.svg' ); ?>" alt="" width="46" height="46" decoding="async">
			<span class="tv-logo__text">
				<strong>TechViet</strong>
				<span>Store</span>
			</span>
		</a>

		<nav class="tv-nav" aria-label="Menu chính">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'tv-nav-list',
					'fallback_cb'    => 'techviet_menu_fallback',
					'depth'          => 2,
				)
			);
			?>
		</nav>

		<div class="tv-header__actions">
			<a class="tv-hotline" href="tel:<?php echo esc_attr( techviet_tel( $tv_phone ) ); ?>">
				<span class="tv-hotline__icon" aria-hidden="true">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.2.2 2.4.6 3.6.1.4 0 .8-.2 1l-2.3 2.2z" fill="currentColor"/></svg>
				</span>
				<span class="tv-hotline__text">
					<span>Hotline tư vấn</span>
					<strong><?php echo esc_html( $tv_phone ); ?></strong>
				</span>
			</a>
			<a class="tv-btn tv-btn-primary tv-header__cta" href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>">Nhận tư vấn</a>
			<button class="tv-menu-toggle" type="button" aria-expanded="false" aria-controls="tv-mobile-nav" aria-label="Mở menu">
				<span></span><span></span><span></span>
			</button>
		</div>
	</div>

	<div class="tv-mobile-nav" id="tv-mobile-nav" hidden>
		<nav aria-label="Menu di động">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'tv-mobile-nav__list',
					'fallback_cb'    => 'techviet_menu_fallback',
					'depth'          => 2,
				)
			);
			?>
		</nav>
		<div class="tv-mobile-nav__contact">
			<a class="tv-btn tv-btn-primary" href="tel:<?php echo esc_attr( techviet_tel( $tv_phone ) ); ?>">Gọi <?php echo esc_html( $tv_phone ); ?></a>
			<p><?php echo esc_html( $tv_address ); ?></p>
			<p><a href="mailto:<?php echo esc_attr( $tv_email ); ?>"><?php echo esc_html( $tv_email ); ?></a></p>
		</div>
	</div>
</header>

<main id="tv-main" class="tv-main">
