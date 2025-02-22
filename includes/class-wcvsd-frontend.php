<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 前端類別
 *
 * @package WC_Variation_Stock_Display
 */
class WCVSD_Frontend {
    /**
     * 庫存文字快取
     *
     * @var array
     */
    private $stock_text_cache = array();

    /**
     * 建構函數
     */
    public function __construct() {
        // 修改變體名稱
        add_filter('woocommerce_product_variation_title', array($this, 'modify_variation_title'), 10, 4);
        add_filter('woocommerce_product_title', array($this, 'modify_product_title'), 10, 2);
        
        // 修改變體選項名稱
        add_filter('woocommerce_variation_option_name', array($this, 'modify_variation_option_name'), 99, 4);
        
        // 修改變體屬性顯示
        add_filter('woocommerce_attribute', array($this, 'modify_attribute_display'), 99, 3);
        
        // 修改下拉選單選項
        add_filter('woocommerce_dropdown_variation_attribute_options_html', array($this, 'modify_dropdown_options'), 99, 2);
        
        // 修改購物車中的變體名稱
        add_filter('woocommerce_cart_item_name', array($this, 'modify_cart_item_name'), 99, 3);
        
        // 修改訂單中的變體名稱
        add_filter('woocommerce_order_item_name', array($this, 'modify_order_item_name'), 99, 2);
    }

    /**
     * 獲取庫存顯示文字
     *
     * @param WC_Product $product 產品物件
     * @return string 庫存文字
     */
    private function get_stock_text($product) {
        if (!$product) {
            return '';
        }

        $product_id = $product->get_id();
        
        // 檢查快取
        if (isset($this->stock_text_cache[$product_id])) {
            return $this->stock_text_cache[$product_id];
        }

        $text = '';
        if ($product->managing_stock()) {
            $stock = $product->get_stock_quantity();
            if ($stock === 0 && get_option('wcvsd_show_zero_as_text', 'yes') === 'yes') {
                $text = get_option('wcvsd_zero_stock_text', __('Out of Stock', 'wc-variation-stock-display'));
            } else {
                $text = (string) $stock;
            }
        } else {
            $stock_status = $product->get_stock_status();
            if ($stock_status === 'instock') {
                $text = __('In Stock', 'wc-variation-stock-display');
            } elseif ($stock_status === 'outofstock') {
                $text = __('Out of Stock', 'wc-variation-stock-display');
            }
        }

        // 儲存到快取
        $this->stock_text_cache[$product_id] = $text;

        return $text;
    }

    /**
     * 修改變體標題
     *
     * @param string $title 標題
     * @param WC_Product|null $product 產品物件
     * @param WC_Product|null $parent_product 父產品物件
     * @param array|null $variation_attributes 變體屬性
     * @return string 修改後的標題
     */
    public function modify_variation_title($title, $product = null, $parent_product = null, $variation_attributes = null) {
        if (!$product || get_option('wcvsd_show_stock', 'yes') !== 'yes') {
            return $title;
        }

        $separator = get_option('wcvsd_separator', ' - ');
        $stock_text = $this->get_stock_text($product);
        
        return $stock_text ? $title . esc_html($separator) . esc_html($stock_text) : $title;
    }

    /**
     * 修改產品標題
     *
     * @param string $title 標題
     * @param WC_Product|null $product 產品物件
     * @return string 修改後的標題
     */
    public function modify_product_title($title, $product = null) {
        if (!$product || !$product->is_type('variation') || get_option('wcvsd_show_stock', 'yes') !== 'yes') {
            return $title;
        }

        $separator = get_option('wcvsd_separator', ' - ');
        $stock_text = $this->get_stock_text($product);
        
        return $stock_text ? $title . esc_html($separator) . esc_html($stock_text) : $title;
    }

