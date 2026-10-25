<?php
/**
 * Chi tiết sản phẩm.
 *
 * @package TechVietStore
 */

get_header();
techviet_breadcrumbs();

$tv_phone = techviet_mod( 'techviet_phone' );
$tv_email = techviet_mod( 'techviet_email' );

while ( have_posts() ) :
	the_post();

	$tv_id       = get_the_ID();
	$tv_price    = techviet_get_price( $tv_id );
	$tv_badge    = techviet_get_badge( $tv_id );
	$tv_cat      = techviet_primary_category( $tv_id );
	$tv_specs    = techviet_get_specs( $tv_id );
	$tv_short    = get_post_meta( $tv_id, '_tv_mo_ta_ngan', true );
	$tv_related  = new WP_Query(
		array(
			'post_type'      => 'san_pham',
			'posts_per_page' => 3,
			'post__not_in'   => array( $tv_id ),
			'orderby'        => 'rand',
			'tax_query'      => $tv_cat
				? array(
					array(
						'taxonomy' => 'danh_muc_sp',
						'field'    => 'term_id',
						'terms'    => $tv_cat->term_id,
					),
				)
				: array(),
		)
	);
	?>

	<section class="tv-product-detail">
		<div class="tv-container">
			<div class="tv-product-detail__grid">
				<div class="tv-product-detail__media">
					<?php if ( $tv_badge ) : ?>
						<span class="tv-product-detail__badge"><?php echo esc_html( $tv_badge ); ?></span>
					<?php endif; ?>
					<?php echo techviet_get_product_image( $tv_id, 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<ul class="tv-product-detail__trust">
						<li><span aria-hidden="true">✓</span> Chính hãng 100%</li>
						<li><span aria-hidden="true">✓</span> Bảo hành 24 tháng</li>
						<li><span aria-hidden="true">✓</span> Đổi trả 7 ngày</li>
					</ul>
				</div>

				<div class="tv-product-detail__summary">
					<?php if ( $tv_cat ) : ?>
						<a class="tv-product-detail__cat" href="<?php echo esc_url( get_term_link( $tv_cat ) ); ?>"><?php echo esc_html( $tv_cat->name ); ?></a>
					<?php endif; ?>

					<h1><?php the_title(); ?></h1>

					<div class="tv-rating">
						<span class="tv-rating__stars" aria-hidden="true">★★★★★</span>
						<span>4.9/5 · 128 đánh giá</span>
					</div>

					<?php if ( $tv_short ) : ?>
						<p class="tv-product-detail__desc"><?php echo esc_html( $tv_short ); ?></p>
					<?php endif; ?>

					<div class="tv-product-detail__price">
						<?php if ( $tv_price ) : ?>
							<span class="tv-product-detail__price-value"><?php echo esc_html( $tv_price ); ?></span>
							<span class="tv-price__note">Giá tham khảo — liên hệ để nhận báo giá chính xác</span>
						<?php else : ?>
							<span class="tv-product-detail__price-value">Liên hệ báo giá</span>
							<span class="tv-price__note">TechViet Store sẽ phản hồi trong 15 phút</span>
						<?php endif; ?>
					</div>

					<?php if ( $tv_specs ) : ?>
						<ul class="tv-quick-specs">
							<?php foreach ( array_slice( $tv_specs, 0, 4 ) as $tv_spec ) : ?>
								<li><span><?php echo esc_html( $tv_spec['label'] ); ?></span><strong><?php echo esc_html( $tv_spec['value'] ); ?></strong></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<div class="tv-product-detail__actions">
						<a class="tv-btn tv-btn-primary tv-btn-lg" href="tel:<?php echo esc_attr( techviet_tel( $tv_phone ) ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.2.2 2.4.6 3.6.1.4 0 .8-.2 1l-2.3 2.2z" fill="currentColor"/></svg>
							Gọi tư vấn ngay
						</a>
						<a class="tv-btn tv-btn-outline tv-btn-lg" href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>">Nhận báo giá</a>
					</div>

					<ul class="tv-product-detail__meta">
						<li><strong>Mã sản phẩm:</strong> TV-<?php echo esc_html( str_pad( (string) $tv_id, 4, '0', STR_PAD_LEFT ) ); ?></li>
						<li><strong>Danh mục:</strong> <?php echo $tv_cat ? esc_html( $tv_cat->name ) : '—'; ?></li>
						<li><strong>Bảo hành:</strong> 24 tháng chính hãng</li>
						<li><strong>Vận chuyển:</strong> Miễn phí nội thành, giao toàn quốc</li>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<section class="tv-section tv-product-content">
		<div class="tv-container tv-product-content__grid">
			<div class="tv-product-content__main">
				<h2>Mô tả sản phẩm</h2>
				<div class="tv-prose">
					<?php
					if ( get_the_content() ) {
						the_content();
					} else {
						echo '<p>' . esc_html( techviet_get_short_desc( $tv_id, 60 ) ) . '</p>';
					}
					?>
				</div>

				<?php if ( $tv_specs ) : ?>
					<h2>Thông số kỹ thuật</h2>
					<div class="tv-spec-table-wrap">
						<table class="tv-spec-table">
							<tbody>
								<?php foreach ( $tv_specs as $tv_spec ) : ?>
									<tr>
										<th scope="row"><?php echo esc_html( $tv_spec['label'] ); ?></th>
										<td><?php echo esc_html( $tv_spec['value'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>

			<aside class="tv-product-content__aside">
				<div class="tv-sidebar-box tv-sidebar-box--accent">
					<h3>Cần tư vấn thêm?</h3>
					<p>Chuyên viên TechViet hỗ trợ chọn cấu hình, so sánh sản phẩm và ưu đãi trong 15 phút.</p>
					<a class="tv-btn tv-btn-primary" href="tel:<?php echo esc_attr( techviet_tel( $tv_phone ) ); ?>">Gọi <?php echo esc_html( $tv_phone ); ?></a>
					<a class="tv-sidebar-link" href="mailto:<?php echo esc_attr( $tv_email ); ?>"><?php echo esc_html( $tv_email ); ?></a>
				</div>

				<div class="tv-sidebar-box">
					<h3>Cam kết của TechViet</h3>
					<ul class="tv-check-list">
						<li>Sản phẩm mới, nguyên seal 100%</li>
						<li>Hóa đơn VAT đầy đủ</li>
						<li>Bảo hành chính hãng 24 tháng</li>
						<li>Hỗ trợ kỹ thuật trọn đời</li>
						<li>Trả góp 0% qua thẻ tín dụng</li>
					</ul>
				</div>
			</aside>
		</div>
	</section>

	<?php if ( $tv_related->have_posts() ) : ?>
		<section class="tv-section tv-related">
			<div class="tv-container">
				<div class="tv-section-head">
					<div>
						<span class="tv-eyebrow">Có thể bạn thích</span>
						<h2>Sản phẩm cùng danh mục</h2>
					</div>
					<?php if ( $tv_cat ) : ?>
						<a class="tv-text-link" href="<?php echo esc_url( get_term_link( $tv_cat ) ); ?>">Xem tất cả <span aria-hidden="true">→</span></a>
					<?php endif; ?>
				</div>
				<div class="tv-product-grid">
					<?php
					while ( $tv_related->have_posts() ) :
						$tv_related->the_post();
						get_template_part( 'template-parts/product', 'card', array( 'post_id' => get_the_ID() ) );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
