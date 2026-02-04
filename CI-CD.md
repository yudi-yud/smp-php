# Dokumentasi CI/CD - Multi Environment

## Daftar Isi
1. [Konsep Multi Environment](#konsep-multi-environment)
2. [Alur CI/CD](#alur-cicd)
3. [Tutorial Lengkap Setup CI/CD](#tutorial-lengkap-setup-cicd)
   - [Langkah 1: Persiapan VPS](#langkah-1-persiapan-vps)
   - [Langkah 2: Generate SSH Key](#langkah-2-generate-ssh-key)
   - [Langkah 3: Setup GitHub Secrets](#langkah-3-setup-github-secrets)
   - [Langkah 4: Push Workflow Files](#langkah-4-push-workflow-files)
   - [Langkah 5: Setup VPS untuk Deploy](#langkah-5-setup-vps-untuk-deploy)
   - [Langkah 6: Test Deployment](#langkah-6-test-deployment)
4. [Konfigurasi VPS Lengkap](#konfigurasi-vps-lengkap)
5. [Cara Penggunaan Sehari-hari](#cara-penggunaan-sehari-hari)
6. [Troubleshooting](#troubleshooting)

---

## Konsep Multi Environment

```
┌─────────────────────────────────────────────────────────────────────────┐
│                      MULTI ENVIRONMENT ARCHITECTURE                     │
└─────────────────────────────────────────────────────────────────────────┘

    ┌─────────────────────────────────────────────────────────────────────┐
    │                         REPOSITORY                                  │
    │                                                                      │
    │  ┌──────────┐      ┌──────────┐      ┌──────────┐                  │
    │  │          │      │          │      │          │                  │
    │  │  main    │      │ staging  │      │ develop  │                  │
    │  │          │      │          │      │          │                  │
    │  └────┬─────┘      └────┬─────┘      └────┬─────┘                  │
    │       │                 │                 │                        │
    └───────┼─────────────────┼─────────────────┼────────────────────────┘
            │                 │                 │
            │ Push/PR         │ Push/PR         │ Push
            │                 │                 │
            ▼                 ▼                 ▼
    ┌───────────────┐  ┌───────────────┐  ┌───────────────┐
    │   GitHub      │  │   GitHub      │  │   GitHub      │
    │   Actions     │  │   Actions     │  │   Actions     │
    │               │  │               │  │               │
    │  - Build      │  │  - Build      │  │  - Build      │
    │  - Test       │  │  - Test       │  │  - Test       │
    │  - Deploy     │  │  - Deploy     │  │  - Deploy     │
    └───────┬───────┘  └───────┬───────┘  └───────┬───────┘
            │                 │                 │
            │ SSH             │ SSH             │ SSH
            │                 │                 │
            ▼                 ▼                 ▼
    ┌───────────────┐  ┌───────────────┐  ┌───────────────┐
    │   PRODUCTION  │  │    STAGING    │  │  DEVELOPMENT  │
    │   SERVER      │  │    SERVER     │  │    SERVER     │
    │               │  │               │  │               │
    │  ┌─────────┐  │  │  ┌─────────┐  │  │  ┌─────────┐  │
    │  │  Docker │  │  │  │  Docker │  │  │  │  Docker │  │
    │  │ Compose │  │  │  │ Compose │  │  │  │ Compose │  │
    │  └─────────┘  │  │  └─────────┘  │  │  └─────────┘  │
    │               │  │               │  │               │
    │  URL:         │  │  URL:         │  │  URL:         │
    │  smpn3cipari  │  │  staging.     │  │  dev.         │
    │  .sch.id      │  │  smpn3cipari  │  │  smpn3cipari  │
    │               │  │  .sch.id      │  │  .sch.id      │
    └───────────────┘  └───────────────┘  └───────────────┘

    Akses:            Akses:            Akses:
    - Public          - Internal        - Internal
    - Users           - QA Team         - Dev Team
    - Stable          - Testing         - Experimental
```

---

## Alur CI/CD

### Branch Strategy

| Branch | Environment | Kapan Deploy | Siapa Akses | Tujuan |
|--------|-------------|--------------|-------------|--------|
| `main` | Production | Auto setiap push | Public | Kode stabil, siap untuk user |
| `staging` | Staging | Auto setiap push | Internal/QA | Testing terakhir sebelum production |
| `develop` | Development | Auto setiap push | Dev Team | Gabungan fitur baru untuk internal testing |

### Git Workflow

```
                    ┌──────────┐
                    │  main    │  ← Production Ready
                    └─────┬────┘
                          │
                    ┌─────▼─────┐
                    │ staging   │  ← UAT Testing
                    └─────┬────┘
                          │
                    ┌─────▼─────┐
                    │ develop   │  ← Development
                    └───────────┘

                     ↖           ↗
                      ↖         ↗
                   feature/*   bugfix/*
                      (PR ke develop)
```

### Alur Development

```
1. Developer buat feature branch
   git checkout -b feature/tambah-gallery

2. Coding & commit
   git add .
   git commit -m "Tambah fitur gallery"

3. Push ke GitHub
   git push origin feature/tambah-gallery

4. Pull Request ke develop
   - Review code
   - Automated test
   - Merge ke develop

5. Auto deploy ke Development Server (dev.smpn3cipari.sch.id)

6. QA testing di Development

7. Jika OK, merge develop → staging

8. Auto deploy ke Staging Server (staging.smpn3cipari.sch.id)

9. UAT Testing di Staging

10. Jika OK, merge staging → main

11. Auto deploy ke Production Server (smpn3cipari.sch.id)
```

---

## Tutorial Lengkap Setup CI/CD

### Prasyarat

Sebelum memulai, pastikan Anda sudah memiliki:

- [ ] VPS dengan Docker & Docker Compose terinstall
- [ ] GitHub repository
- [ ] Akses root ke VPS
- [ ] Domain sudah di pointing ke VPS (jika pakai domain)

---

### Langkah 1: Persiapan VPS

#### 1.1 Install Docker di VPS

```bash
# SSH ke VPS
ssh root@76.13.23.109

# Update system
apt update && apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Install Docker Compose
curl -SL https://github.com/docker/compose/releases/download/v2.24.0/docker-compose-linux-x86_64 -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

# Verifikasi instalasi
docker --version
docker-compose --version

# Enable Docker on boot
systemctl enable docker
```

#### 1.2 Buat Direktori Deploy

```bash
# Production
mkdir -p /var/www/smpn3-production
cd /var/www/smpn3-production

# Staging
mkdir -p /var/www/smpn3-staging
cd /var/www/smpn3-staging

# Development
mkdir -p /var/www/smpn3-development
cd /var/www/smpn3-development
```

#### 1.3 Install Git di VPS

```bash
apt install git -y
git --version
```

---

### Langkah 2: Generate SSH Key

#### 2.1 Generate SSH Key di Lokal

```bash
# Di komputer lokal Anda, generate SSH key khusus untuk GitHub Actions
ssh-keygen -t ed25519 -C "github-actions-smpn3" -f ~/.ssh/github_actions_smpn3

# Output:
# Generating public/private ed25519 key pair.
# Your identification has been saved in /home/user/.ssh/github_actions_smpn3
# Your public key has been saved in /home/user/.ssh/github_actions_smpn3.pub
```

#### 2.2 Copy Public Key ke VPS

```bash
# Copy public key ke VPS (jalankan di lokal)
ssh-copy-id -i ~/.ssh/github_actions_smpn3.pub root@76.13.23.109

# Verifikasi SSH key sudah tercopy
ssh -i ~/.ssh/github_actions_smpn3 root@76.13.23.109
```

#### 2.3 Ambil Private Key untuk GitHub

```bash
# Tampilkan private key (jalankan di lokal)
cat ~/.ssh/github_actions_smpn3

# Copy SEMUA output dari -----BEGIN sampai -----END
# Simpan untuk langkah berikutnya
```

**Contoh output private key:**
```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZW
QyNTUxOQAAACACsSaOvwXrrk4hIWZosw6mlcIOxoKVmKMGkbK9F/mdXQAAAJgCy1yCAstc
ggAAAAtzc2gtZWQyNTUxOQAAACACsSaOvwXrrk4hIWZosw6mlcIOxoKVmKMGkbK9F/mdXQ
AAAEAiOWG2e6Hvrz613tCotyyijbjvfGIYWicpErDHeueP5AKxJo6/BeuuTiEhZmizDqaV
wg7GgpWYowaRsr0X+Z1dAAAADmdpdGh1Yi1hY3Rpb25zAQIDBAUGBw==
-----END OPENSSH PRIVATE KEY-----
```

---

### Langkah 3: Setup GitHub Secrets

#### 3.1 Buka GitHub Repository

1. Buka https://github.com/username/repo-name
2. Klik **Settings** (tab atas)
3. Klik menu **Secrets and variables** → **Actions**
4. Klik tombol **New repository secret**

#### 3.2 Tambahkan Production Secrets

Klik **New repository secret** dan tambahkan:

| Name | Secret |
|------|--------|
| `PRODUCTION_SSH_HOST` | `76.13.23.109` |
| `PRODUCTION_SSH_USERNAME` | `root` |
| `PRODUCTION_SSH_PORT` | `22` |
| `PRODUCTION_SSH_KEY` | (paste private key dari langkah 2.3) |
| `PRODUCTION_DEPLOY_PATH` | `/var/www/smpn3-production` |
| `PRODUCTION_COMPOSE_FILE` | `docker-compose.prod.yml` |

**Cara menambahkan:**
1. Name: `PRODUCTION_SSH_HOST`
2. Secret: `76.13.23.109`
3. Klik **Add secret**

Ulangi untuk semua secret production.

#### 3.3 Tambahkan Staging Secrets

| Name | Secret |
|------|--------|
| `STAGING_SSH_HOST` | `76.13.23.109` (atau IP berbeda) |
| `STAGING_SSH_USERNAME` | `root` |
| `STAGING_SSH_PORT` | `22` |
| `STAGING_SSH_KEY` | (paste private key yang sama) |
| `STAGING_DEPLOY_PATH` | `/var/www/smpn3-staging` |
| `STAGING_COMPOSE_FILE` | `docker-compose.staging.yml` |

#### 3.4 Tambahkan Development Secrets

| Name | Secret |
|------|--------|
| `DEVELOPMENT_SSH_HOST` | `76.13.23.109` (atau IP berbeda) |
| `DEVELOPMENT_SSH_USERNAME` | `root` |
| `DEVELOPMENT_SSH_PORT` | `22` |
| `DEVELOPMENT_SSH_KEY` | (paste private key yang sama) |
| `DEVELOPMENT_DEPLOY_PATH` | `/var/www/smpn3-development` |
| `DEVELOPMENT_COMPOSE_FILE` | `docker-compose.dev.yml` |

---

### Langkah 4: Push Workflow Files

#### 4.1 Pastikan Workflow Files Sudah Ada

Di repository lokal Anda, pastikan file-file berikut sudah ada:

```
.github/
└── workflows/
    ├── production.yml
    ├── staging.yml
    ├── development.yml
    └── pr-check.yml
```

#### 4.2 Push ke GitHub

```bash
# Di komputer lokal, di repository project

# Commit workflow files
git add .github/workflows/
git commit -m "Add CI/CD workflows"
git push origin main
```

#### 4.3 Verifikasi di GitHub

1. Buka repository di GitHub
2. Klik tab **Actions**
3. Anda akan melihat workflow sudah terdaftar

---

### Langkah 5: Setup VPS untuk Deploy

#### 5.1 Clone Repository di VPS (Production)

```bash
# SSH ke VPS
ssh root@76.13.23.109

# Masuk ke direktori production
cd /var/www/smpn3-production

# Clone repository (GANTI dengan URL repo Anda)
git clone https://github.com/username/smp-php.git .

# Atau clone specific branch
git clone -b main https://github.com/username/smp-php.git .
```

#### 5.2 Buat Docker Compose File

```bash
# Buat docker-compose.prod.yml
nano /var/www/smpn3-production/docker-compose.prod.yml
```

**Paste konfigurasi berikut:**

```yaml
name: smpn3-production

services:
  web:
    image: php:8.4-apache
    container_name: smpn3_prod_web
    ports:
      - "80:80"
    volumes:
      - ./app:/var/www/html
    environment:
      - DB_HOST=db
      - DB_NAME=smpn3_production
      - DB_USER=smpn3_user
      - DB_PASSWORD=smpn3_password_2024
    depends_on:
      db:
        condition: service_healthy
    restart: always
    networks:
      - production_network

  db:
    image: mysql:8.0
    container_name: smpn3_prod_db
    environment:
      MYSQL_ROOT_PASSWORD: root_password_2024
      MYSQL_DATABASE: smpn3_production
      MYSQL_USER: smpn3_user
      MYSQL_PASSWORD: smpn3_password_2024
    volumes:
      - mysql_data:/var/lib/mysql
      - ./database.sql:/docker-entrypoint-initdb.d/database.sql:ro
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-proot_password_2024"]
      interval: 10s
      timeout: 5s
      retries: 5
    restart: always
    networks:
      - production_network

volumes:
  mysql_data:

networks:
  production_network:
    driver: bridge
```

#### 5.3 Buat Environment File

```bash
# Buat .env.production
nano /var/www/smpn3-production/.env.production
```

```bash
MYSQL_ROOT_PASSWORD=root_password_2024
```

#### 5.4 Setup Container Pertama Kali

```bash
cd /var/www/smpn3-production

# Jalankan container
docker compose -f docker-compose.prod.yml up -d

# Cek status
docker ps

# Cek log
docker logs smpn3_prod_web
```

#### 5.5 Ulangi untuk Staging dan Development

```bash
# Staging
cd /var/www/smpn3-staging
git clone -b staging https://github.com/username/smp-php.git .
# Buat docker-compose.staging.yml (ubah nama container jadi smpn3_staging_*)
docker compose -f docker-compose.staging.yml up -d

# Development
cd /var/www/smpn3-development
git clone -b develop https://github.com/username/smp-php.git .
# Buat docker-compose.dev.yml (ubah nama container jadi smpn3_dev_*)
docker compose -f docker-compose.dev.yml up -d
```

---

### Langkah 6: Test Deployment

#### 6.1 Test Development Deployment

```bash
# Di lokal, push ke develop
git checkout develop
git pull origin develop
echo "# Test CI/CD" >> README.md
git add README.md
git commit -m "Test CI/CD deployment"
git push origin develop
```

**Cek di GitHub:**
1. Buka **Actions** tab
2. Klik workflow "Deploy to Development"
3. Lihat apakah deployment berhasil

**Cek di VPS:**
```bash
ssh root@76.13.23.109
cd /var/www/smpn3-development
git log --oneline -1  # Harus menunjukkan commit terbaru
docker ps  # Container harus running
```

#### 6.2 Test Staging Deployment

```bash
# Merge develop ke staging
git checkout staging
git pull origin staging
git merge develop
git push origin staging
```

#### 6.3 Test Production Deployment

```bash
# Merge staging ke main
git checkout main
git pull origin main
git merge staging
git push origin main
```

---

## Konfigurasi VPS Lengkap

### Production Docker Compose

**File:** `/var/www/smpn3-production/docker-compose.prod.yml`

```yaml
name: smpn3-production

services:
  web:
    build:
      context: ./app
      dockerfile: Dockerfile
    container_name: smpn3_prod_web
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./app/upload:/var/www/html/upload
      - ./app/img:/var/www/html/img
      - ./certs:/etc/nginx/certs:ro
    environment:
      - DB_HOST=db
      - DB_NAME=smpn3_production
      - DB_USER=smpn3_user
      - DB_PASSWORD=smpn3_password_2024
    depends_on:
      db:
        condition: service_healthy
    restart: always
    networks:
      - production_network

  db:
    image: mysql:8.0
    container_name: smpn3_prod_db
    environment:
      MYSQL_ROOT_PASSWORD: root_password_2024
      MYSQL_DATABASE: smpn3_production
      MYSQL_USER: smpn3_user
      MYSQL_PASSWORD: smpn3_password_2024
    volumes:
      - mysql_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-proot_password_2024"]
      interval: 10s
      timeout: 5s
      retries: 5
    restart: always
    networks:
      - production_network

volumes:
  mysql_data:

networks:
  production_network:
    driver: bridge
```

### NGINX Config (Opsional, jika pakai NGINX)

**File:** `/var/www/smpn3-production/nginx/nginx.conf`

```nginx
server {
    listen 80;
    server_name smpn3cipari.sch.id;

    root /var/www/html;
    index index.php index.html;

    # Static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # PHP files
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Pretty URL
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

---

## Cara Penggunaan Sehari-hari

### Scenario 1: Menambahkan Fitur Baru

```bash
# 1. Buat branch feature dari develop
git checkout develop
git pull origin develop
git checkout -b feature/tambah-upload-gambar

# 2. Coding...
# Edit file, commit perubahan
git add .
git commit -m "Tambah fitur upload gambar"

# 3. Push ke GitHub
git push origin feature/tambah-upload-gambar

# 4. Buat Pull Request di GitHub
# - Buka repository
# - Klik "Pull requests"
# - Klik "New pull request"
# - Pilih base: develop ← compare: feature/tambah-upload-gambar
# - Klik "Create pull request"
# - Isi description, lalu "Create pull request"

# 5. Setelah direview dan merge, otomatis deploy ke Development Server
# 6. Cek di http://dev.smpn3cipari.sch.id

# 7. Jika OK, buat PR dari develop ke staging
# 8. Cek di http://staging.smpn3cipari.sch.id

# 9. Jika OK, buat PR dari staging ke main
# 10. Deploy otomatis ke production: https://smpn3cipari.sch.id
```

### Scenario 2: Hotfix Bug di Production

```bash
# 1. Buat branch hotfix dari main
git checkout main
git pull origin main
git checkout -b hotfix/fix-login-error

# 2. Perbaiki bug
# Edit file, commit
git add .
git commit -m "Fix: Perbaiki error login"

# 3. Push dan buat PR ke main
git push origin hotfix/fix-login-error

# 4. Merge ke main (langsung deploy ke production)

# 5. Jangan lupa merge juga ke develop & staging
git checkout develop
git merge hotfix/fix-login-error
git push origin develop

git checkout staging
git merge develop
git push origin staging
```

---

## Troubleshooting

### 1. Deployment Gagal: Permission Denied

**Error:**
```
Permission denied (publickey)
```

**Solusi:**
```bash
# Di VPS, pastikan authorized_keys ada
ssh root@76.13.23.109
cat ~/.ssh/authorized_keys

# Jika belum ada, copy manual
mkdir -p ~/.ssh
chmod 700 ~/.ssh
nano ~/.ssh/authorized_keys
# Paste public key (yang ada di .ssh/github_actions_smpn3.pub)
chmod 600 ~/.ssh/authorized_keys
```

### 2. Deployment Gagal: Port Already in Use

**Error:**
```
Bind for 0.0.0.0:80 failed: port is already allocated
```

**Solusi:**
```bash
# Di VPS, cek port yang terpakai
netstat -tlnp | grep :80

# Stop container yang pakai port 80
docker stop $(docker ps -q -f publish=80)

# Atau ubah port di docker-compose
```

### 3. Container Tidak Start

**Cek log:**
```bash
ssh root@76.13.23.109
cd /var/www/smpn3-production
docker compose -f docker-compose.prod.yml logs
docker compose -f docker-compose.prod.yml logs db
docker compose -f docker-compose.prod.yml logs web
```

### 4. Database Connection Error

**Error:**
```
SQLSTATE[HY000] [2002] Connection refused
```

**Solusi:**
```bash
# Cek container db running
docker ps | grep db

# Cek database dari dalam container
docker exec -it smpn3_prod_db mysql -uroot -p
```

### 5. GitHub Actions Timeout

**Solusi:**
```yaml
# Tambahkan timeout di workflow
- name: Deploy to Production
  timeout-minutes: 30
  run: |
    # ... deployment commands
```

---

## Best Practices

### 1. Selalu Pull Request

Jangan push langsung ke `main`, `staging`, atau `develop`. Selalu buat Pull Request dari feature branch.

### 2. Commit Message yang Jelas

```bash
# ✅ Good
git commit -m "feat: Tambah fitur upload gambar dengan resize"
git commit -m "fix: Perbaiki error login saat password ada spasi"
git commit -m "docs: Update dokumentasi CI/CD"

# ❌ Bad
git commit -m "update"
git commit -m "fix"
git commit -m "asd"
```

### 3. Backup Database Sebelum Deploy

Production workflow sudah otomatis backup, tapi pastikan:
```bash
# Cek backup di VPS
ssh root@76.13.23.109
ls -la /var/www/smpn3-production/backups/
```

### 4. Monitor Deployment

Selalu cek GitHub Actions setelah merge:
- Green checkmark ✅ = Deployment berhasil
- Red X ❌ = Deployment gagal, cek log

### 5. Health Check

Setup health check endpoint:
```php
// file: health.php
<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'database' => connected_to_db() ? 'connected' : 'disconnected'
]);
```

---

## Ringkasan

```
┌─────────────────────────────────────────────────────────────────────┐
│                       WORKFLOW SUMMARY                             │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  feature/*  → PR → develop  →  Auto Deploy → Development Server    │
│                          │                                          │
│                          ↓ (setelah OK)                            │
│                       staging   →  Auto Deploy → Staging Server     │
│                          │                                          │
│                          ↓ (setelah UAT OK)                        │
│                       main      →  Auto Deploy → Production Server  │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Checklist Setup

- [ ] VPS siap dengan Docker & Docker Compose
- [ ] SSH key sudah generate dan copy ke VPS
- [ ] GitHub Secrets sudah diisi semua
- [ ] Workflow files sudah di-push ke GitHub
- [ ] Repository sudah di-clone di setiap environment VPS
- [ ] Docker compose sudah di-setup untuk tiap environment
- [ ] Container sudah running di semua environment
- [ ] Test deployment berhasil untuk semua branch
- [ ] Branch protection sudah diaktifkan untuk main & staging
- [ ] Tim sudah paham workflow development

---

*Document Version: 2.0*
*Last Updated: 2026-02-04*
