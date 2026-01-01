# Chat Platform Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build a multi-tenant SaaS chat platform with embeddable widget, Laravel backend, and Nuxt dashboard.

**Architecture:** Monorepo with Laravel API + Reverb WebSocket server, Nuxt dashboard (renders widget via iframe), and a tiny embed script. PostgreSQL for data, Redis for pub/sub and caching, Bunny CDN for files, Brevo for email.

**Tech Stack:** Laravel 11, Laravel Reverb, Nuxt 3, Nuxt UI, Pinia, PostgreSQL 16, Redis 7, Docker, Vite

---

## Phase 1: Project Scaffolding

### Task 1.1: Initialize Monorepo Structure

**Files:**
- Create: `docker-compose.yml`
- Create: `.env.example`
- Create: `.gitignore`
- Create: `README.md`

**Step 1: Create docker-compose.yml**

```yaml
services:
  api:
    build:
      context: ./api
      dockerfile: Dockerfile
    ports:
      - "8000:8000"
    volumes:
      - ./api:/var/www/html
    depends_on:
      - postgres
      - redis
    environment:
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=chat
      - DB_USERNAME=chat
      - DB_PASSWORD=secret
      - REDIS_HOST=redis
    networks:
      - chat-network

  reverb:
    build:
      context: ./api
      dockerfile: Dockerfile
    command: php artisan reverb:start --host=0.0.0.0 --port=8080
    ports:
      - "8080:8080"
    volumes:
      - ./api:/var/www/html
    depends_on:
      - redis
    environment:
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_PORT=5432
      - DB_DATABASE=chat
      - DB_USERNAME=chat
      - DB_PASSWORD=secret
      - REDIS_HOST=redis
    networks:
      - chat-network

  dashboard:
    build:
      context: ./dashboard
      dockerfile: Dockerfile
    ports:
      - "3000:3000"
    volumes:
      - ./dashboard:/app
      - /app/node_modules
    environment:
      - NUXT_PUBLIC_API_URL=http://localhost:8000
      - NUXT_PUBLIC_REVERB_HOST=localhost
      - NUXT_PUBLIC_REVERB_PORT=8080
    networks:
      - chat-network

  postgres:
    image: postgres:16-alpine
    ports:
      - "5432:5432"
    environment:
      POSTGRES_DB: chat
      POSTGRES_USER: chat
      POSTGRES_PASSWORD: secret
    volumes:
      - postgres-data:/var/lib/postgresql/data
    networks:
      - chat-network

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    volumes:
      - redis-data:/data
    networks:
      - chat-network

networks:
  chat-network:
    driver: bridge

volumes:
  postgres-data:
  redis-data:
```

**Step 2: Create .env.example**

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=chat
DB_USERNAME=chat
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# App URLs
APP_URL=http://localhost:8000
DASHBOARD_URL=http://localhost:3000

# Reverb
REVERB_APP_ID=chat-app
REVERB_APP_KEY=chat-key
REVERB_APP_SECRET=chat-secret
REVERB_HOST=localhost
REVERB_PORT=8080

# Bunny CDN
BUNNY_STORAGE_ZONE=
BUNNY_STORAGE_API_KEY=
BUNNY_CDN_URL=

# Brevo
BREVO_API_KEY=
MAIL_FROM_ADDRESS=
```

**Step 3: Create .gitignore**

```gitignore
# Dependencies
node_modules/
vendor/

# Environment
.env
.env.local
.env.*.local

# Build outputs
.output/
.nuxt/
dist/

# IDE
.idea/
.vscode/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Logs
*.log
npm-debug.log*

