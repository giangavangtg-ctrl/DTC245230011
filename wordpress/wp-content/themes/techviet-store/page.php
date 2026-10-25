<?php
/**
 * Trang tĩnh mặc định.
 *
 * @package TechVietStore
 */

get_header();
techviet_breadcrumbs();

$tv_phone = techviet_mod( 'techviet_phone' );
$tv_email = techviet_mod( 'techviet_email' );
?>

<?php while ( have_posts() ) : the_post(); ?>
	<section class="tv-page-hero">
		<div class="tv-container">
			<span class="tv-eyebrow">TechViet Store</span>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="tv-section tv-page-content">
		<div class="tv-container tv-page-content__grid">
			<article class="tv-prose tv-page-content__main">
				<?php the_content(); ?>
			</article>

			<aside class="tv-page-content__aside">
				<div class="tv-sidebar-box tv-sidebar-box--accent">
					<h3>Cần hỗ trợ?</h3>
					<p>Đội ngũ TechViet Store sẵn sàng tư vấn từ 08:00 đến 21:00 mỗi ngày.</p>
					<a class="tv-btn tv-btn-primary" href="tel:<?php echo esc_attr( techviet_tel( $tv_phone ) ); ?>">Gọi <?php echo esc_html( $tv_phone ); ?></a>
					<a class="tv-sidebar-link" href="mailto:<?php echo esc_attr( $tv_email ); ?>"><?php echo esc_html( $tv_email ); ?></a>
				</div>

				<div class="tv-sidebar-box">
					<h3>Danh mục sản phẩm</h3>
					<ul>
						<?php
						$tv_terms = get_terms(
							array(
								'taxonomy'   => 'danh_muc_sp',
								'hide_empty' => true,
							)
						);
						if ( ! is_wp_error( $tv_terms ) && $tv_terms ) :
							foreach ( $tv_terms as $tv_term ) :
								?>
								<li><a href="<?php echo esc_url( get_term_link( $tv_term ) ); ?>"><?php echo esc_html( $tv_term->name ); ?></a></li>
								<?php
							endforeach;
						endif;
						?>
					</ul>
				</div>
			</aside>
		</div>
	</section>
<?php endwhile; ?>

<?php
get_footer();
