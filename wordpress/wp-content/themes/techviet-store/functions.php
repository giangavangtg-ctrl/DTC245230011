<?php
/**
 * TechViet Store — Theme functions.
 *
 * @package TechVietStore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TECHVIET_THEME_VERSION', '1.0.0' );

/**
 * Giá trị mặc định cho các tùy biến giao diện.
 *
 * @return array<string,string>
 */
function techviet_defaults() {
	return array(
		'techviet_announcement'   => 'Miễn phí giao hàng toàn quốc cho đơn từ 1.000.000 ₫',
		'techviet_phone'          => '1900 6868',
		'techviet_email'          => 'hotro@techviet.vn',
		'techviet_address'        => '123 Nguyễn Văn Linh, Quận 7, TP. Hồ Chí Minh',
		'techviet_hours'          => '08:00 – 21:00 (Thứ 2 – Chủ nhật)',
		'techviet_hero_eyebrow'   => 'TECHVIET STORE • CÔNG NGHỆ CHÍNH HÃNG',
		'techviet_hero_title'     => 'Công nghệ hiện đại – Nâng tầm trải nghiệm',
		'techviet_hero_subtitle'  => 'Khám phá laptop, điện thoại và phụ kiện công nghệ được tuyển chọn kỹ càng — thiết kế tinh tế, hiệu năng mạnh mẽ và dịch vụ hậu mãi tận tâm.',
		'techviet_hero_btn_text'  => 'Khám phá sản phẩm',
		'techviet_hero_btn_url'   => '/san-pham/',
		'techviet_hero_btn2_text' => 'Tư vấn miễn phí',
		'techviet_hero_btn2_url'  => '/lien-he/',
		'techviet_facebook'       => '',
		'techviet_youtube'        => '',
		'techviet_zalo'           => '',
		'techviet_footer_intro'   => 'TechViet Store mang đến sản phẩm công nghệ chính hãng, thông tin minh bạch và trải nghiệm mua sắm đáng tin cậy cho mọi gia đình Việt.',
	);
}

/**
 * Lấy giá trị tùy biến kèm mặc định.
 *
 * @param string $key Tên setting.
 * @return string
 */
function techviet_mod( $key ) {
	$defaults = techviet_defaults();
	return (string) get_theme_mod( $key, $defaults[ $key ] ?? '' );
}

/**
 * Chuẩn hoá liên kết nội bộ (bắt đầu bằng "/") thành URL đầy đủ.
 *
 * @param string $url Liên kết.
 * @return string
 */
function techviet_link( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return home_url( '/' );
	}
	if ( str_starts_with( $url, '/' ) ) {
		return home_url( $url );
	}
	return $url;
}

/**
 * Số điện thoại dạng tel:.
 *
 * @param string $phone Số hiển thị.
 * @return string
 */
function techviet_tel( $phone ) {
	return preg_replace( '/[^0-9+]/', '', (string) $phone );
}

/**
 * Thiết lập theme.
 */
function techviet_setup() {
	load_theme_textdomain( 'techviet', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/main.css' );

	add_image_size( 'techviet-card', 720, 540, true );
	add_image_size( 'techviet-wide', 1400, 900, false );

	register_nav_menus(
		array(
			'primary' => 'Menu chính',
			'footer'  => 'Menu chân trang',
		)
	);
}
add_action( 'after_setup_theme', 'techviet_setup' );

/**
 * Nhúng CSS/JS.
 */
function techviet_assets() {
	$theme = wp_get_theme();

	wp_enqueue_style(
		'techviet-base',
		get_stylesheet_uri(),
		array(),
		$theme->get( 'Version' )
	);

	wp_enqueue_style(
		'techviet-main',
		get_template_directory_uri() . '/assets/css/main.css',
		array( 'techviet-base' ),
		(string) filemtime( get_template_directory() . '/assets/css/main.css' )
	);

	wp_enqueue_script(
		'techviet-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		(string) filemtime( get_template_directory() . '/assets/js/main.js' ),
		true
	);
	wp_script_add_data( 'techviet-main', 'defer', true );
}
add_action( 'wp_enqueue_scripts', 'techviet_assets' );

/**
 * Kích thước nội dung.
 */
function techviet_content_width() {
	$GLOBALS['content_width'] = 1200;
}
add_action( 'after_setup_theme', 'techviet_content_width', 0 );

/**
 * Sắp xếp sản phẩm theo thứ tự menu, sau đó theo ngày đăng.
 *
 * @param WP_Query $query Query chính.
 */
function techviet_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'san_pham' ) || $query->is_tax( 'danh_muc_sp' ) ) {
		$query->set( 'posts_per_page', 9 );
		$query->set(
			'orderby',
			array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			)
		);
	}
}
add_action( 'pre_get_posts', 'techviet_archive_query' );

/**
 * Thêm class cho body.
 *
 * @param array $classes Class hiện có.
 * @return array
 */
