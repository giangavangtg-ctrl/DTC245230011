# TechViet Store — Đề 3: Website Quảng bá Sản phẩm bằng WordPress

> **Môn học:** Triển khai và Quản trị Hệ thống Phần mềm  
> **Đề tài:** Website quảng bá sản phẩm công nghệ — TechViet Store  
> **Môi trường:** Docker Desktop trên Windows 11 (WSL2), truy cập qua `http://localhost:8081`  
> **Công nghệ:** WordPress · MySQL · Nginx · Prometheus · Grafana · Loki · Promtail · Telegraf · Docker Compose  
> **Repository:** https://github.com/giangavangtg-ctrl/DTC245230011

---

## 1. Tổng quan

TechViet Store là website quảng bá sản phẩm công nghệ (laptop, điện thoại, phụ kiện) được xây dựng trên WordPress, chạy trong Docker Compose và được công bố qua Nginx reverse proxy. Hệ thống có đầy đủ:

- Giao diện responsive, nội dung tiếng Việt, hình ảnh minh hoạ cục bộ (không dùng URL ngoài).
- Danh mục và trang chi tiết sản phẩm do WordPress quản lý.
- Trang giới thiệu, liên hệ, chính sách bảo hành/đổi trả.
- Form liên hệ có nonce, honeypot, kiểm tra dữ liệu; lưu yêu cầu vào WordPress.
- WordPress Admin + Gutenberg + WP-CLI + REST API để quản trị nội dung.
- Giám sát metrics bằng Prometheus + Grafana, thu thập log bằng Loki + Promtail.
- Các biện pháp hardening: cổng quản trị chỉ bind loopback, mạng tách biệt, tài khoản MySQL least-privilege, security headers, HTTPS self-signed.

> Ảnh minh chứng trong thư mục [`docs/screenshots`](docs/screenshots) được chụp từ hệ thống đang chạy thực tế, không qua chỉnh sửa nội dung.

---

## 2. Tính năng website

| Nhóm | Nội dung |
|---|---|
| Trang chủ | Hero “Công nghệ hiện đại – Nâng tầm trải nghiệm”, giới thiệu thương hiệu, danh mục, sản phẩm nổi bật, dịch vụ – bảo hành, CTA liên hệ, footer |
| Sản phẩm | Lưu trữ bằng CPT `san_pham`; danh mục Laptop, Điện thoại, Phụ kiện công nghệ; ảnh, tên, mô tả ngắn, giá tham khảo, nút xem chi tiết |
| Trang chi tiết | Thông số kỹ thuật, mô tả, giá tham khảo, sản phẩm liên quan, CTA tư vấn |
| Giới thiệu | Câu chuyện thương hiệu, giá trị, showroom, cam kết dịch vụ |
| Liên hệ | Thông tin showroom/hotline/email, form liên hệ, bản đồ minh hoạ, giờ mở cửa |
| Chính sách | Bảo hành 24 tháng, đổi trả, vận chuyển, bảo mật |
| Quản trị | WordPress Admin/Gutenberg, WP-CLI, REST API; menu, trang, sản phẩm, danh mục đều sửa được |

---

## 3. Kiến trúc hệ thống

```
                              ┌──────────────────────────────┐
                              │          Trình duyệt         │
                              │  http://localhost:8081       │
                              │  https://localhost:8443      │
                              └──────────────┬───────────────┘
                                             │ (chỉ bind 127.0.0.1)
                              ┌──────────────▼───────────────┐
                              │            Nginx             │
                              │  reverse proxy · TLS · CSP   │
                              │  security headers            │
                              └───────┬───────────────┬──────┘
                                      │ frontend      │ frontend
                              ┌───────▼───────┐   ┌───▼────────────┐
                              │   WordPress   │   │ nginx-exporter │
                              │ Apache :80    │   │ :9113          │
                              └───┬───────┬───┘   └───┬────────────┘
                                  │       │           │
                     backend      │       │           │ monitoring
                              ┌───▼───────▼───┐   ┌───▼─────────────────────┐
                              │    MySQL 8    │   │ Prometheus :9090        │
                              │  :3306 nội bộ │   │ Grafana :3000           │
                              └───▲───────▲───┘   │ Loki :3100              │
                                  │       │       │ Promtail (docker.sock)  │
                         ┌────────┘       └────┐  │ Telegraf (docker.sock)  │
                  ┌──────┴──────┐   ┌──────────┴──┴──┐                    │
                  │ phpMyAdmin  │   │ mysqld-exporter │                    │
                  │ :80 → 8080  │   │ :9104           │                    │
                  └─────────────┘   └─────────────────┘                    │
                                                          └─────────────────┘

  backend  : db, wordpress, phpmyadmin, wpcli, mysqld-exporter
  frontend : nginx, wordpress, nginx-exporter
  monitoring: prometheus, grafana, loki, promtail, telegraf, exporters
```

**Nguyên tắc:**

