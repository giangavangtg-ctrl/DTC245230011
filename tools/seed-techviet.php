<?php
/**
 * Seed nội dung TechViet Store — chạy qua WP-CLI:
 *   docker compose --profile tools run --rm -T wpcli eval-file /tools/seed-techviet.php
 *
 * Nguyên tắc:
 * - Idempotent: chạy nhiều lần không tạo bản ghi trùng.
 * - Chỉ cập nhật trang/sản phẩm do script này quản lý (theo slug).
 * - Không xóa page/post khác, không xóa term, không đụng dữ liệu MySQL thủ công.
 * - Toàn bộ nội dung tạo qua WordPress API (wp_insert_post / wp_update_post).
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 1 );
}

/**
 * Dựng nội dung Gutenberg từ mảng block đơn giản.
 *
 * @param array<int,array{0:string,1:string|array<int,string>}> $blocks Danh sách block.
 * @return string
 */
function tv_seed_blocks( array $blocks ) {
	$out = array();

	foreach ( $blocks as $block ) {
		$type = $block[0];
		$data = $block[1];

		switch ( $type ) {
			case 'h2':
				$out[] = "<!-- wp:heading -->\n<h2>" . $data . "</h2>\n<!-- /wp:heading -->";
				break;
			case 'h3':
				$out[] = "<!-- wp:heading {\"level\":3} -->\n<h3>" . $data . "</h3>\n<!-- /wp:heading -->";
				break;
			case 'p':
				$out[] = "<!-- wp:paragraph -->\n<p>" . $data . "</p>\n<!-- /wp:paragraph -->";
				break;
			case 'ul':
			case 'ol':
				$items = '';
				foreach ( (array) $data as $item ) {
					$items .= '<li>' . $item . '</li>';
				}
				$out[] = "<!-- wp:list -->\n<" . $type . ">" . $items . "</" . $type . ">\n<!-- /wp:list -->";
				break;
			case 'quote':
				$out[] = "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><p>" . $data . "</p></blockquote>\n<!-- /wp:quote -->";
				break;
			case 'shortcode':
				$out[] = "<!-- wp:shortcode -->\n" . $data . "\n<!-- /wp:shortcode -->";
				break;
		}
	}

	return implode( "\n\n", $out );
}

/**
 * Tạo hoặc cập nhật một trang theo slug.
 *
 * @param array<string,mixed> $args Thông tin trang.
 * @return int ID trang.
 */
function tv_seed_page( array $args ) {
	$existing = get_page_by_path( $args['slug'] );
	$data     = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $args['title'],
		'post_name'    => $args['slug'],
		'post_content' => $args['content'],
		'post_excerpt' => $args['excerpt'] ?? '',
	);

	if ( $existing ) {
		$data['ID'] = $existing->ID;
		$id         = wp_update_post( wp_slash( $data ), true );
	} else {
		$id = wp_insert_post( wp_slash( $data ), true );
	}

	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( 'Trang "' . $args['title'] . '": ' . $id->get_error_message() );
		return 0;
	}

	WP_CLI::log( sprintf( '  %s trang: %s (#%d /%s/)', $existing ? 'Cập nhật' : 'Tạo', $args['title'], $id, $args['slug'] ) );
	return (int) $id;
}

WP_CLI::log( '=== TechViet Store — seed nội dung ===' );

/* -------------------------------------------------------------------------
 * 1. Cấu hình site
 * ---------------------------------------------------------------------- */
update_option( 'blogname', 'TechViet Store' );
update_option( 'blogdescription', 'Công nghệ hiện đại – Nâng tầm trải nghiệm' );
update_option( 'blog_public', 1 );
update_option( 'timezone_string', 'Asia/Ho_Chi_Minh' );
update_option( 'date_format', 'd/m/Y' );
update_option( 'time_format', 'H:i' );
update_option( 'start_of_week', 1 );
update_option( 'posts_per_page', 9 );
update_option( 'default_comment_status', 'closed' );
update_option( 'default_ping_status', 'closed' );
update_option( 'home', 'http://localhost:8081' );
update_option( 'siteurl', 'http://localhost:8081' );
update_option( 'permalink_structure', '/%postname%/' );

WP_CLI::log( '  Đã cấu hình tên site, múi giờ, permalink /%postname%/' );

/* -------------------------------------------------------------------------
 * 2. Danh mục sản phẩm
 * ---------------------------------------------------------------------- */
$tv_terms = array(
	'laptop'          => array(
		'name'        => 'Laptop',
		'description' => 'Laptop văn phòng, gaming và đồ hoạ mỏng nhẹ, cấu hình rõ ràng, bảo hành chính hãng 24 tháng.',
	),
	'dien-thoai'      => array(
		'name'        => 'Điện thoại',
		'description' => 'Điện thoại 5G, camera chất lượng và pin bền cho công việc lẫn giải trí, hỗ trợ trả góp 0%.',
	),
	'phu-kien'        => array(
		'name'        => 'Phụ kiện công nghệ',
		'description' => 'Tai nghe, bàn phím, pin sạc và phụ kiện chính hãng giúp góc làm việc gọn gàng, hiệu quả hơn.',
	),
);

$tv_term_ids = array();

