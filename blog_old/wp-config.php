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
define('DB_NAME', 'kayasmu3_wp955');

/** MySQL database username */
define('DB_USER', 'kayasmu3_wp955');

/** MySQL database password */
define('DB_PASSWORD', 'pS5jn7!-4T');

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
define('AUTH_KEY',         'xtiihcpujakyecfuqwrbq3pfjdsukosfec4b4vdo05qvzzmnz4eyifiylcanwqa7');
define('SECURE_AUTH_KEY',  'hrmx4gvbx2d6wsozissglzykmrm3gosesdusnykwqq2o5uxhpcmagrm5bjof8834');
define('LOGGED_IN_KEY',    'ofpjdxnf0pwvhib3ahtjjwjcms1tcmygxag4hdznpi4g79gokby8ifzhnryzngks');
define('NONCE_KEY',        'iwepav3eshj1ywuzcmmuizq62chp5ltqtk6cgj1oqkwbvaqhqhwxldovfwnqyvzc');
define('AUTH_SALT',        'pyu7boa3d3ms3xb3h4ggooxhdt5uau3cg1mbdbvii9b03pwjqy2xhznqkb6tjbg7');
define('SECURE_AUTH_SALT', 'accixqlgdxqqlhuz88fwvq5vvr1rc9lqr1yc1c4bap9bcwxc2omilkaygerj37r4');
define('LOGGED_IN_SALT',   'dmzntbq6le74d8zquqsadjorp9ig9nkwbgheygfqjhjn9fx2owwvpztssjr5zn30');
define('NONCE_SALT',       'yseg204xihg7uzov6pkoql75k1fbmw0hqnszv9feznzyt26iokju4c9thrm52gfp');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp41_';

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
