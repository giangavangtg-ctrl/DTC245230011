<?php
/**
 * Template trang Liên hệ.
 *
 * @package TechVietStore
 */

get_header();
techviet_breadcrumbs();

$tv_phone   = techviet_mod( 'techviet_phone' );
$tv_email   = techviet_mod( 'techviet_email' );
$tv_address = techviet_mod( 'techviet_address' );
$tv_hours   = techviet_mod( 'techviet_hours' );
?>

<?php while ( have_posts() ) : the_post(); ?>
	<section class="tv-page-hero">
		<div class="tv-container">
			<span class="tv-eyebrow">Kết nối với TechViet</span>
			<h1><?php the_title(); ?></h1>
			<p>Gọi hotline, gửi yêu cầu tư vấn hoặc ghé showroom — chúng tôi luôn sẵn sàng hỗ trợ bạn.</p>
		</div>
	</section>

	<section class="tv-section tv-contact-page">
		<div class="tv-container tv-contact-page__grid">
			<div class="tv-contact-page__form">
				<h2>Gửi yêu cầu tư vấn</h2>
				<p class="tv-contact-page__lead">Điền thông tin bên dưới, chuyên viên TechViet sẽ liên hệ lại trong vòng 15 phút (giờ hành chính).</p>
				<?php the_content(); ?>
			</div>

			<aside class="tv-contact-page__info">
				<div class="tv-contact-card">
					<h3>Thông tin cửa hàng</h3>
					<ul class="tv-contact-list">
						<li>
							<span aria-hidden="true">📍</span>
							<div><strong>Showroom</strong><span><?php echo esc_html( $tv_address ); ?></span></div>
						</li>
						<li>
							<span aria-hidden="true">☎</span>
							<div><strong>Hotline</strong><a href="tel:<?php echo esc_attr( techviet_tel( $tv_phone ) ); ?>"><?php echo esc_html( $tv_phone ); ?></a></div>
						</li>
						<li>
							<span aria-hidden="true">✉</span>
							<div><strong>Email</strong><a href="mailto:<?php echo esc_attr( $tv_email ); ?>"><?php echo esc_html( $tv_email ); ?></a></div>
						</li>
						<li>
							<span aria-hidden="true">🕘</span>
							<div><strong>Giờ mở cửa</strong><span><?php echo esc_html( $tv_hours ); ?></span></div>
						</li>
					</ul>
				</div>

				<div class="tv-contact-card tv-contact-card--soft">
					<h3>Hỗ trợ nhanh</h3>
					<p>Doanh nghiệp cần báo giá số lượng lớn hoặc hợp đồng bảo trì?</p>
					<a class="tv-sidebar-link" href="mailto:<?php echo esc_attr( $tv_email ); ?>?subject=Báo giá doanh nghiệp">Gửi email cho phòng kinh doanh →</a>
				</div>
			</aside>
		</div>
	</section>

	<section class="tv-section tv-faq">
		<div class="tv-container">
			<div class="tv-section-head tv-section-head--center">
				<div>
					<span class="tv-eyebrow">Câu hỏi thường gặp</span>
					<h2>Bạn cần biết trước khi liên hệ</h2>
				</div>
			</div>
			<div class="tv-faq__grid">
				<details>
					<summary>TechViet Store có xuất hóa đơn VAT không?</summary>
					<p>Có. Chúng tôi xuất hóa đơn VAT cho tất cả đơn hàng, kể cả khách hàng cá nhân và doanh nghiệp.</p>
				</details>
				<details>
					<summary>Thời gian phản hồi yêu cầu tư vấn là bao lâu?</summary>
					<p>Trong giờ hành chính, chuyên viên phản hồi trong khoảng 15 phút. Ngoài giờ, yêu cầu sẽ được xử lý vào đầu ngày làm việc kế tiếp.</p>
				</details>
				<details>
					<summary>Có hỗ trợ mua trả góp không?</summary>
					<p>Có. TechViet Store hỗ trợ trả góp 0% qua thẻ tín dụng và trả góp qua công ty tài chính với thủ tục nhanh gọn.</p>
				</details>
				<details>
					<summary>Tôi có thể đến dùng thử sản phẩm trước khi mua?</summary>
					<p>Hoàn toàn được. Showroom mở cửa từ 08:00 đến 21:00 mỗi ngày, đội ngũ kỹ thuật sẽ hỗ trợ bạn trải nghiệm sản phẩm trực tiếp.</p>
				</details>
			</div>
		</div>
	</section>
<?php endwhile; ?>

<?php
get_footer();