foreach ( $tv_terms as $slug => $term ) {
	$existing = term_exists( $slug, 'danh_muc_sp' );

	if ( $existing ) {
		$term_id = is_array( $existing ) ? (int) $existing['term_id'] : (int) $existing;
		wp_update_term(
			$term_id,
			'danh_muc_sp',
			array(
				'name'        => $term['name'],
				'description' => $term['description'],
			)
		);
		WP_CLI::log( '  Cập nhật danh mục: ' . $term['name'] . ' (#' . $term_id . ')' );
	} else {
		$created = wp_insert_term(
			$term['name'],
			'danh_muc_sp',
			array(
				'slug'        => $slug,
				'description' => $term['description'],
			)
		);
		if ( is_wp_error( $created ) ) {
			WP_CLI::warning( 'Danh mục ' . $term['name'] . ': ' . $created->get_error_message() );
			continue;
		}
		$term_id = (int) $created['term_id'];
		WP_CLI::log( '  Tạo danh mục: ' . $term['name'] . ' (#' . $term_id . ')' );
	}

	$tv_term_ids[ $slug ] = $term_id;
}

/* -------------------------------------------------------------------------
 * 3. Trang nội dung
 * ---------------------------------------------------------------------- */
$tv_home_blocks = array(
	array( 'h2', 'Công nghệ chính hãng cho mọi nhu cầu' ),
	array( 'p', 'TechViet Store là hệ thống bán lẻ laptop, điện thoại và phụ kiện công nghệ tại TP. Hồ Chí Minh. Mỗi sản phẩm đều được kiểm tra kỹ trước khi giao đến khách hàng, đi kèm thông tin cấu hình rõ ràng và chính sách bảo hành minh bạch.' ),
	array( 'p', 'Chúng tôi tin rằng một sản phẩm công nghệ tốt không chỉ mạnh mẽ mà còn phải phù hợp với cách bạn làm việc, học tập và giải trí mỗi ngày.' ),
	array(
		'ul',
		array(
			'<strong>Chính hãng 100%</strong> — bảo hành theo tiêu chuẩn nhà sản xuất, hỗ trợ 1 đổi 1 trong 30 ngày đầu.',
			'<strong>Tư vấn đúng nhu cầu</strong> — phân tích ngân sách và mục đích sử dụng trước khi đề xuất.',
			'<strong>Hậu mãi tận tâm</strong> — hỗ trợ kỹ thuật 24/7, giao hàng toàn quốc và xuất hoá đơn VAT.',
		)
	),
);

$tv_about_blocks = array(
	array( 'p', 'TechViet Store ra đời năm 2014 từ một cửa hàng nhỏ tại Quận 7, TP. Hồ Chí Minh. Chúng tôi bắt đầu với niềm tin đơn giản: người mua công nghệ xứng đáng nhận được thông tin rõ ràng, sản phẩm chính hãng và dịch vụ hậu mãi tử tế.' ),
	array( 'h2', 'Hành trình hơn 10 năm đồng hành' ),
	array( 'p', 'Từ những chiếc laptop văn phòng đầu tiên, TechViet Store đã mở rộng danh mục sang điện thoại, phụ kiện và thiết bị cho doanh nghiệp. Đến nay, hơn 50.000 khách hàng cá nhân và tổ chức đã tin chọn TechViet cho nhu cầu công nghệ hằng ngày.' ),
	array( 'p', 'Chúng tôi vận hành showroom trải nghiệm, đội ngũ tư vấn kỹ thuật và quy trình bảo hành tập trung — để mỗi sản phẩm bán ra đều có người chịu trách nhiệm đến cùng.' ),
	array( 'h2', 'Chúng tôi khác biệt ở đâu?' ),
	array(
		'ul',
		array(
			'<strong>Tư vấn dựa trên nhu cầu thật</strong>, không chạy theo doanh số hay đẩy cấu hình vượt ngân sách.',
			'<strong>Giá và chính sách công khai</strong> trên từng trang sản phẩm, không phụ phí ẩn.',
			'<strong>Kỹ thuật viên nội bộ</strong> hỗ trợ cài đặt, chuyển dữ liệu và xử lý sự cố tận nơi.',
			'<strong>Bảo hành 24 tháng</strong>, 1 đổi 1 trong 30 ngày đầu nếu lỗi nhà sản xuất.',
		)
	),
	array( 'h2', 'Định hướng phát triển' ),
	array( 'p', 'TechViet Store hướng đến mô hình bán lẻ công nghệ “hiểu người dùng”: danh mục được tuyển chọn, nội dung so sánh dễ hiểu và trải nghiệm hậu mãi liền mạch. Chúng tôi tiếp tục đầu tư vào đội ngũ, showroom và hệ thống vận hành để phục vụ khách hàng nhanh hơn, minh bạch hơn.' ),
);

$tv_contact_blocks = array(
	array( 'shortcode', '[techviet_contact_form]' ),
);

