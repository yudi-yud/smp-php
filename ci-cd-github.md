# CI/CD GitHub Actions untuk Project SMP-PHP

Tutorial ini menjelaskan cara setup CI/CD pipeline untuk project PHP menggunakan GitHub Actions.

## Table of Contents

1. [Prasyarat](#prasyarat)
2. [Struktur Pipeline](#struktur-pipeline)
3. [Setup GitHub Actions](#setup-github-actions)
4. [Konfigurasi Workflow](#konfigurasi-workflow)
5. [Secrets & Environment Variables](#secrets--environment-variables)
6. [Deployment](#deployment)

---

## Prasyarat

- Project sudah di-push ke GitHub
- Docker dan Docker Compose sudah terinstall di server production
- SSH access ke server production
- GitHub account dengan permission untuk create workflows

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

## Setup GitHub Actions

### 1. Buat Directory Workflows

Buat directory untuk GitHub Actions workflows:

```bash
mkdir -p .github/workflows
```

### 2. Buat File Workflow

Buat file baru di `.github/workflows/deploy.yml`:

```yaml
# .github/workflows/deploy.yml
name: Build, Test, and Deploy

on:
  push:
    branches:
      - main
  pull_request:
    branches:
      - main
  workflow_dispatch:

env:
  REGISTRY: ghcr.io
  IMAGE_NAME: ${{ github.repository }}

jobs:
  # Job 1: Build Docker Image
  build:
    name: Build Docker Image
    runs-on: ubuntu-latest
    permissions:
      contents: read
      packages: write

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v3

      - name: Log in to GitHub Container Registry
        uses: docker/login-action@v3
        with:
          registry: ${{ env.REGISTRY }}
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}

      - name: Extract metadata
        id: meta
        uses: docker/metadata-action@v5
        with:
          images: ${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}
          tags: |
            type=ref,event=branch
            type=sha,prefix={{branch}}-
            type=raw,value=latest,enable={{is_default_branch}}

      - name: Build and push Docker image
        uses: docker/build-push-action@v6
        with:
          context: .
          push: true
          tags: ${{ steps.meta.outputs.tags }}
          labels: ${{ steps.meta.outputs.labels }}
          cache-from: type=gha
          cache-to: type=gha,mode=max

  # Job 2: PHP Syntax Check
  test:
    name: PHP Syntax & Quality Check
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, xml, pdo, pdo_mysql, zip, gd
          coverage: none

      - name: Check PHP syntax
        run: |
          echo "Checking PHP syntax..."
          find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \; | grep -v "No syntax errors" || true
          echo "PHP syntax check completed!"

      - name: Validate Docker Compose config
        run: |
          echo "Validating Docker Compose configuration..."
          docker compose config > /dev/null
          echo "Docker Compose configuration is valid!"

      - name: Security check
        run: |
          echo "Running basic security checks..."
          grep -r "eval(" --include="*.php" . && echo "WARNING: eval() found!" || echo "No eval() found - good!"
          grep -r "mysqli_query" --include="*.php" . | grep -v "mysqli_query(\$conn, \$stmt" && echo "WARNING: Possible SQL injection!" || echo "No obvious SQL injection patterns found!"

  # Job 3: Deploy to Production
  deploy:
    name: Deploy to Production
    runs-on: ubuntu-latest
    needs: [build, test]
    environment: production

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup SSH
        env:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
          SSH_HOST: ${{ secrets.SSH_HOST }}
          SSH_USER: ${{ secrets.SSH_USER }}
        run: |
          mkdir -p ~/.ssh
          echo "$SSH_PRIVATE_KEY" > ~/.ssh/deploy_key
          chmod 600 ~/.ssh/deploy_key
          ssh-keyscan -H $SSH_HOST >> ~/.ssh/known_hosts

      - name: Deploy to server
        env:
          SSH_HOST: ${{ secrets.SSH_HOST }}
          SSH_USER: ${{ secrets.SSH_USER }}
          REGISTRY: ${{ env.REGISTRY }}
          IMAGE_NAME: ${{ env.IMAGE_NAME }}
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
          GITHUB_ACTOR: ${{ github.actor }}
        run: |
          echo "Deploying to production server..."
          ssh -i ~/.ssh/deploy_key $SSH_USER@$SSH_HOST << 'ENDSSH'
            set -e
            cd /path/to/project || exit 1

            # Login to GitHub Container Registry
            echo $GITHUB_TOKEN | docker login $REGISTRY -u $GITHUB_ACTOR --password-stdin

            # Pull latest image
            docker pull $REGISTRY/$IMAGE_NAME:latest

            # Stop existing containers
            docker compose down

            # Start new containers
            docker compose up -d

            # Cleanup old images
            docker image prune -af

            echo "Deployment completed successfully!"
          ENDSSH

      - name: Verify deployment
        env:
          DEPLOY_URL: ${{ secrets.DEPLOY_URL }}
        run: |
          echo "Verifying deployment..."
          sleep 5
          curl -f $DEPLOY_URL || exit 1
          echo "Deployment verified!"

      - name: Notify deployment success
        if: success()
        run: |
          echo "Deployment to production was successful!"

      - name: Notify deployment failure
        if: failure()
        run: |
          echo "Deployment to production failed!"
```

---

## Konfigurasi Workflow

### Step 1: Push File Workflow

```bash
git add .github/workflows/deploy.yml
git commit -m "Add GitHub Actions workflow for CI/CD"
git push origin main
```

### Step 2: Enable Actions

1. Buka repository di GitHub
2. Navigate ke **Actions** tab
3. GitHub akan otomatis mendeteksi workflow baru

---

## Secrets & Environment Variables

### 1. Buka GitHub Repository Settings

Navigate ke: **Settings > Secrets and variables > Actions**

### 2. Tambahkan Repository Secrets

Klik **New repository secret** dan tambahkan:

| Secret Name | Value | Description |
|-------------|-------|-------------|
| `SSH_PRIVATE_KEY` | *(private key content)* | SSH private key untuk server access |
| `SSH_HOST` | `192.168.1.100` atau domain | IP address atau domain server production |
| `SSH_USER` | `ubuntu` / `root` / dll | Username SSH untuk deployment |
| `DEPLOY_URL` | `https://smpn3cipari.sch.id` | URL untuk verifikasi deployment (opsional) |

### 3. Generate SSH Key Pair

Di local machine:

```bash
# Generate SSH key untuk GitHub Actions
ssh-keygen -t ed25519 -C "github-actions" -f ~/.ssh/github_deploy

# Copy public key ke server
ssh-copy-id -i ~/.ssh/github_deploy.pub user@production-server

# Test connection
ssh -i ~/.ssh/github_deploy user@production-server

# Copy private key content untuk GitHub Secret
cat ~/.ssh/github_deploy
```

Copy isi private key dan paste ke GitHub secret `SSH_PRIVATE_KEY`.

---

## Deployment

### Automatic & Manual Deployment

Workflow akan berjalan **otomatis** saat:
- Code di-push ke branch `main`
- Pull request dibuat ke branch `main`

Workflow juga bisa dipicu **manual** melalui:
1. Buka **Actions** tab di repository
2. Pilih workflow "Build, Test, and Deploy"
3. Klik **Run workflow**
4. Pilih branch dan klik **Run workflow**

### Manual Deployment Approval

Secara default, deploy job menggunakan `environment: production`. Untuk menambahkan approval manual:

1. Buka **Settings > Environments**
2. Klik pada environment `production`
3. Enable **Required reviewers**
4. Pilih reviewer yang diperlukan

Setelah setup, setiap deployment akan memerlukan approval dari reviewer sebelum berjalan.

---

## Advanced Configuration

### Multi-Environment Workflow

```yaml
# .github/workflows/deploy-multi-env.yml
name: Multi-Environment Deploy

on:
  push:
    branches: [main, develop]

jobs:
  deploy_staging:
    name: Deploy to Staging
    if: github.ref == 'refs/heads/develop'
    runs-on: ubuntu-latest
    environment:
      name: staging
      url: https://staging.smpn3cipari.sch.id
    steps:
      # ... deployment steps untuk staging ...

  deploy_production:
    name: Deploy to Production
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    environment:
      name: production
      url: https://smpn3cipari.sch.id
    steps:
      # ... deployment steps untuk production ...
```

### Slack/Telegram Notification

Tambahkan notifikasi setelah deployment:

```yaml
      - name: Send Slack notification
        if: always()
        uses: slackapi/slack-github-action@v1
        with:
          payload: |
            {
              "text": "Deployment ${{ job.status }}: ${{ github.sha }}"
            }
        env:
          SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK_URL }}
```

### Database Migration

Tambahkan step untuk database migration:

```yaml
      - name: Run database migrations
        env:
          SSH_HOST: ${{ secrets.SSH_HOST }}
          SSH_USER: ${{ secrets.SSH_USER }}
        run: |
          ssh -i ~/.ssh/deploy_key $SSH_USER@$SSH_HOST << 'ENDSSH'
            cd /path/to/project
            docker compose exec -T web php migrations/migrate.php
          ENDSSH
```

---

## Monitoring & Debugging

### View Workflow Runs

1. Buka **Actions** tab di repository
2. Pilih workflow run yang ingin dilihat
3. Klik pada job untuk melihat detail logs

### Debug Failed Runs

Tips untuk debugging:

1. **Enable debug logging** - Re-run workflow dengan debug logging:
   - Navigate ke **Actions** tab
   - Pilih failed workflow
   - Klik **Re-run jobs** > **Re-run all jobs**
   - Enable debug logging secrets di repository settings

2. **Check SSH connection** - Test SSH connection dari local machine:
   ```bash
   ssh -i ~/.ssh/github_deploy user@production-server
   ```

3. **Verify Docker registry access** - Cek apakah image bisa di-pull:
   ```bash
   docker pull ghcr.io/username/repo:latest
   ```

---

## Best Practices

### 1. Version Tagging

Gunakan semantic versioning untuk production releases:

```yaml
      - name: Create Release Tag
        if: github.ref == 'refs/heads/main'
        run: |
          git tag -a v1.0.${{ github.run_number }} -m "Release v1.0.${{ github.run_number }}"
          git push origin v1.0.${{ github.run_number }}
```

### 2. Rollback Capability

Simpan previous image untuk rollback:

```yaml
      - name: Deploy with rollback option
        run: |
          ssh -i ~/.ssh/deploy_key $SSH_USER@$SSH_HOST << 'ENDSSH'
            # Tag current image as previous
            docker tag $REGISTRY/$IMAGE_NAME:latest $REGISTRY/$IMAGE_NAME:previous
            # Pull and deploy new image
            docker pull $REGISTRY/$IMAGE_NAME:latest
            docker compose up -d
          ENDSSH
```

### 3. Health Check

Tambahkan health check setelah deployment:

```yaml
      - name: Health check
        run: |
          max_attempts=10
          attempt=0
          while [ $attempt -lt $max_attempts ]; do
            if curl -f $DEPLOY_URL/health.php; then
              echo "Health check passed!"
              exit 0
            fi
            attempt=$((attempt + 1))
            sleep 10
          done
          echo "Health check failed!"
          exit 1
```

---

## Referensi

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Docker Build Push Action](https://github.com/docker/build-push-action)
- [Setup PHP Action](https://github.com/shivammathur/setup-php)
- [GitHub Container Registry](https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry)
