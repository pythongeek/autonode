<?php
namespace AutoNode;

defined( 'ABSPATH' ) || exit;

final class Compatibility {

    private static bool $initialized = false;
    private static bool $is_agentic  = false;

    public static function init(): void {
        if ( self::$initialized ) return;
        self::$initialized = true;

        $theme             = wp_get_theme();
        self::$is_agentic  = str_contains( strtolower( $theme->get( 'Name' ) ), 'agentic' )
                          || str_contains( strtolower( $theme->get_stylesheet() ), 'agentic' );

        add_filter( 'admin_body_class', [ __CLASS__, 'admin_body_class' ] );

        if ( is_admin() ) {
            add_action( 'admin_notices', [ __CLASS__, 'app_password_notice' ] );
        }
    }

    public static function is_agentic_theme(): bool {
        return self::$is_agentic;
    }

    public static function admin_body_class( string $classes ): string {
        $screen = get_current_screen();
        if ( ! $screen ) return $classes;
        $ours = [ 'toplevel_page_amp-cm', 'amp-cm_page_autonode-keys', 'amp-cm_page_autonode-webhooks', 'amp-cm_page_autonode-logs', 'amp-cm_page_autonode-settings', 'amp-cm_page_autonode-docs', 'amp-cm_page_autonode-compat' ];
        if ( in_array( $screen->id, $ours, true ) ) {
            $classes .= ' autonode-page';
        }
        return $classes;
    }

    public static function app_password_notice(): void {
        $screen = get_current_screen();
        if ( ! $screen ) return;
        if ( ! in_array( $screen->id, [ 'toplevel_page_amp-cm', 'amp-cm_page_autonode-settings' ], true ) ) return;
        if ( ! apply_filters( 'wp_is_application_passwords_available', false ) ) return; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WP core filter.
        if ( get_option( 'autonode_apppass_dismissed' ) ) return;
        echo '<div class="notice notice-warning is-dismissible"><p>';
        printf( 
            /* translators: %s: link to security recommendation */
            __( '<strong>AutoNode WP:</strong> Your theme enables WordPress Application Passwords globally. %s', 'autonode' ), 
            '<a href="' . esc_url( admin_url( 'admin.php?page=autonode-compat' ) ) . '">' . esc_html__( 'View security recommendation →', 'autonode' ) . '</a>'
        );
        echo '</p></div>';
    }

    public static function status(): array {
        $theme    = wp_get_theme();
        $app_pass = apply_filters( 'wp_is_application_passwords_available', false ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WP core filter.
        return [
            'theme_name'      => $theme->get( 'Name' ),
            'theme_slug'      => $theme->get_stylesheet(),
            'parent_slug'     => $theme->get_template(),
            'is_agentic'      => self::$is_agentic,
            'app_pass_active' => $app_pass,
        ];
    }
}
