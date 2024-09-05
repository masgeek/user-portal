<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'akilimo_portal' );

/** Database username */
define( 'DB_USER', 'fuelrod' );

/** Database password */
define( 'DB_PASSWORD', 'fuelrod' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/** force direct method instead of FTP **/
define('FS_METHOD','direct');

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'k$%jc^F]bqm(CD~v(Q%xODn#>28_viDhQ]uX1 KmzOl}Q<ML(PUGx:fws*Uugg>K' );
define( 'SECURE_AUTH_KEY',  'hj&R9k&$DDyqnr6(HY%`mw(# q}HB{wu2>98Z:!R:3or&;v.f{M1vwb%T5=$H.52' );
define( 'LOGGED_IN_KEY',    'm4rUSJ2-]MQ!o%hfd vt .L<SR|PRiIc?R_AH7c14Ij<r6DZeg4V6nM7&z&$xeCk' );
define( 'NONCE_KEY',        ']kaLU.aiCsF![.}xqR-F+-O&l]K6qF.D8)agS(mI_E/x`tagH72B9L):h+UN+2 X' );
define( 'AUTH_SALT',        'qp+CL~&U/14fRe7ShATl*)9J]I!<ilj)ZyDh1m]oz,V7cAZN,`I*Z[4fQZyoK?#g' );
define( 'SECURE_AUTH_SALT', '}Be:(6 }&)NjMz~6iouCt>)2LnJ/RTtRy`gaBiZoP2QrDh_T[{]?UCA^;=7IX8qM' );
define( 'LOGGED_IN_SALT',   '0:>d<Q0/2Hn$*xP0e[Koo4!/NDo0Jmlnlo9 >J*m#qeI?dOJL,o-vSlCw0?wH9@f' );
define( 'NONCE_SALT',       'oqk_XE92`QwC$ujP0]bd>9Di#g3.Oo8t!J>TmJX<X$vJm@P zSX{+J HXxr1/l |' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'up_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