$tv_warranty_blocks = array(
	array( 'p', 'TechViet Store áp dụng chính sách bảo hành minh bạch cho toàn bộ sản phẩm bán ra. Thông tin dưới đây giúp bạn hiểu rõ quyền lợi và quy trình tiếp nhận.' ),
	array( 'h2', '1. Thời hạn bảo hành' ),
	array(
		'ul',
		array(
			'Laptop, điện thoại: <strong>24 tháng</strong> theo tiêu chuẩn nhà sản xuất.',
			'Tai nghe, bàn phím, pin sạc: <strong>12 tháng</strong>.',
			'Phụ kiện đi kèm (cáp, sạc, hộp): <strong>6 tháng</strong>.',
			'Thời hạn được tính từ ngày xuất hoá đơn hoặc ngày kích hoạt bảo hành điện tử, tuỳ thời điểm nào muộn hơn.',
		)
	),
	array( 'h2', '2. Điều kiện được bảo hành' ),
	array(
		'ul',
		array(
			'Sản phẩm còn tem niêm phong, serial trùng khớp với hoá đơn.',
			'Lỗi phát sinh từ nhà sản xuất trong điều kiện sử dụng bình thường.',
			'Không có dấu hiệu rơi vỡ, vào nước, cháy nổ hoặc sửa chữa tại nơi không được uỷ quyền.',
		)
	),
	array( 'h2', '3. Quy trình tiếp nhận' ),
	array(
		'ol',
		array(
			'Liên hệ hotline <strong>1900 6868</strong> hoặc mang sản phẩm đến showroom gần nhất.',
			'Kỹ thuật viên kiểm tra, xác nhận tình trạng và tạo phiếu tiếp nhận.',
			'Thời gian xử lý trung bình 3–7 ngày làm việc; sản phẩm cần gửi hãng có thể lâu hơn và sẽ được thông báo cụ thể.',
			'Nhận lại sản phẩm tại showroom hoặc qua chuyển phát nếu bạn ở tỉnh khác.',
		)
	),
	array( 'h2', '4. Trường hợp không thuộc phạm vi bảo hành' ),
	array(
		'ul',
		array(
			'Thiết bị đã bị tháo, sửa hoặc thay thế linh kiện bên ngoài.',
			'Hư hỏng do va đập, chất lỏng, hoả hoạn, sét đánh hoặc nguồn điện không ổn định.',
			'Hao mòn tự nhiên của pin, vỏ, màn hình sau thời gian dài sử dụng.',
			'Dữ liệu cá nhân trong thiết bị: khách hàng cần sao lưu trước khi gửi bảo hành.',
		)
	),
	array( 'h2', '5. Liên hệ bảo hành' ),
	array( 'p', 'Hotline: <strong>1900 6868</strong> · Email: <strong>hotro@techviet.vn</strong> · Địa chỉ: <strong>123 Nguyễn Văn Linh, Quận 7, TP. Hồ Chí Minh</strong>. Thời gian tiếp nhận: 08:00 – 21:00 hằng ngày.' ),
);

$tv_return_blocks = array(
	array( 'p', 'TechViet Store hỗ trợ đổi trả linh hoạt để bạn yên tâm trải nghiệm sản phẩm. Chính sách dưới đây áp dụng cho đơn hàng mua trực tiếp tại showroom và đặt qua hotline/website.' ),
	array( 'h2', '1. Thời gian đổi trả' ),
	array(
		'ul',
		array(
			'<strong>Đổi mới 1:1 trong 30 ngày</strong> nếu sản phẩm lỗi do nhà sản xuất.',
			'<strong>Đổi sang sản phẩm khác trong 7 ngày</strong> nếu sản phẩm còn nguyên hộp, chưa kích hoạt và không có dấu hiệu sử dụng.',
			'<strong>Trả hàng hoàn tiền trong 7 ngày</strong> với sản phẩm lỗi nhà sản xuất không còn hàng thay thế.',
		)
	),
	array( 'h2', '2. Điều kiện đổi trả' ),
	array(
		'ul',
		array(
			'Có hoá đơn mua hàng hoặc thông tin tra cứu đơn hàng.',
			'Sản phẩm đầy đủ phụ kiện, hộp, tem và quà tặng đi kèm (nếu có).',
			'Không trầy xước do người dùng, không vào nước, không tự tháo sửa.',
			'Với sản phẩm đã kích hoạt bảo hành điện tử, chỉ áp dụng đổi trả khi có lỗi kỹ thuật.',
		)
	),
	array( 'h2', '3. Chi phí và thời gian xử lý' ),
	array(
		'ul',
		array(
			'Miễn phí đổi trả nếu lỗi thuộc về nhà sản xuất hoặc do TechViet Store giao sai sản phẩm.',
			'Khách hàng chịu chi phí vận chuyển nếu đổi sang sản phẩm khác vì thay đổi nhu cầu.',
			'Thời gian hoàn tiền: 3–7 ngày làm việc kể từ khi TechViet Store nhận lại và kiểm tra sản phẩm.',
		)
	),
	array( 'h2', '4. Cách thức yêu cầu đổi trả' ),
	array(
		'ol',
		array(
			'Gọi hotline <strong>1900 6868</strong> hoặc email <strong>hotro@techviet.vn</strong> kèm mã đơn hàng.',
			'Cung cấp hình ảnh/video tình trạng sản phẩm để được hỗ trợ nhanh.',
			'Mang sản phẩm đến showroom hoặc gửi chuyển phát theo hướng dẫn của nhân viên.',
			'Nhận sản phẩm thay thế hoặc tiền hoàn theo phương thức đã thống nhất.',
		)
	),
	array( 'quote', 'TechViet Store không bán hàng giả, hàng nhái hoặc hàng đã qua sử dụng được bán như hàng mới. Mọi sản phẩm đều có tem và chính sách bảo hành rõ ràng.' ),
);

