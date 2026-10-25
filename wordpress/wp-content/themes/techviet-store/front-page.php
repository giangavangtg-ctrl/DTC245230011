<?php
/**
 * Trang chủ TechViet Store.
 *
 * @package TechVietStore
 */

get_header();

$tv_hero_title = techviet_mod( 'techviet_hero_title' );
$tv_title_part = preg_split( '/\s+[–-]\s+/u', $tv_hero_title, 2 );
$tv_phone      = techviet_mod( 'techviet_phone' );
$tv_email      = techviet_mod( 'techviet_email' );
$tv_address    = techviet_mod( 'techviet_address' );
$tv_hours      = techviet_mod( 'techviet_hours' );

$tv_page_content = '';
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$tv_page_content = apply_filters( 'the_content', get_the_content() );
	}
}

$tv_categories = get_terms(
	array(
		'taxonomy'   => 'danh_muc_sp',
		'hide_empty' => true,
		'orderby'    => 'term_id',
		'order'      => 'ASC',
	)
);

$tv_featured = new WP_Query(
	array(
		'post_type'      => 'san_pham',
		'posts_per_page' => 6,
		'meta_key'       => '_tv_noi_bat',
		'meta_value'     => '1',
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
	)
);

if ( ! $tv_featured->have_posts() ) {
	$tv_featured = new WP_Query(
		array(
			'post_type'      => 'san_pham',
			'posts_per_page' => 6,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
}
?>

<section class="tv-hero">
	<div class="tv-hero__glow tv-hero__glow--a" aria-hidden="true"></div>
	<div class="tv-hero__glow tv-hero__glow--b" aria-hidden="true"></div>
	<div class="tv-container tv-hero__inner">
		<div class="tv-hero__content">
			<span class="tv-eyebrow"><?php echo esc_html( techviet_mod( 'techviet_hero_eyebrow' ) ); ?></span>
			<h1 class="tv-hero__title">
				<?php if ( 2 === count( $tv_title_part ) ) : ?>
					<?php echo esc_html( $tv_title_part[0] ); ?> –<br>
					<span class="tv-gradient-text"><?php echo esc_html( $tv_title_part[1] ); ?></span>
				<?php else : ?>
					<?php echo esc_html( $tv_hero_title ); ?>
				<?php endif; ?>
			</h1>
			<p class="tv-hero__subtitle"><?php echo esc_html( techviet_mod( 'techviet_hero_subtitle' ) ); ?></p>

			<div class="tv-hero__actions">
				<a class="tv-btn tv-btn-primary tv-btn-lg" href="<?php echo esc_url( techviet_link( techviet_mod( 'techviet_hero_btn_url' ) ) ); ?>">
					<?php echo esc_html( techviet_mod( 'techviet_hero_btn_text' ) ); ?>
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h12.2l-4.6-4.6L14 6l6 6-6 6-1.4-1.4 4.6-4.6H5v-2z" fill="currentColor"/></svg>
				</a>
				<a class="tv-btn tv-btn-outline tv-btn-lg" href="<?php echo esc_url( techviet_link( techviet_mod( 'techviet_hero_btn2_url' ) ) ); ?>">
					<?php echo esc_html( techviet_mod( 'techviet_hero_btn2_text' ) ); ?>
				</a>
			</div>

			<ul class="tv-hero__trust">
				<li><span aria-hidden="true">✓</span> Hàng chính hãng 100%</li>
				<li><span aria-hidden="true">✓</span> Bảo hành 24 tháng</li>
				<li><span aria-hidden="true">✓</span> Hỗ trợ 24/7</li>
			</ul>

			<div class="tv-hero__stats">
				<div><strong>10+</strong><span>Năm kinh nghiệm</span></div>
				<div><strong>50.000+</strong><span>Khách hàng tin chọn</span></div>
				<div><strong>4.9/5</strong><span>Điểm hài lòng</span></div>
			</div>
		</div>

		<div class="tv-hero__visual">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-tech.svg' ); ?>" alt="Laptop, điện thoại và phụ kiện công nghệ tại TechViet Store" width="900" height="700" fetchpriority="high">
		</div>
	</div>
</section>

<section class="tv-benefits" aria-label="Cam kết dịch vụ">
	<div class="tv-container tv-benefits__grid">
		<div class="tv-benefit">
			<span class="tv-benefit__icon" aria-hidden="true">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M3 6h11v9H3V6zm12 3h3.5l2.5 3.2V15h-6V9zM6 18a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm11 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" stroke="currentColor" stroke-width="1.6"/></svg>
			</span>
			<div><strong>Giao hàng hỏa tốc</strong><span>Nội thành trong 2 giờ</span></div>
		</div>
		<div class="tv-benefit">
			<span class="tv-benefit__icon" aria-hidden="true">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5c0 4.4-3 8.3-7 9.7C8 19.3 5 15.4 5 11V6l7-3zm-1 5v4.2l3 1.8 1-1.6-2.5-1.5V8h-1.5z" fill="currentColor"/></svg>
			</span>
			<div><strong>Bảo hành 24 tháng</strong><span>Chính hãng, tận nơi</span></div>
		</div>
		<div class="tv-benefit">
			<span class="tv-benefit__icon" aria-hidden="true">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4 7h16v10H4V7zm2 2v6h12V9H6zm2 8h8v2H8v-2z" fill="currentColor"/></svg>
			</span>
			<div><strong>Trả góp 0%</strong><span>Duyệt nhanh trong 15 phút</span></div>
		</div>
		<div class="tv-benefit">
			<span class="tv-benefit__icon" aria-hidden="true">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 3a9 9 0 1 0 9 9h-2a7 7 0 1 1-2-4.9V10h2V3h-7v2h3.2A8.9 8.9 0 0 0 12 3zm-1 4v5.4l4 2.4.9-1.5-3.4-2V7h-1.5z" fill="currentColor"/></svg>
			</span>
			<div><strong>Đổi trả 7 ngày</strong><span>Miễn phí nếu lỗi NSX</span></div>
		</div>
	</div>
</section>

<section class="tv-section tv-categories">
	<div class="tv-container">
		<div class="tv-section-head">
			<div>
				<span class="tv-eyebrow">Danh mục sản phẩm</span>
				<h2>Chọn đúng thiết bị cho bạn</h2>
				<p>Từ laptop cho công việc, điện thoại cho cuộc sống đến phụ kiện hoàn thiện trải nghiệm.</p>
			</div>
			<a class="tv-text-link" href="<?php echo esc_url( get_post_type_archive_link( 'san_pham' ) ); ?>">Xem tất cả sản phẩm <span aria-hidden="true">→</span></a>
		</div>

		<?php if ( ! is_wp_error( $tv_categories ) && $tv_categories ) : ?>
			<div class="tv-category-grid">
				<?php foreach ( $tv_categories as $tv_category ) : ?>
					<article class="tv-category-card">
						<a class="tv-category-card__media" href="<?php echo esc_url( get_term_link( $tv_category ) ); ?>">
							<img src="<?php echo esc_url( techviet_get_category_image( $tv_category ) ); ?>" alt="<?php echo esc_attr( $tv_category->name ); ?>" width="600" height="420" loading="lazy">
							<span class="tv-category-card__count"><?php echo esc_html( $tv_category->count ); ?> sản phẩm</span>
						</a>
						<div class="tv-category-card__body">
							<h3><a href="<?php echo esc_url( get_term_link( $tv_category ) ); ?>"><?php echo esc_html( $tv_category->name ); ?></a></h3>
							<p><?php echo esc_html( $tv_category->description ? $tv_category->description : 'Sản phẩm được tuyển chọn, bảo hành chính hãng.' ); ?></p>
							<a class="tv-card-link" href="<?php echo esc_url( get_term_link( $tv_category ) ); ?>">Khám phá <span aria-hidden="true">→</span></a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="tv-section tv-products-section">
	<div class="tv-container">
		<div class="tv-section-head">
			<div>
				<span class="tv-eyebrow">Sản phẩm nổi bật</span>
				<h2>Được khách hàng yêu thích</h2>
				<p>Những sản phẩm cân bằng tốt nhất giữa hiệu năng, thiết kế và giá trị sử dụng.</p>
			</div>
			<a class="tv-text-link" href="<?php echo esc_url( get_post_type_archive_link( 'san_pham' ) ); ?>">Toàn bộ sản phẩm <span aria-hidden="true">→</span></a>
		</div>

		<?php if ( $tv_featured->have_posts() ) : ?>
			<div class="tv-product-grid">
				<?php
				while ( $tv_featured->have_posts() ) :
					$tv_featured->the_post();
					get_template_part( 'template-parts/product', 'card', array( 'post_id' => get_the_ID() ) );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<div class="tv-section-cta">
				<a class="tv-btn tv-btn-primary tv-btn-lg" href="<?php echo esc_url( get_post_type_archive_link( 'san_pham' ) ); ?>">Khám phá tất cả sản phẩm</a>
			</div>
		<?php else : ?>
			<div class="tv-empty">
				<p>Sản phẩm đang được cập nhật. Vui lòng quay lại sau hoặc liên hệ hotline <a href="tel:<?php echo esc_attr( techviet_tel( $tv_phone ) ); ?>"><?php echo esc_html( $tv_phone ); ?></a>.</p>
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="tv-section tv-about">
	<div class="tv-container tv-about__grid">
		<div class="tv-about__visual">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/about-showcase.svg' ); ?>" alt="Không gian trải nghiệm sản phẩm tại TechViet Store" width="760" height="640" loading="lazy">
			<div class="tv-about__badge">
				<strong>10+</strong>
				<span>năm đồng hành<br>cùng khách hàng</span>
			</div>
		</div>
		<div class="tv-about__content tv-prose">
			<span class="tv-eyebrow">Về TechViet Store</span>
			<?php if ( $tv_page_content ) : ?>
				<?php echo wp_kses_post( $tv_page_content ); ?>
			<?php else : ?>
				<h2>Công nghệ cho mọi nhà</h2>
				<p>TechViet Store mang đến laptop, điện thoại và phụ kiện công nghệ chính hãng với thông tin minh bạch, giá cả hợp lý và dịch vụ hậu mãi tận tâm.</p>
			<?php endif; ?>
			<a class="tv-btn tv-btn-outline" href="<?php echo esc_url( home_url( '/gioi-thieu/' ) ); ?>">Tìm hiểu về chúng tôi</a>
		</div>
	</div>
</section>

<section class="tv-section tv-services">
	<div class="tv-container">
		<div class="tv-section-head tv-section-head--center">
			<div>
				<span class="tv-eyebrow">Dịch vụ &amp; hỗ trợ</span>
				<h2>An tâm trong suốt quá trình sử dụng</h2>
				<p>Không chỉ bán sản phẩm, TechViet Store đồng hành cùng bạn từ lúc chọn mua đến sau bán hàng.</p>
			</div>
		</div>
		<div class="tv-service-grid">
			<article class="tv-service-card">
				<span class="tv-service-card__num">01</span>
				<span class="tv-service-card__icon" aria-hidden="true">
					<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M4 4h16v12H4V4zm2 2v8h12V6H6zm-2 12h16v2H4v-2z" fill="currentColor"/></svg>
				</span>
				<h3>Tư vấn 1:1</h3>
				<p>Chuyên viên phân tích nhu cầu, ngân sách và đề xuất cấu hình phù hợp nhất — hoàn toàn miễn phí.</p>
				<a class="tv-card-link" href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>">Đặt lịch tư vấn <span aria-hidden="true">→</span></a>
			</article>
			<article class="tv-service-card">
				<span class="tv-service-card__num">02</span>
				<span class="tv-service-card__icon" aria-hidden="true">
					<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5c0 4.4-3 8.3-7 9.7C8 19.3 5 15.4 5 11V6l7-3zm-1 5v4.2l3 1.8 1-1.6-2.5-1.5V8h-1.5z" fill="currentColor"/></svg>
				</span>
				<h3>Bảo hành tận tâm</h3>
				<p>Bảo hành chính hãng 24 tháng, hỗ trợ tận nơi và xử lý nhanh trong 48 giờ làm việc.</p>
				<a class="tv-card-link" href="<?php echo esc_url( home_url( '/chinh-sach-bao-hanh/' ) ); ?>">Xem chính sách <span aria-hidden="true">→</span></a>
			</article>
			<article class="tv-service-card">
				<span class="tv-service-card__num">03</span>
				<span class="tv-service-card__icon" aria-hidden="true">
					<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M12 2a7 7 0 0 1 7 7v4a3 3 0 0 1-3 3h-1v-2h1a1 1 0 0 0 1-1v-1h-2V9a3 3 0 0 0-6 0v3H7v1a1 1 0 0 0 1 1h1v2H8a3 3 0 0 1-3-3v-4a7 7 0 0 1 7-7zm-2 16h4v2h-4v-2z" fill="currentColor"/></svg>
				</span>
				<h3>Hỗ trợ kỹ thuật 24/7</h3>
				<p>Cài đặt, chuyển dữ liệu, vệ sinh máy và xử lý sự cố từ xa qua hotline, Zalo hoặc tại cửa hàng.</p>
				<a class="tv-card-link" href="tel:<?php echo esc_attr( techviet_tel( $tv_phone ) ); ?>">Gọi hỗ trợ <span aria-hidden="true">→</span></a>
			</article>
		</div>
	</div>
</section>

<section class="tv-section tv-testimonials">
	<div class="tv-container">
		<div class="tv-section-head tv-section-head--center">
			<div>
				<span class="tv-eyebrow">Khách hàng nói gì</span>
				<h2>Niềm tin được xây từ trải nghiệm</h2>
			</div>
		</div>
		<div class="tv-testimonial-grid">
			<figure class="tv-testimonial">
				<div class="tv-testimonial__stars" aria-label="5 trên 5 sao">★★★★★</div>
				<blockquote>“Mình mua laptop cho công việc thiết kế, được tư vấn rất kỹ về màn hình và cấu hình. Máy giao trong ngày, đóng gói cẩn thận.”</blockquote>
				<figcaption><span class="tv-avatar" aria-hidden="true">MA</span><span><strong>Minh Anh</strong><small>Graphic Designer</small></span></figcaption>
			</figure>
			<figure class="tv-testimonial">
				<div class="tv-testimonial__stars" aria-label="5 trên 5 sao">★★★★★</div>
				<blockquote>“Đặt điện thoại cho gia đình, nhân viên hỗ trợ chuyển dữ liệu từ máy cũ miễn phí. Bảo hành rõ ràng, không phát sinh chi phí.”</blockquote>
				<figcaption><span class="tv-avatar" aria-hidden="true">QH</span><span><strong>Quốc Huy</strong><small>Kinh doanh tự do</small></span></figcaption>
			</figure>
			<figure class="tv-testimonial">
				<div class="tv-testimonial__stars" aria-label="5 trên 5 sao">★★★★★</div>
				<blockquote>“Công ty mình đặt 12 bộ phụ kiện, có hóa đơn và bảo hành đầy đủ. Đội ngũ TechViet phản hồi rất nhanh khi cần hỗ trợ.”</blockquote>
				<figcaption><span class="tv-avatar" aria-hidden="true">TL</span><span><strong>Thanh Loan</strong><small>Quản lý văn phòng</small></span></figcaption>
			</figure>
		</div>
	</div>
</section>

<section class="tv-section tv-home-contact">
	<div class="tv-container tv-home-contact__grid">
		<div>
			<span class="tv-eyebrow">Ghé thăm cửa hàng</span>
			<h2>Trải nghiệm sản phẩm trực tiếp</h2>
			<p>Đến showroom TechViet Store để dùng thử laptop, điện thoại và phụ kiện trước khi quyết định. Đội ngũ kỹ thuật viên luôn sẵn sàng hỗ trợ.</p>
			<ul class="tv-contact-list">
				<li><span aria-hidden="true">📍</span><div><strong>Địa chỉ</strong><span><?php echo esc_html( $tv_address ); ?></span></div></li>
				<li><span aria-hidden="true">☎</span><div><strong>Hotline</strong><a href="tel:<?php echo esc_attr( techviet_tel( $tv_phone ) ); ?>"><?php echo esc_html( $tv_phone ); ?></a></div></li>
				<li><span aria-hidden="true">✉</span><div><strong>Email</strong><a href="mailto:<?php echo esc_attr( $tv_email ); ?>"><?php echo esc_html( $tv_email ); ?></a></div></li>
				<li><span aria-hidden="true">🕘</span><div><strong>Giờ mở cửa</strong><span><?php echo esc_html( $tv_hours ); ?></span></div></li>
			</ul>
			<a class="tv-btn tv-btn-primary" href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>">Liên hệ &amp; chỉ đường</a>
		</div>
		<div class="tv-home-contact__visual">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/showroom.svg' ); ?>" alt="Showroom TechViet Store" width="720" height="560" loading="lazy">
		</div>
	</div>
</section>

<?php
get_footer();
