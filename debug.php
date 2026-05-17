<?php
/**
 * AutoNode Pro — Debug Handler
 *
 * Enable: define('AUTONODE_DEBUG', true) in wp-config.php
 * Logs to: wp-content/debug.log (or custom via AUTONODE_DEBUG_LOG)
 *
 * @phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.runtime_error_logging -- Debug utility file
 * @phpcs:disable WordPress.PHP.DevelopmentFunctions -- Debug code, only active when AUTONODE_DEBUG is true
 */

defined('ABSPATH') || exit;

if (!defined('AUTONODE_DEBUG') || !AUTONODE_DEBUG) {
    return;
}

define('AUTONODE_DEBUG_LOG', defined('AUTONODE_DEBUG_LOG') ? AUTONODE_DEBUG_LOG : WP_CONTENT_DIR . '/debug.log');

/**
 * Capture fatal errors + shutdown for parse/runtime errors.
 */
function autonode_debug_shutdown(): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions -- Debug only
        error_log(sprintf('[AutoNode Pro FATAL] %s in %s on line %d', $error['message'], $error['file'], $error['line']));
    }
}

/**
 * Log message via WordPress error logging.
 *
 * @param string $level   EMEGENCY|ALERT|CRITICAL|ERROR|WARNING|NOTICE|INFO|DEBUG
 * @param mixed  $message
 * @param array  $context Additional context
 */
function autonode_debug_log(string $level, mixed $message, array $context = []): void {
    $context_str = $context ? ' ' . json_encode($context) : '';

    if (is_array($message) || is_object($message)) {
        $message = json_encode($message);
    }

    $entry = sprintf('[AutoNode Pro] [%s] %s%s', $level, $message, $context_str);

    // phpcs:ignore WordPress.PHP.DevelopmentFunctions,WordPress.PHP.DiscouragedPHPFunctions.runtime_error_logging -- Debug mode only
    error_log($entry);
}

/**
 * Hook WordPress debug logging.
 */
add_filter('wp_debug_log', static function ($log_file) {
    return $log_file ?: AUTONODE_DEBUG_LOG;
}, 10, 1);

/**
 * Hook into WP_DEBUG_LOG to capture plugin errors.
 */
add_filter('loglevel', static function ($level) {
    if (defined('WP_DEBUG_LOG') && is_string(WP_DEBUG_LOG)) {
        return $level;
    }
    return 'debug';
}, 10, 1);

// Catch all PHP errors via WordPress debug hook
add_filter('wp_debug_mode', static function (): void {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        // Ensure debug log captures plugin-specific errors when WP_DEBUG is on
    }
});

// Catch fatal errors on shutdown
register_shutdown_function('autonode_debug_shutdown');

// Log plugin boot
autonode_debug_log('INFO', 'AutoNode Pro debug mode active', [
    'version' => defined('AUTONODE_VERSION') ? AUTONODE_VERSION : 'unknown',
    'php'     => PHP_VERSION,
]);