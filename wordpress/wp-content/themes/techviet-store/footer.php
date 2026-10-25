<?php
/**
 * Footer.
 *
 * @package TechVietStore
 */

$tv_phone   = techviet_mod( 'techviet_phone' );
$tv_email   = techviet_mod( 'techviet_email' );
$tv_address = techviet_mod( 'techviet_address' );
$tv_hours   = techviet_mod( 'techviet_hours' );
$tv_socials = array(
	'facebook' => array( 'label' => 'Facebook', 'url' => techviet_mod( 'techviet_facebook' ) ),
	'youtube'  => array( 'label' => 'YouTube', 'url' => techviet_mod( 'techviet_youtube' ) ),
	'zalo'     => array( 'label' => 'Zalo', 'url' => techviet_mod( 'techviet_zalo' ) ),
);
$tv_terms   = get_terms(
	array(
		'taxonomy'   => 'danh_muc_sp',
		'hide_empty' => true,
	)
);
?>
</main>

<section class="tv-consult" aria-labelledby="tv-consult-title">
	<div class="tv-container tv-consult__inner">
		<div class="tv-consult__text">
			<span class="tv-eyebrow">Đồng hành cùng bạn</span>
			<h2 id="tv-consult-title">Chưa biết chọn sản phẩm nào?</h2>
			<p>Đội ngũ TechViet Store sẵn sàng tư vấn cấu hình, so sánh sản phẩm và ưu đãi phù hợp với nhu cầu của bạn.</p>
		</div>
		<div class="tv-consult__actions">
			<a class="tv-btn tv-btn-light tv-btn-lg" href="tel:<?php echo esc_attr( techviet_tel( $tv_phone ) ); ?>">
				Gọi <?php echo esc_html( $tv_phone ); ?>
			</a>
			<a class="tv-btn tv-btn-outline-light tv-btn-lg" href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>">Gửi yêu cầu tư vấn</a>
		</div>
	</div>
</section>

<footer class="tv-footer">
	<div class="tv-container">
		<div class="tv-footer__grid">
			<div class="tv-footer__brand">
				<a class="tv-logo tv-logo--light" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img class="tv-logo__mark" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-mark.svg' ); ?>" alt="" width="44" height="44" loading="lazy">
					<span class="tv-logo__text"><strong>TechViet</strong><span>Store</span></span>
				</a>
				<p><?php echo esc_html( techviet_mod( 'techviet_footer_intro' ) ); ?></p>
				<?php if ( array_filter( wp_list_pluck( $tv_socials, 'url' ) ) ) : ?>
					<div class="tv-socials" aria-label="Mạng xã hội">
						<?php foreach ( $tv_socials as $key => $social ) : ?>
							<?php if ( $social['url'] ) : ?>
								<a href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $social['label'] ); ?></a>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="tv-footer__col">
				<h3>Sản phẩm</h3>
				<ul>
					<?php if ( ! is_wp_error( $tv_terms ) && $tv_terms ) : ?>
						<?php foreach ( $tv_terms as $tv_term ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $tv_term ) ); ?>"><?php echo esc_html( $tv_term->name ); ?></a></li>
						<?php endforeach; ?>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'san_pham' ) ); ?>">Tất cả sản phẩm</a></li>
				</ul>
			</div>

			<div class="tv-footer__col">
				<h3>Hỗ trợ khách hàng</h3>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/gioi-thieu/' ) ); ?>">Giới thiệu TechViet</a></li>
					<li><a href="<?php echo esc_url( home_url( '/chinh-sach-bao-hanh/' ) ); ?>">Chính sách bảo hành</a></li>
					<li><a href="<?php echo esc_url( home_url( '/chinh-sach-doi-tra/' ) ); ?>">Chính sách đổi trả</a></li>
					<li><a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>">Liên hệ &amp; chỉ đường</a></li>
				</ul>
			</div>

			<div class="tv-footer__col tv-footer__contact">
				<h3>Liên hệ</h3>
				<ul>
					<li>
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z" fill="currentColor"/></svg>
						<span><?php echo esc_html( $tv_address ); ?></span>
					</li>
					<li>
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.2.2 2.4.6 3.6.1.4 0 .8-.2 1l-2.3 2.2z" fill="currentColor"/></svg>
						<a href="tel:<?php echo esc_attr( techviet_tel( $tv_phone ) ); ?>"><?php echo esc_html( $tv_phone ); ?></a>
					</li>
					<li>
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 5h18v14H3V5zm2 2v.4l7 4.7 7-4.7V7H5zm14 10V9.9l-7 4.6-7-4.6V17h14z" fill="currentColor"/></svg>
						<a href="mailto:<?php echo esc_attr( $tv_email ); ?>"><?php echo esc_html( $tv_email ); ?></a>
					</li>
					<li>
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 5v5.4l4 2.4-.8 1.3-4.7-2.8V7h1.5z" fill="currentColor"/></svg>
						<span><?php echo esc_html( $tv_hours ); ?></span>
					</li>
				</ul>
			</div>
		</div>

		<div class="tv-footer__bottom">
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> TechViet Store. Bảo lưu mọi quyền.</p>
			<p>Thiết kế &amp; triển khai bởi <strong>TechViet DevOps</strong> · WordPress · Docker</p>
		</div>
	</div>
</footer>

<button class="tv-backtop" type="button" aria-label="Lên đầu trang" hidden>
	<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5l7 7-1.4 1.4L13 8.8V19h-2V8.8L6.4 13.4 5 12l7-7z" fill="currentColor"/></svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