- Người dùng chỉ truy cập qua Nginx. WordPress và MySQL **không publish cổng** ra host.
- phpMyAdmin, Prometheus, Grafana, Loki chỉ bind `127.0.0.1`.
- Prometheus scrape Telegraf, mysqld-exporter, nginx-exporter trong mạng `monitoring`/`frontend`.
- Promtail và Telegraf đọc Docker Engine API qua socket read-only để lấy log/metrics container.

---

## 4. Thành phần và phiên bản

| Thành phần | Image / phiên bản | Vai trò |
|---|---|---|
| WordPress | `wordpress:6.7.2-apache` | CMS, giao diện, quản trị nội dung |
| MySQL | `mysql:8.0.40` | Cơ sở dữ liệu WordPress |
| phpMyAdmin | `phpmyadmin:5.2.2-apache` | Quản trị DB (loopback) |
| Nginx | `nginx:1.27-alpine` | Reverse proxy, TLS, security headers |
| Prometheus | `prom/prometheus:v2.54.1` | Thu thập metrics |
| Grafana | `grafana/grafana:11.2.2` | Dashboard, datasource Prometheus + Loki |
| Loki | `grafana/loki:3.2.0` | Log aggregation |
| Promtail | `grafana/promtail:3.2.0` | Thu thập log container qua Docker socket |
| Telegraf | `telegraf:1.31` | Metrics container qua Docker API |
| mysqld-exporter | `prom/mysqld-exporter:v0.14.0` | Metrics MySQL |
| nginx-exporter | `nginx/nginx-prometheus-exporter:1.3.0` | Metrics Nginx |
| WP-CLI | `wordpress:cli-2.11.0` (profile `tools`) | Quản trị WordPress bằng CLI |

> **Ghi chú:** image WordPress là `6.7.2-apache`, còn lõi WordPress trong DB báo `6.7.1`. Đây là chênh lệch phiên bản giữa image và core đã cài; hệ thống vẫn chạy ổn định. Không bật tự động cập nhật core (`WP_AUTO_UPDATE_CORE=false`) để tránh thay đổi ngoài ý muốn trong môi trường bài tập.

---

## 5. Cấu trúc thư mục

```
De3-WordPress/
├── compose.yaml                    # Docker Compose (12 service, gồm wpcli profile tools + 6 volume + 3 network)
├── .env                            # Biến môi trường thật — KHÔNG commit
├── .env.example                    # Mẫu biến môi trường
├── .gitignore                      # Bỏ qua .env, SSL key/cert, log, data
├── README.md
├── docs/
│   ├── BAO-CAO-DE3.md              # Báo cáo nội dung
│   └── screenshots/                # Ảnh minh chứng chụp từ hệ thống thật
├── nginx/
│   ├── conf.d/default.conf         # Reverse proxy HTTP/HTTPS + security headers
│   └── ssl/                        # Chứng chỉ self-signed (bị .gitignore, tạo bằng tools/gen-selfsigned.sh)
├── prometheus/
│   └── prometheus.yml              # 4 scrape job: prometheus, docker, mysql, nginx
├── grafana/
│   └── provisioning/
│       ├── datasources/datasources.yml
│       └── dashboards/             # 3 dashboard JSON
├── loki/
│   └── loki-config.yml
├── promtail/
│   └── promtail-config.yml
├── telegraf/
│   └── telegraf.conf
├── tools/
│   ├── gen-selfsigned.sh           # Tạo chứng chỉ self-signed (SAN localhost)
│   └── seed-techviet.php           # Seed nội dung mẫu bằng WP-CLI
└── wordpress/
    └── wp-content/
        ├── themes/techviet-store/  # Theme tự phát triển (bind mount read-only)
        └── plugins/techviet-core/  # CPT, taxonomy, form liên hệ
```

---

## 6. Yêu cầu môi trường

- Docker Desktop ≥ 4.x (Windows/macOS) hoặc Docker Engine + Compose v2 (Linux).
- Docker Compose v2 (`docker compose`, không phải `docker-compose`).
- RAM cấp cho Docker ≥ 4 GB (khuyến nghị 6 GB nếu chạy đủ monitoring).
- Trình duyệt hiện đại.
- Cổng host còn trống: `8081`, `8443`, `8080`, `3000`, `9090`, `3100`.
- Trên Windows, nên dùng Git Bash/WSL để chạy lệnh; nếu dùng Git Bash cần `MSYS_NO_PATHCONV=1` cho các lệnh có đường dẫn trong container (xem mục 15).

---

## 7. Cấu hình môi trường

```bash
cp .env.example .env
# Sửa .env và đặt mật khẩu mạnh, không dùng giá trị mẫu
```

Các biến chính:

| Biến | Ý nghĩa |
|---|---|
| `MYSQL_ROOT_PASSWORD` | Mật khẩu root MySQL (không dùng cho ứng dụng) |
| `MYSQL_DATABASE` | Tên DB, mặc định `wordpress_db` |
| `MYSQL_USER` / `MYSQL_PASSWORD` | Tài khoản WordPress dùng để kết nối DB |
| `WORDPRESS_DB_*` | Phải khớp thông tin DB ở trên |
| `WORDPRESS_TABLE_PREFIX` | Tiền tố bảng, dự án dùng `wpsec_` |
| `WORDPRESS_DEBUG` | `false` cho môi trường demo |
| `PMA_HOST` / `PMA_PORT` | `db` / `3306` |
| `GF_SECURITY_ADMIN_USER` / `GF_SECURITY_ADMIN_PASSWORD` | Tài khoản Grafana |
| `PROMETHEUS_RETENTION` | Thời gian lưu metrics, mặc định `15d` |
| `MYSQL_EXPORTER_USER` / `MYSQL_EXPORTER_PASSWORD` | Tài khoản MySQL chỉ đọc cho exporter |

**Quy tắc bảo mật:**

- `.env` đã nằm trong `.gitignore`; không commit mật khẩu thật.
- `.env.example` chỉ chứa placeholder.
- Chứng chỉ trong `nginx/ssl/` cũng bị `.gitignore` (chứa private key).
- Không đưa mật khẩu vào README, ảnh chụp, commit message hay log.

**Tạo chứng chỉ HTTPS (bắt buộc sau khi clone):**

`nginx/ssl/` bị `.gitignore` nên repo không chứa private key. Trước lần chạy đầu tiên, tạo chứng chỉ self-signed có SAN cho `localhost`/`127.0.0.1`:

```bash
bash tools/gen-selfsigned.sh
# Tạo lại khi cần: FORCE=1 bash tools/gen-selfsigned.sh
```

Nếu đã tạo lại chứng chỉ khi stack đang chạy, restart Nginx để nạp cert mới:

```bash
docker compose restart nginx
```

Kiểm tra nhanh:

```bash
git check-ignore -v .env nginx/ssl/selfsigned.key
```

---

## 8. Khởi chạy

```bash
# 0. Clone mã nguồn (bỏ qua nếu đã có thư mục dự án)
git clone https://github.com/giangavangtg-ctrl/DTC245230011.git
cd DTC245230011

# 1. Tạo .env từ mẫu và sửa mật khẩu (xem mục 7)
cp .env.example .env

# 2. Tạo chứng chỉ self-signed cho HTTPS (bắt buộc lần đầu)
bash tools/gen-selfsigned.sh

# 3. Kiểm tra cú pháp và biến môi trường
docker compose config --quiet

# 4. Khởi động toàn bộ stack
docker compose up -d

# 5. Theo dõi trạng thái
docker compose ps

# 6. Xem log khi cần
docker compose logs -f nginx wordpress db
```

### Dừng hệ thống

```bash
# Dừng, GIỮ NGUYÊN volume/dữ liệu
docker compose down

# CHỈ dùng khi thực sự muốn xoá toàn bộ DB, WordPress, Prometheus, Grafana, Loki
# docker compose down -v
```

> **Cảnh báo:** `down -v` xoá named volume, mất database và nội dung WordPress. Không dùng trong quá trình demo/báo cáo.

### Cài WordPress lần đầu (chỉ khi volume mới)

Truy cập `http://localhost:8081/wp-admin/install.php` và làm theo hướng dẫn, hoặc dùng WP-CLI:

```bash
docker compose --profile tools run --rm wpcli wp core install \
  --url="http://localhost:8081" \
  --title="TechViet Store" \
  --admin_user="admin" \
  --admin_password="MAT_KHAU_MANH_CUA_BAN" \
  --admin_email="admin@techviet.vn"
```

Sau đó seed nội dung mẫu:

```bash
docker compose --profile tools run --rm wpcli wp eval-file /tools/seed-techviet.php
```

Script seed tạo danh mục, 9 sản phẩm, các trang nội dung, menu và cấu hình trang chủ tĩnh. Script có kiểm tra tồn tại nên chạy lại không nhân đôi dữ liệu.

---

## 9. Địa chỉ truy cập

| Dịch vụ | URL | Ghi chú |
|---|---|---|
| Website | http://localhost:8081 | Qua Nginx |
| Website HTTPS | https://localhost:8443 | Chứng chỉ self-signed, trình duyệt sẽ cảnh báo |
| WordPress Admin | http://localhost:8081/wp-admin/ | Tài khoản tạo khi cài WordPress |
| REST API | http://localhost:8081/wp-json/wp/v2/ | `pages`, `san_pham`, … |
| phpMyAdmin | http://localhost:8080 | Chỉ `127.0.0.1`, đăng nhập DB |
| Prometheus | http://localhost:9090 | Chỉ `127.0.0.1` |
| Grafana | http://localhost:3000 | Chỉ `127.0.0.1`, tài khoản trong `.env` |
| Loki API | http://localhost:3100 | Chỉ `127.0.0.1` |

> Không có cổng MySQL (3306) hay WordPress (80) nào được publish ra host. Mọi truy cập website đi qua Nginx.

---

## 10. Kiểm tra trạng thái

