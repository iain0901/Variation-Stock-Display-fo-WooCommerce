<?php
/**
 * Plugin Name: WooCommerce Variation Stock Display
 * Plugin URI: https://github.com/3dstu/wc-variation-stock-display
 * Description: Display stock quantity next to variation names in WooCommerce product pages, cart, and orders.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 3.0
 * WC tested up to: 8.5
 * Author: 3DSTU
 * Author URI: https://3dstu.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wc-variation-stock-display
 * Domain Path: /languages
 *
 * @package WC_Variation_Stock_Display
 */

// 防止直接訪問此文件
if (!defined('ABSPATH')) {
    exit;
}

// 定義常數
define('WCVSD_VERSION', '1.0.0');
define('WCVSD_PLUGIN_FILE', __FILE__);
define('WCVSD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCVSD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WCVSD_PLUGIN_BASENAME', plugin_basename(__FILE__));

// 確保不重複載入
if (!class_exists('WC_Variation_Stock_Display')) {

    /**
     * 主要外掛類別
     */
    class WC_Variation_Stock_Display {
        /**
         * 單例實例
         *
         * @var WC_Variation_Stock_Display
         */
        private static $instance = null;

        /**
         * 獲取單例實例
         *
         * @return WC_Variation_Stock_Display
         */
        public static function get_instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * 建構函數
         */
        private function __construct() {
            // 初始化外掛
            add_action('plugins_loaded', array($this, 'init'));
            
            // 註冊啟用和停用鉤子
            register_activation_hook(WCVSD_PLUGIN_FILE, array($this, 'activate'));
            register_deactivation_hook(WCVSD_PLUGIN_FILE, array($this, 'deactivate'));
            
            // 加入解除安裝鉤子
            register_uninstall_hook(WCVSD_PLUGIN_FILE, array('WC_Variation_Stock_Display', 'uninstall'));
        }

        /**
         * 初始化外掛
         */
        public function init() {
            // 檢查相容性
            if (!$this->check_compatibility()) {
                return;
            }

            // 載入語言檔
            $this->load_textdomain();

            // 包含必要的檔案
            $this->includes();

            // 初始化類別
            $this->init_classes();
        }

        /**
         * 檢查相容性
         *
         * @return bool
         */
        private function check_compatibility() {
            // 檢查 WooCommerce 是否已安裝並啟用
            if (!class_exists('WooCommerce')) {
                add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
                return false;
            }

            // 檢查 WooCommerce 版本
            if (version_compare(WC_VERSION, '3.0', '<')) {
                add_action('admin_notices', array($this, 'woocommerce_version_notice'));
                return false;
            }

            return true;
        }

        /**
         * 載入語言檔
         */
        private function load_textdomain() {
            load_plugin_textdomain(
                'wc-variation-stock-display',
                false,
                dirname(WCVSD_PLUGIN_BASENAME) . '/languages'
            );
        }

        /**
         * 包含必要的檔案
         */
        private function includes() {
            require_once WCVSD_PLUGIN_DIR . 'includes/class-wcvsd-admin.php';
            require_once WCVSD_PLUGIN_DIR . 'includes/class-wcvsd-frontend.php';
        }

        /**
         * 初始化類別
         */
        private function init_classes() {
            new WCVSD_Admin();
            new WCVSD_Frontend();
        }

        /**
         * 顯示 WooCommerce 未安裝的提示訊息
         */
        public function woocommerce_missing_notice() {
            ?>
            <div class="error">
                <p><?php 
                printf(
                    wp_kses(
                        __('WooCommerce Variation Stock Display requires WooCommerce to work. Please %sinstall WooCommerce%s first.', 'wc-variation-stock-display'),
                        array('a' => array('href' => array()))
                    ),
                    '<a href="' . esc_url(admin_url('plugin-install.php?tab=search&type=term&s=WooCommerce')) . '">',
                    '</a>'
                );
                ?></p>
            </div>
            <?php
        }

        /**
         * 顯示 WooCommerce 版本不相容的提示訊息
         */
        public function woocommerce_version_notice() {
            ?>
            <div class="error">
                <p><?php 
                esc_html_e('WooCommerce Variation Stock Display requires WooCommerce 3.0 or later. Please update your WooCommerce.', 'wc-variation-stock-display');
                ?></p>
            </div>
            <?php
        }

        /**
         * 啟用外掛時的動作
         */
        public function activate() {
            // 檢查 PHP 版本
            if (version_compare(PHP_VERSION, '7.2', '<')) {
                deactivate_plugins(WCVSD_PLUGIN_BASENAME);
                wp_die(
                    sprintf(
                        esc_html__('WooCommerce Variation Stock Display requires PHP 7.2 or later. Your current PHP version is %s. Please upgrade your PHP.', 'wc-variation-stock-display'),
                        PHP_VERSION
                    )
                );
            }

            // 設定預設選項
            add_option('wcvsd_separator', ' - ');
            add_option('wcvsd_show_stock', 'yes');
            add_option('wcvsd_zero_stock_text', __('Out of Stock', 'wc-variation-stock-display'));
            add_option('wcvsd_show_zero_as_text', 'yes');

            // 清除暫存
            wp_cache_flush();
        }

        /**
         * 停用外掛時的動作
         */
        public function deactivate() {
            // 清除暫存
            wp_cache_flush();
        }

        /**
         * 解除安裝外掛時的動作
         */
        public static function uninstall() {
            // 移除所有選項
            delete_option('wcvsd_separator');
            delete_option('wcvsd_show_stock');
            delete_option('wcvsd_zero_stock_text');
            delete_option('wcvsd_show_zero_as_text');

            // 清除暫存
            wp_cache_flush();
        }
    }

    // 初始化外掛
    WC_Variation_Stock_Display::get_instance();
} 