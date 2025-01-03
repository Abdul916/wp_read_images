<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp_read_images' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
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
define( 'AUTH_KEY',         'xwH]SxXn]M,mU)PV(`T@qB&LmdV]ShUFINF,XIy#n(Mb+h}MJ2*)JQ 0U@d~7rWI' );
define( 'SECURE_AUTH_KEY',  '{erY4Yk<E`?_EPBcs`)QB:5mbp8lQpQCljssboQXDbB<bontbi`lY;rVZ# j^.Cg' );
define( 'LOGGED_IN_KEY',    'Fc(ccS2wVgR>)R:ncN0u]U;=Pr).uzFKPRCda8KxO/&3tnhU3qNb@V.z<,Z}X$ E' );
define( 'NONCE_KEY',        'hoB!U2hpiWdig4{giY]Uhdy(0[(yvWk;1,]6{DW9lYb1]1FOT)1}%E3JS{^P5El~' );
define( 'AUTH_SALT',        ':aULx@++R8CdNUKL7Eg&o{#D2VS/5.4d+Q=t}{=~h/_?$8.l384{Ztf/:fU&6&Xx' );
define( 'SECURE_AUTH_SALT', '?3%38i*R[~{TZ)=p}y0*81YrBCF^[vW=g;Y0vU4vCd}@y5ww!#5V)JFLbL#=Lc&S' );
define( 'LOGGED_IN_SALT',   '5IslIHAK,r%O,[V||K%=lL.SXn)gVeHi]` 76s`Q>GDr4,l/CO591r(<^KMF1;`A' );
define( 'NONCE_SALT',       'N4844[~RtM!b{wFsi7+cyoL*o2(]/(NWe NVR0SQp4|fa+NU<^+|N>3T8cpnp++e' );

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
// define( 'WP_DEBUG', false );

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