$tv_pages = array(
	array(
		'slug'    => 'trang-chu',
		'title'   => 'Trang chủ',
		'content' => tv_seed_blocks( $tv_home_blocks ),
	),
	array(
		'slug'    => 'gioi-thieu',
		'title'   => 'Giới thiệu',
		'content' => tv_seed_blocks( $tv_about_blocks ),
		'excerpt' => 'Hơn 10 năm đồng hành cùng khách hàng Việt với sản phẩm công nghệ chính hãng và dịch vụ hậu mãi tận tâm.',
	),
	array(
		'slug'    => 'lien-he',
		'title'   => 'Liên hệ',
		'content' => tv_seed_blocks( $tv_contact_blocks ),
		'excerpt' => 'Gọi hotline 1900 6868, gửi yêu cầu tư vấn hoặc ghé showroom TechViet Store tại Quận 7, TP. Hồ Chí Minh.',
	),
	array(
		'slug'    => 'chinh-sach-bao-hanh',
		'title'   => 'Chính sách bảo hành',
		'content' => tv_seed_blocks( $tv_warranty_blocks ),
		'excerpt' => 'Bảo hành chính hãng 24 tháng, tiếp nhận và xử lý minh bạch trong 3–7 ngày làm việc.',
	),
	array(
		'slug'    => 'chinh-sach-doi-tra',
		'title'   => 'Chính sách đổi trả',
		'content' => tv_seed_blocks( $tv_return_blocks ),
		'excerpt' => 'Đổi mới 1:1 trong 30 ngày nếu lỗi nhà sản xuất, đổi sản phẩm trong 7 ngày với điều kiện còn nguyên hộp.',
	),
);

$tv_page_ids = array();
foreach ( $tv_pages as $tv_page ) {
	$id = tv_seed_page( $tv_page );
	if ( $id ) {
		$tv_page_ids[ $tv_page['slug'] ] = $id;
	}
}

/* -------------------------------------------------------------------------
 * 4. Sản phẩm
 * ---------------------------------------------------------------------- */
