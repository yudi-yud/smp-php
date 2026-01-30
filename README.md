# 🐳 Panduan Docker Lengkap: Dari Nol Sampai Jalan

Panduan ini berisi langkah-langkah *step-by-step* untuk melakukan deployment aplikasi PHP ke VPS menggunakan Docker dan Docker Compose secara otomatis.



---

## 🛠 1. Persiapan di VPS
*Langkah ini dilakukan satu kali di terminal VPS (bukan di komputer lokal).*

### SSH ke VPS
Buka PowerShell atau Command Prompt di Windows Anda:
```bash
ssh root@ip-vps-anda
 Panduan Docker Lengkap - Dari Nol Sampai Jalan                                                                        
  
  V saya buat panduan step-by-step yang jelas!                                                                                
  ---                                                                                                                      
  PERSIAPAN DI VPS (SEKALI DI VPS, BUKAN DI KOMPUTER ANDA)  

  1. SSH ke VPS

  Buka PowerShell/Command Prompt di Windows Anda:
  ssh root@ip-vps-anda
  Masukkan password VPS Anda.

  2. Update VPS

  apt update && apt upgrade -y

  3. Install Docker

  curl -fsSL https://get.docker.com | sh

  4. Install Docker Compose

  curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o
  /usr/local/bin/docker-compose
  chmod +x /usr/local/bin/docker-compose

  5. Verifikasi Docker

  docker --version
  docker-compose --version
  Harus muncul versi Docker.

  ---
  UPLOAD PROJECT KE VPS

  Cara Paling Mudah: Upload via FileZilla

  Di komputer Anda (Windows):

  1. Compress folder project:
    - Buka Windows Explorer
    - Pergi ke D:\Yudi\development\smp-php
    - Klik kanan folder → Send to → Compressed folder
    - Tunggu sebentar, file smp-php.zip terbentuk
  2. Install FileZilla (kalau belum):
    - Download: https://filezilla-project.org/download.php?type=client
    - Install seperti aplikasi biasa
  3. Buka FileZilla:
    - Klik File → Site Manager
    - Klik New Site
    - Isi:
        - Protocol: SFTP
      - Host: IP VPS Anda (contoh: 123.45.67.89)
      - Port: 22
      - User: root
      - Password: password VPS Anda
    - Klik Connect
  4. Upload file zip:
    - Kiri: cari file smp-php.zip di komputer Anda
    - Kanan: folder /root/
    - Drag & drop file zip dari kiri ke kanan
    - Tunggu upload selesai

  ---
  EKSTRAK DAN SETUP DI VPS

  Kembali ke terminal SSH VPS Anda:

  1. Install unzip

  apt install unzip -y

  2. Extract file

  cd /root
  unzip smp-php.zip

  3. Masuk folder project

  cd smp-php

  4. Ganti password (PENTING!)

  nano docker-compose.yml

  Cari baris ini dan ganti password-nya:
  MYSQL_ROOT_PASSWORD: password_anda_yang_kuat
  MYSQL_PASSWORD: password_anda_yang_kuat

  Tekan Ctrl+X, lalu Y, lalu Enter untuk save.

  5. Setup firewall

  ufw allow 22/tcp    # SSH
  ufw allow 80/tcp    # HTTP
  ufw allow 443/tcp   # HTTPS
  ufw enable

  ---
  JALANKAN DOCKER (INILAH YANG MEMBUAT CONTAINER)

  Masih di terminal VPS:

  docker-compose up -d --build

  Tunggu proses download dan build (5-10 menit tergantung internet VPS).

  Output yang diharapkan:
  ✓ Pulled mysql:8.0
  ✓ Pulled phpmyadmin
  ✓ Built web
  ✓ Created smpn3_cipari_db
  ✓ Created smpn3_cipari_web
  ✓ Created smpn3_cipari_phpmyadmin

  ---
  SELESAI! CIAPAKA

  Cek container berjalan:
  docker-compose ps

  Harusnya muncul:
  NAME                    STATUS
  smpn3_cipari_web       Up (healthy)
  smpn3_cipari_db        Up (healthy)
  smpn3_cipari_phpmyadmin Up

  Akses website:
  - Website: http://IP-VPS-ANDA/
  - Admin Panel: http://IP-VPS-ANDA/admin/
    - Username: admin
    - Password: admin123
  - phpMyAdmin: `http://IP-VPS-ANDA:8080**

  ---
  📋 RINGKASAN PERINTAH (COPY-PASTE INI KE VPS)

  # 1. Update VPS
  apt update && apt upgrade -y

  # 2. Install Docker
  curl -fsSL https://get.docker.com | sh

  # 3. Install Docker Compose
  curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o 
  /usr/local/bin/docker-compose
  chmod +x /usr/local/bin/docker-compose

  # 4. Install unzip (untuk extract zip yang diupload)
  apt install unzip -y

  # 5. Extract project
  cd /root
  unzip smp-php.zip
  cd smp-php

  # 6. Ganti password di docker-compose.yml (nano docker-compose.yml)

  # 7. Setup firewall
  ufw allow 22/tcp
  ufw allow 80/tcp
  ufw allow 443/tcp
  ufw enable

  # 8. Jalankan Docker (INILAH YANG MEMBUAT CONTAINER)
  docker-compose up -d --build

  ---
  ❌ TIDAK PERLU:

  - ❌ Install Apache sendiri → Docker sudah include Apache
  - ❌ Install PHP sendiri → Docker sudah include PHP 8.4
  - ❌ Install MySQL sendiri → Docker sudah include MySQL 8.0
  - ❌ Konfigurasi Apache → Docker sudah auto-configure

  Docker melakukan SEMUANYA otomatis!

  ---
  Butuh bantu dengan bagian mana? Kita bisa lanjut step-by-step.

✻ Cogitated for 1m 4s
