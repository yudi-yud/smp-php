# 🐳 Docker Deployment Guide - SMPN 3 Satu Atap Cipari

## Persiapan Sebelum Deploy

### 1. VPS Requirements
- **OS:** Ubuntu 20.04/22.04 atau Debian 10/11
- **RAM:** Minimal 1GB (rekomendasi 2GB)
- **Storage:** Minimal 20GB
- **VPS Provider:** DigitalOcean, Linode, Vultr, dll

### 2. Install Docker di VPS

SSH ke VPS Anda:
```bash
ssh root@ip-vps-anda
```

Update sistem:
```bash
apt update && apt upgrade -y
```

Install Docker & Docker Compose:
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Install Docker Compose
curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose
```

Verifikasi instalasi:
```bash
docker --version
docker-compose --version
```

---

## 🚀 Cara Deploy

### Metode 1: Upload File via FileZilla (Recommended)

**1. Compress project di komputer lokal:**
```
Buka Windows Explorer → Klik kanan folder `D:\Yudi\development\smp-php` → Send to → Compressed folder
```

**2. Upload via FileZilla:**
- Connect to VPS via SFTP (FileZilla)
- Drag & drop file zip ke `/root/`

**3. Di VPS, extract dan setup:**
```bash
# Install unzip
apt install unzip -y

# Extract file
unzip smp-php.zip

# Masuk folder
cd smp-php

# Jalankan Docker Compose
docker-compose up -d --build
```

---

### Metode 2: Git Clone (Kalau project di GitHub)

**Di VPS:**
```bash
# Clone repository
git clone https://github.com/username/smp-php.git /var/www/smp-php
cd /var/www/smp-php

# Jalankan Docker Compose
docker-compose up -d --build
```

---

## ✅ Cek Deploy

Cek container berjalan:
```bash
docker-compose ps
```

Harusnya muncul:
```
NAME                    STATUS
smpn3_cipari_web       Up (healthy)
smpn3_cipari_db        Up (healthy)
smpn3_cipari_phpmyadmin Up
```

Cek logs:
```bash
docker-compose logs -f web
```

Akses website:
- **Website:** `http://ip-vps-anda/`
- **phpMyAdmin:** `http://ip-vps-anda:8080`
  - User: `root`
  - Password: `root_password_2024`

---

## 🔧 Perintah Docker Berguna

**Lihat logs:**
```bash
docker-compose logs web
docker-compose logs db
```

**Restart container:**
```bash
docker-compose restart web
```

**Stop container:**
```bash
docker-compose down
```

**Update application:**
```bash
docker-compose down
git pull  # kalau pakai git
docker-compose up -d --build
```

**Backup database:**
```bash
docker exec smpn3_cipari_db mysqldump -u root -p"root_password_2024" smpn3_cipari > backup_$(date +%Y%m%d).sql
```

**Restore database:**
```bash
docker exec -i smpn3_cipari_db mysql -u root -p"root_password_2024" smpn3_cipari < backup_file.sql
```

---

## 🔒 Keamanan

### 1. Ganti Password Default

Edit `docker-compose.yml`, ganti password:
```yaml
MYSQL_ROOT_PASSWORD: password_baru_anda_sangat_kuat
MYSQL_PASSWORD: password_baru_anda_sangat_kuat
```

### 2. Setup Firewall

```bash
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP
ufw allow 443/tcp   # HTTPS
ufw enable
```

### 3. Setup SSL dengan Let's Encrypt

Install Certbot:
```bash
apt install certbot python3-certbot-apache -y
```

Request SSL:
```bash
certbot certonly --standalone -d domain-anda.com
```

Update `docker-compose.yml` untuk HTTPS:
```yaml
services:
  web:
    ports:
      - "443:443"
      - "80:80"
    volumes:
      - ./certs:/etc/apache2/ssl
```

---

## 📝 Update Password Default

Ubah password di `docker-compose.yml`:
- `MYSQL_ROOT_PASSWORD` → ganti jadi password kuat
- `MYSQL_PASSWORD` → ganti jadi password kuat
- `PMA_PASSWORD` → samakan dengan MYSQL_ROOT_PASSWORD

Jalankan ulang:
```bash
docker-compose down
docker-compose up -d
```

---

## 🎯 Tips & Troubleshooting

**Website tidak muncul?**
```bash
# Cek container status
docker-compose ps

# Cek logs
docker-compose logs web

# Rebuild
docker-compose down
docker-compose up -d --build
```

**Database error?**
```bash
# Cek database container
docker exec -it smpn3_cipari_db mysql -u root -p"root_password_2024"

# Di MySQL prompt:
USE smpn3_cipari;
SHOW TABLES;
```

**Login phpMyAdmin gagal?**
- Pastikan container db sudah healthy: `docker-compose ps`
- Cek logs: `docker-compose logs phpmyadmin`

---

## 📦 Struktur Database

Database akan otomatis dibuat saat pertama kali start:
- **Database:** smpn3_cipari
- **User:** smpn3_user
- **Password:** smpn3_password_2024
- **Tabel:** admin_users, news, achievements, programs, gallery, testimonials, features, contacts, site_settings

Admin default:
- Username: **admin**
- Password: **admin123**

---

## 🚢 CI/CD Bonus (Opsional)

Kalau mau auto-deploy via GitHub Actions, buat file `.github/workflows/deploy.yml`:

```yaml
name: Deploy to VPS

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to VPS
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.VPS_IP }}
          username: ${{ secrets.VPS_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /root/smp-php
            git pull
            docker-compose down
            docker-compose up -d --build
```

---

## 📞 Support

Kalau ada masalah, cek:
1. Docker logs: `docker-compose logs`
2. Container status: `docker-compose ps`
3. VPS resources: `htop` atau `free -m`