$tv_products = array(
	array(
		'slug'       => 'techviet-ultrabook-14',
		'title'      => 'TechViet UltraBook 14',
		'category'   => 'laptop',
		'price'      => '18.990.000đ',
		'badge'      => 'Bán chạy',
		'short'      => 'Laptop văn phòng mỏng nhẹ, màn hình 2.2K sắc nét và pin 60Wh cho cả ngày làm việc.',
		'intro'      => 'TechViet UltraBook 14 cân bằng giữa hiệu năng, thời lượng pin và tính di động. Máy phù hợp với nhân viên văn phòng, sinh viên và người làm nội dung cần một thiết bị nhẹ, bền, khởi động nhanh mỗi ngày.',
		'highlights' => array(
			'<strong>Intel Core i5-1335U</strong> 10 nhân, xử lý mượt tác vụ văn phòng, Photoshop cơ bản và họp trực tuyến.',
			'<strong>Màn hình 14 inch 2.2K</strong> 300 nit, viền mỏng, hiển thị rõ chữ và màu sắc trung thực.',
			'<strong>16GB RAM LPDDR5</strong> và SSD 512GB NVMe giúp mở nhiều ứng dụng mà không giật lag.',
			'<strong>Pin 60Wh, sạc nhanh 65W USB-C</strong> — dùng trọn ngày làm việc, sạc 50% trong khoảng 35 phút.',
			'<strong>Khối lượng 1.35kg</strong>, vỏ nhôm cứng cáp, bản lề mở 180 độ.',
		),
		'audience'   => 'Phù hợp với nhân viên văn phòng, sinh viên kinh tế – kỹ thuật và người thường xuyên di chuyển. Nếu bạn cần thêm sức mạnh đồ hoạ, hãy tham khảo TechViet Gaming Pro 16.',
		'specs'      => array(
			'CPU: Intel Core i5-1335U (10 nhân, 12 luồng)',
			'RAM: 16GB LPDDR5 5200MHz',
			'Ổ cứng: 512GB SSD NVMe PCIe 4.0',
			'Màn hình: 14 inch 2.2K (2240 x 1400), 300 nit',
			'Đồ hoạ: Intel Iris Xe Graphics',
			'Pin: 60Wh, sạc nhanh 65W USB-C',
			'Khối lượng: 1.35kg',
			'Bảo hành: 24 tháng chính hãng',
		),
		'featured'   => true,
		'order'      => 1,
	),
	array(
		'slug'       => 'techviet-gaming-pro-16',
		'title'      => 'TechViet Gaming Pro 16',
		'category'   => 'laptop',
		'price'      => '32.490.000đ',
		'badge'      => 'Hiệu năng cao',
		'short'      => 'Laptop gaming 16 inch 165Hz, RTX 4060 và hệ thống tản nhiệt 3 chế độ cho game thủ lẫn người làm đồ hoạ.',
		'intro'      => 'TechViet Gaming Pro 16 được thiết kế cho những tác vụ nặng: chơi game AAA, dựng video 4K và render 3D. Hệ thống tản nhiệt nâng cấp giữ hiệu năng ổn định trong thời gian dài, trong khi màn hình 165Hz mang lại trải nghiệm hình ảnh mượt mà.',
		'highlights' => array(
			'<strong>Intel Core i7-13650HX</strong> và <strong>RTX 4060 8GB</strong> xử lý tốt game AAA ở thiết lập cao.',
			'<strong>Màn hình 16 inch WQXGA 165Hz</strong>, 100% sRGB, phù hợp chơi game và chỉnh màu.',
			'<strong>16GB DDR5</strong> nâng cấp tối đa 32GB, SSD 1TB NVMe tốc độ cao.',
			'<strong>Tản nhiệt 2 quạt – 4 ống đồng</strong>, ba chế độ Hiệu năng / Cân bằng / Yên tĩnh.',
			'<strong>Bàn phím RGB</strong>, phím cơ học quang, hành trình phím 1.8mm.',
		),
		'audience'   => 'Dành cho game thủ, sinh viên đồ hoạ, người dựng video và kỹ sư cần hiệu năng cao. Máy nặng 2.45kg nên phù hợp làm việc tại bàn hoặc di chuyển có balo chuyên dụng.',
		'specs'      => array(
			'CPU: Intel Core i7-13650HX',
			'GPU: NVIDIA GeForce RTX 4060 8GB GDDR6',
			'RAM: 16GB DDR5 4800MHz (2 khe, tối đa 32GB)',
			'Ổ cứng: 1TB SSD NVMe PCIe 4.0',
			'Màn hình: 16 inch WQXGA 165Hz, 100% sRGB',
			'Tản nhiệt: 2 quạt, 4 ống đồng, 3 chế độ hiệu năng',
			'Pin: 90Wh, sạc 230W',
			'Khối lượng: 2.45kg',
		),
		'featured'   => true,
		'order'      => 2,
	),
	array(
		'slug'       => 'techviet-air-13',
		'title'      => 'TechViet Air 13',
		'category'   => 'laptop',
		'price'      => '15.290.000đ',
		'badge'      => 'Mỏng nhẹ',
		'short'      => 'Laptop 13.3 inch chỉ 1.05kg, pin 18 giờ và màn hình 2.5K chống chói — bạn đồng hành cho ngày dài di chuyển.',
		'intro'      => 'TechViet Air 13 là chiếc laptop dành cho người ưu tiên sự nhẹ nhàng. Máy khởi động nhanh, chạy êm, thời lượng pin đủ cho một ngày học tập hoặc làm việc ngoài văn phòng mà không cần mang theo sạc.',
		'highlights' => array(
			'<strong>Chỉ 1.05kg</strong>, dày 14.9mm, dễ dàng bỏ vào balo hoặc túi tote.',
			'<strong>Pin 18 giờ</strong> theo điều kiện sử dụng văn phòng, sạc nhanh USB-C.',
			'<strong>Màn hình 13.3 inch 2.5K</strong>, 400 nit, chống chói, phủ 100% sRGB.',
			'<strong>Bàn phím êm, vân tay tích hợp nút nguồn</strong>, webcam Full HD có che riêng tư.',
			'<strong>Không quạt tản nhiệt</strong> — vận hành yên tĩnh trong lớp học, quán cà phê.',
		),
		'audience'   => 'Phù hợp với sinh viên, nhân viên văn phòng, người viết nội dung và khách hàng thường xuyên di chuyển. Máy xử lý tốt tác vụ văn phòng, họp online và chỉnh ảnh nhẹ.',
		'specs'      => array(
			'CPU: Intel Core i5-1230U',
			'RAM: 16GB LPDDR5',
			'Ổ cứng: 512GB SSD NVMe',
			'Màn hình: 13.3 inch 2.5K, 400 nit, chống chói',
			'Pin: 18 giờ sử dụng thông thường',
			'Khối lượng: 1.05kg',
			'Bảo mật: Vân tay, webcam có che riêng tư',
			'Bảo hành: 24 tháng chính hãng',
		),
		'featured'   => true,
		'order'      => 3,
	),
	array(
		'slug'       => 'techviet-nova-5g',
		'title'      => 'TechViet Nova 5G',
		'category'   => 'dien-thoai',
		'price'      => '9.990.000đ',
		'badge'      => '5G',
		'short'      => 'Điện thoại 5G màn hình AMOLED 120Hz, camera 108MP OIS và sạc nhanh 67W trong tầm giá dưới 10 triệu.',
		'intro'      => 'TechViet Nova 5G mang đến trải nghiệm mượt mà với màn hình AMOLED 120Hz, hiệu năng ổn định và camera 108MP có chống rung quang học. Đây là lựa chọn cân bằng cho người dùng cần một chiếc điện thoại đa năng, pin bền và kết nối 5G.',
		'highlights' => array(
			'<strong>Màn hình 6.7 inch AMOLED 120Hz</strong>, độ sáng 1.200 nit, hiển thị rõ ngoài trời.',
			'<strong>Camera 108MP OIS</strong> + góc rộng 8MP, chụp đêm rõ nét, quay video 4K.',
			'<strong>Dimensity 7200</strong>, 12GB RAM và 256GB bộ nhớ, chơi game phổ biến ổn định.',
			'<strong>Pin 5.000mAh, sạc 67W</strong> — sạc đầy khoảng 45 phút.',
			'<strong>5G, Wi-Fi 6, NFC</strong>, loa kép và kháng nước IP54.',
		),
		'audience'   => 'Phù hợp với người dùng cá nhân, nhân viên văn phòng và sinh viên cần điện thoại 5G pin bền, camera tốt trong tầm giá dưới 10 triệu.',
		'specs'      => array(
			'Màn hình: 6.7 inch AMOLED, 120Hz',
			'Chip: MediaTek Dimensity 7200',
			'RAM/Bộ nhớ: 12GB / 256GB',
			'Camera sau: 108MP OIS + 8MP góc rộng + 2MP macro',
			'Camera trước: 16MP',
			'Pin: 5.000mAh, sạc nhanh 67W',
			'Hệ điều hành: Android 14',
			'Kết nối: 5G, Wi-Fi 6, NFC',
		),
		'featured'   => true,
		'order'      => 4,
	),
	array(
		'slug'       => 'techviet-nova-ultra',
		'title'      => 'TechViet Nova Ultra',
		'category'   => 'dien-thoai',
		'price'      => '16.490.000đ',
		'badge'      => 'Cao cấp',
		'short'      => 'Flagship với Snapdragon 8 Gen 3, camera 200MP và màn hình LTPO 144Hz — hiệu năng đỉnh cao, thiết kế sang trọng.',
		'intro'      => 'TechViet Nova Ultra là chiếc flagship dành cho người dùng chuyên nghiệp: hiệu năng Snapdragon 8 Gen 3, cụm camera 200MP và màn hình LTPO 144Hz. Máy hoàn thiện từ khung kim loại và kính cường lực, đạt chuẩn chống nước IP68.',
		'highlights' => array(
			'<strong>Snapdragon 8 Gen 3</strong>, 16GB RAM và 512GB UFS 4.0 — xử lý mọi tác vụ nặng.',
			'<strong>Camera chính 200MP OIS</strong> + tele 50MP + góc rộng 50MP, quay video 8K.',
			'<strong>Màn hình 6.8 inch LTPO AMOLED 144Hz</strong>, 2.600 nit, viền cực mỏng.',
			'<strong>Pin 5.500mAh</strong>, sạc có dây 120W và sạc không dây 50W.',
			'<strong>IP68</strong>, loa kép, vân tay siêu âm dưới màn hình.',
		),
		'audience'   => 'Dành cho người dùng cao cấp, nhà sáng tạo nội dung và doanh nhân cần camera tốt, hiệu năng mạnh và thiết kế chỉn chu. Hỗ trợ trả góp 0% qua thẻ tín dụng.',
		'specs'      => array(
			'Màn hình: 6.8 inch LTPO AMOLED, 144Hz, 2.600 nit',
			'Chip: Snapdragon 8 Gen 3',
			'RAM/Bộ nhớ: 16GB / 512GB',
			'Camera sau: 200MP OIS + 50MP tele + 50MP góc rộng',
			'Pin: 5.500mAh, sạc 120W có dây / 50W không dây',
			'Chuẩn chống nước: IP68',
			'Bảo mật: Vân tay siêu âm, Face unlock',
			'Bảo hành: 24 tháng chính hãng',
		),
		'featured'   => true,
		'order'      => 5,
	),
	array(
		'slug'       => 'techviet-lite-4g',
		'title'      => 'TechViet Lite 4G',
		'category'   => 'dien-thoai',
		'price'      => '4.290.000đ',
		'badge'      => 'Giá tốt',
		'short'      => 'Điện thoại pin 5.000mAh, màn hình 90Hz và camera 50MP — lựa chọn tiết kiệm, bền bỉ cho nhu cầu cơ bản.',
		'intro'      => 'TechViet Lite 4G tập trung vào những gì người dùng cần nhất: pin lâu, màn hình lớn và độ bền. Máy phù hợp làm điện thoại chính cho nhu cầu cơ bản hoặc máy phụ cho công việc, học online.',
		'highlights' => array(
			'<strong>Pin 5.000mAh</strong>, sử dụng thoải mái 2 ngày với nhu cầu cơ bản.',
			'<strong>Màn hình 6.6 inch 90Hz</strong>, vuốt chạm mượt, chữ hiển thị rõ.',
			'<strong>Camera 50MP</strong> chụp đủ sáng tốt, quay video Full HD.',
			'<strong>8GB RAM / 128GB</strong>, hỗ trợ thẻ nhớ microSD.',
			'<strong>Vân tay cạnh bên</strong>, jack 3.5mm, loa ngoài to rõ.',
		),
		'audience'   => 'Phù hợp với học sinh, sinh viên, người lớn tuổi hoặc người cần điện thoại phụ pin bền. Không phù hợp nếu bạn cần chơi game nặng hoặc quay video chuyên nghiệp.',
		'specs'      => array(
			'Màn hình: 6.6 inch IPS LCD, 90Hz',
			'Chip: MediaTek Helio G88',
			'RAM/Bộ nhớ: 8GB / 128GB (hỗ trợ thẻ nhớ)',
			'Camera sau: 50MP + 2MP',
			'Pin: 5.000mAh, sạc 18W',
			'Bảo mật: Cảm biến vân tay cạnh bên',
			'Kết nối: 4G LTE, Wi-Fi, Bluetooth 5.0',
			'Bảo hành: 24 tháng chính hãng',
		),
		'featured'   => false,
		'order'      => 6,
	),
	array(
		'slug'       => 'techviet-buds-pro',
		'title'      => 'TechViet Buds Pro',
		'category'   => 'phu-kien',
		'price'      => '1.890.000đ',
		'badge'      => 'Chống ồn',
		'short'      => 'Tai nghe không dây chống ồn 42dB, pin 30 giờ và chế độ xuyên âm — tập trung làm việc, thoải mái di chuyển.',
		'intro'      => 'TechViet Buds Pro kết hợp chống ồn chủ động mạnh mẽ với âm thanh chi tiết và thời lượng pin dài. Thiết kế in-ear ôm tai, phù hợp cho cả không gian văn phòng lẫn chuyến đi dài.',
		'highlights' => array(
			'<strong>Chống ồn chủ động 42dB</strong>, chế độ xuyên âm khi cần nghe thông báo.',
			'<strong>Driver 11mm</strong>, âm bass chắc, mid rõ, hỗ trợ codec AAC.',
			'<strong>Pin 8 giờ</strong> trên tai nghe, tổng 30 giờ cùng hộp sạc.',
			'<strong>Bluetooth 5.3 multipoint</strong> — kết nối đồng thời laptop và điện thoại.',
			'<strong>IPX5</strong>, cảm biến chạm, sạc USB-C và không dây chuẩn Qi.',
		),
		'audience'   => 'Phù hợp với nhân viên văn phòng, sinh viên học online và người thường xuyên di chuyển. Nếu bạn cần tai nghe chụp tai cho studio, hãy liên hệ để được tư vấn dòng chuyên dụng.',
		'specs'      => array(
			'Driver: 11mm, âm thanh Hi-Res',
			'Chống ồn: ANC chủ động 42dB',
			'Thời lượng pin: 8 giờ (tai nghe) / 30 giờ (kèm hộp sạc)',
			'Kết nối: Bluetooth 5.3, multipoint',
			'Chuẩn chống nước: IPX5',
			'Sạc: USB-C và sạc không dây',
			'Phụ kiện: 3 bộ nút tai, cáp USB-C',
			'Bảo hành: 12 tháng',
		),
		'featured'   => true,
		'order'      => 7,
	),
	array(
		'slug'       => 'techviet-keyboard-k87',
		'title'      => 'TechViet Keyboard K87',
		'category'   => 'phu-kien',
		'price'      => '1.290.000đ',
		'badge'      => 'Hot-swap',
		'short'      => 'Bàn phím cơ TKL 87 phím, hot-swap, kết nối 3 chế độ và đèn RGB — gõ êm, dễ nâng cấp switch.',
		'intro'      => 'TechViet Keyboard K87 là bàn phím cơ không dây dành cho người dùng cần sự linh hoạt: layout TKL gọn gàng, switch hot-swap dễ thay, kết nối Bluetooth/2.4GHz/USB-C và đèn nền RGB tuỳ chỉnh.',
		'highlights' => array(
			'<strong>Layout 87 phím TKL</strong>, tiết kiệm diện tích bàn làm việc.',
			'<strong>Hot-swap 3/5 pin</strong> — thay switch mà không cần hàn.',
			'<strong>Kết nối 3 chế độ</strong>: Bluetooth 5.1, 2.4GHz và USB-C có dây.',
			'<strong>Đèn RGB 16,8 triệu màu</strong>, nhiều hiệu ứng và phím macro.',
			'<strong>Pin 4.000mAh</strong>, dùng đến 120 giờ khi tắt LED.',
		),
		'audience'   => 'Phù hợp với người làm văn phòng, lập trình viên và game thủ thích bàn phím cơ gọn gàng. Switch Red phù hợp gõ nhanh, không gây tiếng ồn lớn.',
		'specs'      => array(
			'Layout: 87 phím (TKL)',
			'Switch: Red linear, hot-swap 3/5 pin',
			'Kết nối: Bluetooth 5.1 / 2.4GHz / USB-C',
			'Đèn nền: RGB 16,8 triệu màu',
			'Pin: 4.000mAh, dùng 120 giờ khi tắt LED',
			'Kê tay: Nhựa ABS êm',
			'Phụ kiện: Cáp USB-C, keycap rút, hộp đựng',
			'Bảo hành: 12 tháng',
		),
		'featured'   => false,
		'order'      => 8,
	),
	array(
		'slug'       => 'techviet-powerbank-20k',
		'title'      => 'TechViet PowerBank 20K',
		'category'   => 'phu-kien',
		'price'      => '790.000đ',
		'badge'      => 'Sạc nhanh',
		'short'      => 'Pin sạc dự phòng 20.000mAh, sạc nhanh 22.5W và 3 cổng — đủ pin cho điện thoại, tai nghe và tablet.',
		'intro'      => 'TechViet PowerBank 20K cung cấp dung lượng lớn trong thân máy gọn gàng. Màn hình LED hiển thị phần trăm pin, ba cổng sạc và chuẩn sạc nhanh 22.5W giúp bạn yên tâm trong ngày dài.',
		'highlights' => array(
			'<strong>Dung lượng 20.000mAh</strong>, sạc đầy điện thoại 4–5 lần.',
			'<strong>Sạc nhanh 22.5W</strong> qua USB-C PD và QC 3.0.',
			'<strong>3 cổng</strong>: 2 USB-A và 1 USB-C vào/ra, sạc đồng thời 3 thiết bị.',
			'<strong>Màn hình LED</strong> hiển thị chính xác phần trăm pin còn lại.',
			'<strong>Bảo vệ 8 lớp</strong>: quá dòng, quá áp, quá nhiệt, ngắn mạch.',
		),
		'audience'   => 'Phù hợp với người di chuyển nhiều, nhân viên kinh doanh, sinh viên và gia đình cần nguồn pin dự phòng cho nhiều thiết bị.',
		'specs'      => array(
			'Dung lượng: 20.000mAh',
			'Công suất: 22.5W (USB-C PD + QC 3.0)',
			'Cổng: 2 USB-A, 1 USB-C (vào/ra)',
			'Màn hình: LED hiển thị phần trăm pin',
			'Sạc đồng thời: 3 thiết bị',
			'An toàn: Bảo vệ quá dòng, quá áp, quá nhiệt',
			'Khối lượng: 395g',
			'Bảo hành: 12 tháng',
		),
		'featured'   => false,
		'order'      => 9,
	),
);