# Laravel
storage/*.key
storage/logs/*
bootstrap/cache/*

# Testing
coverage/
.phpunit.result.cache
```

**Step 4: Create README.md**

```markdown
# Reusable Chat Platform

Multi-tenant SaaS chat platform with embeddable widget.

## Quick Start

```bash
# Copy environment file
cp .env.example .env

# Start services
docker-compose up -d

# Install Laravel dependencies
docker-compose exec api composer install

# Run migrations
docker-compose exec api php artisan migrate

# Install Nuxt dependencies
docker-compose exec dashboard npm install
```

## Services

| Service | URL |
|---------|-----|
| API | http://localhost:8000 |
| Reverb (WebSocket) | ws://localhost:8080 |
| Dashboard | http://localhost:3000 |
| PostgreSQL | localhost:5432 |
| Redis | localhost:6379 |

## Documentation

See `docs/plans/` for design documents and implementation plans.
```

**Step 5: Commit**

```bash
git add docker-compose.yml .env.example .gitignore README.md
git commit -m "chore: initialize monorepo structure with Docker"
```

---

### Task 1.2: Scaffold Laravel API

**Files:**
- Create: `api/` directory with Laravel 11

**Step 1: Create Laravel project**

```bash
composer create-project laravel/laravel api
```

**Step 2: Create api/Dockerfile**

```dockerfile
FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for caching
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

# Copy application code
COPY . .

# Generate autoloader
RUN composer dump-autoload --optimize

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port
EXPOSE 8000

# Start PHP built-in server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

**Step 3: Install Laravel packages**

```bash
cd api
composer require laravel/reverb
composer require laravel/sanctum
composer require --dev phpunit/phpunit
```

**Step 4: Configure Laravel for PostgreSQL and Redis**

Edit `api/config/database.php` - set default to pgsql (already configured).

Edit `api/.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=chat
DB_USERNAME=chat
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379

BROADCAST_CONNECTION=reverb
```

**Step 5: Publish Reverb config**

```bash
php artisan install:broadcasting
php artisan vendor:publish --tag=reverb-config
```

**Step 6: Commit**

```bash
git add api/
git commit -m "feat: scaffold Laravel API with Reverb and Sanctum"
```

---

### Task 1.3: Scaffold Nuxt Dashboard

**Files:**
- Create: `dashboard/` directory with Nuxt 3

**Step 1: Create Nuxt project with Nuxt UI**

```bash
npx nuxi@latest init dashboard -t ui
```

**Step 2: Create dashboard/Dockerfile**

```dockerfile
FROM node:20-alpine

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy application code
COPY . .

# Expose port
EXPOSE 3000

# Start development server
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
```

**Step 3: Install additional dependencies**

```bash
cd dashboard
npm install @vueuse/core pinia @pinia/nuxt laravel-echo pusher-js
```

**Step 4: Configure nuxt.config.ts**

```typescript
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },

  modules: [
    '@nuxt/ui',
    '@pinia/nuxt',
  ],

  runtimeConfig: {
    public: {
      apiUrl: process.env.NUXT_PUBLIC_API_URL || 'http://localhost:8000',
      reverbHost: process.env.NUXT_PUBLIC_REVERB_HOST || 'localhost',
      reverbPort: process.env.NUXT_PUBLIC_REVERB_PORT || '8080',
    }
  },

  css: ['~/assets/css/main.css'],
})
```

**Step 5: Create assets/css/main.css with theme tokens**

```css
:root {
  /* Surfaces */
  --chat-bg-primary: #fafafa;
  --chat-bg-secondary: #ffffff;
  --chat-bg-tertiary: #f3f4f6;

  /* Accent */
  --chat-accent: #2563eb;
  --chat-accent-soft: #dbeafe;

  /* Text */
  --chat-text-primary: #111827;
  --chat-text-secondary: #6b7280;
  --chat-text-inverse: #ffffff;

  /* Bubbles */
  --chat-bubble-sent: var(--chat-accent);
  --chat-bubble-received: var(--chat-bg-tertiary);

  /* Shadows */
  --chat-shadow-sm: 0 1px 2px rgba(0,0,0,0.04);
  --chat-shadow-md: 0 4px 12px rgba(0,0,0,0.06);
  --chat-shadow-float: 0 8px 30px rgba(0,0,0,0.08);

  /* Typography */
  --chat-font-display: 'Satoshi', system-ui, sans-serif;
  --chat-font-body: 'Inter', system-ui, sans-serif;

  /* Borders */
  --chat-radius-sm: 8px;
  --chat-radius-md: 12px;
  --chat-radius-lg: 20px;
  --chat-radius-bubble: 18px;
}