    /**
     * 修改變體選項名稱
     *
     * @param string $name 名稱
     * @param WC_Product|null $product 產品物件
     * @param string|null $attribute 屬性
     * @param string|null $values 值
     * @return string 修改後的名稱
     */
    public function modify_variation_option_name($name, $product = null, $attribute = null, $values = null) {
        if (!$product || !$attribute || get_option('wcvsd_show_stock', 'yes') !== 'yes') {
            return $name;
        }

        if (!$product instanceof WC_Product_Variable) {
            return $name;
        }

        $variations = $product->get_available_variations();
        $separator = get_option('wcvsd_separator', ' - ');
        $attribute_key = sanitize_title($attribute);

        foreach ($variations as $variation) {
            $variation_obj = wc_get_product($variation['variation_id']);
            if (!$variation_obj) {
                continue;
            }

            $variation_attributes = $variation['attributes'];
            if (isset($variation_attributes['attribute_' . $attribute_key]) && 
                $variation_attributes['attribute_' . $attribute_key] === $name) {
                
                $stock_text = $this->get_stock_text($variation_obj);
                return $stock_text ? $name . esc_html($separator) . esc_html($stock_text) : $name;
            }
        }

        return $name;
    }

    /**
     * 修改屬性顯示
     *
     * @param string $text 文字
     * @param object $attribute 屬性
     * @param array $values 值
     * @return string 修改後的文字
     */
    public function modify_attribute_display($text, $attribute, $values) {
        if (get_option('wcvsd_show_stock', 'yes') !== 'yes') {
            return $text;
        }

        global $product;
        if (!$product || !$product instanceof WC_Product_Variable) {
            return $text;
        }

        $variations = $product->get_available_variations();
        $separator = get_option('wcvsd_separator', ' - ');
        $attribute_key = sanitize_title($attribute->get_name());

        foreach ($variations as $variation) {
            $variation_obj = wc_get_product($variation['variation_id']);
            if (!$variation_obj) {
                continue;
            }

            $variation_attributes = $variation['attributes'];
            if (isset($variation_attributes['attribute_' . $attribute_key])) {
                $value = $variation_attributes['attribute_' . $attribute_key];
                $modified_name = $this->modify_variation_option_name($value, $product, $attribute->get_name(), null);
                $text = str_replace($value, esc_html($modified_name), $text);
            }
        }

        return $text;
    }

    /**
     * 修改下拉選單選項
     *
     * @param string $html HTML
     * @param array $args 參數
     * @return string 修改後的 HTML
     */
    public function modify_dropdown_options($html, $args) {
        if (get_option('wcvsd_show_stock', 'yes') !== 'yes') {
            return $html;
        }

        if (!isset($args['product']) || !$args['product'] instanceof WC_Product_Variable) {
            return $html;
        }

        $product = $args['product'];
        $attribute = $args['attribute'];
        $options = $args['options'];

        foreach ($options as $option) {
            $original_name = wc_attribute_label($option, $product);
            $modified_name = $this->modify_variation_option_name($option, $product, $attribute, null);
            $html = str_replace('>' . esc_html($original_name) . '<', '>' . esc_html($modified_name) . '<', $html);
        }

        return $html;
    }

    /**
     * 修改購物車項目名稱
     *
     * @param string $name 名稱
     * @param array $cart_item 購物車項目
     * @param string $cart_item_key 購物車項目鍵值
     * @return string 修改後的名稱
     */
    public function modify_cart_item_name($name, $cart_item, $cart_item_key) {
        if (!isset($cart_item['variation_id']) || get_option('wcvsd_show_stock', 'yes') !== 'yes') {
            return $name;
        }

        $product = wc_get_product($cart_item['variation_id']);
        if (!$product) {
            return $name;
        }

        $separator = get_option('wcvsd_separator', ' - ');
        $stock_text = $this->get_stock_text($product);
        
        return $stock_text ? $name . esc_html($separator) . esc_html($stock_text) : $name;
    }

    /**
     * 修改訂單項目名稱
     *
     * @param string $name 名稱
     * @param WC_Order_Item $item 訂單項目
     * @return string 修改後的名稱
     */
    public function modify_order_item_name($name, $item) {
        if (!$item->get_variation_id() || get_option('wcvsd_show_stock', 'yes') !== 'yes') {
            return $name;
        }

        $product = wc_get_product($item->get_variation_id());
        if (!$product) {
            return $name;
        }

        $separator = get_option('wcvsd_separator', ' - ');
        $stock_text = $this->get_stock_text($product);
        
        return $stock_text ? $name . esc_html($separator) . esc_html($stock_text) : $name;
    }
} 