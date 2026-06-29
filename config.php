<?php
// config.php
session_start();

// KeyAuth API Configuration (based on your index.php)
define('KEYAUTH_API_URL', 'https://your-domain.com/path/to/index.php'); // Your KeyAuth API endpoint
define('OWNER_ID', 'your-10-digit-ownerid'); // Your 10-character OwnerID from KeyAuth
define('APP_NAME', 'your-app-name'); // Your application name

// Session expiry (matches your API's sessionexpiry)
define('SESSION_EXPIRY', 3600); // 1 hour default

// Database (for storing user sessions locally — optional)
define('DB_HOST', 'localhost');
define('DB_NAME', 'keyauth_db');
define('DB_USER', 'root');
define('DB_PASS', '');

/define('KEYAUTH_API_URL', 'https://primeauth.com/index.php');
define('SITE_URL', 'https://primeauth.com');