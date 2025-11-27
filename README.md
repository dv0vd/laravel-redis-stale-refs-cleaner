# Laravel Redis Stale References Cleaner

A lightweight Laravel console command that helps you keep your Redis cache clean and efficient by purging stale (broken) cache references.

In Laravel 6–8, there was no built-in way to safely clean up Redis sets containing references to keys that no longer exist. Laravel 9 introduced a helper command (`php artisan cache:prune-stale-tags`), but older versions require a custom solution — this package fills that gap.

## Why This Package Exists

When using Redis to store reference sets (e.g., `standard_ref` for temporary caches or `forever_ref` for permanent caches), over time some referenced keys may expire or be deleted.
This can lead to stale references, which in turn can cause:
- Wasted memory in Redis
- Inaccurate cache statistics
- Unexpected behavior when iterating over cache sets

Without a safe cleanup process, these stale references accumulate, slowly “clogging” your cache and potentially affecting application performance.

This command automates the cleanup, safely removing references to keys that no longer exist, keeping your Redis cache tidy and efficient.

## Features
- Safe purging of stale cache references from Redis sets
- Works with both phpredis (`ext-redis`) and `predis/predis` clients
- Fully compatible with **Laravel 6+**
- Chunked processing for large sets to avoid memory issues
- Logs progress and provides a summary of purged keys
- Easy to run via Artisan console

## Installation

```bash
composer require dv0vd/laravel-redis-stale-refs-cleaner
```

## Usage
1. Run the command:
```bash
php artisan redis:purge-stale-refs
```
2. Sit back, relax, and enjoy your clean Redis!