[data-theme="dark"] {
  --chat-bg-primary: #0f0f0f;
  --chat-bg-secondary: #1a1a1a;
  --chat-bg-tertiary: #262626;
  --chat-text-primary: #fafafa;
  --chat-text-secondary: #a1a1aa;
  --chat-bubble-received: #262626;
}
```

**Step 6: Commit**

```bash
git add dashboard/
git commit -m "feat: scaffold Nuxt dashboard with Nuxt UI and Pinia"
```

---

### Task 1.4: Scaffold Widget Embed

**Files:**
- Create: `widget/` directory

**Step 1: Initialize widget package**

```bash
mkdir -p widget/src
cd widget
npm init -y
npm install -D vite typescript
```

**Step 2: Create widget/vite.config.ts**

```typescript
import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  build: {
    lib: {
      entry: resolve(__dirname, 'src/widget.ts'),
      name: 'ChatWidget',
      fileName: 'widget',
      formats: ['iife']
    },
    outDir: 'dist',
    minify: 'terser',
  },
})
```

**Step 3: Create widget/src/widget.ts**

```typescript
interface WidgetConfig {
  workspace: string
  userToken?: string
  position?: 'bottom-right' | 'bottom-left'
  theme?: 'light' | 'dark' | 'auto'
}

class ChatWidgetManager {
  private iframe: HTMLIFrameElement | null = null
  private container: HTMLDivElement | null = null
  private config: WidgetConfig
  private isOpen = false
  private baseUrl: string

  constructor(config: WidgetConfig) {
    this.config = {
      position: 'bottom-right',
      theme: 'auto',
      ...config
    }
    this.baseUrl = (window as any).__CHAT_WIDGET_URL__ || 'http://localhost:3000'
  }

  init() {
    this.createContainer()
    this.createLauncher()
    this.setupMessageListener()
  }

  private createContainer() {
    this.container = document.createElement('div')
    this.container.id = 'chat-widget-container'
    this.container.style.cssText = `
      position: fixed;
      ${this.config.position === 'bottom-right' ? 'right: 20px;' : 'left: 20px;'}
      bottom: 20px;
      z-index: 999999;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    `
    document.body.appendChild(this.container)
  }

  private createLauncher() {
    const launcher = document.createElement('button')
    launcher.id = 'chat-widget-launcher'
    launcher.innerHTML = `
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
      </svg>
    `
    launcher.style.cssText = `
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: var(--chat-accent, #2563eb);
      color: white;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      transition: transform 0.2s, box-shadow 0.2s;
    `
    launcher.addEventListener('click', () => this.toggle())
    launcher.addEventListener('mouseenter', () => {
      launcher.style.transform = 'scale(1.05)'
      launcher.style.boxShadow = '0 6px 20px rgba(0,0,0,0.2)'
    })
    launcher.addEventListener('mouseleave', () => {
      launcher.style.transform = 'scale(1)'
      launcher.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)'
    })
    this.container!.appendChild(launcher)
  }

  private createIframe() {
    if (this.iframe) return

    const params = new URLSearchParams({
      workspace: this.config.workspace,
      theme: this.config.theme || 'auto',
      ...(this.config.userToken && { token: this.config.userToken })
    })

    this.iframe = document.createElement('iframe')
    this.iframe.src = `${this.baseUrl}/widget?${params.toString()}`
    this.iframe.style.cssText = `
      width: 380px;
      height: 520px;
      border: none;
      border-radius: 16px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.12);
      position: absolute;
      bottom: 70px;
      ${this.config.position === 'bottom-right' ? 'right: 0;' : 'left: 0;'}
      display: none;
      background: white;
    `
    this.container!.appendChild(this.iframe)
  }

  private setupMessageListener() {
    window.addEventListener('message', (event) => {
      if (event.origin !== this.baseUrl) return

      const { type, data } = event.data
      switch (type) {
        case 'chat:close':
          this.close()
          break
        case 'chat:message':
          this.dispatchEvent('message', data)
          break
        case 'chat:ready':
          this.dispatchEvent('ready', data)
          break
      }
    })
  }

  private dispatchEvent(name: string, detail: any) {
    const event = new CustomEvent(`chat:${name}`, { detail })
    window.dispatchEvent(event)
  }

  toggle() {
    if (this.isOpen) {
      this.close()
    } else {
      this.open()
    }
  }

  open() {
    this.createIframe()
    if (this.iframe) {
      this.iframe.style.display = 'block'
      this.isOpen = true
      this.dispatchEvent('open', {})
    }
  }

  close() {
    if (this.iframe) {
      this.iframe.style.display = 'none'
      this.isOpen = false
      this.dispatchEvent('close', {})
    }
  }
}