$tv_product_count = 0;

foreach ( $tv_products as $product ) {
	$content_blocks = array(
		array( 'p', $product['intro'] ),
		array( 'h2', 'Điểm nổi bật' ),
		array( 'ul', $product['highlights'] ),
		array( 'h2', 'Phù hợp với ai?' ),
		array( 'p', $product['audience'] ),
	);

	$post_data = array(
		'post_type'    => 'san_pham',
		'post_status'  => 'publish',
		'post_title'   => $product['title'],
		'post_name'    => $product['slug'],
		'post_content' => tv_seed_blocks( $content_blocks ),
		'post_excerpt' => $product['short'],
		'menu_order'   => $product['order'],
	);

	$existing = get_page_by_path( $product['slug'], OBJECT, 'san_pham' );

	if ( $existing ) {
		$post_data['ID'] = $existing->ID;
		$post_id         = wp_update_post( wp_slash( $post_data ), true );
		$action          = 'Cập nhật';
	} else {
		$post_id = wp_insert_post( wp_slash( $post_data ), true );
		$action  = 'Tạo';
	}

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( 'Sản phẩm ' . $product['title'] . ': ' . $post_id->get_error_message() );
		continue;
	}

	$post_id = (int) $post_id;

	update_post_meta( $post_id, '_tv_gia', $product['price'] );
	update_post_meta( $post_id, '_tv_nhan', $product['badge'] );
	update_post_meta( $post_id, '_tv_mo_ta_ngan', $product['short'] );
	update_post_meta( $post_id, '_tv_thong_so', implode( "\n", $product['specs'] ) );

	if ( $product['featured'] ) {
		update_post_meta( $post_id, '_tv_noi_bat', '1' );
	} else {
		delete_post_meta( $post_id, '_tv_noi_bat' );
	}

	if ( isset( $tv_term_ids[ $product['category'] ] ) ) {
		wp_set_object_terms( $post_id, array( $tv_term_ids[ $product['category'] ] ), 'danh_muc_sp', false );
	}

	++$tv_product_count;
	WP_CLI::log( sprintf( '  %s sản phẩm: %s (#%d)', $action, $product['title'], $post_id ) );
}

