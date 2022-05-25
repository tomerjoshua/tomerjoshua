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
define( 'DB_NAME', 'u342939820_mkda4' );
/** MySQL database username */
define( 'DB_USER', 'u342939820_lA8Vl' );
/** MySQL database password */
define( 'DB_PASSWORD', 'BC4OTnUc8a' );
/** MySQL hostname */
define( 'DB_HOST', 'mysql' );
/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );
/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );
/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '|:hB)1@`4 Pia&4DnXv3|w2}C6}7|S[F)*gMOwRFeO_rlInOzWm$<3eQ,cUZ:P Y' );
define( 'SECURE_AUTH_KEY',   '<cXE$Y*8Fs}l+Fz4TXh*xr E`_|KW/ XIRa]ENc7cD>|`Gs.vB4*ujM1}6;+BN{J' );
define( 'LOGGED_IN_KEY',     '-n[qneTuk%vi5tF|U>)=SG39W,M^bEJDYa*!QqmDxk |0=g9nweT|P0EbtMs~}R[' );
define( 'NONCE_KEY',         '.yzF!AqI?c], +>k&y*h$?*FNla8GPf_1%+=bq,W}8G/qZLd#MSOLEi%oMmZ7OA`' );
define( 'AUTH_SALT',         ' m^V;o<JZZb]Pj``ni.}1}_:Tq#7(,tm!q/6ai/$/wli6Eb[}~#ldMf340R_GqMl' );
define( 'SECURE_AUTH_SALT',  'QdIQxN(AA:3QPBWqBFE^UHaU``B~:B~HY:nGo?uNs><s&*Mu+JIEFtGRJ*P@b;(j' );
define( 'LOGGED_IN_SALT',    'QPA->`^s%K$yx/j4p&.F|JK*`8BS>~Ijt&zaRGJfkP|8_}G~/yq!?IP7^FmGvF0S' );
define( 'NONCE_SALT',        'aN?3!*x AKY|s*H*^2f nW|?x$L9>DV_63>I6e{)T+GZ8 lvoVGYjM<LN0@.;9Gz' );
define( 'WP_CACHE_KEY_SALT', 'QcH>UT`.W[Mj{%xn^wt?eI{QVm0.)2FS`2/>wO){]`usAA,YyW[6KJ0ODD`:mHX~' );
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';