// Web Component
class ChatWidgetElement extends HTMLElement {
  private widget: ChatWidgetManager | null = null

  static get observedAttributes() {
    return ['workspace', 'user-token', 'position', 'theme']
  }

  connectedCallback() {
    const config: WidgetConfig = {
      workspace: this.getAttribute('workspace') || '',
      userToken: this.getAttribute('user-token') || undefined,
      position: (this.getAttribute('position') as 'bottom-right' | 'bottom-left') || 'bottom-right',
      theme: (this.getAttribute('theme') as 'light' | 'dark' | 'auto') || 'auto',
    }
    this.widget = new ChatWidgetManager(config)
    this.widget.init()
  }

  open() {
    this.widget?.open()
  }

  close() {
    this.widget?.close()
  }

  toggle() {
    this.widget?.toggle()
  }
}

// Register web component
customElements.define('chat-widget', ChatWidgetElement)

// Auto-init from script tag data attributes
document.addEventListener('DOMContentLoaded', () => {
  const script = document.querySelector('script[data-workspace]') as HTMLScriptElement
  if (script) {
    const widget = new ChatWidgetManager({
      workspace: script.dataset.workspace || '',
      userToken: script.dataset.userToken,
      position: (script.dataset.position as 'bottom-right' | 'bottom-left') || 'bottom-right',
      theme: (script.dataset.theme as 'light' | 'dark' | 'auto') || 'auto',
    })
    widget.init()
    ;(window as any).chatWidget = widget
  }
})

export { ChatWidgetManager, ChatWidgetElement }
```

**Step 4: Create widget/tsconfig.json**

```json
{
  "compilerOptions": {
    "target": "ES2020",
    "module": "ESNext",
    "moduleResolution": "bundler",
    "strict": true,
    "declaration": true,
    "outDir": "dist",
    "lib": ["ES2020", "DOM"]
  },
  "include": ["src/**/*"]
}
```

**Step 5: Update widget/package.json scripts**

```json
{
  "name": "chat-widget",
  "version": "1.0.0",
  "scripts": {
    "build": "vite build",
    "dev": "vite build --watch"
  }
}
```

**Step 6: Build widget**

```bash
npm run build
```

**Step 7: Commit**

```bash
git add widget/
git commit -m "feat: scaffold embeddable widget with web component"
```

---

## Phase 2: Database & Models

### Task 2.1: Create Database Migrations

**Files:**
- Create: `api/database/migrations/` (multiple migration files)

**Step 1: Create workspaces migration**

```bash
cd api
php artisan make:migration create_workspaces_table
```

Edit the migration file:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug', 100)->unique();
            $table->string('plan', 20)->default('free');
            $table->uuid('owner_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
```

**Step 2: Create workspace_settings migration**

```bash
php artisan make:migration create_workspace_settings_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_settings', function (Blueprint $table) {
            $table->uuid('workspace_id')->primary();
            $table->boolean('read_receipts_enabled')->default(true);
            $table->boolean('online_status_enabled')->default(true);
            $table->boolean('typing_indicators_enabled')->default(true);
            $table->integer('file_size_limit_mb')->default(10);
            $table->integer('rate_limit_per_minute')->default(60);
            $table->string('webhook_url', 500)->nullable();
            $table->string('webhook_secret', 100)->nullable();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_settings');
    }
};
```

**Step 3: Create workspace_themes migration**

```bash
php artisan make:migration create_workspace_themes_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_themes', function (Blueprint $table) {
            $table->uuid('workspace_id')->primary();
            $table->string('preset', 20)->default('professional');
            $table->string('primary_color', 7)->nullable();
            $table->string('background_color', 7)->nullable();
            $table->string('font_family', 100)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->string('position', 20)->default('bottom-right');
            $table->text('custom_css')->nullable();
            $table->boolean('dark_mode_enabled')->default(true);
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_themes');
    }
};
```

**Step 4: Create admins migration**