```bash
# Trạng thái container + healthcheck
docker compose ps

# Healthcheck chi tiết
docker inspect de3_wordpress --format '{{json .State.Health}}'

# Kiểm tra Nginx
curl -sI http://localhost:8081/ | head
curl -skI https://localhost:8443/ | head

# Kiểm tra Prometheus targets
curl -s http://localhost:9090/api/v1/targets | jq -r '.data.activeTargets[] | "\(.labels.job)\t\(.health)"'

# Kiểm tra datasource Grafana
curl -s -u "$GF_SECURITY_ADMIN_USER:$GF_SECURITY_ADMIN_PASSWORD" \
  http://localhost:3000/api/datasources | jq -r '.[] | "\(.name)\t\(.type)\t\(.uid)"'

# Kiểm tra Loki sẵn sàng
curl -s http://localhost:3100/ready
```

Kết quả mong đợi (đã kiểm tra thực tế):

- 11 container chạy; 10 container có healthcheck `healthy`.
- `de3_nginx_exporter` không có healthcheck vì image distroless không chứa `wget/curl`; trạng thái thật được xác minh qua Prometheus target `nginx` = `UP`.
- Prometheus có **4/4 target UP**: `prometheus`, `docker`, `mysql`, `nginx`.

---

## 11. Quản trị WordPress

### 11.1. Qua WordPress Admin / Gutenberg

- Vào `http://localhost:8081/wp-admin/`.
- Sửa trang, bài viết, sản phẩm, danh mục, menu bằng Gutenberg.
- Sản phẩm là CPT **Sản phẩm** (`san_pham`) với các trường:
  - Giá tham khảo: `_tv_gia`
  - Nhãn nổi bật: `_tv_nhan`
  - Mô tả ngắn: `_tv_mo_ta_ngan`
  - Thông số kỹ thuật: `_tv_thong_so`
  - Sản phẩm nổi bật: `_tv_noi_bat`
- Ảnh sản phẩm: nếu có **Featured image** thì dùng ảnh đó; nếu chưa có, theme dùng ảnh minh hoạ SVG cục bộ theo slug tại `assets/images/products/`.
- Yêu cầu liên hệ được lưu vào CPT **Yêu cầu liên hệ** (`tv_lien_he`), xem tại menu admin cùng tên.

### 11.2. Qua WP-CLI

```bash
# Liệt kê sản phẩm
docker compose --profile tools run --rm wpcli wp post list --post_type=san_pham

# Cập nhật giá một sản phẩm
docker compose --profile tools run --rm wpcli wp post meta update 18 _tv_gia "18.990.000đ"

# Xem cấu hình trang chủ
docker compose --profile tools run --rm wpcli wp option get show_on_front
docker compose --profile tools run --rm wpcli wp option get page_on_front
```

Trên Git Bash, thêm `MSYS_NO_PATHCONV=1` trước `docker` nếu gặp lỗi đường dẫn bị chuyển thành `C:/...`.

### 11.3. Qua REST API

```bash
curl -s "http://localhost:8081/wp-json/wp/v2/pages?per_page=5"
curl -s "http://localhost:8081/wp-json/wp/v2/san_pham?per_page=9"
```

CPT `san_pham` và taxonomy `danh_muc_sp` đều bật `show_in_rest`, nên có thể đọc/ghi qua REST khi xác thực bằng Application Password hoặc cookie admin. Với thao tác ghi, nên dùng WordPress Admin/Gutenberg hoặc WP-CLI trong phạm vi bài tập.

### 11.4. Nội dung hiện có

- 9 sản phẩm, 3 danh mục: Laptop, Điện thoại, Phụ kiện công nghệ.
- Trang: Trang chủ (tĩnh), Giới thiệu, Liên hệ, Chính sách bảo hành, Chính sách đổi trả.
- Menu **TechViet Primary** gồm 7 mục, có menu con theo danh mục.
- Permalink `/%postname%/`; danh mục sản phẩm dùng `/danh-muc-san-pham/<slug>/`.

---

## 12. Nginx, HTTPS và security headers

### 12.1. Cấu hình

- HTTP: `127.0.0.1:8081` → container `nginx:80` → `wordpress:80`.
- HTTPS: `127.0.0.1:8443` → container `nginx:443`.
- `server_tokens off` (ẩn phiên bản Nginx).
- `proxy_hide_header X-Powered-By` và `X-Frame-Options` trùng từ WordPress.
- `resolver 127.0.0.11` để Nginx resolve tên service Docker lúc runtime.
- `client_max_body_size 128M`, timeout proxy phù hợp WordPress.
- `/nginx_status` được phục vụ trên **server nội bộ cổng 8080** (không publish ra host), chỉ cho phép dải `172.16.0.0/12` — dùng cho `nginx-prometheus-exporter`. Truy cập qua `http://localhost:8081/nginx_status` sẽ không còn metrics.
- Chặn truy cập file ẩn (`/.env`, `/.git`).

### 12.2. Security headers

Có trên cả HTTP và HTTPS:

- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `Content-Security-Policy` với `default-src 'self'`, `frame-ancestors 'self'`, `form-action 'self'`, …
- HTTPS bổ sung `Strict-Transport-Security: max-age=31536000; includeSubDomains`.

**Lưu ý CSP:** `frame-src 'self' blob:` và `img-src ... blob:` là bắt buộc để Gutenberg hiển thị iframe canvas và ảnh xem trước khi upload. Nếu siết CSP bỏ `blob:`, trình soạn thảo WordPress sẽ trắng nội dung.

