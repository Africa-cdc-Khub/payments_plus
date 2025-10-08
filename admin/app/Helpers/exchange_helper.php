<?php

/**
 * Helper functions to make parent Cphia2025 classes work with Laravel
 */

if (!function_exists('getConnection')) {
    /**
     * Get PDO connection compatible with parent Cphia2025 classes
     * This bridges Laravel's database to the parent classes
     */
    function getConnection() {
        return DB::connection()->getPdo();
    }
}