```bash
php artisan make:migration create_admins_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('name');
            $table->boolean('is_super_admin')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        // Add foreign key to workspaces
        Schema::table('workspaces', function (Blueprint $table) {
            $table->foreign('owner_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
        });
        Schema::dropIfExists('admins');
    }
};
```

**Step 5: Create workspace_members migration**

```bash
php artisan make:migration create_workspace_members_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_members', function (Blueprint $table) {
            $table->uuid('workspace_id');
            $table->uuid('admin_id');
            $table->string('role', 20)->default('agent');
            $table->timestamps();

            $table->primary(['workspace_id', 'admin_id']);
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_members');
    }
};
```

**Step 6: Create api_keys migration**

```bash
php artisan make:migration create_api_keys_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->string('name', 100);
            $table->string('key_hash');
            $table->string('key_prefix', 20);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->index('key_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
```

**Step 7: Create chat_users migration**

```bash
php artisan make:migration create_chat_users_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->string('external_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->boolean('is_anonymous')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->unique(['workspace_id', 'external_id']);
            $table->index(['workspace_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_users');
    }
};
```

**Step 8: Create sessions migration**

```bash
php artisan make:migration create_chat_sessions_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->uuid('chat_user_id');
            $table->string('token')->unique();
            $table->jsonb('context')->default('{}');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('chat_user_id')->references('id')->on('chat_users')->onDelete('cascade');
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};
```

**Step 9: Create conversations migration**

```bash
php artisan make:migration create_conversations_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->string('type', 20)->default('direct');
            $table->string('name')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('chat_users')->onDelete('set null');
            $table->index(['workspace_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
```

**Step 10: Create participants migration**

```bash
php artisan make:migration create_participants_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->uuid('conversation_id');
            $table->uuid('chat_user_id');
            $table->string('role', 20)->default('member');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamp('muted_until')->nullable();

            $table->primary(['conversation_id', 'chat_user_id']);
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreign('chat_user_id')->references('id')->on('chat_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
```

**Step 11: Create messages migration**

```bash
php artisan make:migration create_messages_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id');
            $table->uuid('sender_id');
            $table->text('content')->nullable();
            $table->string('type', 20)->default('text');
            $table->jsonb('metadata')->default('{}');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('chat_users')->onDelete('cascade');
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
```

**Step 12: Create attachments migration**

```bash
php artisan make:migration create_attachments_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_id');
            $table->string('filename');
            $table->string('mime_type', 100);
            $table->integer('size_bytes');
            $table->string('url', 500);
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
```

**Step 13: Create reactions migration**

```bash
php artisan make:migration create_reactions_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->uuid('message_id');
            $table->uuid('chat_user_id');
            $table->string('emoji', 20);
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['message_id', 'chat_user_id', 'emoji']);
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->foreign('chat_user_id')->references('id')->on('chat_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
```

**Step 14: Create bans migration**

```bash
php artisan make:migration create_bans_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->uuid('chat_user_id');
            $table->uuid('banned_by')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('chat_user_id')->references('id')->on('chat_users')->onDelete('cascade');
            $table->foreign('banned_by')->references('id')->on('admins')->onDelete('set null');
            $table->index(['workspace_id', 'chat_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bans');
    }
};
```

**Step 15: Create blocks migration**

```bash
php artisan make:migration create_blocks_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->uuid('workspace_id');
            $table->uuid('blocker_id');
            $table->uuid('blocked_id');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['workspace_id', 'blocker_id', 'blocked_id']);
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('blocker_id')->references('id')->on('chat_users')->onDelete('cascade');
            $table->foreign('blocked_id')->references('id')->on('chat_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
```

**Step 16: Run migrations**

```bash
php artisan migrate
```

**Step 17: Commit**

```bash
git add api/database/migrations/
git commit -m "feat: add database migrations for all entities"
```

---

### Task 2.2: Create Eloquent Models

**Files:**
- Create: `api/app/Models/` (multiple model files)

**Step 1: Create Workspace model**

```bash
php artisan make:model Workspace
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Workspace extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'plan',
        'owner_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'owner_id');
    }

    public function settings(): HasOne
    {
        return $this->hasOne(WorkspaceSettings::class);
    }

    public function theme(): HasOne
    {
        return $this->hasOne(WorkspaceTheme::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function chatUsers(): HasMany
    {
        return $this->hasMany(ChatUser::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
```

