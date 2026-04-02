# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **Laravel 12 school inventory management system** with multi-warehouse support, built as a full-stack web application with RESTful API endpoints.

**Key Features:**
- Multi-warehouse inventory management
- Product management (with variants, categories, brands)
- Order management and checkout
- Stock transfers and adjustments
- User management with role-based permissions
- Filament admin panel
- RESTful API for mobile/web clients

---

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Admin Panel**: Filament 4.0
- **Authentication**: Laravel Fortify + Sanctum (API tokens)
- **Authorization**: Spatie Laravel Permission
- **Frontend**: Tailwind CSS 4, Vite, Blade templates
- **Database**: MySQL (production), SQLite (testing)
- **Queue**: Sync driver (default), Redis optional
- **Testing**: PHPUnit 11.5

---

## Key Architecture Patterns

### 1. Multi-Warehouse Architecture
- Users assigned to warehouses via `warehouse_users` pivot
- Product stock tracked per warehouse via `warehouse_products` pivot
- Admin users see all warehouses; staff users only see assigned warehouse
- All inventory queries filter by user's accessible warehouses
- See `app/Http/Controllers/Api/ProductApiController.php` for the pattern

### 2. Database Structure
```
Core models:
- User (with user_type: admin|staff|customer|seller)
- Product (with variants, images, categories, brands)
- Warehouse
- WarehouseProduct (pivot: quantity per product per warehouse)
- Order/OrderItem
- InventoryTransaction (audit trail)
- StockTransfer/StockAdjustment

Pivot tables:
- warehouse_users (user-warehouse assignments)
- model_has_roles, model_has_permissions (Spatie)
- role_has_permissions (Spatie)
```

### 3. API Design
- Base URL: `/api/v1`
- JSON responses with `{status, message, data}` format
- Authentication: Bearer token (Sanctum)
- Role/permission middleware on endpoints
- See `API_DOCUMENTATION.md` for complete API reference

### 4. Filament Admin Panel
- Located at `/admin` route
- Requires `admin` role
- Panel provider: `app/Providers/Filament/AdminPanelProvider.php`
- Resources auto-discovered from `app/Filament/Resources/`

### 5. Frontend Patterns
- Web routes use Blade templates (resources/views/)
- API routes return JSON
- Tailwind CSS 4 with Vite for asset compilation
- Alpine.js for interactivity (lightweight, no React/Vue)

---

## Common Development Commands

### Setup (First Time)
```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan migrate --force
```

### Development Server
```bash
# Start everything (Laravel server + queue + Vite dev server)
composer dev

# Or individually:
php artisan serve          # Laravel server (http://localhost:8000)
npm run dev                # Vite dev server with HMR
php artisan queue:listen   # Queue worker
```

### Database
```bash
php artisan migrate                # Run migrations
php artisan migrate --seed         # Run migrations + seeders
php artisan db:seed --class=ProductSeeder  # Specific seeder
php artisan migrate:fresh --seed   # Reset DB and reseed
php artisan tinker                 # Interactive shell
```

### Testing
```bash
composer test                      # Run all tests (clears config first)
php artisan test                   # Run all tests
php artisan test --filter ApiAuthTest  # Single test class
vendor/bin/phpunit tests/Feature/ApiAuthTest.php  # Direct PHPUnit
./vendor/bin/pest                  # If using Pest (not currently)
```
**Note**: Testing uses SQLite in-memory database. Tests use `DatabaseMigrations` trait.

### Code Quality
```bash
vendor/bin/pint                    # Laravel Pint (PHP code style)
vendor/bin/phpstan analyse app     # PHPStan (if installed)
```

### Build Frontend Assets
```bash
npm run build                      # Production build (minified)
npm run dev                        # Development with HMR
```

### Cache & Optimization
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear         # Clear all caches
php artisan optimize              # Optimize for production
```

### Model & Factory
```bash
php artisan make:model Product -mfsr    # Model + migration + factory + seeder + resource
php artisan make:controller Api/ProductApiController --api
php artisan make:request StoreProductRequest
php artisan make:middleware CheckPermission
```

### Filament Admin
```bash
php artisan filament:install          # Install if not already
php artisan make:filament-resource Product  # Create admin resource
php artisan make:filament-page ProductStats  # Dashboard page
php artisan filament:upgrade          # Upgrade Filament
```

### Debugging
```bash
php artisan route:list                # List all routes
php artisan config:show               # Show config (if using spatie/config)
php artisan queue:failed             # List failed jobs
php artisan queue:retry {id}         # Retry failed job
tail -f storage/logs/laravel.log     # View logs
```

### API Routes
- Web routes: `routes/web.php` (Blade views + some JSON responses)
- API routes: Add to `routes/api.php` or extend `app/Http/Controllers/Api/`

---

## Important Code Patterns

### 1. Permission Checks
```php
// Route middleware
Route::middleware('permission:manage-inventory')->group(function () {
    // Routes...
});

// Controller
$this->authorize('edit', $product);

// Blade
@can('delete_products')
    <button>Delete</button>
@endcan
```

### 2. Warehouse-Scoped Queries
```php
// Get user's accessible warehouses
$warehouses = auth()->user()->warehouses;

