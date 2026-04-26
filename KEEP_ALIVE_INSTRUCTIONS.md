# Render Server Keep-Alive Instructions

This solution will keep your Render server active by sending periodic requests to prevent it from shutting down after 15 minutes of inactivity.

## What I've Created

1. **Health Check Endpoint** (`/health`) - A lightweight endpoint in your Laravel app that responds with server status
2. **Keep-Alive Script** (`keep-alive.js`) - Node.js script that pings your server every 5-10 minutes
3. **Package Configuration** (`keep-alive-package.json`) - Minimal package.json for the script

## Setup Instructions

### 1. Update Your Render URL

Edit `keep-alive.js` and change this line:
```javascript
const RENDER_URL = 'https://your-app-name.onrender.com';
```

Replace `your-app-name.onrender.com` with your actual Render app URL.

### 2. Configure Ping Interval (Optional)

By default, the script pings every 5 minutes. To change to 10 minutes, edit:
```javascript
const PING_INTERVAL = 10 * 60 * 1000; // 10 minutes
```

### 3. Run the Keep-Alive Script

**Option A: Run directly with Node.js**
```bash
node keep-alive.js
```

**Option B: Use the package.json script**
```bash
# Copy the package.json first
cp keep-alive-package.json package.json
npm install
npm start
```

### 4. Keep Running Continuously

For continuous operation, run it in the background:

**Windows (PowerShell):**
```powershell
Start-Process -WindowStyle Hidden node keep-alive.js
```

**Windows (Command Prompt):**
```cmd
start /B node keep-alive.js
```

**Linux/Mac (using nohup):**
```bash
nohup node keep-alive.js > keep-alive.log 2>&1 &
```

**Using PM2 (recommended for production):**
```bash
npm install -g pm2
pm2 start keep-alive.js --name "render-keep-alive"
pm2 save
pm2 startup
```

## How It Works

- The script sends a GET request to `https://your-app.onrender.com/health` every 5-10 minutes
- The `/health` endpoint returns a lightweight JSON response with server status
- Each request counts as "activity" and prevents Render from shutting down your server
- The script includes error handling and timeout protection

## Monitoring

The script logs all activity to the console:
- ✅ Successful pings with server response
- ⚠️ HTTP errors or non-200 status codes
- ❌ Connection errors or timeouts
- 📊 Server response data when available

## Alternative Solutions

If you prefer not to run a local script:

1. **Use a cron job service** (like cron-job.org) to ping your `/health` endpoint
2. **Use GitHub Actions** to create a scheduled workflow
3. **Use a free monitoring service** like UptimeRobot to monitor your `/health` endpoint

## Security Note

The `/health` endpoint is public and doesn't require authentication. If you want to secure it, you can:
- Add a simple API key check
- Restrict it to specific IP addresses
- Move it to the authenticated routes section

## Troubleshooting

- **Script stops working**: Check your internet connection and Render URL
- **Server still shuts down**: Ensure the interval is less than 15 minutes
- **Permission errors**: Make sure Node.js is installed and you have execution permissions

Your Render server should now stay active as long as the keep-alive script is running!
