const https = require('https');
const http = require('http');

// Configuration - UPDATE THIS WITH YOUR RENDER URL
const RENDER_URL = 'https://your-app-name.onrender.com';
const HEALTH_ENDPOINT = '/health';
const PING_INTERVAL = 5 * 60 * 1000; // 5 minutes in milliseconds (change to 10 * 60 * 1000 for 10 minutes)

function pingServer() {
    const url = new URL(RENDER_URL + HEALTH_ENDPOINT);
    const isHttps = url.protocol === 'https:';
    const client = isHttps ? https : http;
    
    const options = {
        hostname: url.hostname,
        port: url.port || (isHttps ? 443 : 80),
        path: url.pathname,
        method: 'GET',
        headers: {
            'User-Agent': 'Keep-Alive-Script/1.0'
        }
    };

    const req = client.request(options, (res) => {
        let data = '';
        
        res.on('data', (chunk) => {
            data += chunk;
        });
        
        res.on('end', () => {
            const timestamp = new Date().toISOString();
            if (res.statusCode === 200) {
                console.log(`[${timestamp}] ✅ Server pinged successfully - Status: ${res.statusCode}`);
                try {
                    const response = JSON.parse(data);
                    console.log(`[${timestamp}] 📊 Server response:`, response);
                } catch (e) {
                    console.log(`[${timestamp}] 📄 Raw response: ${data}`);
                }
            } else {
                console.log(`[${timestamp}] ⚠️  Server responded with status: ${res.statusCode}`);
            }
        });
    });

    req.on('error', (err) => {
        const timestamp = new Date().toISOString();
        console.error(`[${timestamp}] ❌ Error pinging server:`, err.message);
    });

    req.setTimeout(10000, () => {
        const timestamp = new Date().toISOString();
        console.error(`[${timestamp}] ⏰ Request timeout after 10 seconds`);
        req.destroy();
    });

    req.end();
}

// Start the ping process
console.log('🚀 Keep-alive script started');
console.log(`📍 Pinging ${RENDER_URL}${HEALTH_ENDPOINT} every ${PING_INTERVAL / 60000} minutes`);
console.log('⏰ First ping starting now...');

// Ping immediately on start
pingServer();

// Then ping at regular intervals
setInterval(pingServer, PING_INTERVAL);

// Handle graceful shutdown
process.on('SIGINT', () => {
    console.log('\n👋 Keep-alive script stopped');
    process.exit(0);
});

process.on('SIGTERM', () => {
    console.log('\n👋 Keep-alive script terminated');
    process.exit(0);
});