### 12.3. Kiểm tra

```bash
docker exec de3_nginx nginx -t
curl -sI http://localhost:8081/ | grep -Ei 'server|x-frame|content-security|referrer'
curl -skI https://localhost:8443/ | grep -Ei 'strict-transport|content-security'
```

Chứng chỉ self-signed hiện tại:

- Subject/Issuer: `CN=localhost`, `O=De3Lab`
- Hiệu lực: `2026-10-25` → `2027-10-25`
- Chỉ dùng cho demo/lab; không dùng cho production.

Đã kiểm tra: trang HTTPS không còn URL `http://` nào (menu “Trang chủ” dùng đường dẫn tương đối `/`), nên không có mixed content.

---

## 13. Prometheus — Thu thập metrics

Cấu hình: [`prometheus/prometheus.yml`](prometheus/prometheus.yml)

| Job | Target | Nguồn metrics |
|---|---|---|
| `prometheus` | `localhost:9090` | Tự giám sát Prometheus |
| `docker` | `docker-metrics:9273` | Telegraf Docker input: CPU, RAM, network, trạng thái container |
| `mysql` | `mysqld-exporter:9104` | MySQL global status/variables, InnoDB |
| `nginx` | `nginx-exporter:9113` | `stub_status`: connections, requests |

Mở `http://localhost:9090/targets` để xem trạng thái. Ảnh minh chứng: [`prometheus-targets.png`](docs/screenshots/prometheus-targets.png).

### 13.1. Vì sao dùng Telegraf thay cAdvisor?

Trên Docker Desktop for Windows (WSL2), cAdvisor v0.49 chỉ đọc được cgroup gốc `/` và không nhận diện container (log báo lỗi “failed to identify the read-write layer ID”). Đã thử privileged, `--cgroupns=host`, containerd socket nhưng không khắc phục. Telegraf đọc Docker Engine API nên hoạt động ổn định trên cả Linux và Docker Desktop, đồng thời cho metrics theo từng container.

Telegraf chạy bằng root để đọc `/var/run/docker.sock` (socket thuộc root trong container), socket mount **read-only**, bật `no-new-privileges`. Cổng `9273` chỉ dùng nội bộ mạng `monitoring`.

### 13.2. PromQL mẫu (đã chạy thực tế)

```promql
# Số container đang chạy của project
count(docker_container_cpu_usage_percent{com_docker_compose_project="de3-wordpress"})

# CPU % theo container
docker_container_cpu_usage_percent{com_docker_compose_project="de3-wordpress", container_name!="de3_docker_metrics"}

# RAM theo container
docker_container_mem_usage{com_docker_compose_project="de3-wordpress"}

# Network RX/TX theo container (bytes/giây)
rate(docker_container_net_rx_bytes{com_docker_compose_project="de3-wordpress"}[1m])
rate(docker_container_net_tx_bytes{com_docker_compose_project="de3-wordpress"}[1m])

# Uptime container (giây)
docker_container_status_uptime_ns{com_docker_compose_project="de3-wordpress"} / 1000000000

# Nginx requests/giây
rate(nginx_http_requests_total[1m])

# MySQL queries/giây
rate(mysql_global_status_queries[1m])
```

> Telegraf chuyển label Docker (dấu chấm) thành dấu gạch dưới, ví dụ `com.docker.compose.project` → `com_docker_compose_project`.

---

## 14. Grafana — Dashboard

- Datasource tự động provision: **Prometheus** (mặc định) và **Loki**, xem [`datasources.yml`](grafana/provisioning/datasources/datasources.yml).
- 3 dashboard tự động load:

| Dashboard | UID | Nội dung |
|---|---|---|
| Docker Containers Overview | `de3-docker-overview` | Số container, CPU, RAM, network RX/TX, uptime, log Loki |
| Nginx & Dịch vụ | `de3-nginx-overview` | Nginx UP, connections, MySQL connected/uptime, requests/s, QPS, log Nginx & WordPress |
| MySQL Overview | `de3-mysql-overview` | Connections, QPS, uptime, InnoDB |

Truy cập `http://localhost:3000`, đăng nhập bằng tài khoản trong `.env`, vào **Dashboards**.

Ảnh minh chứng:

- [`grafana-docker.png`](docs/screenshots/grafana-docker.png) — CPU/RAM/network thật của 11 container + log Loki.
- [`grafana-nginx.png`](docs/screenshots/grafana-nginx.png) — Nginx UP, requests/s, MySQL QPS, log Nginx/WordPress.

Dashboard dùng UID datasource cố định (`PBFA97CFB590B2093` cho Prometheus, `P8E80F9AEF21F6940` cho Loki) để panel logs hiển thị đúng nguồn.

---

## 15. Loki + Promtail — Logging

- Promtail tự phát hiện container qua Docker socket (`docker_sd_configs`), gắn nhãn:
  - `compose_project` = `de3-wordpress`
  - `container` = tên container (ví dụ `de3_wordpress`)
  - `service` = tên service Compose (ví dụ `nginx`, `wordpress`, `db`)
  - `service_name`, `image`
