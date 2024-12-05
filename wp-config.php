<?php
require_once('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
#$dotenv = Dotenv::createImmutable(__DIR__ . '/../agwise_config/.env');

$dotenv->load();

$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD']);

$dotenv->ifPresent('DEBUG')->isBoolean();

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
define('DB_NAME', $_ENV['DB_NAME']);

/** Database username */
define('DB_USER', $_ENV['DB_USER']);

/** Database password */
define('DB_PASSWORD', $_ENV['DB_PASSWORD']);

/** Database hostname */
define('DB_HOST', $_ENV['DB_HOST']);

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );
/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define('WP_CACHE', true); // WP-Optimize Cache

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
define( 'AUTH_KEY',         'gi+jD_ZqJKmHcHK.z@wrS9a9bB5Ye9H_Vy)N9/jlaPUe4Lw<uQ  -)E!Ehr,gI`{' );
define( 'SECURE_AUTH_KEY',  'sonep+W0|*|jzQ?/kVka_I-Ks~5N:w4:SqD@~~@6=`a|4#^|oX}^&a3CW=t*4iX9' );
define( 'LOGGED_IN_KEY',    '4b5)* }hr*s3Jh&U#/zo6uVn!7Vh]qYD}EUaupd`AGFf*rpF6CGT(r/ffC(>,uK:' );
define( 'NONCE_KEY',        '4wQG)Pol*E/BTIeX=^*u=Wv7I{(4vze6f0l)c,o|`!{*vm(X?Z,H#P}4s.sd*C({' );
define( 'AUTH_SALT',        '@1-r{B88r*`iLX};e)G(YQm!ta=Xe;D8w?SzU=V?-5]4oq>&k9+{p)g<CAS9i=F0' );
define( 'SECURE_AUTH_SALT', 'sHuo5=y6Ec:Z7]3tRS(V{#U7#8!VdN@V- 2[Nuj1ypwW.f(Rn3[op|acycq#)qi^' );
define( 'LOGGED_IN_SALT',   '2Zd>uYCG0|-$_a?].Fi}~3J*g%*j=R`,I]D8jHvgM`]quXng*9lVlz` S~f?%>nd' );
define( 'NONCE_SALT',       'jP2@fnv4TX8IPNf9QMx@ICPa,L%r9J;fu5{zF=DGZfT.=a|Z)/;g5E}WeL%bFVWj' );
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
define('WP_DEBUG', $_ENV['DEBUG']);
define('WP_DEBUG_LOG', $_ENV['DEBUG']);
define('WP_DEBUG_DISPLAY', $_ENV['DEBUG']);
/* Add any custom values between this line and the "stop editing" line. */
define('FS_METHOD', 'direct');
define('FS_CHMOD_DIR', 0755);
define('FS_CHMOD_FILE', 0644);

//define( 'WP_DEBUG_LOG', true );
#define( 'WP_DEBUG_DISPLAY', true);
//@ini_set( 'display_errors', 0 );
/* Add any custom values between this line and the "stop editing" line. */
/* That's all, stop editing! Happy publishing. */
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';