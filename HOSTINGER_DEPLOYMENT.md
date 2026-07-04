# Hostinger Deployment Guide - Queue & Cron Setup

## ✅ What You Have Setup

- **Queue Driver**: Database (perfect for shared hosting)
- **Queue Table**: `jobs` (auto-created by Laravel)
- **Notifications**: Web Push via minishlink/web-push

---

## 🚀 STEP 1: Database Table for Queue

Your `jobs` table is already created! Verify it exists in your database.

If not, run during deployment:
```bash
php artisan migrate
```

---

## 📋 STEP 2: Hostinger Cron Job Setup

### Login to Hostinger Account

1. Go to **Hostinger Dashboard** → **Hosting** → **Manage**
2. Click **Advanced** → **Cron Jobs**
3. Add a new cron job with these settings:

### **Cron Job 1: Process Queue (Every 5 minutes)**

```bash
0 */1 * * * cd /home/yourusername/public_html && php artisan queue:work database --max-time=59 --max-jobs=100 >> /dev/null 2>&1
```

Or simpler (every minute):
```bash
* * * * * cd /home/yourusername/public_html && php artisan queue:work database --attempts=3 >> /dev/null 2>&1
```

**What this does:**
- Runs every 1 minute automatically
- Processes push notifications from the `jobs` table
- Logs failed jobs (retries 3 times)

### **Cron Job 2: Laravel Scheduler (Every Minute)**

```bash
* * * * * cd /home/yourusername/public_html && php artisan schedule:run >> /dev/null 2>&1
```

This runs any scheduled tasks you set up later.

---

## 🔧 Environment Variables for Production

Update your `.env` on Hostinger:

```env
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=database

# Hostinger typically provides these, update accordingly
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# Web Push VAPID Keys (keep your current ones)
VAPID_PUBLIC_KEY=BKagQUhtrSil_Vcm6K2WcO3vb_1OOY_IYRXCvUD06RjhEOoZscKLYFpn-tmYUL38Yg7dVhVKVHeK9zYugau9va4
VAPID_PRIVATE_KEY=PKUj9RLY8IxYOtsoy64_DWwRe0MaZkZk7IqbTyYhrRk
```

---

## 📁 Hostinger File Structure

When uploading to Hostinger:

```
/home/yourusername/
├── public_html/ (upload Laravel's public/ contents here)
├── app/ (your Laravel app folder)
├── config/
├── routes/
├── storage/ (writable permissions needed)
├── bootstrap/
├── vendor/
├── .env (your production .env file)
├── artisan
└── composer.json
```

**Important:** Your main Laravel files should be outside `public_html` for security. Hostinger typically provides a way to set your root directory.

---

## 🔐 File Permissions

Set these permissions via FTP or Hostinger File Manager:

```bash
# Storage folder needs write permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# For additional security
chmod -R 755 app/ config/ routes/
```

---

## ✅ How Queue Processing Works on Hostinger

**Without dedicated queue server** (like you have), the cron job method is clean:

1. **🕐 Every minute**, cron runs: `php artisan queue:work`
2. **⏭️ It processes** all queued jobs (notifications)
3. **📤 Web push notifications** get sent immediately
4. **✔️ Jobs are marked** as complete and deleted from queue

---

## 🧪 Test Queue on Hostinger

### Step 1: Push a test job
```bash
# Via SSH or Laravel console
php artisan tinker

\App\Jobs\SendPushNotification::dispatch(
    $userId,
    'Test Title',
    'Test Body',
    ['type' => 'test']
);
```

### Step 2: Check jobs in database
```bash
select * from jobs;
```

### Step 3: Manually process (to test)
```bash
php artisan queue:work database --once
```

### Step 4: Verify it's gone
```bash
select * from jobs; # Should be empty if successful
```

---

## 🐛 Debugging Queue Issues

### Check job failures
```bash
# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Flush failed jobs
php artisan queue:flush
```

### Monitor queue status
```bash
php artisan queue:monitor default:50
```

### Logs
Check in Hostinger **File Manager** → `public_html/storage/logs/laravel.log`

```bash
tail -f storage/logs/laravel.log
```

---

## 📊 Monitoring Setup

### Option 1: Simple Check (Free)

Add a route to check queue health:

```php
Route::get('/admin/queue-status', function () {
    $pending = \DB::table('jobs')->count();
    $failed = \DB::table('failed_jobs')->count();
    
    return response()->json([
        'pending_jobs' => $pending,
        'failed_jobs' => $failed,
        'status' => $pending === 0 ? 'healthy' : 'processing',
    ]);
});
```

Visit: `https://yourdomain.com/admin/queue-status`

### Option 2: Email on Failure

Edit `app/Jobs/SendPushNotification.php` to email on failure:

```php
public function failed(\Throwable $exception)
{
    Log::error('Push notification failed', ['error' => $exception->getMessage()]);
    
    // Email admin
    Mail::to('admin@example.com')->send(
        new \Illuminate\Mail\Mailable\ErrorMail($exception)
    );
}
```

---

## 🎯 Complete Deployment Checklist

- [ ] Upload files to Hostinger via FTP/File Manager
- [ ] Set correct file permissions (775 for storage)
- [ ] Create/migrate database (`php artisan migrate`)
- [ ] Update `.env` with production credentials
- [ ] Run `composer install --optimize-autoloader`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Set VAPID keys in `.env`
- [ ] Create `jobs` table migration is in migration files ✓
- [ ] Add 2 cron jobs in Hostinger panel
- [ ] Test queue processing
- [ ] Test push notifications work

---

## 🆘 Hostinger Specific Issues

### Issue: "Permission Denied" for artisan
**Solution**: Use SSH or change to PHP CLI execution
```bash
/usr/bin/php artisan queue:work database
```

### Issue: Cron not running
**Solution**: Check cron logs in Hostinger → Advanced → Cron Logs

### Issue: Queue jobs stuck
**Solution**: Connect via SSH and run:
```bash
php artisan queue:work database --once --tries=1
```

### Issue: Run out of execution time
**Solution**: Modify cron to limit:
```bash
php artisan queue:work database --max-time=59 --max-jobs=50
```

---

## 💡 Pro Tips

1. **Keep backups** of your `.env` before uploading
2. **Test everything locally first** (you're doing this!)
3. **Monitor queue status** weekly to catch issues early
4. **Set up email alerts** if queue gets too large
5. **Use optimization commands:**
   ```bash
   php artisan optimize
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## 📞 Support

If cron jobs aren't working:
1. Contact Hostinger Support
2. Ask them to enable cron job execution
3. Provide them this command format and ask them to test

Good luck! 🚀