function techviet_body_classes( $classes ) {
	$classes[] = 'tv-site';
	if ( is_front_page() ) {
		$classes[] = 'tv-front';
	}
	return $classes;
}
add_filter( 'body_class', 'techviet_body_classes' );

/**
 * Độ dài excerpt.
 *
 * @param int $length Số từ.
 * @return int
 */
function techviet_excerpt_length( $length ) {
	return is_admin() ? $length : 24;
}
add_filter( 'excerpt_length', 'techviet_excerpt_length' );

/**
 * Dấu "…" của excerpt.
 *
 * @return string
 */
function techviet_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'techviet_excerpt_more' );

/**
 * Tắt emoji script không cần thiết ở frontend (giảm request).
 */
function techviet_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'techviet_disable_emojis' );

/**
 * Menu dự phòng khi chưa gán menu.
 */
function techviet_menu_fallback() {
	$items = array(
		array( 'label' => 'Trang chủ', 'url' => home_url( '/' ) ),
		array( 'label' => 'Sản phẩm', 'url' => get_post_type_archive_link( 'san_pham' ) ),
		array( 'label' => 'Giới thiệu', 'url' => home_url( '/gioi-thieu/' ) ),
		array( 'label' => 'Liên hệ', 'url' => home_url( '/lien-he/' ) ),
	);

	echo '<ul class="tv-nav-list">';
	foreach ( $items as $item ) {
		printf(
			'<li class="menu-item"><a href="%1$s">%2$s</a></li>',
			esc_url( $item['url'] ),
			esc_html( $item['label'] )
		);
	}
	echo '</ul>';
}

/**
 * Lấy giá sản phẩm.
 *
 * @param int $post_id ID sản phẩm.
 * @return string
 */
function techviet_get_price( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return (string) get_post_meta( $post_id, '_tv_gia', true );
}

/**
 * Lấy nhãn sản phẩm.
 *
 * @param int $post_id ID sản phẩm.
 * @return string
 */
function techviet_get_badge( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return (string) get_post_meta( $post_id, '_tv_nhan', true );
}

/**
 * Mô tả ngắn của sản phẩm.
 *
 * @param int $post_id ID sản phẩm.
 * @param int $words   Số từ tối đa.
 * @return string
 */
function techviet_get_short_desc( $post_id = 0, $words = 22 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$short   = (string) get_post_meta( $post_id, '_tv_mo_ta_ngan', true );

	if ( '' === $short ) {
		$short = get_the_excerpt( $post_id );
	}

	return wp_trim_words( wp_strip_all_tags( $short ), $words, '…' );
}

/**
 * Ảnh sản phẩm: featured image, nếu chưa có thì dùng ảnh minh hoạ cục bộ.
 *
 * @param int    $post_id ID sản phẩm.
 * @param string $size    Kích thước ảnh.
 * @return string HTML <img> hoặc chuỗi rỗng.
 */
function techviet_get_product_image( $post_id = 0, $size = 'techviet-card' ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail(
			$post_id,
			$size,
			array(
				'class'   => 'tv-product-img',
				'loading' => 'lazy',
			)
		);
	}

	$slug    = get_post_field( 'post_name', $post_id );
	$theme   = get_template_directory();
	$uri     = get_template_directory_uri();
	$product = $theme . '/assets/images/products/' . $slug . '.svg';

	if ( file_exists( $product ) ) {
		$src = $uri . '/assets/images/products/' . $slug . '.svg';
	} else {
		$terms    = get_the_terms( $post_id, 'danh_muc_sp' );
		$cat_slug = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : 'laptop';
		$cat_file = $theme . '/assets/images/categories/' . $cat_slug . '.svg';
		$src      = file_exists( $cat_file )
			? $uri . '/assets/images/categories/' . $cat_slug . '.svg'
			: $uri . '/assets/images/placeholder.svg';
	}

	return sprintf(
		'<img class="tv-product-img" src="%1$s" alt="%2$s" width="720" height="540" loading="lazy" decoding="async">',
		esc_url( $src ),
		esc_attr( get_the_title( $post_id ) )
	);
}

/**
 * Ảnh danh mục.
 *
 * @param WP_Term $term Term danh mục.
 * @return string URL ảnh.
 */
function techviet_get_category_image( $term ) {
	$file = get_template_directory() . '/assets/images/categories/' . $term->slug . '.svg';
	if ( file_exists( $file ) ) {
		return get_template_directory_uri() . '/assets/images/categories/' . $term->slug . '.svg';
	}
	return get_template_directory_uri() . '/assets/images/placeholder.svg';
}

/**
 * Tách thông số kỹ thuật thành mảng [label, value].
 *
 * @param int $post_id ID sản phẩm.
 * @return array<int,array{label:string,value:string}>
 */
