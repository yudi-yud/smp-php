# CI/CD GitLab untuk Project SMP-PHP

Tutorial ini menjelaskan cara setup CI/CD pipeline untuk project PHP menggunakan GitLab CI/CD.

## Table of Contents

1. [Prasyarat](#prasyarat)
2. [Struktur Pipeline](#struktur-pipeline)
3. [Setup GitLab CI/CD](#setup-gitlab-cicd)
4. [Konfigurasi Pipeline](#konfigurasi-pipeline)
5. [Variables & Secrets](#variables--secrets)
6. [Deployment](#deployment)

---

## Prasyarat

- Project sudah di-push ke GitLab
- Docker dan Docker Compose sudah terinstall di server production
- SSH access ke server production

---

## Struktur Pipeline

Pipeline akan memiliki 3 stage utama:

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   BUILD     │ -> │    TEST     │ -> │  DEPLOY     │
│  (Docker)   │    │  (PHP Lint) │    │  (SSH)      │
└─────────────┘    └─────────────┘    └─────────────┘
```

---

## Setup GitLab CI/CD

### 1. Buat File `.gitlab-ci.yml`

Buat file baru bernama `.gitlab-ci.yml` di root directory project:

```yaml
# .gitlab-ci.yml
image: docker:27.0.0-cli

services:
  - docker:27.0.0-dind

stages:
  - build
  - test
  - deploy

variables:
  DOCKER_DRIVER: overlay2
  DOCKER_TLS_CERTDIR: "/certs"
  IMAGE_NAME: $CI_REGISTRY_IMAGE:$CI_COMMIT_SHORT_SHA
  IMAGE_LATEST: $CI_REGISTRY_IMAGE:latest

before_script:
  - echo "$CI_REGISTRY_PASSWORD" | docker login -u "$CI_REGISTRY_USER" --password-stdin $CI_REGISTRY

# Stage 1: Build Docker Image
build:
  stage: build
  script:
    - echo "Building Docker image..."
    - docker build -t $IMAGE_NAME -t $IMAGE_LATEST .
    - docker push $IMAGE_NAME
    - docker push $IMAGE_LATEST
  only:
    - main

# Stage 2: Test
test:syntax:
  stage: test
  image: php:8.4-cli
  before_script:
    - apk add --no-cache composer
  script:
    - echo "Checking PHP syntax..."
    - find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \; | grep -v "No syntax errors"
    - echo "PHP syntax check passed!"
  allow_failure: false

test:docker-compose:
  stage: test
  script:
    - echo "Testing Docker Compose configuration..."
    - docker compose config
    - echo "Docker Compose configuration is valid!"
  allow_failure: false

# Stage 3: Deploy to Production
deploy:production:
  stage: deploy
  image: alpine:latest
  before_script:
    - apk add --no-cache openssh-client
    - eval $(ssh-agent -s)
    - echo "$SSH_PRIVATE_KEY" | tr -d '\r' | ssh-add -
    - mkdir -p ~/.ssh
    - chmod 700 ~/.ssh
    - ssh-keyscan -H $DEPLOY_SERVER >> ~/.ssh/known_hosts
  script:
    - echo "Deploying to production server..."
    - ssh $DEPLOY_USER@$DEPLOY_SERVER << 'EOF'
      cd /path/to/project
      docker login -u $CI_REGISTRY_USER -p $CI_REGISTRY_PASSWORD $CI_REGISTRY
      docker pull $IMAGE_NAME
      docker compose down
      IMAGE_TAG=$CI_COMMIT_SHORT_SHA docker compose up -d
      docker image prune -f
      echo "Deployment completed!"
      EOF
  only:
    - main
  when: manual
  environment:
    name: production
    url: https://smpn3cipari.sch.id
```

### 2. Update File `.gitignore`

Pastikan file berikut ada di `.gitignore`:

```gitignore
# CI/CD
.gitlab-ci.yml
.env.local
.env.production

# Docker
*.log

# Uploads & temporary files
upload/*
img/gallery/*
!upload/.gitkeep
!img/gallery/.gitkeep
```

---

## Konfigurasi Pipeline

### Step 1: Enable GitLab CI/CD

1. Buka project di GitLab
2. Navigate ke **Settings > General > Visibility, project features, permissions**
3. Pastikan **CI/CD** sudah enabled

### Step 2: Push File Configuration

```bash
git add .gitlab-ci.yml
git commit -m "Add GitLab CI/CD configuration"
git push origin main
```

---

## Variables & Secrets

### 1. Buka GitLab CI/CD Settings

Navigate ke: **Settings > CI/CD > Variables**

### 2. Tambahkan Variables Berikut:

| Variable | Type | Protected | Masked | Description |
|----------|------|-----------|--------|-------------|
| `CI_REGISTRY_USER` | Variable | ✓ | ✓ | GitLab username untuk container registry |
| `CI_REGISTRY_PASSWORD` | Variable | ✓ | ✓ | GitLab password/token untuk container registry |
| `SSH_PRIVATE_KEY` | File | ✓ | ✓ | SSH private key untuk server access |
| `DEPLOY_SERVER` | Variable | ✓ | ✗ | IP address atau domain server production |
| `DEPLOY_USER` | Variable | ✓ | ✗ | Username SSH untuk deployment |
| `DB_PASSWORD` | Variable | ✓ | ✓ | Database password (jika perlu) |

### 3. Generate SSH Key Pair

Di local machine:

```bash
# Generate SSH key
ssh-keygen -t ed25519 -C "gitlab-ci" -f ~/.ssh/gitlab_deploy

# Copy public key ke server
ssh-copy-id -i ~/.ssh/gitlab_deploy.pub user@production-server

# Copy private key content untuk GitLab variable
cat ~/.ssh/gitlab_deploy
```

Copy isi private key dan paste ke GitLab variable `SSH_PRIVATE_KEY`.

---

## Deployment

### Manual Deployment

Secara default, deploy stage diset sebagai **manual**. Untuk deploy:

1. Buka **CI/CD > Pipelines** di GitLab
2. Pilih pipeline yang ingin deploy
3. Klik tombol **Play** pada job `deploy:production`
4. Confirm deployment

### Automatic Deployment

Untuk membuat deployment otomatis, hapus baris `when: manual` dari job `deploy:production`.

---

## Troubleshooting

### Pipeline gagal saat build

Cek log error di job output. Masalah umum:
- Dockerfile syntax error
- Missing dependencies
- Registry authentication failed

### SSH Connection Failed

Pastikan:
- SSH key sudah benar
- Server accessible dari GitLab runners
- User permission sudah benar

### Docker Compose Error

Validasi konfigurasi:
```bash
docker compose config
```

---

## Advanced Configuration

### Multi-Environment (Staging & Production)

```yaml
deploy:staging:
  stage: deploy
  script:
    - echo "Deploying to staging..."
    # SSH ke staging server
  only:
    - develop
  environment:
    name: staging
    url: https://staging.smpn3cipari.sch.id

deploy:production:
  stage: deploy
  script:
    - echo "Deploying to production..."
    # SSH ke production server
  only:
    - main
  when: manual
  environment:
    name: production
    url: https://smpn3cipari.sch.id
```

### Notification ke Slack/Telegram

Tambahkan notifikasi setelah deployment:

```yaml
notify:success:
  stage: .post
  script:
    - curl -X POST $SLACK_WEBHOOK -H 'Content-Type: application/json' -d '{"text":"Deployment successful!"}'
  when: on_success
```

---

## Referensi

- [GitLab CI/CD Documentation](https://docs.gitlab.com/ee/ci/)
- [Docker in GitLab CI](https://docs.gitlab.com/ee/ci/docker/)
- [GitLab Registry](https://docs.gitlab.com/ee/user/packages/container_registry/)
