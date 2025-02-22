<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 管理員類別
 *
 * @package WC_Variation_Stock_Display
 */
class WCVSD_Admin {
    /**
     * 建構函數
     */
    public function __construct() {
        // 添加設定頁面
        add_action('admin_menu', array($this, 'add_admin_menu'));
        // 註冊設定
        add_action('admin_init', array($this, 'register_settings'));
        // 添加設定連結
        add_filter('plugin_action_links_' . WCVSD_PLUGIN_BASENAME, array($this, 'add_settings_link'));
        // 加入設定驗證
        add_filter('pre_update_option_wcvsd_separator', array($this, 'validate_separator'), 10, 2);
        add_filter('pre_update_option_wcvsd_zero_stock_text', array($this, 'validate_zero_stock_text'), 10, 2);
    }

    /**
     * 添加設定連結到外掛頁面
     *
     * @param array $links 外掛動作連結陣列
     * @return array 修改後的連結陣列
     */
    public function add_settings_link($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=wc-variation-stock-display')),
            esc_html__('Settings', 'wc-variation-stock-display')
        );
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * 添加管理選單
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            esc_html__('Variation Stock Display Settings', 'wc-variation-stock-display'),
            esc_html__('Variation Stock Display', 'wc-variation-stock-display'),
            'manage_woocommerce',
            'wc-variation-stock-display',
            array($this, 'settings_page')
        );
    }

    /**
     * 註冊設定
     */
    public function register_settings() {
        register_setting(
            'wcvsd_options',
            'wcvsd_separator',
            array(
                'type' => 'string',
                'description' => 'Separator between variation name and stock quantity',
                'default' => ' - ',
                'sanitize_callback' => array($this, 'sanitize_separator')
            )
        );

        register_setting(
            'wcvsd_options',
            'wcvsd_show_stock',
            array(
                'type' => 'string',
                'description' => 'Whether to show stock quantity',
                'default' => 'yes',
                'sanitize_callback' => array($this, 'sanitize_yes_no')
            )
        );

        register_setting(
            'wcvsd_options',
            'wcvsd_zero_stock_text',
            array(
                'type' => 'string',
                'description' => 'Text to display for zero stock',
                'default' => __('Out of Stock', 'wc-variation-stock-display'),
                'sanitize_callback' => array($this, 'sanitize_zero_stock_text')
            )
        );

        register_setting(
            'wcvsd_options',
            'wcvsd_show_zero_as_text',
            array(
                'type' => 'string',
                'description' => 'Whether to show zero stock as text',
                'default' => 'yes',
                'sanitize_callback' => array($this, 'sanitize_yes_no')
            )
        );
    }

    /**
     * 驗證分隔符號
     *
     * @param string $new_value 新的值
     * @param string $old_value 舊的值
     * @return string 驗證後的值
     */
    public function validate_separator($new_value, $old_value) {
        if (strlen($new_value) > 10) {
            add_settings_error(
                'wcvsd_separator',
                'separator_too_long',
                __('Separator must be 10 characters or less.', 'wc-variation-stock-display')
            );
            return $old_value;
        }
        return $new_value;
    }

    /**
     * 驗證零庫存文字
     *
     * @param string $new_value 新的值
     * @param string $old_value 舊的值
     * @return string 驗證後的值
     */
    public function validate_zero_stock_text($new_value, $old_value) {
        if (strlen($new_value) > 50) {
            add_settings_error(
                'wcvsd_zero_stock_text',
                'zero_stock_text_too_long',
                __('Zero stock text must be 50 characters or less.', 'wc-variation-stock-display')
            );
            return $old_value;
        }
        return $new_value;
    }

    /**
     * 清理分隔符號
     *
     * @param string $value 要清理的值
     * @return string 清理後的值
     */
    public function sanitize_separator($value) {
        return sanitize_text_field($value);
    }

    /**
     * 清理是/否選項
     *
     * @param string $value 要清理的值
     * @return string 清理後的值
     */
    public function sanitize_yes_no($value) {
        return $value === 'yes' ? 'yes' : 'no';
    }

    /**
     * 清理零庫存文字
     *
     * @param string $value 要清理的值
     * @return string 清理後的值
     */
    public function sanitize_zero_stock_text($value) {
        return sanitize_text_field($value);
    }

    /**
     * 設定頁面內容
     */
    public function settings_page() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wc-variation-stock-display'));
        }

        // 檢查 nonce
        if (isset($_POST['_wpnonce']) && !wp_verify_nonce(sanitize_key($_POST['_wpnonce']), 'wcvsd_options-options')) {
            wp_die(esc_html__('Security check failed.', 'wc-variation-stock-display'));
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
                <?php
                settings_fields('wcvsd_options');
                do_settings_sections('wcvsd_options');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="wcvsd_separator"><?php esc_html_e('Separator', 'wc-variation-stock-display'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="wcvsd_separator" name="wcvsd_separator" 
                                value="<?php echo esc_attr(get_option('wcvsd_separator', ' - ')); ?>" class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Set the separator between variation name and stock quantity', 'wc-variation-stock-display'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="wcvsd_show_stock"><?php esc_html_e('Show Stock', 'wc-variation-stock-display'); ?></label>
                        </th>
                        <td>
                            <select id="wcvsd_show_stock" name="wcvsd_show_stock">
                                <option value="yes" <?php selected(get_option('wcvsd_show_stock', 'yes'), 'yes'); ?>>
                                    <?php esc_html_e('Yes', 'wc-variation-stock-display'); ?>
                                </option>
                                <option value="no" <?php selected(get_option('wcvsd_show_stock', 'yes'), 'no'); ?>>
                                    <?php esc_html_e('No', 'wc-variation-stock-display'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Whether to show stock quantity after variation name', 'wc-variation-stock-display'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="wcvsd_show_zero_as_text"><?php esc_html_e('Zero Stock Text Display', 'wc-variation-stock-display'); ?></label>
                        </th>
                        <td>
                            <select id="wcvsd_show_zero_as_text" name="wcvsd_show_zero_as_text">
                                <option value="yes" <?php selected(get_option('wcvsd_show_zero_as_text', 'yes'), 'yes'); ?>>
                                    <?php esc_html_e('Yes', 'wc-variation-stock-display'); ?>
                                </option>
                                <option value="no" <?php selected(get_option('wcvsd_show_zero_as_text', 'yes'), 'no'); ?>>
                                    <?php esc_html_e('No', 'wc-variation-stock-display'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Whether to display zero stock as custom text', 'wc-variation-stock-display'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="wcvsd_zero_stock_text"><?php esc_html_e('Zero Stock Text', 'wc-variation-stock-display'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="wcvsd_zero_stock_text" name="wcvsd_zero_stock_text" 
                                value="<?php echo esc_attr(get_option('wcvsd_zero_stock_text', __('Out of Stock', 'wc-variation-stock-display'))); ?>" class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Set the text to display when stock is 0', 'wc-variation-stock-display'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
} 