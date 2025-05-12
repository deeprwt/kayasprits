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
define( 'DB_NAME', 'kayasmu3_wp757' );

/** MySQL database username */
define( 'DB_USER', 'kayasmu3_wp757' );

/** MySQL database password */
define( 'DB_PASSWORD', 'L1dpn)S44!' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'b2mgmrg8szv5vcoaoplagglyl1oxkdhrmdev7w6toqsw6u1v0dif8c2lzsnjiwse' );
define( 'SECURE_AUTH_KEY',  'ybmxzoqtkb9ytl2oif2gqhkxyee97pni9rpncvqyehvqgn2t92bmt25wnzpcuucz' );
define( 'LOGGED_IN_KEY',    '1rwt3t2vpw18lfjeziubbt6w3phkx0kg6iwrorhagkqhbdjqyvogdxlqohxfgvzt' );
define( 'NONCE_KEY',        'wmqns7lrkmja4eqs7s9qp0hlsj7c9rvtqefz7dvgjehl8mdsaddypv3lz8asefsl' );
define( 'AUTH_SALT',        'jqpqdmwcu5lvmfmu5fpyulghup7ac0gpucumf9mjy38poedszwyjddkda6rroi6l' );
define( 'SECURE_AUTH_SALT', '4g5zryvos48he4wgxqcx65jt6fouyjujgxzaic1mfwyghswmlmucobienvea2dhe' );
define( 'LOGGED_IN_SALT',   'iqottmqdd6k2c97jjlvcbraenhlekxucrs7vprryxtj6wxrbjg3luolggkjf8hqw' );
define( 'NONCE_SALT',       'ozkkun5cufkfewnxebxdlzemgjdsbbrlkzx7kjyjqq8t19dfb5jwc0hfeceatoni' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpir_';

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
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
