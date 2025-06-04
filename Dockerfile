# Dockerfile.app
# Dockerfile untuk Aplikasi Laravel dengan FrankenPHP

# Menggunakan base image FrankenPHP.
# Kamu bisa memilih versi PHP dan versi Caddy yang berbeda.
# Contoh: ghcr.io/dunglas/frankenphp:1.1-php8.4-alpine
# "alpine" untuk ukuran image yang lebih kecil
FROM ghcr.io/dunglas/frankenphp:1.1-php8.4-alpine

# Set working directory di dalam kontainer
WORKDIR /var/www/html

# Install dependencies sistem yang dibutuhkan Laravel
# `build-base` untuk kompilasi, `libpng-dev`, `libjpeg-turbo-dev` untuk gd, `libzip-dev` untuk zip
# `mysql-client` untuk debugging database dari dalam kontainer
# FrankenPHP sudah memiliki beberapa ekstensi default, jadi ini mungkin lebih sedikit
RUN apk add --no-cache \
    build-base \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    mysql-client \
    git \
    curl \
    nano # Editor dasar untuk debugging

# Install ekstensi PHP yang umum digunakan oleh Laravel
# `pdo_mysql` untuk koneksi database MySQL
# `gd` untuk manipulasi gambar
# `zip` untuk kompresi file
# `bcmath` untuk operasi matematika presisi tinggi
# `opcache` sudah ada di FrankenPHP, jadi tidak perlu lagi
RUN docker-php-ext-install pdo_mysql gd zip bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Salin semua file proyek dari host ke dalam kontainer
COPY . .

# Jalankan composer install
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Berikan izin yang benar untuk folder storage dan bootstrap/cache
# FrankenPHP berjalan sebagai user yang sesuai, tapi ini tetap praktik yang baik.
RUN chown -R frankenphp:frankenphp /var/www/html/storage \
    && chown -R frankenphp:frankenphp /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Konfigurasi Caddyfile (opsional, FrankenPHP bisa generate otomatis)
# Jika kamu perlu konfigurasi Caddy yang lebih kompleks, buat file Caddyfile
# Contoh: COPY Caddyfile /etc/caddy/Caddyfile
# Untuk Laravel, umumnya Caddyfile default sudah cukup

# Expose port 80 (HTTP) dan 443 (HTTPS)
# FrankenPHP akan mendengarkan di sini
EXPOSE 80 443

# Perintah default saat kontainer dimulai
# FrankenPHP akan otomatis menemukan public/index.php dan menjalankannya
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "laravel"]
# Atau lebih sederhana jika menggunakan default Caddyfile bawaan FrankenPHP untuk Laravel:
# CMD ["frankenphp", "run"]
# Untuk kasus ini, karena kita tidak membuat Caddyfile custom, gunakan CMD ["frankenphp", "run"]
