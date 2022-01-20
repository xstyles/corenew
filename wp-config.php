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
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'corenew' );

/** MySQL database username */
define( 'DB_USER', 'coredb_user' );

/** MySQL database password */
define( 'DB_PASSWORD', 'pfqW.z!]7GpE' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',         '8C-<3]6r*hkjC!_EySF]P+[xw/f8hV6Js/v/bnf-Qwj8F@&z[.,:R3o3NX~WNOce' );
define( 'SECURE_AUTH_KEY',  '-:Jf~8bD`z=0:XF>_&$iRI 22X8ulK^N7O^_~ME5;0|IcY42b(Vt`R:r[J>IIa4-' );
define( 'LOGGED_IN_KEY',    'hT)q[_vVPO{Z`hw$V;ii&R-G,)cULvGu?dw1!Nt~I(6#bg3p3Hzm/I`0=f=;3b&#' );
define( 'NONCE_KEY',        'qqk_doAO~Xg<lV2kp_,9?yup5=Ry_m.o7Z!3OM~ni1`+&Zi{dm}=vxs08_y_0uRk' );
define( 'AUTH_SALT',        ',nDSffYA`zhN>:L~<uk6W}kVa:?6w|zGBEna<sF9CE!T{Y&eyqntU*0fA<D./Y?O' );
define( 'SECURE_AUTH_SALT', 'd=tD}+[V.&W:=Y[5h,>f:(dGI-~~?V:~Q9cMXBs{mpXqX,-Gi`m5Vb]0[.Nm_>`o' );
define( 'LOGGED_IN_SALT',   ' *J9$X=y0l9l)sBQ>(PAAFMEaB49nU[X3G[Z]r{<H?c%i|d-zuGImL+lF1=z_d L' );
define( 'NONCE_SALT',       '$~J6y}P[V_#/s^>[ls6XQ9PC1,w6PZUW@_eo7A@:w,>LhDx:`mDWSZYn0aVfz_q~' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
// Enable WP_DEBUG mode
define( 'WP_DEBUG', true );

// Enable Debug logging to the /wp-content/debug.log file
define( 'WP_DEBUG_LOG', true );

// Disable display of errors and warnings
define( 'WP_DEBUG_DISPLAY', true );
@ini_set( 'display_errors', 1 );

// Use dev versions of core JS and CSS files (only needed if you are modifying these core files)
define( 'SCRIPT_DEBUG', true );

/* Add any custom values between this line and the "stop editing" line. */

define('WP_MEMORY_LIMIT', '512M');

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
