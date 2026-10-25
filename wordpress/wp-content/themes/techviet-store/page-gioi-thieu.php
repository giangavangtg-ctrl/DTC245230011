<?php
/**
 * Template trang Giới thiệu.
 *
 * @package TechVietStore
 */

get_header();
techviet_breadcrumbs();
?>

<?php while ( have_posts() ) : the_post(); ?>
	<section class="tv-page-hero tv-page-hero--about">
		<div class="tv-container">
			<span class="tv-eyebrow">Về chúng tôi</span>
			<h1><?php the_title(); ?></h1>
			<p>TechViet Store — đơn vị bán lẻ sản phẩm công nghệ chính hãng, đồng hành cùng khách hàng Việt từ năm 2014.</p>
		</div>
	</section>

	<section class="tv-stats-band">
		<div class="tv-container tv-stats-band__grid">
			<div><strong>10+</strong><span>Năm hoạt động</span></div>
			<div><strong>50.000+</strong><span>Khách hàng</span></div>
			<div><strong>12</strong><span>Showroom toàn quốc</span></div>
			<div><strong>4.9/5</strong><span>Điểm hài lòng</span></div>
		</div>
	</section>

	<section class="tv-section tv-about-page">
		<div class="tv-container tv-about-page__grid">
			<div class="tv-about-page__media">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/about-showcase.svg' ); ?>" alt="Đội ngũ và không gian TechViet Store" width="760" height="640" loading="lazy">
			</div>
			<div class="tv-prose tv-about-page__content">
				<?php the_content(); ?>
			</div>
		</div>
	</section>

	<section class="tv-section tv-values">
		<div class="tv-container">
			<div class="tv-section-head tv-section-head--center">
				<div>
					<span class="tv-eyebrow">Giá trị cốt lõi</span>
					<h2>Chúng tôi làm việc bằng sự tử tế</h2>
				</div>
			</div>
			<div class="tv-value-grid">
				<article class="tv-value-card">
					<span class="tv-value-card__icon" aria-hidden="true">01</span>
					<h3>Chính trực</h3>
					<p>Thông tin sản phẩm, giá bán và chính sách bảo hành được công bố rõ ràng. Không bán hàng giả, hàng nhái hoặc che giấu lỗi.</p>
				</article>
				<article class="tv-value-card">
					<span class="tv-value-card__icon" aria-hidden="true">02</span>
					<h3>Tận tâm</h3>
					<p>Mỗi khách hàng được tư vấn dựa trên nhu cầu thật, không chạy theo doanh số. Hậu mãi nhanh chóng và minh bạch.</p>
				</article>
				<article class="tv-value-card">
					<span class="tv-value-card__icon" aria-hidden="true">03</span>
					<h3>Không ngừng cải tiến</h3>
					<p>Đội ngũ kỹ thuật liên tục cập nhật kiến thức, quy trình và công nghệ để mang đến trải nghiệm tốt hơn mỗi ngày.</p>
				</article>
			</div>
		</div>
	</section>

	<section class="tv-section tv-about-cta">
		<div class="tv-container tv-about-cta__inner">
			<div>
				<h2>Hơn 50.000 khách hàng đã tin chọn TechViet Store</h2>
				<p>Hãy để chúng tôi giúp bạn tìm đúng sản phẩm cho công việc và cuộc sống.</p>
			</div>
			<a class="tv-btn tv-btn-light tv-btn-lg" href="<?php echo esc_url( get_post_type_archive_link( 'san_pham' ) ); ?>">Khám phá sản phẩm</a>
		</div>
	</section>
<?php endwhile; ?>

<?php
get_footer();
