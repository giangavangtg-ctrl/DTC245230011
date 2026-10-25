<?php
/**
 * Plugin Name:       TechViet Core
 * Plugin URI:        https://localhost:8081
 * Description:       Đăng ký loại nội dung Sản phẩm, danh mục sản phẩm và form liên hệ cho website TechViet Store.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            TechViet DevOps
 * License:           GPL-2.0-or-later
 * Text Domain:       techviet
 *
 * @package TechVietCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const TECHVIET_CORE_VERSION = '1.0.0';

/**
 * Đăng ký CPT "san_pham" và taxonomy "danh_muc_sp".
 */
function techviet_register_product_cpt() {
	$labels = array(
		'name'                  => 'Sản phẩm',
		'singular_name'         => 'Sản phẩm',
		'menu_name'             => 'Sản phẩm',
		'add_new'               => 'Thêm sản phẩm',
		'add_new_item'          => 'Thêm sản phẩm mới',
		'edit_item'             => 'Sửa sản phẩm',
		'new_item'              => 'Sản phẩm mới',
		'view_item'             => 'Xem sản phẩm',
		'view_items'            => 'Xem danh sách sản phẩm',
		'search_items'          => 'Tìm sản phẩm',
		'not_found'             => 'Chưa có sản phẩm nào.',
		'not_found_in_trash'    => 'Không tìm thấy sản phẩm trong thùng rác.',
		'all_items'             => 'Tất cả sản phẩm',
		'featured_image'        => 'Ảnh sản phẩm',
		'set_featured_image'    => 'Chọn ảnh sản phẩm',
		'remove_featured_image' => 'Bỏ ảnh sản phẩm',
		'use_featured_image'    => 'Dùng làm ảnh sản phẩm',
	);

	register_post_type(
		'san_pham',
		array(
			'labels'             => $labels,
			'public'             => true,
			'has_archive'        => 'san-pham',
			'rewrite'            => array(
				'slug'       => 'san-pham',
				'with_front' => false,
			),
			'menu_icon'          => 'dashicons-laptop',
			'menu_position'      => 21,
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'custom-fields' ),
			'show_in_rest'       => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'capability_type'    => 'post',
		)
	);

	register_taxonomy(
		'danh_muc_sp',
		array( 'san_pham' ),
		array(
			'labels'            => array(
				'name'              => 'Danh mục sản phẩm',
				'singular_name'     => 'Danh mục sản phẩm',
				'search_items'      => 'Tìm danh mục',
				'all_items'         => 'Tất cả danh mục',
				'parent_item'       => 'Danh mục cha',
				'parent_item_colon' => 'Danh mục cha:',
				'edit_item'         => 'Sửa danh mục',
				'update_item'       => 'Cập nhật danh mục',
				'add_new_item'      => 'Thêm danh mục mới',
				'new_item_name'     => 'Tên danh mục mới',
				'menu_name'         => 'Danh mục',
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'danh-muc-san-pham',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'techviet_register_product_cpt' );

/**
 * Đăng ký CPT lưu yêu cầu liên hệ (không public).
 */
function techviet_register_contact_cpt() {
	register_post_type(
		'tv_lien_he',
		array(
			'labels'          => array(
				'name'          => 'Yêu cầu liên hệ',
				'singular_name' => 'Yêu cầu liên hệ',
				'menu_name'     => 'Yêu cầu liên hệ',
				'all_items'     => 'Tất cả yêu cầu',
				'edit_item'     => 'Xem yêu cầu',
				'not_found'     => 'Chưa có yêu cầu nào.',
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 22,
			'supports'        => array( 'title', 'editor' ),
			'capability_type' => 'post',
			'capabilities'    => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'techviet_register_contact_cpt' );

/**
 * Meta box thông tin sản phẩm.
 */
function techviet_add_product_meta_box() {
	add_meta_box(
		'techviet_product_details',
		'Thông tin bán hàng',
		'techviet_render_product_meta_box',
		'san_pham',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'techviet_add_product_meta_box' );

/**
 * Hiển thị meta box.
 *
 * @param WP_Post $post Bài viết hiện tại.
 */
function techviet_render_product_meta_box( $post ) {
	wp_nonce_field( 'techviet_save_product_meta', 'techviet_product_nonce' );

	$price       = get_post_meta( $post->ID, '_tv_gia', true );
	$short_desc  = get_post_meta( $post->ID, '_tv_mo_ta_ngan', true );
	$specs       = get_post_meta( $post->ID, '_tv_thong_so', true );
	$featured    = get_post_meta( $post->ID, '_tv_noi_bat', true );
	$badge       = get_post_meta( $post->ID, '_tv_nhan', true );
	?>
	<style>
		.tv-field{margin:0 0 16px}
		.tv-field label{display:block;font-weight:600;margin-bottom:6px}
		.tv-field input[type=text],.tv-field textarea{width:100%}
		.tv-field .description{margin-top:4px;color:#646970}
		.tv-cols{display:grid;grid-template-columns:1fr 1fr;gap:16px}
		@media(max-width:782px){.tv-cols{grid-template-columns:1fr}}
	</style>
	<div class="tv-cols">
		<div class="tv-field">
			<label for="tv_gia">Giá tham khảo</label>
			<input type="text" id="tv_gia" name="tv_gia" value="<?php echo esc_attr( $price ); ?>" placeholder="Ví dụ: 24.990.000 ₫">
			<p class="description">Hiển thị trên thẻ sản phẩm và trang chi tiết. Có thể để trống nếu “Liên hệ để báo giá”.</p>
		</div>
		<div class="tv-field">
			<label for="tv_nhan">Nhãn nổi bật</label>
			<input type="text" id="tv_nhan" name="tv_nhan" value="<?php echo esc_attr( $badge ); ?>" placeholder="Ví dụ: Bán chạy, Mới, -10%">
			<p class="description">Nhãn nhỏ hiển thị ở góc ảnh sản phẩm.</p>
		</div>
	</div>
	<div class="tv-field">
		<label for="tv_mo_ta_ngan">Mô tả ngắn</label>
		<textarea id="tv_mo_ta_ngan" name="tv_mo_ta_ngan" rows="2" maxlength="220" placeholder="Một câu mô tả nổi bật của sản phẩm"><?php echo esc_textarea( $short_desc ); ?></textarea>
		<p class="description">Tối đa 220 ký tự. Dùng cho thẻ sản phẩm ở trang chủ và trang danh sách.</p>
	</div>
	<div class="tv-field">
		<label for="tv_thong_so">Thông số kỹ thuật</label>
		<textarea id="tv_thong_so" name="tv_thong_so" rows="6" placeholder="CPU: Intel Core i7-1360P&#10;RAM: 16 GB&#10;Ổ cứng: 512 GB SSD"><?php echo esc_textarea( $specs ); ?></textarea>
		<p class="description">Mỗi dòng một thông số theo dạng <code>Tên: Giá trị</code>.</p>
	</div>
	<div class="tv-field">
		<label>
			<input type="checkbox" name="tv_noi_bat" value="1" <?php checked( '1', $featured ); ?>>
			Hiển thị trong khu vực “Sản phẩm nổi bật” ở trang chủ
		</label>
	</div>
	<?php
}

/**
 * Lưu meta sản phẩm.
 *
 * @param int $post_id ID bài viết.
 */
function techviet_save_product_meta( $post_id ) {
	if ( ! isset( $_POST['techviet_product_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['techviet_product_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'techviet_save_product_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'tv_gia'         => '_tv_gia',
		'tv_mo_ta_ngan'  => '_tv_mo_ta_ngan',
		'tv_thong_so'    => '_tv_thong_so',
		'tv_nhan'        => '_tv_nhan',
	);

	foreach ( $fields as $input => $meta_key ) {
		$value = isset( $_POST[ $input ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $input ] ) ) : '';
		if ( '' === $value ) {
			delete_post_meta( $post_id, $meta_key );
		} else {
			update_post_meta( $post_id, $meta_key, $value );
		}
	}

	$featured = isset( $_POST['tv_noi_bat'] ) ? '1' : '';
	if ( $featured ) {
		update_post_meta( $post_id, '_tv_noi_bat', '1' );
	} else {
		delete_post_meta( $post_id, '_tv_noi_bat' );
	}
}
add_action( 'save_post_san_pham', 'techviet_save_product_meta' );

/**
 * Cột quản trị cho danh sách sản phẩm.
 *
 * @param array $columns Các cột hiện có.
 * @return array
 */
function techviet_product_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['tv_anh']  = 'Ảnh';
			$new['tv_gia']  = 'Giá';
			$new['tv_noi_bat'] = 'Nổi bật';
		}
	}
	return $new;
}
add_filter( 'manage_san_pham_posts_columns', 'techviet_product_columns' );

/**
 * Nội dung các cột tùy chỉnh.
 *
 * @param string $column  Tên cột.
 * @param int    $post_id ID bài viết.
 */
function techviet_render_product_column( $column, $post_id ) {
	if ( 'tv_anh' === $column ) {
		if ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail( $post_id, array( 56, 56 ), array( 'style' => 'border-radius:8px;object-fit:cover' ) );
		} else {
			echo '<span style="color:#8c8f94">Chưa có ảnh</span>';
		}
	}

	if ( 'tv_gia' === $column ) {
		$price = get_post_meta( $post_id, '_tv_gia', true );
		echo $price ? esc_html( $price ) : '—';
	}

	if ( 'tv_noi_bat' === $column ) {
		echo get_post_meta( $post_id, '_tv_noi_bat', true ) ? '<span style="color:#00a32a">★</span>' : '—';
	}
}
add_action( 'manage_san_pham_posts_custom_column', 'techviet_render_product_column', 10, 2 );

/**
 * Shortcode hiển thị form liên hệ.
 *
 * @return string
 */
function techviet_contact_form_shortcode() {
	$status = isset( $_GET['lien-he'] ) ? sanitize_key( wp_unslash( $_GET['lien-he'] ) ) : '';

	ob_start();
	?>
	<div class="tv-contact-form-wrap" id="lien-he-form">
		<?php if ( 'success' === $status ) : ?>
			<div class="tv-alert tv-alert-success" role="status">
				<strong>Cảm ơn bạn!</strong> Yêu cầu đã được gửi thành công. TechViet Store sẽ liên hệ trong thời gian sớm nhất.
			</div>
		<?php elseif ( 'error' === $status ) : ?>
			<div class="tv-alert tv-alert-error" role="alert">
				<strong>Chưa gửi được.</strong> Vui lòng kiểm tra lại thông tin và thử lại.
			</div>
		<?php endif; ?>

		<form class="tv-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="techviet_contact">
			<?php wp_nonce_field( 'techviet_contact_submit', 'techviet_contact_nonce' ); ?>
			<div class="tv-hp" aria-hidden="true">
				<label for="tv_website">Website</label>
				<input type="text" id="tv_website" name="tv_website" tabindex="-1" autocomplete="off">
			</div>

			<div class="tv-form-grid">
				<p class="tv-form-field">
					<label for="tv_name">Họ và tên <span>*</span></label>
					<input type="text" id="tv_name" name="tv_name" required maxlength="100" autocomplete="name" placeholder="Nguyễn Văn A">
				</p>
				<p class="tv-form-field">
					<label for="tv_phone">Số điện thoại <span>*</span></label>
					<input type="tel" id="tv_phone" name="tv_phone" required maxlength="20" autocomplete="tel" placeholder="09xx xxx xxx">
				</p>
				<p class="tv-form-field">
					<label for="tv_email">Email</label>
					<input type="email" id="tv_email" name="tv_email" maxlength="120" autocomplete="email" placeholder="ban@email.com">
				</p>
				<p class="tv-form-field">
					<label for="tv_subject">Nhu cầu</label>
					<select id="tv_subject" name="tv_subject">
						<option value="Tư vấn sản phẩm">Tư vấn sản phẩm</option>
						<option value="Báo giá doanh nghiệp">Báo giá doanh nghiệp</option>
						<option value="Bảo hành - Kỹ thuật">Bảo hành - Kỹ thuật</option>
						<option value="Hợp tác">Hợp tác</option>
						<option value="Khác">Khác</option>
					</select>
				</p>
			</div>

			<p class="tv-form-field">
				<label for="tv_message">Nội dung <span>*</span></label>
				<textarea id="tv_message" name="tv_message" rows="5" required maxlength="2000" placeholder="Bạn cần tư vấn sản phẩm nào?"></textarea>
			</p>

			<p class="tv-form-consent">
				<label>
					<input type="checkbox" name="tv_consent" value="1" required>
					Tôi đồng ý để TechViet Store liên hệ lại theo thông tin đã cung cấp.
				</label>
			</p>

			<button type="submit" class="tv-btn tv-btn-primary tv-btn-lg">Gửi yêu cầu <span aria-hidden="true">→</span></button>
		</form>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'techviet_contact_form', 'techviet_contact_form_shortcode' );

/**
 * Xử lý form liên hệ.
 */
function techviet_handle_contact_form() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/lien-he/' );

	if ( ! isset( $_POST['techviet_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['techviet_contact_nonce'] ) ), 'techviet_contact_submit' ) ) {
		wp_safe_redirect( add_query_arg( 'lien-he', 'error', $redirect ) . '#lien-he-form' );
		exit;
	}

	$honeypot = isset( $_POST['tv_website'] ) ? sanitize_text_field( wp_unslash( $_POST['tv_website'] ) ) : '';
	$name     = isset( $_POST['tv_name'] ) ? sanitize_text_field( wp_unslash( $_POST['tv_name'] ) ) : '';
	$phone    = isset( $_POST['tv_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['tv_phone'] ) ) : '';
	$email    = isset( $_POST['tv_email'] ) ? sanitize_email( wp_unslash( $_POST['tv_email'] ) ) : '';
	$subject  = isset( $_POST['tv_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['tv_subject'] ) ) : 'Tư vấn sản phẩm';
	$message  = isset( $_POST['tv_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tv_message'] ) ) : '';
	$consent  = isset( $_POST['tv_consent'] );

	if ( '' !== $honeypot || '' === $name || '' === $phone || '' === $message || ! $consent ) {
		wp_safe_redirect( add_query_arg( 'lien-he', 'error', $redirect ) . '#lien-he-form' );
		exit;
	}

	$content = "Họ tên: {$name}\n"
		. "Điện thoại: {$phone}\n"
		. "Email: {$email}\n"
		. "Nhu cầu: {$subject}\n"
		. "Nội dung:\n{$message}";

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'tv_lien_he',
			'post_status'  => 'publish',
			'post_title'   => sprintf( '%s – %s', $name, wp_date( 'd/m/Y H:i' ) ),
			'post_content' => $content,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_safe_redirect( add_query_arg( 'lien-he', 'error', $redirect ) . '#lien-he-form' );
		exit;
	}

	update_post_meta( $post_id, '_tv_phone', $phone );
	update_post_meta( $post_id, '_tv_email', $email );
	update_post_meta( $post_id, '_tv_subject', $subject );

	// Gửi email nếu môi trường cho phép (không chặn luồng nếu thất bại).
	$admin_email = get_option( 'admin_email' );
	if ( $admin_email ) {
		wp_mail(
			$admin_email,
			'[TechViet Store] Yêu cầu liên hệ mới: ' . $subject,
			$content,
			array( 'Reply-To: ' . $email )
		);
	}

	wp_safe_redirect( add_query_arg( 'lien-he', 'success', $redirect ) . '#lien-he-form' );
	exit;
}
add_action( 'admin_post_techviet_contact', 'techviet_handle_contact_form' );
add_action( 'admin_post_nopriv_techviet_contact', 'techviet_handle_contact_form' );

/**
 * Hiển thị thông tin liên hệ trong trang quản trị.
 *
 * @param WP_Post $post Bài viết.
 */
function techviet_contact_meta_box( $post ) {
	$phone   = get_post_meta( $post->ID, '_tv_phone', true );
	$email   = get_post_meta( $post->ID, '_tv_email', true );
	$subject = get_post_meta( $post->ID, '_tv_subject', true );
	?>
	<table class="widefat striped">
		<tbody>
			<tr><th>Điện thoại</th><td><?php echo esc_html( $phone ); ?></td></tr>
			<tr><th>Email</th><td><?php echo esc_html( $email ); ?></td></tr>
			<tr><th>Nhu cầu</th><td><?php echo esc_html( $subject ); ?></td></tr>
		</tbody>
	</table>
	<?php
}
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box( 'techviet_contact_info', 'Thông tin người gửi', 'techviet_contact_meta_box', 'tv_lien_he', 'side', 'high' );
	}
);

/**
 * Kích hoạt plugin: đăng ký CPT rồi flush rewrite rules.
 */
function techviet_core_activate() {
	techviet_register_product_cpt();
	techviet_register_contact_cpt();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'techviet_core_activate' );

/**
 * Hủy kích hoạt: flush rewrite rules.
 */
function techviet_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'techviet_core_deactivate' );
