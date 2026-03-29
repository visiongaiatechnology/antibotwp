<?php
if (!defined('ABSPATH')) exit;

/**
 * PLATIN / DIAMANT VGT SUPREME STATUS
 * VGT_Security: Gehärtete kryptografische Validierung & Zero-Trust IP Resolution
 */
class VGT_Security {
    
    private static function get_dynamic_salt(int $offset_days = 0): string {
        $date = gmdate('Y-m-d', time() + ($offset_days * 86400));
        return hash('sha512', $date . wp_salt('auth') . wp_salt('secure_auth') . 'VGT_MATRIX_SALT');
    }

    /**
     * DIAMANT STATUS: Deterministische IP-Resolution mit Trusted-Proxy-Fallback.
     */
    private static function get_client_fingerprint(int $offset_days = 0): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        // VGT Logic: Strikte Zero-Trust IP Resolution. 
        // Forwarded-Headers werden nur analysiert, wenn explizit ein Trusted Proxy konfiguriert ist (Cloudflare, AWS ELB).
        // Wir verhindern Header-Spoofing in Docker/Local-Netzwerken.
        $is_trusted_proxy_env = defined('VGT_TRUSTED_PROXIES_ACTIVE') && VGT_TRUSTED_PROXIES_ACTIVE;
        
        if ($is_trusted_proxy_env) {
            $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR'];
            foreach ($headers as $header) {
                if (!empty($_SERVER[$header])) {
                    $ip_chain = array_map('trim', explode(',', $_SERVER[$header]));
                    $client_ip = filter_var($ip_chain[0], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
                    if ($client_ip) {
                        $ip = $client_ip;
                        break;
                    }
                }
            }
        }
        
        $ua = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? 'unknown_vgt_agent');
        return hash('sha384', $ip . $ua . self::get_dynamic_salt($offset_days));
    }

    public static function generate_challenge(): array {
        $timestamp = time();
        $difficulty = (int) get_option('vgt_shield_difficulty', 3);
        $seed = hash_hmac('sha256', $timestamp . self::get_client_fingerprint(0), self::get_dynamic_salt(0));
        
        return [
            'seed'       => $seed,
            'timestamp'  => $timestamp,
            'difficulty' => $difficulty
        ];
    }

    public static function get_pow_payload(): string {
        if (!empty($_POST['vgt_pow_payload'])) {
            return sanitize_text_field(wp_unslash($_POST['vgt_pow_payload']));
        }
        if (!empty($_SERVER['HTTP_X_VGT_SHIELD_POW'])) {
            return sanitize_text_field(wp_unslash($_SERVER['HTTP_X_VGT_SHIELD_POW']));
        }
        return '';
    }

    public static function validate_pow(string $payload): bool {
        if (empty($payload)) return false;

        $data = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['seed'], $data['timestamp'], $data['nonce'])) {
            return false;
        }

        $timestamp = (int) $data['timestamp'];
        $duration = time() - $timestamp;
        
        // Strikte TTL: Maximal 30 Minuten (1800s) für PoW Gültigkeit. 3600s bietet zu hohe Angriffsfläche.
        if ($duration < 1 || $duration > 1800) return false;

        $hash_input = $data['seed'] . $data['nonce'];
        $hash = hash('sha256', $hash_input);
        
        // Replay Protection (O(1) Memory Cache präferiert, DB Fallback)
        $cache_key = 'vgt_pow_' . $hash;
        if (wp_cache_get($cache_key, 'vgt_shield') || get_transient($cache_key)) {
            return false;
        }

        $expected_seed_today     = hash_hmac('sha256', $timestamp . self::get_client_fingerprint(0), self::get_dynamic_salt(0));
        $expected_seed_yesterday = hash_hmac('sha256', $timestamp . self::get_client_fingerprint(-1), self::get_dynamic_salt(-1));

        if (!hash_equals($expected_seed_today, $data['seed']) && !hash_equals($expected_seed_yesterday, $data['seed'])) {
            return false;
        }

        $difficulty = (int) get_option('vgt_shield_difficulty', 3);
        $target_prefix = str_repeat('0', $difficulty);
        
        $is_valid = str_starts_with($hash, $target_prefix);

        if ($is_valid) {
            wp_cache_set($cache_key, 1, 'vgt_shield', 1800);
            set_transient($cache_key, 1, 1800);
        }

        return $is_valid;
    }
}