- Loki lưu log tại volume `loki_data`; Promtail lưu vị trí đọc tại `/var/lib/promtail/positions.yaml` trong volume `promtail_positions` (giữ qua restart, tránh gửi lại log cũ).
- Loki chỉ bind `127.0.0.1:3100`.

### 15.1. Kiểm tra labels

```bash
curl -s http://localhost:3100/loki/api/v1/labels
curl -s "http://localhost:3100/loki/api/v1/label/service/values?start=$(($(date +%s)-3600))000000000&end=$(date +%s)000000000"
```

Kết quả thực tế: labels gồm `compose_project`, `container`, `service`, `service_name`; `compose_project` = `de3-wordpress`; `service` có `nginx`, `wordpress`, `db`, `grafana`, `loki`, `promtail`, `prometheus`, `phpmyadmin`, …

### 15.2. LogQL mẫu (đã chạy thực tế)

```logql
# 1. Toàn bộ log của project
{compose_project="de3-wordpress"}

# 2. Log Nginx có request GET
{service="nginx"} |= "GET"

# 3. Log Nginx của trang liên hệ (dùng để kiểm tra form)
{service="nginx"} |= "/lien-he/"

# 4. Log WordPress có POST (đăng nhập/admin-ajax)
{service="wordpress"} |= "POST"

# 5. Log MySQL
{service="db"}

# 6. Log phpMyAdmin
{service="phpmyadmin"}

# 7. Log lỗi của Grafana
{service="grafana"} |= "level=error"
```

Kết quả kiểm tra (trong 1 giờ gần nhất):

- `{compose_project="de3-wordpress"}` → 4 stream, hàng trăm dòng (đã giới hạn 200 dòng/lần query).
- `{service="nginx"} |= "GET"` → có dữ liệu, ví dụ request `GET /lien-he/?lien-he=success HTTP/1.1 200`.
- `{service="wordpress"} |= "POST"` → 34 dòng.
- `{service="db"}` → 15 dòng (gồm cảnh báo `mysql_native_password` deprecated).
- `{service="phpmyadmin"}` → 99 dòng.

### 15.3. Xem log trong Grafana Explore

1. Mở `http://localhost:3000` → **Explore**.
2. Chọn datasource **Loki**.
3. Nhập query, ví dụ `{service="wordpress"} |= "POST"`, chọn time range **Last 1 hour**.
4. Bấm **Run query**; log hiển thị kèm labels `compose_project`, `container`, `service`.

> Lưu ý: các container test cũ (`tv-cadvisor-test`, `tv-telegraf-test`, `de3_cadvisor`) có thể còn giá trị label trong Loki lịch sử, nhưng không còn chạy. Dashboard chỉ query project `de3-wordpress`.

---

## 16. Hardening

| Biện pháp | Chi tiết / minh chứng | Trạng thái |
|---|---|:---:|
| Bí mật qua `.env` | `.env` bị `.gitignore`; `.env.example` chỉ placeholder; `git check-ignore` xác nhận | ✅ |
| Không publish DB | MySQL chỉ có `3306/tcp` nội bộ, không có mapping host | ✅ |
| Không publish WordPress | WordPress chỉ `80/tcp` nội bộ; truy cập qua Nginx | ✅ |
| Cổng quản trị loopback | phpMyAdmin `127.0.0.1:8080`, Grafana `127.0.0.1:3000`, Prometheus `127.0.0.1:9090`, Loki `127.0.0.1:3100`, Nginx `127.0.0.1:8081/8443` | ✅ |
| Mạng tách biệt | 3 network: `backend`, `frontend`, `monitoring` | ✅ (backend `internal:false` do giới hạn DNS Docker Desktop; bù bằng không publish port) |
| MySQL least privilege | `wp_user` chỉ `ALL PRIVILEGES` trên `wordpress_db`; `exporter` chỉ `PROCESS`, `REPLICATION CLIENT`, `SELECT` trên `performance_schema` | ✅ |
| Mật khẩu mạnh | Sinh ngẫu nhiên, độ dài 12–24 ký tự; không đưa vào repo/README | ✅ |
| `no-new-privileges` | Bật cho toàn bộ 11 container | ✅ |
| Chạy non-root khi hỗ trợ | Prometheus (nobody), Grafana (uid 472), Loki (uid 10001), mysqld-exporter (nobody) | ✅ |
| Ngoại lệ có lý do | Telegraf + Promtail chạy root để đọc Docker socket (mount read-only); WordPress/Apache, MySQL, Nginx, phpMyAdmin có master process root theo image chính thức, worker drop xuống `www-data`/`mysql`/`nginx` | ⚠️ đã ghi rõ |
| Security headers | X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, CSP; HTTPS có HSTS | ✅ |
| Ẩn thông tin | `server_tokens off`, ẩn `X-Powered-By`, chặn file ẩn | ✅ |
| HTTPS | Self-signed `CN=localhost`, TLS 1.2/1.3, HSTS trên HTTPS | ✅ (demo) |
| Giảm bề mặt WordPress | `DISALLOW_FILE_EDIT=true`, `WP_AUTO_UPDATE_CORE=false`, table prefix `wpsec_`, debug `false` | ✅ |
| Grafana | `GF_USERS_ALLOW_SIGN_UP=false`, mật khẩu admin từ `.env` | ✅ |
| phpMyAdmin | `PMA_ARBITRARY=0`, chỉ bind loopback | ✅ |