// Filter products by warehouse
Product::whereHas('warehouseProducts', function ($query) use ($warehouseIds) {
    $query->whereIn('warehouse_id', $warehouseIds);
})->get();
```

### 3. Stock Management
- Stock per warehouse: `WarehouseProduct` model (`quantity` field)
- Total stock: `$product->total_stock` (accessor sums across warehouses)
- Adjustments: `StockAdjustment` + `StockAdjustmentItem`
- Transfers: `StockTransfer` + `StockTransferItem`
- Transactions: `InventoryTransaction` (audit log)

### 4. API Response Format
```php
return response()->json([
    'success' => true,
    'message' => 'Product created successfully',
    'data' => $product->load('images', 'category')
]);
```

### 5. Model Boot Methods (Auto-Actions)
- `Product` model automatically:
  - Generates slug from name
  - Generates SKU if not provided
  - Creates `WarehouseProduct` rows for all active warehouses on creation

---

## Testing Guidelines

### Test Structure
- `tests/Unit/` - Unit tests (single classes)
- `tests/Feature/` - Feature/integration tests (HTTP, database)
- Use `DatabaseMigrations` trait to reset DB between tests
- Use `Sanctum::actingAs($user)` for API auth

### Example Test Pattern (from `tests/Feature/ApiWarehouseProductTest.php`)
```php
public function test_staff_sees_only_assigned_warehouse()
{
    $w1 = Warehouse::create(['name' => 'W1', 'code' => 'W1', 'is_active' => true]);
    $product = Product::factory()->create(['is_active' => true]);
    WarehouseProduct::create(['warehouse_id' => $w1->id, 'product_id' => $product->id, 'quantity' => 7]);

    $staff = User::factory()->create(['user_type' => 'staff']);
    $staff->warehouses()->attach([$w1->id]);

    Sanctum::actingAs($staff, [], 'web');
    $response = $this->getJson('/api/products');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.stock', 7);
}
```

---

## Code Style & Conventions

- **PHP**: PSR-12, enforced by Laravel Pint (`vendor/bin/pint`)
- **Models**: Extend `Illuminate\Database\Eloquent\Model`
- **Controllers**: API controllers in `app/Http/Controllers/Api/`, web controllers in `app/Http/Controllers/`
- **Requests**: Form requests in `app/Http/Requests/` for validation
- **Middleware**: `app/Http/Middleware/` (e.g., `CheckPermission.php`)
- **Routes**: Web routes in `routes/web.php`, API routes typically added to same file or `routes/api.php`
- **Model relationships**: snake_case plural for hasMany, singular for belongsTo

---

## Environment & Configuration

- **Local**: `.env` file (DO NOT commit)
- **Production**: Set environment variables directly
- Important `.env` keys:
  - `APP_KEY` (run `php artisan key:generate`)
  - `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
  - `SANCTUM_STATEFUL_DOMAINS` for SPA domains

---

## Existing Commands & Important Files

### Custom Artisan Commands
- `app/Console/Commands/PopulateOrderItems.php` - Auto-creates order items from order details

### Seeders
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/ProductSeeder.php`

### Important Configuration
- `config/permission.php` - Spatie permission settings
- `config/scramble.php` - API documentation (OpenAPI/Swagger)
- `config/fortify.php` - Authentication features
- `.env.example` - Template environment variables

---

## Known Issues & Gotchas

1. **WarehouseProduct Creation**: Creating a product automatically creates `WarehouseProduct` rows for all active warehouses via model event (see `app/Models/Product.php` line 124-138). This cannot be bulk-created via factory without handling the observer.

2. **API vs Web Routes**: Web routes (`routes/web.php`) serve both Blade views and some JSON endpoints (like order creation). API routes are prefixed with `/api/v1` (see `API_DOCUMENTATION.md`). Keep web and API controllers separate (Api controllers in `app/Http/Controllers/Api/`).

3. **Filament Access**: Only users with `admin` role can access `/admin`. Middleware: `\Spatie\Permission\Middleware\RoleMiddleware::class.':admin'`.

4. **Stock Calculation**: Staff users see stock for their single warehouse; admins see aggregated stock across all assigned warehouses. The `ProductApiController@index` handles this distinction.

5. **Storage Files with CORS**: Product images served via route `/images/{path}` with CORS headers. Storage is linked: `public/build` for Vite assets, `public/storage` for uploaded files (run `php artisan storage:link`).

---

## Future Claude Code: How to Be Most Helpful

When working on this codebase:

1. **Preserve warehouse scoping**: Any query involving products, stock, or inventory must respect user's warehouse access unless explicitly for admin reports.
2. **Maintain API response format**: Follow `{status, message, data}` structure.
3. **Use existing patterns**: Follow controller methods in `app/Http/Controllers/Api/` for consistency.
4. **Check permissions**: New endpoints should use Spatie permission middleware.
5. **Test warehouse isolation**: Staff should never see other warehouses' data.
6. **Consider Filament**: For admin UI features, add resources in `app/Filament/Resources/`.
7. **Use factories for testing**: Factories exist for `User`, `Product`, `Warehouse`, etc.
8. **Run Pint before committing**: Style changes should follow PSR-12.

---

## References

- **API Documentation**: `API_DOCUMENTATION.md`
- **Deployment Guide**: `DEPLOYMENT_GUIDE.md`
- **Laravel Docs**: https://laravel.com/docs/12.x
- **Filament Docs**: https://filamentphp.com/docs
- **Spatie Permission**: https://spatie.be/docs/laravel-permission
- **Laravel Sanctum**: https://laravel.com/docs/sanctum
