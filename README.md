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
