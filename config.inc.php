<?php
/* Configuration for phpMyAdmin */

// Database connection settings
$cfg['Servers'][1]['host'] = '127.0.0.1';
$cfg['Servers'][1]['port'] = '3306';
$cfg['Servers'][1]['user'] = 'psuser';
$cfg['Servers'][1]['password'] = 'admin';

// Language and theme settings
$cfg['DefaultLang'] = 'en';
$cfg['ThemeDefault'] = 'pmahomme';

// Other optional configurations
// $cfg['UploadDir'] = '';
// $cfg['SaveDir'] = '';

// Security settings (optional)
// $cfg['Servers'][1]['auth_type'] = 'cookie';
// $cfg['Servers'][1]['AllowNoPassword'] = true;

// Hide warning messages (remove these in production)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
