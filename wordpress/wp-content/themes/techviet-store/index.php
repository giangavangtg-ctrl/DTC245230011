<?php
/**
 * Template dự phòng — danh sách bài viết.
 *
 * @package TechVietStore
 */

get_header();
techviet_breadcrumbs();
?>

<section class="tv-page-hero">
	<div class="tv-container">
		<span class="tv-eyebrow">TechViet Blog</span>
		<h1><?php echo is_search() ? 'Kết quả tìm kiếm' : 'Tin tức &amp; hướng dẫn'; ?></h1>
		<?php if ( is_search() ) : ?>
			<p>Từ khóa: <strong><?php echo esc_html( get_search_query() ); ?></strong></p>
		<?php else : ?>
			<p>Kiến thức chọn mua, mẹo sử dụng và cập nhật sản phẩm công nghệ từ TechViet Store.</p>
		<?php endif; ?>
	</div>
</section>

<section class="tv-section">
	<div class="tv-container">
		<?php if ( have_posts() ) : ?>
			<div class="tv-post-grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<article class="tv-post-card">
						<a class="tv-post-card__thumb" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'techviet-card' ); ?>
							<?php else : ?>
								<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/categories/laptop.svg' ); ?>" alt="" width="720" height="540" loading="lazy">
							<?php endif; ?>
						</a>
						<div class="tv-post-card__body">
							<span class="tv-post-card__date"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></span>
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?></p>
							<a class="tv-card-link" href="<?php the_permalink(); ?>">Đọc tiếp <span aria-hidden="true">→</span></a>
						</div>
					</article>
				<?php endwhile; ?>
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
				<h2>Không tìm thấy nội dung</h2>
				<p>Thử từ khóa khác hoặc quay về trang chủ.</p>
				<a class="tv-btn tv-btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Về trang chủ</a>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
