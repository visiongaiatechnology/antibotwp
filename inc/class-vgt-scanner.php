<?php
if (!defined('ABSPATH')) exit;

class VGT_Scanner {
    /**
     * DIAMANT STATUS: Strikte Sandbox-Validierung & O(1) Stream-Processing.
     * Eliminiert Path Traversal (CWE-22) und Memory Leaks.
     */
    public static function scan_plugin_for_hooks($plugin_folder) {
        $base_dir = wp_normalize_path(WP_PLUGIN_DIR);
        $requested_path = wp_normalize_path($base_dir . '/' . dirname($plugin_folder));
        $real_path = realpath($requested_path);
        
        if (!$real_path || strpos(wp_normalize_path($real_path), $base_dir) !== 0 || !is_dir($real_path)) {
            return new WP_Error('security_violation', 'VGT-SHIELD: Sandbox Escape Detektiert und Blockiert.');
        }

        $hooks = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($real_path));
        $pattern = '/(?:do_action|apply_filters)\s*\(\s*[\'"]([a-zA-Z0-9_\-]+)[\'"]/S';

        foreach ($files as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') continue;

            $handle = @fopen($file->getRealPath(), 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (preg_match_all($pattern, $line, $matches)) {
                        foreach ($matches[1] as $match) {
                            $hooks[$match] = true;
                        }
                    }
                }
                fclose($handle);
            }
        }

        $unique_hooks = array_keys($hooks);
        sort($unique_hooks);
        return $unique_hooks;
    }
}