**Điểm cần lưu ý khi bảo vệ:**

- Không tuyên bố “toàn bộ container chạy non-root”. Thực tế các image chính thức (MySQL, WordPress/Apache, phpMyAdmin, Nginx) có master process root; tiến trình xử lý request drop quyền. Telegraf/Promtail buộc phải root để đọc Docker socket — đã giảm thiểu bằng socket read-only + `no-new-privileges` + không publish cổng.
- HTTPS self-signed chỉ phù hợp lab; production cần CA thật và redirect HTTP → HTTPS.
- Backend network không đặt `internal: true` vì Docker Desktop DNS không phân giải được trong internal network; bù lại MySQL không publish cổng và chỉ container backend kết nối được.

---

## 17. Kiểm thử — 12 điểm

| # | Hạng mục | Cách kiểm tra / minh chứng | Kết quả |
|---:|---|---|:---:|
| 1 | Stack khởi động đúng thứ tự, health ổn định | `docker compose ps`; 11 container chạy, 10 healthy; `nginx-exporter` không healthcheck nhưng target UP | ✅ Pass |
| 2 | Website truy cập qua Nginx | `curl -I http://localhost:8081/` → 200; hero + nội dung tiếng Việt | ✅ Pass |
| 3 | Giao diện đủ thành phần | Header/logo/menu/hotline, hero, giới thiệu, danh mục, sản phẩm nổi bật, dịch vụ/bảo hành, liên hệ/footer | ✅ Pass |
| 4 | Responsive + menu mobile | Test 1440px và 390px; `scrollWidth == innerWidth`; toggle mở menu (aria-expanded=true) | ✅ Pass |
| 5 | Menu/liên kết/nút hoạt động | Các URL chính trả 200: `/`, `/san-pham/`, 3 danh mục, 9 chi tiết, `/gioi-thieu/`, `/lien-he/`, chính sách | ✅ Pass |
| 6 | Danh sách + chi tiết sản phẩm, ảnh cục bộ | 9 sản phẩm, 3 danh mục; ảnh SVG trong theme; không có URL ảnh ngoài | ✅ Pass |
| 7 | Giới thiệu/Liên hệ + form | Form gửi thật trong Chrome → `?lien-he=success`; entry `tv_lien_he` #47 lưu DB; honeypot/nonce hoạt động | ✅ Pass (email chưa xác minh do không có SMTP) |
| 8 | Quản trị WordPress | Đăng nhập Admin, sửa sản phẩm bằng Gutenberg; WP-CLI; REST `/wp-json/wp/v2/pages` và `/san_pham` → 200 | ✅ Pass |
| 9 | Nginx reverse proxy/HTTPS/headers | `nginx -t` OK; HTTP 200, HTTPS 200; đủ security headers; HTTPS không có URL `http://` | ✅ Pass |
| 10 | Prometheus metrics thật | 4/4 target UP; metrics CPU/RAM/network/MySQL/Nginx có giá trị | ✅ Pass |
| 11 | Grafana dashboard | 2 datasource healthy; 3 dashboard; dữ liệu thật; log Loki hiển thị | ✅ Pass |
| 12 | Loki/Promtail + LogQL + hardening | 4 label; ≥3 query trả dữ liệu; hardening theo bảng mục 16 | ✅ Pass |

**Chưa kiểm chứng / giới hạn:**

- Gửi email từ form liên hệ: code có `wp_mail()` nhưng môi trường Docker không cấu hình SMTP, nên **chưa xác minh nhận email**. Yêu cầu liên hệ vẫn được lưu vào WordPress và xem được trong Admin.
- Cảnh báo chứng chỉ self-signed trên trình duyệt là bình thường với lab.
- Không kiểm thử tải lớn/đa người dùng; đây là môi trường demo.

---

## 18. Xử lý sự cố

### 18.1. Nginx 502 Bad Gateway

```bash
docker compose ps wordpress
docker compose logs --tail=50 nginx wordpress
docker exec de3_nginx nginx -t
docker compose restart nginx
```

Nginx dùng `resolver 127.0.0.11` và biến upstream để tránh lỗi DNS khi container WordPress được tạo lại.

### 18.2. Grafana trả 401 dù đúng mật khẩu

Mật khẩu đã lưu trong volume Grafana có thể khác `.env`. Đặt lại:

```bash
docker exec de3_grafana grafana cli admin reset-admin-password "MAT_KHAU_MOI"
```

Sau đó đăng nhập lại; không cần xoá volume.

### 18.3. Panel Grafana “No data” (đặc biệt panel Loki)