**Step 2: Create WorkspaceSettings model**

```bash
php artisan make:model WorkspaceSettings
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceSettings extends Model
{
    protected $table = 'workspace_settings';
    protected $primaryKey = 'workspace_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'workspace_id',
        'read_receipts_enabled',
        'online_status_enabled',
        'typing_indicators_enabled',
        'file_size_limit_mb',
        'rate_limit_per_minute',
        'webhook_url',
        'webhook_secret',
    ];

    protected $casts = [
        'read_receipts_enabled' => 'boolean',
        'online_status_enabled' => 'boolean',
        'typing_indicators_enabled' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
```

**Step 3: Create WorkspaceTheme model**

```bash
php artisan make:model WorkspaceTheme
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceTheme extends Model
{
    protected $table = 'workspace_themes';
    protected $primaryKey = 'workspace_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'workspace_id',
        'preset',
        'primary_color',
        'background_color',
        'font_family',
        'logo_url',
        'position',
        'custom_css',
        'dark_mode_enabled',
    ];

    protected $casts = [
        'dark_mode_enabled' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
```

**Step 4: Create Admin model**

```bash
php artisan make:model Admin
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasUuids, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'name',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_super_admin' => 'boolean',
    ];

    public function ownedWorkspaces(): HasMany
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    public function workspaceMemberships(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }
}
```

**Step 5: Create remaining models**

Create the following models with similar patterns:
- `WorkspaceMember`
- `ApiKey`
- `ChatUser`
- `ChatSession`
- `Conversation`
- `Participant`
- `Message`
- `Attachment`
- `Reaction`
- `Ban`
- `Block`

(Each model follows the same pattern with appropriate fillable fields, relationships, and casts)

**Step 6: Commit**

```bash
git add api/app/Models/
git commit -m "feat: add Eloquent models for all entities"
```

---

## Phase 3: API Development

### Task 3.1: Set Up API Authentication

**Files:**
- Create: `api/app/Http/Middleware/ValidateApiKey.php`
- Create: `api/app/Http/Middleware/ValidateSessionToken.php`
- Modify: `api/bootstrap/app.php`

**Step 1: Create API Key middleware**

```php
<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json(['error' => 'API key required'], 401);
        }

        $keyHash = hash('sha256', $apiKey);
        $key = ApiKey::where('key_hash', $keyHash)
            ->whereNull('revoked_at')
            ->first();

        if (!$key) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $key->update(['last_used_at' => now()]);
        $request->merge(['workspace' => $key->workspace]);

        return $next($request);
    }
}
```

**Step 2: Create Session Token middleware**

```php
<?php

namespace App\Http\Middleware;

use App\Models\ChatSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSessionToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Session token required'], 401);
        }

        $session = ChatSession::where('token', $token)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with(['chatUser', 'workspace'])
            ->first();

        if (!$session) {
            return response()->json(['error' => 'Invalid or expired session'], 401);
        }

        $request->merge([
            'chatSession' => $session,
            'chatUser' => $session->chatUser,
            'workspace' => $session->workspace,
        ]);

        return $next($request);
    }
}
```