function techviet_get_specs( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$raw     = (string) get_post_meta( $post_id, '_tv_thong_so', true );

	if ( '' === $raw ) {
		return array();
	}

	$specs = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = preg_split( '/\s*[:：]\s*/', $line, 2 );
		if ( 2 === count( $parts ) ) {
			$specs[] = array(
				'label' => $parts[0],
				'value' => $parts[1],
			);
		} else {
			$specs[] = array(
				'label' => 'Thông tin',
				'value' => $line,
			);
		}
	}

	return $specs;
}

/**
 * Lấy danh mục chính của sản phẩm.
 *
 * @param int $post_id ID sản phẩm.
 * @return WP_Term|null
 */
function techviet_primary_category( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$terms   = get_the_terms( $post_id, 'danh_muc_sp' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}
	return $terms[0];
}

/**
 * Breadcrumb đơn giản.
 */
function techviet_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	echo '<nav class="tv-breadcrumb" aria-label="Đường dẫn"><div class="tv-container">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">Trang chủ</a><span aria-hidden="true">/</span>';

	if ( is_post_type_archive( 'san_pham' ) ) {
		echo '<span aria-current="page">Sản phẩm</span>';
	} elseif ( is_tax( 'danh_muc_sp' ) ) {
		echo '<a href="' . esc_url( get_post_type_archive_link( 'san_pham' ) ) . '">Sản phẩm</a><span aria-hidden="true">/</span>';
		echo '<span aria-current="page">' . esc_html( single_term_title( '', false ) ) . '</span>';
	} elseif ( is_singular( 'san_pham' ) ) {
		echo '<a href="' . esc_url( get_post_type_archive_link( 'san_pham' ) ) . '">Sản phẩm</a><span aria-hidden="true">/</span>';
		echo '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_singular() || is_page() ) {
		echo '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_archive() ) {
		echo '<span aria-current="page">' . esc_html( wp_strip_all_tags( get_the_archive_title() ) ) . '</span>';
	} elseif ( is_search() ) {
		echo '<span aria-current="page">Tìm kiếm</span>';
	} elseif ( is_404() ) {
		echo '<span aria-current="page">Không tìm thấy trang</span>';
	}

	echo '</div></nav>';
}

/**
 * Customizer: thông tin cửa hàng và nội dung hero.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function techviet_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'techviet_store',
		array(
			'title'       => 'TechViet Store — Thông tin & Trang chủ',
			'description' => 'Thay đổi hotline, địa chỉ, email và nội dung banner trang chủ.',
			'priority'    => 30,
		)
	);

	$text_fields = array(
		'techviet_announcement'   => 'Thông báo trên thanh đầu trang',
		'techviet_phone'          => 'Hotline',
		'techviet_email'          => 'Email hỗ trợ',
		'techviet_address'        => 'Địa chỉ cửa hàng',
		'techviet_hours'          => 'Giờ mở cửa',
		'techviet_hero_eyebrow'   => 'Dòng nhỏ phía trên tiêu đề banner',
		'techviet_hero_title'     => 'Tiêu đề banner',
		'techviet_hero_subtitle'  => 'Mô tả banner',
		'techviet_hero_btn_text'  => 'Nút chính — nhãn',
		'techviet_hero_btn2_text' => 'Nút phụ — nhãn',
		'techviet_facebook'       => 'Facebook URL',
		'techviet_youtube'        => 'YouTube URL',
		'techviet_zalo'           => 'Zalo URL',
		'techviet_footer_intro'   => 'Giới thiệu ngắn ở chân trang',
	);

	foreach ( $text_fields as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => techviet_defaults()[ $key ] ?? '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $label,
				'section' => 'techviet_store',
				'type'    => 'text',
			)
		);
	}

	$url_fields = array(
		'techviet_hero_btn_url'  => 'Nút chính — liên kết',
		'techviet_hero_btn2_url' => 'Nút phụ — liên kết',
	);

	foreach ( $url_fields as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => techviet_defaults()[ $key ] ?? '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $label,
				'section' => 'techviet_store',
				'type'    => 'url',
			)
		);
	}
}
add_action( 'customize_register', 'techviet_customize_register' );

/**
 * JSON-LD cho trang chi tiết sản phẩm.
 */
function techviet_product_schema() {
	if ( ! is_singular( 'san_pham' ) ) {
		return;
	}

	$post_id = get_the_ID();
	$price   = preg_replace( '/[^0-9]/', '', techviet_get_price( $post_id ) );

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Product',
		'name'        => get_the_title( $post_id ),
		'description' => techviet_get_short_desc( $post_id, 40 ),
		'url'         => get_permalink( $post_id ),
		'brand'       => array(
			'@type' => 'Brand',
			'name'  => 'TechViet',
		),
	);

	if ( $price ) {
		$schema['offers'] = array(
			'@type'         => 'Offer',
			'price'         => $price,
			'priceCurrency' => 'VND',
			'availability'  => 'https://schema.org/InStock',
			'url'           => get_permalink( $post_id ),
		);
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
	);
}
add_action( 'wp_head', 'techviet_product_schema' );