/* -------------------------------------------------------------------------
 * 5. Trang tĩnh làm trang chủ
 * ---------------------------------------------------------------------- */
if ( ! empty( $tv_page_ids['trang-chu'] ) ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', (int) $tv_page_ids['trang-chu'] );
	update_option( 'page_for_posts', 0 );
	WP_CLI::log( '  Đặt trang tĩnh làm trang chủ: #' . $tv_page_ids['trang-chu'] );
}

/* -------------------------------------------------------------------------
 * 6. Menu chính
 * ---------------------------------------------------------------------- */
$tv_menu_name = 'TechViet Primary';
$tv_menu      = wp_get_nav_menu_object( $tv_menu_name );

if ( $tv_menu ) {
	$tv_menu_id = (int) $tv_menu->term_id;
	$old_items  = wp_get_nav_menu_items( $tv_menu_id );
	if ( $old_items ) {
		foreach ( $old_items as $old_item ) {
			wp_delete_post( $old_item->ID, true );
		}
	}
	WP_CLI::log( '  Làm mới menu: ' . $tv_menu_name . ' (#' . $tv_menu_id . ')' );
} else {
	$tv_menu_id = wp_create_nav_menu( $tv_menu_name );
	if ( is_wp_error( $tv_menu_id ) ) {
		WP_CLI::warning( 'Menu: ' . $tv_menu_id->get_error_message() );
		$tv_menu_id = 0;
	} else {
		WP_CLI::log( '  Tạo menu: ' . $tv_menu_name . ' (#' . $tv_menu_id . ')' );
	}
}