**Step 3: Register middleware in bootstrap/app.php**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'api.key' => \App\Http\Middleware\ValidateApiKey::class,
        'session.token' => \App\Http\Middleware\ValidateSessionToken::class,
    ]);
})
```

**Step 4: Commit**

```bash
git add api/app/Http/Middleware/ api/bootstrap/app.php
git commit -m "feat: add API key and session token middleware"
```

---

### Task 3.2: Create Consumer API Controllers

**Files:**
- Create: `api/app/Http/Controllers/Api/V1/SessionController.php`
- Create: `api/app/Http/Controllers/Api/V1/UserController.php`
- Create: `api/app/Http/Controllers/Api/V1/ConversationController.php`
- Create: `api/app/Http/Controllers/Api/V1/ModerationController.php`
- Modify: `api/routes/api.php`

(Detailed controller implementations for each endpoint as specified in the design document)

**Step 1-N: Create each controller with proper validation, logic, and responses**

**Final Step: Commit**

```bash
git add api/app/Http/Controllers/Api/V1/ api/routes/api.php
git commit -m "feat: add consumer API endpoints (v1)"
```

---

### Task 3.3: Create Widget API Controllers

**Files:**
- Create: `api/app/Http/Controllers/Widget/` (multiple controllers)

(Controllers for widget endpoints: messages, conversations, reactions, typing, etc.)

---

### Task 3.4: Create Dashboard API Controllers

**Files:**
- Create: `api/app/Http/Controllers/Dashboard/` (multiple controllers)

(Controllers for dashboard: auth, workspaces, settings, themes, analytics, etc.)

---

## Phase 4: Real-time Events

### Task 4.1: Create Reverb Events

**Files:**
- Create: `api/app/Events/MessageCreated.php`
- Create: `api/app/Events/MessageDeleted.php`
- Create: `api/app/Events/ReactionAdded.php`
- Create: `api/app/Events/UserTyping.php`
- Create: `api/app/Events/MessagesRead.php`

(Event classes that broadcast to appropriate channels)

---

### Task 4.2: Configure Reverb Channels

**Files:**
- Modify: `api/routes/channels.php`

(Channel authorization for private and presence channels)

---

## Phase 5: Nuxt Dashboard

### Task 5.1: Create Dashboard Layout

**Files:**
- Create: `dashboard/layouts/dashboard.vue`
- Create: `dashboard/components/dashboard/Sidebar.vue`

---

### Task 5.2: Create Auth Pages

**Files:**
- Create: `dashboard/pages/login.vue`
- Create: `dashboard/pages/register.vue`
- Create: `dashboard/composables/useAuth.ts`

---

### Task 5.3: Create Chat Components

**Files:**
- Create: `dashboard/components/chat/ChatWindow.vue`
- Create: `dashboard/components/chat/MessageList.vue`
- Create: `dashboard/components/chat/MessageBubble.vue`
- Create: `dashboard/components/chat/MessageInput.vue`
- Create: `dashboard/components/chat/EmojiPicker.vue`
- Create: `dashboard/components/chat/TypingIndicator.vue`

---

### Task 5.4: Create Widget Page

**Files:**
- Create: `dashboard/pages/widget/index.vue`
- Create: `dashboard/layouts/widget.vue`

---

### Task 5.5: Create Dashboard Pages

**Files:**
- Create: `dashboard/pages/dashboard/index.vue`
- Create: `dashboard/pages/dashboard/conversations/index.vue`
- Create: `dashboard/pages/dashboard/conversations/[id].vue`
- Create: `dashboard/pages/dashboard/users/index.vue`
- Create: `dashboard/pages/dashboard/analytics.vue`
- Create: `dashboard/pages/dashboard/settings.vue`
- Create: `dashboard/pages/dashboard/theme.vue`

---

## Phase 6: Integration & Testing

### Task 6.1: Create Feature Tests

**Files:**
- Create: `api/tests/Feature/Api/V1/SessionTest.php`
- Create: `api/tests/Feature/Api/V1/ConversationTest.php`
- Create: `api/tests/Feature/Api/V1/MessageTest.php`

---

### Task 6.2: End-to-End Testing

**Files:**
- Create: `dashboard/tests/e2e/` (Playwright tests)

---

### Task 6.3: Documentation

**Files:**
- Create: `docs/api/` (API documentation)
- Update: `README.md`

---

## Execution Checklist

- [ ] Phase 1: Project Scaffolding (Tasks 1.1-1.4)
- [ ] Phase 2: Database & Models (Tasks 2.1-2.2)
- [ ] Phase 3: API Development (Tasks 3.1-3.4)
- [ ] Phase 4: Real-time Events (Tasks 4.1-4.2)
- [ ] Phase 5: Nuxt Dashboard (Tasks 5.1-5.5)
- [ ] Phase 6: Integration & Testing (Tasks 6.1-6.3)

---

## Notes

- Run `docker-compose up -d` to start all services
- Run migrations with `docker-compose exec api php artisan migrate`
- Seed test data with `docker-compose exec api php artisan db:seed`
- Access dashboard at `http://localhost:3000`
- API available at `http://localhost:8000`
- WebSocket at `ws://localhost:8080`
