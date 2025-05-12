<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'kayasmu3_wp820');

/** MySQL database username */
define('DB_USER', 'kayasmu3_wp820');

/** MySQL database password */
define('DB_PASSWORD', ']H9S7X38[p');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '3a7t3hf6yi1h5fv1ugvqtpafokgrhfxoxkwh6xxqcaktcb5kryrcgxdxjyjwz95s');
define('SECURE_AUTH_KEY',  'ra84xjiomj90hkt43nfwbppmb9dkeywhdejd1tbeo4y1juchhbysqeulow7rdplm');
define('LOGGED_IN_KEY',    'c7d37zxpx8ffkxsqf0qtcmcwvbmrcarszfnrssfzasrh2lrwd9dlc8uwhofxbfcx');
define('NONCE_KEY',        'wourmv9rihzrcfiv3yugwvtq9h87h27jglrm6sy0bzu6l6rdb3xcb8uhmmoazsim');
define('AUTH_SALT',        'nhxqcqwpmufjomvucj9id3xjrzjduylqlhayj0kfvn31yo2miqqy8nz1gih1e5im');
define('SECURE_AUTH_SALT', 'exdkv9p0yxfeytb57eqtaxsde5oinnaakqreq7cegzmmyshjnaeoawhpxqewcz5k');
define('LOGGED_IN_SALT',   'ptfgqlzkbgfgbywh0pqd1tr8zpq2g5z5dgybjeoygijg0unwkvgvcy30yuyqshm9');
define('NONCE_SALT',       'j07t8vp3bp2q4e5xvtody5phnhbfhx4rcgnghncn64pv8ufzzklhocrggget6lko');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wpfj_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
