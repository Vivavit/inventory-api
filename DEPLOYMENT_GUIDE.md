# Deployment Guide - Inventory API

## Quick Deployment Checklist

### Before Deploying
- [ ] Test locally: `npm install && npm build && php artisan serve`
- [ ] Commit all changes including `package-lock.json`
- [ ] Push to GitHub

### On Server (or Render.com)

#### 1. Build Frontend Assets (CRITICAL)
```bash
npm install
npm build
```
This creates `public/build/` with compiled CSS & JS

#### 2. Install PHP Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

#### 3. Setup Environment
- Ensure `.env` file is created with:
  - `APP_KEY` (generate with `php artisan key:generate`)
  - Database credentials
  - `APP_ENV=production`
  - `APP_DEBUG=false`

#### 4. Run Database Migrations
```bash
php artisan migrate --force
```

#### 5. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

#### 6. Fix Permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### For Render.com Deployment

**Add to Render Dashboard Build Command:**
```bash
npm install && npm build && composer install --no-dev --optimize-autoloader && php artisan migrate --force
```

### Testing Deployment

1. Check frontend assets load:
   - Open browser DevTools
   - Go to Sources tab
   - Verify `.js` and `.css` files load from `public/build/`

2. Check Laravel logs for errors:
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. Test API endpoints:
   ```bash
   curl https://your-domain/api/me
   ```

### Common 500 Errors & Solutions

| Issue | Solution |
|-------|----------|
| Missing Vite assets | Run `npm build` |
| Database not migrated | Run `php artisan migrate --force` |
| Missing APP_KEY | Run `php artisan key:generate` |
| Permission errors | Fix storage/cache permissions |
| Outdated composer | Run `composer install --no-dev` |

### Never Forget

- ✅ Always commit `package-lock.json` and `composer.lock`
- ✅ Test build locally before pushing
- ✅ Check error logs first when debugging
- ✅ Use `--force` flag for migrations in production
