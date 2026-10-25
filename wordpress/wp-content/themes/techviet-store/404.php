<?php
/**
 * Trang 404.
 *
 * @package TechVietStore
 */

get_header();
?>

<section class="tv-404">
	<div class="tv-container tv-404__inner">
		<span class="tv-404__code" aria-hidden="true">404</span>
		<h1>Trang bạn tìm không tồn tại</h1>
		<p>Đường dẫn có thể đã thay đổi hoặc sản phẩm đã được chuyển sang danh mục khác.</p>
		<div class="tv-404__actions">
			<a class="tv-btn tv-btn-primary tv-btn-lg" href="<?php echo esc_url( home_url( '/' ) ); ?>">Về trang chủ</a>
			<a class="tv-btn tv-btn-outline tv-btn-lg" href="<?php echo esc_url( get_post_type_archive_link( 'san_pham' ) ); ?>">Xem sản phẩm</a>
		</div>
	</div>
</section>

<?php
get_footer();
