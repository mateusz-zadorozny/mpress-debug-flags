<?php
/**
 * Plugin Name:       Mpress Debug Flags
 * Plugin URI:        https://mpress.cc
 * Description:       Pozwala włączać logowanie dla konkretnych flag/stałych zdefiniowanych w wp-config.php. Użyj mpress_debug_log('wiadomosc', 'NAZWA_FLAGI').
 * Version:           1.0.0
 * Author:            Mateusz Zadorożny
 * Author URI:        https://mpress.cc
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Zapobiegamy bezpośredniemu dostępowi
}

/**
 * Zapisuje do logu (error_log) lub robi print_r, jeżeli stała-flagę ustawiono na true.
 *
 * @param mixed  $message    Dowolny komunikat lub zmienna (string/array/object).
 * @param string $debug_flag Nazwa stałej (bez $), np. 'DEB_FUNKCJA_KTORA_SPRAWDZAM'.
 */
if (!function_exists('mpress_debug_log')) {
    function mpress_debug_log($message, $debug_flag)
    {
        // Upewniamy się, że debug_flag to string i jest niepuste
        if (!is_string($debug_flag) || empty($debug_flag)) {
            return;
        }

        // Sprawdzamy, czy taka stała istnieje i czy jest true
        if (defined($debug_flag) && constant($debug_flag)) {
            // Budujemy prefiks logu z nazwą flagi
            $prefix = '[Debug][' . $debug_flag . '] ';

            if (is_array($message) || is_object($message)) {
                // Jeśli tablica lub obiekt, to print_r do error_log
                error_log($prefix . print_r($message, true));
            } else {
                // W przeciwnym wypadku zapisujemy string
                error_log($prefix . $message);
            }
        }
        // Jeśli stała nie jest zdefiniowana lub jest false, nic nie robimy
    }
}