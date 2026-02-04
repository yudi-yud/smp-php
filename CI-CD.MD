# Dokumentasi CI/CD - Multi Environment

## Daftar Isi
1. [Konsep Multi Environment](#konsep-multi-environment)
2. [Alur CI/CD](#alur-cicd)
3. [Setup GitHub Actions](#setup-github-actions)
4. [Konfigurasi VPS](#konfigurasi-vps)
5. [Workflow Files](#workflow-files)
6. [Cara Penggunaan](#cara-penggunaan)

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
                    └─────┬─────┘
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

## Setup GitHub Actions

### 1. Buat Repository Secrets

Di GitHub repository, masuk ke: **Settings → Secrets and variables → Actions**

Tambahkan secrets berikut:

#### Production Server Secrets
```
PRODUCTION_SSH_HOST          = 1.2.3.4 (IP VPS Production)
PRODUCTION_SSH_USERNAME      = root
PRODUCTION_SSH_PORT          = 22
PRODUCTION_SSH_KEY           = (Private SSH Key)
PRODUCTION_DEPLOY_PATH       = /var/www/smpn3-production
PRODUCTION_COMPOSE_FILE      = docker-compose.prod.yml
```

#### Staging Server Secrets
```
STAGING_SSH_HOST             = 1.2.3.5 (IP VPS Staging)
STAGING_SSH_USERNAME         = root
STAGING_SSH_PORT             = 22
STAGING_SSH_KEY              = (Private SSH Key)
STAGING_DEPLOY_PATH          = /var/www/smpn3-staging
STAGING_COMPOSE_FILE         = docker-compose.staging.yml
```

#### Development Server Secrets
```
DEVELOPMENT_SSH_HOST         = 1.2.3.6 (IP VPS Development)
DEVELOPMENT_SSH_USERNAME     = root
DEVELOPMENT_SSH_PORT         = 22
DEVELOPMENT_SSH_KEY          = (Private SSH Key)
DEVELOPMENT_DEPLOY_PATH      = /var/www/smpn3-development
DEVELOPMENT_COMPOSE_FILE     = docker-compose.dev.yml
```

### 2. Generate SSH Key

```bash
# Di komputer lokal, generate SSH key
ssh-keygen -t ed25519 -C "github-actions" -f ~/.ssh/github_actions

# Copy public key ke VPS
ssh-copy-id -i ~/.ssh/github_actions.pub root@PRODUCTION_IP
ssh-copy-id -i ~/.ssh/github_actions.pub root@STAGING_IP
ssh-copy-id -i ~/.ssh/github_actions.pub root@DEVELOPMENT_IP

# Copy private key ke GitHub Secrets
cat ~/.ssh/github_actions
# Copy output dan paste ke SECRET_SSH_KEY
```

---

## Konfigurasi VPS

### 1. Siapkan Direktori di Setiap Server

```bash
# Di Production Server
mkdir -p /var/www/smpn3-production
cd /var/www/smpn3-production

# Di Staging Server
mkdir -p /var/www/smpn3-staging
cd /var/www/smpn3-staging

# Di Development Server
mkdir -p /var/www/smpn3-development
cd /var/www/smpn3-development
```

### 2. Setup Docker Compose per Environment

#### Production Compose File
```yaml
# docker-compose.prod.yml
name: smpn3-production

services:
  nginx:
    image: nginx:alpine
    container_name: smpn3_prod_nginx
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx/nginx.conf:/etc/nginx/conf.d/default.conf:ro
      - ./certs:/etc/nginx/certs:ro
      - ./app:/var/www/html:ro
    depends_on:
      - php
    restart: always
    networks:
      - production_network

  php:
    image: php:8.4-fpm
    container_name: smpn3_prod_php
    volumes:
      - ./app:/var/www/html:ro
    restart: always
    networks:
      - production_network

  db:
    image: mysql:8.0
    container_name: smpn3_prod_db
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_DATABASE: smpn3_production
    volumes:
      - mysql_data:/var/lib/mysql
    restart: always
    networks:
      - production_network

networks:
  production_network:
    driver: bridge

volumes:
  mysql_data:
```

#### Staging Compose File
```yaml
# docker-compose.staging.yml
name: smpn3-staging

services:
  nginx:
    image: nginx:alpine
    container_name: smpn3_staging_nginx
    ports:
      - "80:80"
    volumes:
      - ./nginx/nginx.conf:/etc/nginx/conf.d/default.conf:ro
      - ./app:/var/www/html:ro
    depends_on:
      - php
    restart: always
    networks:
      - staging_network

  php:
    image: php:8.4-fpm
    container_name: smpn3_staging_php
    volumes:
      - ./app:/var/www/html:ro
    restart: always
    networks:
      - staging_network

  db:
    image: mysql:8.0
    container_name: smpn3_staging_db
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_DATABASE: smpn3_staging
    volumes:
      - mysql_data:/var/lib/mysql
    restart: always
    networks:
      - staging_network

networks:
  staging_network:
    driver: bridge

volumes:
  mysql_data:
```

#### Development Compose File
```yaml
# docker-compose.dev.yml
name: smpn3-development

services:
  nginx:
    image: nginx:alpine
    container_name: smpn3_dev_nginx
    ports:
      - "80:80"
    volumes:
      - ./nginx/nginx.conf:/etc/nginx/conf.d/default.conf:ro
      - ./app:/var/www/html:ro
    depends_on:
      - php
    restart: always
    networks:
      - development_network

  php:
    image: php:8.4-fpm
    container_name: smpn3_dev_php
    volumes:
      - ./app:/var/www/html:ro
    restart: always
    networks:
      - development_network

  db:
    image: mysql:8.0
    container_name: smpn3_dev_db
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_DATABASE: smpn3_development
    volumes:
      - mysql_data:/var/lib/mysql
    restart: always
    networks:
      - development_network

networks:
  development_network:
    driver: bridge

volumes:
  mysql_data:
```

### 3. Environment File per Environment

```bash
# Production: .env.production
MYSQL_ROOT_PASSWORD=prod_secure_password_2024

# Staging: .env.staging
MYSQL_ROOT_PASSWORD=staging_password_2024

# Development: .env.development
MYSQL_ROOT_PASSWORD=dev_password_2024
```

---

## Workflow Files

### 1. Production Workflow

```yaml
# .github/workflows/production.yml

name: Deploy to Production

on:
  push:
    branches:
      - main

jobs:
  deploy:
    name: Deploy to Production Server
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup SSH
        run: |
          mkdir -p ~/.ssh
          echo "${{ secrets.PRODUCTION_SSH_KEY }}" > ~/.ssh/deploy_key
          chmod 600 ~/.ssh/deploy_key
          ssh-keyscan -p ${{ secrets.PRODUCTION_SSH_PORT }} ${{ secrets.PRODUCTION_SSH_HOST }} >> ~/.ssh/known_hosts

      - name: Deploy to Production
        run: |
          # SSH ke server dan deploy
          ssh -i ~/.ssh/deploy_key -p ${{ secrets.PRODUCTION_SSH_PORT }} ${{ secrets.PRODUCTION_SSH_USERNAME }}@${{ secrets.PRODUCTION_SSH_HOST }} << 'ENDSSH'
            cd ${{ secrets.PRODUCTION_DEPLOY_PATH }}

            # Backup database
            docker exec smpn3_prod_db mysqldump -uroot -p${MYSQL_ROOT_PASSWORD} smpn3_production > backup_$(date +%Y%m%d_%H%M%S).sql

            # Pull latest code
            git fetch origin main
            git reset --hard origin/main

            # Rebuild and restart containers
            docker compose -f ${{ secrets.PRODUCTION_COMPOSE_FILE }} down
            docker compose -f ${{ secrets.PRODUCTION_COMPOSE_FILE }} up -d --build

            # Cleanup old images
            docker image prune -af

            echo "Production deployment completed!"
          ENDSSH

      - name: Health Check
        run: |
          sleep 10
          curl -f http://${{ secrets.PRODUCTION_SSH_HOST }}/ || exit 1

      - name: Notify Success
        if: success()
        run: echo "Production deployment successful!"

      - name: Notify Failure
        if: failure()
        run: echo "Production deployment failed!"
```

### 2. Staging Workflow

```yaml
# .github/workflows/staging.yml

name: Deploy to Staging

on:
  push:
    branches:
      - staging

jobs:
  deploy:
    name: Deploy to Staging Server
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup SSH
        run: |
          mkdir -p ~/.ssh
          echo "${{ secrets.STAGING_SSH_KEY }}" > ~/.ssh/deploy_key
          chmod 600 ~/.ssh/deploy_key
          ssh-keyscan -p ${{ secrets.STAGING_SSH_PORT }} ${{ secrets.STAGING_SSH_HOST }} >> ~/.ssh/known_hosts

      - name: Deploy to Staging
        run: |
          ssh -i ~/.ssh/deploy_key -p ${{ secrets.STAGING_SSH_PORT }} ${{ secrets.STAGING_SSH_USERNAME }}@${{ secrets.STAGING_SSH_HOST }} << 'ENDSSH'
            cd ${{ secrets.STAGING_DEPLOY_PATH }}

            # Pull latest code
            git fetch origin staging
            git reset --hard origin/staging

            # Rebuild and restart containers
            docker compose -f ${{ secrets.STAGING_COMPOSE_FILE }} down
            docker compose -f ${{ secrets.STAGING_COMPOSE_FILE }} up -d --build

            echo "Staging deployment completed!"
          ENDSSH

      - name: Run Tests
        run: |
          # Add automated tests here
          curl -f http://${{ secrets.STAGING_SSH_HOST }}/api/health
```

### 3. Development Workflow

```yaml
# .github/workflows/development.yml

name: Deploy to Development

on:
  push:
    branches:
      - develop

jobs:
  deploy:
    name: Deploy to Development Server
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup SSH
        run: |
          mkdir -p ~/.ssh
          echo "${{ secrets.DEVELOPMENT_SSH_KEY }}" > ~/.ssh/deploy_key
          chmod 600 ~/.ssh/deploy_key
          ssh-keyscan -p ${{ secrets.DEVELOPMENT_SSH_PORT }} ${{ secrets.DEVELOPMENT_SSH_HOST }} >> ~/.ssh/known_hosts

      - name: Deploy to Development
        run: |
          ssh -i ~/.ssh/deploy_key -p ${{ secrets.DEVELOPMENT_SSH_PORT }} ${{ secrets.DEVELOPMENT_SSH_USERNAME }}@${{ secrets.DEVELOPMENT_SSH_HOST }} << 'ENDSSH'
            cd ${{ secrets.DEVELOPMENT_DEPLOY_PATH }}

            # Pull latest code
            git fetch origin develop
            git reset --hard origin/develop

            # Rebuild and restart containers
            docker compose -f ${{ secrets.DEVELOPMENT_COMPOSE_FILE }} down
            docker compose -f ${{ secrets.DEVELOPMENT_COMPOSE_FILE }} up -d --build

            echo "Development deployment completed!"
          ENDSSH
```

### 4. Pull Request Workflow (Testing)

```yaml
# .github/workflows/pr.yml

name: Pull Request Checks

on:
  pull_request:
    branches:
      - develop
      - staging
      - main

jobs:
  test:
    name: Run Tests
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, pdo, pdo_mysql

      - name: Install Dependencies
        run: |
          # Add dependency installation here if needed
          composer install --no-interaction

      - name: Run Syntax Check
        run: |
          find . -name "*.php" -exec php -l {} \;

      - name: Run Tests
        run: |
          # Add automated tests here
          echo "Running tests..."

      - name: Lint Code
        run: |
          # Add code linting here
          echo "Linting code..."
```

---

## Cara Penggunaan

### 1. Inisialisasi Repository di Setiap Server

```bash
# Di Production Server
cd /var/www/smpn3-production
git init
git remote add origin https://github.com/username/repo.git
git pull origin main

# Di Staging Server
cd /var/www/smpn3-staging
git init
git remote add origin https://github.com/username/repo.git
git pull origin staging

# Di Development Server
cd /var/www/smpn3-development
git init
git remote add origin https://github.com/username/repo.git
git pull origin develop
```

### 2. Development Workflow Baru

```bash
# 1. Buat branch baru dari develop
git checkout develop
git pull origin develop
git checkout -b feature/fitur-baru

# 2. Coding dan commit
git add .
git commit -m "Tambah fitur baru"

# 3. Push ke GitHub
git push origin feature/fitur-baru

# 4. Buat Pull Request ke develop di GitHub
# 5. Setelah merge, otomatis deploy ke Development Server
```

### 3. Promote ke Staging

```bash
# Setelah testing di development berhasil

# 1. Merge develop ke staging
git checkout staging
git pull origin staging
git merge develop
git push origin staging

# 2. Otomatis deploy ke Staging Server
```

### 4. Promote ke Production

```bash
# Setelah UAT di staging berhasil

# 1. Merge staging ke main
git checkout main
git pull origin main
git merge staging
git push origin main

# 2. Otomatis deploy ke Production Server
```

---

## Proteksi Branch

Di GitHub, atur branch protection:

**Settings → Branches → Add Rule**

### Untuk `main` branch:
- [x] Require pull request before merging
  - [x] Require approvals: 1
- [x] Require status checks to pass before merging
- [x] Require branches to be up to date before merging
- [x] Do not allow bypassing the above settings

### Untuk `staging` branch:
- [x] Require pull request before merging
- [x] Require status checks to pass before merging

---

## Monitoring Deployment

### Lihat Status di GitHub

1. Masuk ke repository
2. Klik tab **Actions**
3. Lihat workflow runs terbaru

### Lihat Log di VPS

```bash
# Production
docker logs smpn3_prod_nginx
docker logs smpn3_prod_php
docker logs smpn3_prod_db

# Staging
docker logs smpn3_staging_nginx

# Development
docker logs smpn3_dev_nginx
```

---

## Troubleshooting

### Deployment Gagal

```bash
# Cek workflow logs di GitHub Actions
# Cek manually di server:
ssh root@production_ip
cd /var/www/smpn3-production
docker compose -f docker-compose.prod.yml logs
```

### Container Tidak Restart

```bash
# Cek service status
docker ps -a

# Restart manual
docker compose -f docker-compose.prod.yml restart
```

### Database Migration

```bash
# Masuk ke container
docker exec -it smpn3_prod_db mysql -uroot -p

# Run migration
mysql> source /path/to/migration.sql;
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

*Document Version: 1.0*
*Last Updated: 2026-02-04*
