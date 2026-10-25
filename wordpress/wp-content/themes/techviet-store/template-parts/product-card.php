<?php
/**
 * Thẻ sản phẩm.
 *
 * @package TechVietStore
 *
 * @var array $args {
 *     @type int $post_id ID sản phẩm.
 * }
 */

$tv_post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
if ( ! $tv_post_id ) {
	return;
}

$tv_price = techviet_get_price( $tv_post_id );
$tv_badge = techviet_get_badge( $tv_post_id );
$tv_cat   = techviet_primary_category( $tv_post_id );
?>
<article class="tv-product-card">
	<a class="tv-product-card__media" href="<?php echo esc_url( get_permalink( $tv_post_id ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $tv_post_id ) ); ?>">
		<?php if ( $tv_badge ) : ?>
			<span class="tv-product-card__badge"><?php echo esc_html( $tv_badge ); ?></span>
		<?php endif; ?>
		<?php echo techviet_get_product_image( $tv_post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
	<div class="tv-product-card__body">
		<?php if ( $tv_cat ) : ?>
			<a class="tv-product-card__cat" href="<?php echo esc_url( get_term_link( $tv_cat ) ); ?>"><?php echo esc_html( $tv_cat->name ); ?></a>
		<?php endif; ?>
		<h3 class="tv-product-card__title">
			<a href="<?php echo esc_url( get_permalink( $tv_post_id ) ); ?>"><?php echo esc_html( get_the_title( $tv_post_id ) ); ?></a>
		</h3>
		<p class="tv-product-card__desc"><?php echo esc_html( techviet_get_short_desc( $tv_post_id ) ); ?></p>
		<div class="tv-product-card__foot">
			<div class="tv-price">
				<?php if ( $tv_price ) : ?>
					<span class="tv-price__value"><?php echo esc_html( $tv_price ); ?></span>
					<span class="tv-price__note">Giá tham khảo</span>
				<?php else : ?>
					<span class="tv-price__value tv-price__value--contact">Liên hệ báo giá</span>
				<?php endif; ?>
			</div>
			<a class="tv-card-link" href="<?php echo esc_url( get_permalink( $tv_post_id ) ); ?>">
				Xem chi tiết
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h12.2l-4.6-4.6L14 6l6 6-6 6-1.4-1.4 4.6-4.6H5v-2z" fill="currentColor"/></svg>
			</a>
		</div>
	</div>
</article>
