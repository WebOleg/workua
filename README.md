# URL Shortener - Enterprise Grade

Modern URL shortening service with advanced analytics and Domain-Driven Design architecture.

## Features

-   URL shortening with custom codes and TTL
-   Real-time analytics (visits, unique visitors, devices, browsers)
-   Redis caching for high performance
-   Event-driven architecture for scalability
-   Health check endpoint for monitoring
-   Structured JSON logging

## Tech Stack

-   Laravel 11 (PHP 8.2)
-   MySQL 8.0
-   Redis
-   Docker Compose

## Quick Start

git clone <repo-url>
cd url-shortener
docker-compose up -d
docker-compose exec app composer install
cp .env.example .env
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
Access: http://localhost:8080
API Endpoints
Create Link:
bashPOST /api/links
{"url": "https://example.com", "custom_code": "mycode", "ttl_minutes": 60}
Get Link:
bashGET /api/links/{shortCode}
Health Check:
bashGET /api/health
Architecture
Domain-Driven Design with Repository Pattern, Service Layer, Events, Value Objects, and SOLID principles.
Tests
bashdocker-compose exec app php artisan test
10/10 tests passing
