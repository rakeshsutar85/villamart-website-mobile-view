<?php
define( 'WP_CACHE', true );

// Begin AIOWPSEC Firewall
if (file_exists('/home/villamart.in/public_html/online/aios-bootstrap.php')) {
	include_once('/home/villamart.in/public_html/online/aios-bootstrap.php');
}
// End AIOWPSEC Firewall

define( 'DB_NAME', 'vill_mrt' );
define( 'DB_USER', 'vill_mrt' );
define( 'DB_PASSWORD', '^69HqFowkM*AbGIt' );
define( 'DB_HOST', '127.0.0.1' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
#######-------------CUSTOM WP-CONFIG CODE STARTS----------------------------
/* Plugin Meta */
define('DISABLE_NAG_NOTICES', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);
define('WP_MEMORY_LIMIT', '256M');
define('AUTOSAVE_INTERVAL', 3000);
define('EMPTY_TRASH_DAYS',  120);
define('WP_POST_REVISIONS', false);
define('ENABLE_CACHE', TRUE);
define('CACHE_EXPIRATION_TIME', 3600);
define('DISABLE_WP_CRON', true);
//define('COOKIE_DOMAIN', 'www.villamart.in');
//define('COOKIE_DOMAIN', false);
define( 'FS_METHOD', 'direct' );
define('UPLOADS', ''.'files'); 
define('WP_CONTENT_DIR', $_SERVER['DOCUMENT_ROOT'] . '/online/lgtapps/');
define('WP_CONTENT_URL', 'https://www.villamart.in/online/lgtapps/');
define('WP_PLUGIN_DIR', $_SERVER['DOCUMENT_ROOT'] . '/online/lgtapps/lgtapp');
define('WP_PLUGIN_URL', 'https://www.villamart.in/online/lgtapps/lgtapp');
define('APP_DIR', $_SERVER['DOCUMENT_ROOT'] . '/online/lgtapps/lgtapp');
define('VISUAL_TEXT_WIDGET',false);
#######-------------CUSTOM WP-CONFIG CODE ENDS----------------------------
define( 'AUTH_KEY',         'e.WieTs00b_st.OA,#R:P)f0h~EiKF<LaruU/lO^O^uJXPY/G_HCe`8p.uC5Y(o&' );
define( 'SECURE_AUTH_KEY',  '`wo8ZiI_;!;$4,@=V8#?.y2:GeaNbxs,ez7zpLU!36z!:MZ|#=8^]%CC[j}A2[ i' );
define( 'LOGGED_IN_KEY',    'h>-s}a9rle1^G:JBpkky}(a:~mI9IJ4t?JrTUlBU$Mk*TXW_]tc,virD$?v:JV#t' );
define( 'NONCE_KEY',        'yCN`r}a%hD-G- 5as$DiB=2QHX*i?Xe;e68uih+R6m[VHgs9(`6q!J[g0}4HbrPW' );
define( 'AUTH_SALT',        'H~%RV=nT7]Cv.q*78dnZ5VLf@o2RuUK^Yy+u|%-;o_%4n*u0}@cY}:7|sQ/z(!x/' );
define( 'SECURE_AUTH_SALT', '-T5`MjZtr``B5~3w_K,r%ys>f[]jF2gEs.[~hS^6pett/V7EkHB^XWy<#=(2bMK7' );
define( 'LOGGED_IN_SALT',   '+q+DPI oIx1mKtH4*zbx W>`}4YPhMfvPx?SwVR}j9@{5]tR9a]&suJ-p~6TnMAg' );
define( 'NONCE_SALT',       'Xgxo*.:>E uuyA/N/d4Hr9Qf+c>jJY`W:3Lz-~Kh5j%?Yz?JG~uz%:}rh=_(P{z^' );
$table_prefix = 'lgt_';
define( 'WP_DEBUG', false );
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'wp-settings.php';//Disable File Edits
define('DISALLOW_FILE_EDIT', true);