- Kiểm tra datasource còn healthy: **Connections → Data sources → Test**.
- Dashboard phải khai báo UID datasource cố định; nếu UID sai/trống, panel logs sẽ “No data”.
- Restart Grafana sau khi sửa provisioning:

```bash
docker compose restart grafana
```

### 18.4. Gutenberg trắng nội dung

- Kiểm tra CSP có `frame-src 'self' blob:` và `img-src ... blob:`.
- Nếu vừa siết CSP, khôi phục `blob:` cho iframe canvas và ảnh preview.
- Đây là lỗi đã gặp và đã sửa trong dự án.

### 18.5. Form liên hệ trả `?lien-he=error`

- Kiểm tra đã nhập họ tên, điện thoại, nội dung và tick đồng ý.
- Honeypot `tv_website` phải để trống.
- Khi test bằng `curl` trong Git Bash, chuỗi tiếng Việt có thể bị lỗi encoding; nên test bằng trình duyệt hoặc dùng file UTF-8 với `--data-urlencode`.
- Không sửa DB trực tiếp; kiểm tra entry trong **WordPress Admin → Yêu cầu liên hệ**.

### 18.6. WP-CLI lỗi đường dẫn trên Git Bash

```bash
MSYS_NO_PATHCONV=1 docker compose --profile tools run --rm wpcli wp post list --post_type=san_pham
```

### 18.7. Prometheus không thấy container metrics

- Kiểm tra Telegraf: `docker compose logs --tail=50 docker-metrics`.
- Kiểm tra target `docker` tại `http://localhost:9090/targets`.
- Telegraf cần `/var/run/docker.sock`; trên Docker Desktop socket phải mount được và container chạy root (đã cấu hình).

### 18.8. Cổng bị chiếm

Đổi mapping trong `compose.yaml` (ví dụ `127.0.0.1:8082:80`) rồi `docker compose up -d nginx`. Không publish ra `0.0.0.0` nếu không có yêu cầu.

---

## 19. Ảnh minh chứng

Tất cả ảnh nằm trong [`docs/screenshots/`](docs/screenshots):

| Ảnh | Nội dung |
|---|---|
| [`homepage-full.png`](docs/screenshots/homepage-full.png) | Toàn trang chủ desktop |
| [`products.png`](docs/screenshots/products.png) | Danh sách 9 sản phẩm |
| [`product-detail.png`](docs/screenshots/product-detail.png) | Chi tiết TechViet UltraBook 14 |
| [`about.png`](docs/screenshots/about.png) | Trang giới thiệu |
| [`contact-success.png`](docs/screenshots/contact-success.png) | Form liên hệ gửi thành công |
| [`homepage-mobile.png`](docs/screenshots/homepage-mobile.png) | Trang chủ mobile 390px |
| [`homepage-mobile-menu.png`](docs/screenshots/homepage-mobile-menu.png) | Menu mobile mở |
| [`wp-admin-products.png`](docs/screenshots/wp-admin-products.png) | Danh sách sản phẩm trong WP Admin |
| [`wp-admin-product-edit.png`](docs/screenshots/wp-admin-product-edit.png) | Gutenberg sửa sản phẩm + meta box |
| [`prometheus-targets.png`](docs/screenshots/prometheus-targets.png) | 4/4 target UP |
| [`grafana-docker.png`](docs/screenshots/grafana-docker.png) | Dashboard Docker + log Loki |
| [`grafana-nginx.png`](docs/screenshots/grafana-nginx.png) | Dashboard Nginx/MySQL + log Loki |

---

## 20. Ghi chú và hạn chế

- Dự án chạy trên Docker Desktop Windows 11 + WSL2; một số hành vi (DNS nội bộ, Docker socket) khác Linux thuần.
- cAdvisor không dùng được trên môi trường này; Telegraf là giải pháp thay thế đã kiểm chứng.
- WordPress core trong DB là 6.7.1; image 6.7.2-apache. Không bật auto-update core.
- Trang chủ dùng static page (ID 6), permalink `/%postname%/`.
- Ảnh sản phẩm là SVG minh hoạ cục bộ; có thể thay bằng Featured Image trong WP Admin mà không sửa code.
- Form liên hệ lưu vào WordPress; email chỉ gửi khi môi trường có SMTP (chưa cấu hình).
- Chứng chỉ HTTPS là self-signed, chỉ dùng cho lab.
- Dữ liệu nhạy cảm (`.env`, private key) không nằm trong Git.

---

## 21. Thông tin dự án

- **Tên website:** TechViet Store
- **WordPress:** 6.7.2-apache (core 6.7.1)
- **Database:** MySQL 8.0.40, prefix `wpsec_`
- **Reverse Proxy:** Nginx 1.27 Alpine
- **Monitoring:** Prometheus 2.54.1 + Grafana 11.2.2
- **Logging:** Loki 3.2.0 + Promtail 3.2.0
- **Container metrics:** Telegraf 1.31
- **Exporters:** mysqld-exporter 0.14.0, nginx-prometheus-exporter 1.3.0
- **Truy cập:** `http://localhost:8081` (HTTP), `https://localhost:8443` (HTTPS self-signed)