if ( $tv_menu_id ) {
	$home_item = wp_update_nav_menu_item(
		$tv_menu_id,
		0,
		array(
			'menu-item-title'  => 'Trang chủ',
			// URL tương đối — tránh lệch scheme/port khi truy cập qua HTTPS.
			'menu-item-url'    => '/',
			'menu-item-type'   => 'custom',
			'menu-item-status' => 'publish',
		)
	);

	$shop_item = wp_update_nav_menu_item(
		$tv_menu_id,
		0,
		array(
			'menu-item-title'     => 'Sản phẩm',
			'menu-item-type'      => 'post_type_archive',
			'menu-item-object'    => 'san_pham',
			'menu-item-object-id' => 0,
			'menu-item-url'       => get_post_type_archive_link( 'san_pham' ),
			'menu-item-status'    => 'publish',
		)
	);

	foreach ( $tv_terms as $slug => $term ) {
		if ( empty( $tv_term_ids[ $slug ] ) ) {
			continue;
		}
		wp_update_nav_menu_item(
			$tv_menu_id,
			0,
			array(
				'menu-item-title'     => $term['name'],
				'menu-item-type'      => 'taxonomy',
				'menu-item-object'    => 'danh_muc_sp',
				'menu-item-object-id' => $tv_term_ids[ $slug ],
				'menu-item-parent-id' => $shop_item,
				'menu-item-status'    => 'publish',
			)
		);
	}

	foreach ( array( 'gioi-thieu', 'lien-he' ) as $slug ) {
		if ( empty( $tv_page_ids[ $slug ] ) ) {
			continue;
		}
		wp_update_nav_menu_item(
			$tv_menu_id,
			0,
			array(
				'menu-item-title'     => $tv_pages[ array_search( $slug, array_column( $tv_pages, 'slug' ), true ) ]['title'],
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $tv_page_ids[ $slug ],
				'menu-item-status'    => 'publish',
			)
		);
	}

	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $tv_menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	WP_CLI::log( '  Gán menu vào vị trí "primary"' );
}

/* -------------------------------------------------------------------------
 * 7. Flush rewrite & báo cáo
 * ---------------------------------------------------------------------- */
flush_rewrite_rules();

WP_CLI::success(
	sprintf(
		'Hoàn tất: %d sản phẩm, %d danh mục, %d trang nội dung. Trang chủ: %s',
		$tv_product_count,
		count( $tv_term_ids ),
		count( $tv_page_ids ),
		home_url( '/' )
	)
);
