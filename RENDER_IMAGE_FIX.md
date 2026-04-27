# Render Deployment - Image Loading Fix

## Problem
Images don't display on Render after deployment even though they upload successfully locally. This happens because:
1. The `public/storage` symlink is NOT created during deployment
2. Images are stored in `storage/app/public/`
3. Without the symlink, `asset('storage/...')` URLs break

## Solution

### Option 1: Update Render Build Command (RECOMMENDED if using Dashboard)

If you're using **Render Dashboard** to configure build steps (not render.yaml):

**Go to Render Dashboard → Your Service → Settings → Build Command**

Replace with:
```bash
npm install && npm build && composer install --no-dev --optimize-autoloader && php artisan migrate --force && php artisan storage:link --force && php artisan config:clear && php artisan cache:clear
```

### Option 2: Use render.yaml (Recommended for Version Control)

A `render.yaml` file has been created at the project root. This ensures build configuration is tracked in GitHub and applied automatically on every deploy.

**To activate:**
1. Push `render.yaml` to GitHub
2. In Render Dashboard: 
   - Go to Service Settings
   - Find "Infrastructure" or "Deployment" section
   - Look for "Infrastructure Configuration File" or similar option
   - Enable render.yaml if available

If your Render service doesn't support render.yaml yet, use Option 1.

### What the Fix Does

The addition of `php artisan storage:link --force` does:
- Creates the symlink: `public/storage` → `storage/app/public`
- The `--force` flag overwrites any existing symlink without error
- Allows `asset('storage/path')` URLs to work
- Falls back to the custom `/storage/{path}` route for additional support

## Verification Steps

### Step 1: After Deployment
1. Check Render deployment logs for success message:
   ```
   'storage' link created successfully
   ```

2. Test in browser:
   - Upload a new product image
   - Verify the image shows in the product list
   - Check URL in browser DevTools (should be like: `https://your-app.onrender.com/storage/products/...`)

### Step 2: Debug if Still Not Working

**Check image database entries:**
```bash
php artisan tinker
>>> $images = App\Models\ProductImage::all();
>>> $images->first()->toArray();
```
Should show `image_path` like: `products/filename.jpg` (NOT `storage/products/filename.jpg`)

**Check URL generation:**
```bash
php artisan tinker
>>> $image = App\Models\ProductImage::first();
>>> $image->url;  // Should return valid asset URL
```

### Step 3: Verify Storage Directory Exists

In Render logs or SSH:
```bash
ls -la public/storage
# Should show symlink → ../../storage/app/public
```

## Environment Variables to Check

Ensure `.env` on Render has:
```env
FILESYSTEM_DISK=public
APP_URL=https://your-app-name.onrender.com
APP_ENV=production
APP_DEBUG=false
```

Verify in Render Dashboard:
- Go to Environment
- Confirm `APP_URL` matches your exact domain (including https://)

## File Upload Permissions

If images upload but symlink fails, you may also need to ensure storage directory is writable:

Add to Render build command if still having issues:
```bash
chmod -R 775 storage bootstrap/cache
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "storage link already exists" error | The `--force` flag in the build command handles this |
| Images show locally but not on Render | Confirm `APP_URL` in .env matches Render domain |
| 404 errors on images | Check image_path in database doesn't have "storage/" prefix |
| Permission denied on storage | Check that Render service has write permission to storage/ |

## Next Steps

1. **Option A (Dashboard)**: Update build command manually in Render Settings
2. **Option B (render.yaml)**: Push changes, confirm render.yaml is enabled
3. Test deployment by uploading a new product image
4. Monitor Render logs during deployment

## Related Files

- [DEPLOYMENT_IMAGE_FIX.md](DEPLOYMENT_IMAGE_FIX.md) - Original image debugging guide
- [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) - General deployment checklist
- [render.yaml](render.yaml) - Automated build configuration
