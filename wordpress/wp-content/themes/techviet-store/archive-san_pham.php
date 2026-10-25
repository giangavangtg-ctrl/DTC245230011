<?php
/**
 * Danh sách sản phẩm (CPT archive + taxonomy).
 *
 * @package TechVietStore
 */

get_header();

$tv_is_tax   = is_tax( 'danh_muc_sp' );
$tv_term     = $tv_is_tax ? get_queried_object() : null;
$tv_terms    = get_terms(
	array(
		'taxonomy'   => 'danh_muc_sp',
		'hide_empty' => true,
		'orderby'    => 'term_id',
		'order'      => 'ASC',
	)
);
$tv_title    = $tv_is_tax && $tv_term ? $tv_term->name : 'Sản phẩm công nghệ';
$tv_desc     = $tv_is_tax && $tv_term && $tv_term->description
	? $tv_term->description
	: 'Laptop, điện thoại và phụ kiện chính hãng — được tuyển chọn kỹ càng, bảo hành minh bạch và hỗ trợ tận tâm.';
$tv_current  = $tv_is_tax && $tv_term ? $tv_term->term_id : 0;
$tv_archive  = get_post_type_archive_link( 'san_pham' );
?>

<?php techviet_breadcrumbs(); ?>

<section class="tv-page-hero">
	<div class="tv-container">
		<span class="tv-eyebrow">TechViet Store</span>
		<h1><?php echo esc_html( $tv_title ); ?></h1>
		<p><?php echo esc_html( $tv_desc ); ?></p>
	</div>
</section>

<section class="tv-section tv-archive">
	<div class="tv-container">
		<div class="tv-filter-bar" role="navigation" aria-label="Lọc theo danh mục">
			<a class="tv-filter <?php echo $tv_current ? '' : 'is-active'; ?>" href="<?php echo esc_url( $tv_archive ); ?>">Tất cả</a>
			<?php if ( ! is_wp_error( $tv_terms ) && $tv_terms ) : ?>
				<?php foreach ( $tv_terms as $tv_item ) : ?>
					<a class="tv-filter <?php echo (int) $tv_current === (int) $tv_item->term_id ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $tv_item ) ); ?>">
						<?php echo esc_html( $tv_item->name ); ?>
						<span class="tv-filter__count"><?php echo esc_html( $tv_item->count ); ?></span>
					</a>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<p class="tv-archive__count">
			<?php
			$tv_total = (int) $GLOBALS['wp_query']->found_posts;
			printf( 'Hiển thị %d sản phẩm', $tv_total );
			?>
		</p>

		<?php if ( have_posts() ) : ?>
			<div class="tv-product-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/product', 'card', array( 'post_id' => get_the_ID() ) );
				endwhile;
				?>
			</div>

			<div class="tv-pagination">
				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => '← Trước',
						'next_text' => 'Sau →',
					)
				);
				?>
			</div>
		<?php else : ?>
			<div class="tv-empty">
				<span aria-hidden="true">🛍️</span>
				<h2>Chưa có sản phẩm trong danh mục này</h2>
				<p>Vui lòng chọn danh mục khác hoặc liên hệ hotline để được tư vấn.</p>
				<a class="tv-btn tv-btn-primary" href="<?php echo esc_url( $tv_archive ); ?>">Xem tất cả sản phẩm</a>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
