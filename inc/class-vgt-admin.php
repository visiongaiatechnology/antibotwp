<?php
if (!defined('ABSPATH')) exit;

class VGT_Admin {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
        add_action('admin_post_vgt_save_settings', [__CLASS__, 'save_settings']);
        add_action('admin_post_vgt_save_custom_hooks', [__CLASS__, 'save_custom_hooks']);
        add_action('wp_ajax_vgt_scan_plugin', [__CLASS__, 'ajax_scan_plugin']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    public static function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_vgt-shield-dashboard') return;
        
        // VGT Supreme: Strikte CSP Einhaltung. Zero Inline Styles.
        wp_enqueue_style(
            'vgt-shield-admin-css', 
            VGT_SHIELD_URL . 'assets/css/vgt-admin.css', 
            [], 
            '6.0.0'
        );
    }

    public static function ajax_scan_plugin() {
        check_ajax_referer('vgt_scan_action', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

        $plugin_file = sanitize_text_field($_POST['plugin_file'] ?? '');
        if (empty($plugin_file)) wp_send_json_error('No plugin selected');

        $hooks = VGT_Scanner::scan_plugin_for_hooks($plugin_file);
        
        if (is_wp_error($hooks)) {
            wp_send_json_error($hooks->get_error_message());
        }

        wp_send_json_success(['hooks' => $hooks]);
    }

    public static function save_custom_hooks() {
        if (!current_user_can('manage_options') || !check_admin_referer('vgt_save_hooks_action', 'vgt_nonce')) {
            wp_die('Unauthorized execution.');
        }

        $hooks = isset($_POST['vgt_custom_hooks']) ? array_map('sanitize_text_field', $_POST['vgt_custom_hooks']) : [];
        update_option('vgt_shield_custom_hooks', $hooks);

        wp_redirect(admin_url('admin.php?page=vgt-shield-dashboard&status=hooks_saved'));
        exit;
    }

    public static function register_menu() {
        add_menu_page(
            'VGT Shield',
            'VGT Shield',
            'manage_options',
            'vgt-shield-dashboard',
            [__CLASS__, 'render_dashboard'],
            'dashicons-shield-alt',
            80
        );
    }

    public static function save_settings() {
        if (!current_user_can('manage_options') || !check_admin_referer('vgt_save_action', 'vgt_nonce')) {
            wp_die('Unauthorized execution.');
        }

        $mode = sanitize_text_field($_POST['vgt_mode'] ?? 'global');
        $difficulty = intval($_POST['vgt_difficulty'] ?? 3);

        update_option('vgt_shield_mode', $mode);
        update_option('vgt_shield_difficulty', $difficulty);

        wp_redirect(admin_url('admin.php?page=vgt-shield-dashboard&status=saved'));
        exit;
    }

    public static function render_dashboard() {
        $mode = get_option('vgt_shield_mode', 'global');
        $difficulty = get_option('vgt_shield_difficulty', 3);
        $custom_hooks = get_option('vgt_shield_custom_hooks', []);
        
        $status_msg = '';
        if (isset($_GET['status'])) {
            if ($_GET['status'] === 'saved') $status_msg = 'Core Parameters synchronized.';
            if ($_GET['status'] === 'hooks_saved') $status_msg = 'Neural Matrix (Hooks) updated.';
        }

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $all_plugins = get_plugins();

        // VGT State-of-Art UI Injection (CSS via wp_enqueue_style geladen)
        ?>
        <div class="vgt-wrapper">
            <div class="vgt-header">
                <h1>VGT Shield</h1>
                <span class="vgt-badge">Diamant Supreme</span>
            </div>

            <?php if ($status_msg): ?>
                <div class="vgt-alert"><?php echo esc_html($status_msg); ?></div>
            <?php endif; ?>

            <div class="vgt-grid">
                <!-- Spalte 1: Core Settings -->
                <div>
                    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                        <input type="hidden" name="action" value="vgt_save_settings">
                        <?php wp_nonce_field('vgt_save_action', 'vgt_nonce'); ?>
                        
                        <div class="vgt-card">
                            <h2>Core Architecture</h2>
                            <div class="vgt-group">
                                <label class="vgt-label">Execution Environment</label>
                                <select name="vgt_mode" class="vgt-select">
                                    <option value="global" <?php selected($mode, 'global'); ?>>Global Infiltration (DOM Listeners)</option>
                                    <option value="manual" <?php selected($mode, 'manual'); ?>>Surgical Intervention (Shortcode)</option>
                                </select>
                            </div>
                            <div class="vgt-group">
                                <label class="vgt-label">Cryptographic Difficulty</label>
                                <select name="vgt_difficulty" class="vgt-select">
                                    <option value="2" <?php selected($difficulty, 2); ?>>Level 2: Low-Latency Mode</option>
                                    <option value="3" <?php selected($difficulty, 3); ?>>Level 3: VGT Standard Protocol</option>
                                    <option value="4" <?php selected($difficulty, 4); ?>>Level 4: High CPU Load (Maximum Security)</option>
                                </select>
                                <p class="vgt-desc">Adjusts the leading zeros target for the SHA-256 Web Worker.</p>
                            </div>
                            <button type="submit" class="vgt-btn">Synchronize Kernel</button>
                        </div>
                    </form>
                    
                    <div class="vgt-card">
                        <h2>Native Hook Matrix</h2>
                        <p class="vgt-desc" style="margin-bottom: 16px;">System intercepts state mutations on:</p>
                        <ul class="vgt-list-native">
                            <li>WooCommerce (Auth & Checkout)</li>
                            <li>Contact Form 7</li>
                            <li>WPForms</li>
                            <li>Gravity Forms</li>
                            <li>WordPress Core Comments</li>
                        </ul>
                    </div>
                </div>

                <!-- Spalte 2: VGT Scanner & Dynamic Hooks -->
                <div>
                    <div class="vgt-card">
                        <h2>Deep Plugin Scanner</h2>
                        <p class="vgt-desc" style="margin-bottom: 20px;">
                            Extract execution pathways (hooks) from any installed module via AST-Regex parsing.
                        </p>
                        
                        <div class="vgt-group" style="display: flex; gap: 12px;">
                            <select id="vgt-plugin-select" class="vgt-select">
                                <option value="">Select Target Module...</option>
                                <?php foreach ($all_plugins as $path => $plugin): ?>
                                    <option value="<?php echo esc_attr($path); ?>"><?php echo esc_html($plugin['Name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="vgt-btn vgt-btn-secondary" id="vgt-scan-btn">
                                Scan <div class="loader" id="vgt-loader"></div>
                            </button>
                        </div>

                        <div id="vgt-scan-results" style="display:none;">
                            <label class="vgt-label" style="margin-top: 16px;">Identified Neural Pathways</label>
                            <div class="vgt-hook-list" id="vgt-hook-container"></div>
                        </div>
                    </div>

                    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                        <input type="hidden" name="action" value="vgt_save_custom_hooks">
                        <?php wp_nonce_field('vgt_save_hooks_action', 'vgt_nonce'); ?>
                        
                        <div class="vgt-card">
                            <h2>Dynamic Execution Hooks</h2>
                            <div class="vgt-hook-list" id="vgt-active-hooks" style="margin-top: 0; margin-bottom: 20px;">
                                <?php if (empty($custom_hooks)): ?>
                                    <span class="vgt-desc">No dynamic hooks active.</span>
                                <?php else: ?>
                                    <?php foreach ($custom_hooks as $hook): ?>
                                        <div class="vgt-hook-item">
                                            <input type="checkbox" name="vgt_custom_hooks[]" value="<?php echo esc_attr($hook); ?>" checked>
                                            <?php echo esc_html($hook); ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="vgt-btn">Commit to Matrix</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('vgt-scan-btn').addEventListener('click', async () => {
                const plugin = document.getElementById('vgt-plugin-select').value;
                if (!plugin) return alert('Select a target plugin first.');

                document.getElementById('vgt-loader').style.display = 'inline-block';
                
                const formData = new FormData();
                formData.append('action', 'vgt_scan_plugin');
                formData.append('plugin_file', plugin);
                formData.append('nonce', '<?php echo wp_create_nonce("vgt_scan_action"); ?>');

                try {
                    const response = await fetch(ajaxurl, { method: 'POST', body: formData });
                    const data = await response.json();
                    
                    if (data.success) {
                        const container = document.getElementById('vgt-hook-container');
                        container.innerHTML = '';
                        
                        data.data.hooks.forEach(hook => {
                            // Filter logic: Only show likely form/submission hooks to avoid cognitive overload
                            if (hook.includes('submit') || hook.includes('process') || hook.includes('validate') || hook.includes('error') || hook.includes('save') || hook.includes('insert')) {
                                container.innerHTML += `
                                    <div class="vgt-hook-item">
                                        <input type="checkbox" form="form-dummy" onchange="addHookToActive(this)" value="${hook}">
                                        ${hook}
                                    </div>
                                `;
                            }
                        });
                        document.getElementById('vgt-scan-results').style.display = 'block';
                    } else {
                        alert('Scan Error: ' + data.data);
                    }
                } catch (e) {
                    alert('System Failure during scan.');
                }
                
                document.getElementById('vgt-loader').style.display = 'none';
            });

            function addHookToActive(checkbox) {
                if (checkbox.checked) {
                    const activeContainer = document.getElementById('vgt-active-hooks');
                    // Remove "Empty" text if present
                    if(activeContainer.innerHTML.includes('Keine dynamischen Hooks aktiv.')) {
                        activeContainer.innerHTML = '';
                    }
                    
                    // Prevent duplicates
                    if(!document.querySelector(`input[name="vgt_custom_hooks[]"][value="${checkbox.value}"]`)) {
                        activeContainer.innerHTML += `
                            <div class="vgt-hook-item">
                                <input type="checkbox" name="vgt_custom_hooks[]" value="${checkbox.value}" checked>
                                ${checkbox.value}
                            </div>
                        `;
                    }
                }
            }
        </script>
        <?php
    }
}