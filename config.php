<?php
// config.php
require __DIR__ . '/vendor/autoload.php';

// App Constants
define('APP_TITLE', 'Document Tracker');

// Google Config
define('GOOGLE_CREDS_PATH', __DIR__ . './auth/google/credentials.json');
define('SPREADSHEET_ID', 'spreadsheet-id');

// S3 Config
define('S3_BUCKET', 'bucket-name');
define('AWS_REGION', 'region');
define('AWS_VERSION', 'version');
define('AWS_KEY', 'key'); 
define('AWS_SECRET', 'secret');

// OwnCloud/WebDAV Config
define('WEBDAV_BASE_URI', 'https://your.owncloud.domain/remote.php/webdav/Documents/'); 
define('WEBDAV_USER', 'your_username'); 
define('WEBDAV_PASSWORD', 'your_password_or_app_token');