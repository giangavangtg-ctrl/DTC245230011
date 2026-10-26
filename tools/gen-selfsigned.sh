#!/usr/bin/env bash
# =============================================
# Tạo chứng chỉ self-signed cho Nginx (demo/lab)
# - Có Subject Alternative Name (SAN) cho localhost và 127.0.0.1
# - Không commit chứng chỉ/private key lên Git (xem .gitignore)
#
# Cách dùng:
#   bash tools/gen-selfsigned.sh          # chỉ tạo nếu chưa có
#   FORCE=1 bash tools/gen-selfsigned.sh  # tạo lại (ghi đè)
# =============================================
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SSL_DIR="$ROOT_DIR/nginx/ssl"
CRT="$SSL_DIR/selfsigned.crt"
KEY="$SSL_DIR/selfsigned.key"

mkdir -p "$SSL_DIR"

if [[ -f "$CRT" && -f "$KEY" && "${FORCE:-0}" != "1" ]]; then
  echo "Chứng chỉ đã tồn tại: $SSL_DIR"
  echo "Dùng FORCE=1 để tạo lại nếu cần."
  exit 0
fi

if ! command -v openssl >/dev/null 2>&1; then
  echo "LỖI: không tìm thấy openssl. Cài OpenSSL hoặc dùng Git Bash trên Windows." >&2
  exit 1
fi

# Dùng file cấu hình thay cho -subj "/C=..." để tránh Git Bash (MSYS)
# tự chuyển chuỗi bắt đầu bằng "/" thành đường dẫn Windows.
CONF="$SSL_DIR/.openssl-generated.cnf"
trap 'rm -f "$CONF"' EXIT

cat > "$CONF" <<'EOF'
[req]
distinguished_name = dn
x509_extensions = v3_req
prompt = no

[dn]
C = VN
ST = HoChiMinh
L = HoChiMinh
O = De3Lab
CN = localhost

[v3_req]
subjectAltName = @alt_names
keyUsage = critical, digitalSignature, keyEncipherment
extendedKeyUsage = serverAuth

[alt_names]
DNS.1 = localhost
IP.1 = 127.0.0.1
EOF

openssl req -x509 -nodes -newkey rsa:2048 -sha256 -days 825 \
  -keyout "$KEY" \
  -out "$CRT" \
  -config "$CONF"

chmod 600 "$KEY" 2>/dev/null || true

echo
echo "Đã tạo chứng chỉ:"
echo "  $CRT"
echo "  $KEY"
echo
openssl x509 -in "$CRT" -noout -subject -dates -ext subjectAltName
