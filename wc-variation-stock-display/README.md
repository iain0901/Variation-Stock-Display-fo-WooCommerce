# WooCommerce Variation Stock Display

Display stock quantity next to variation names in WooCommerce product pages, cart, and orders.

## Description

WooCommerce Variation Stock Display is a WordPress plugin that enhances the product variation display by showing stock quantities alongside variation names. This helps customers quickly see the availability of each product variation without having to select it first.

### Features

- Shows stock quantity next to variation names
- Works in product pages, cart, and orders
- Customizable separator between variation name and stock quantity
- Option to display custom text for zero stock
- Supports both managed and unmanaged stock
- Multi-language support (English and Traditional Chinese included)
- Compatible with WooCommerce 3.0+

## Installation

1. Upload the `wc-variation-stock-display` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WooCommerce > Variation Stock Display to configure the settings

## Requirements

- WordPress 5.0 or later
- WooCommerce 3.0 or later
- PHP 7.2 or later

## Configuration

### Basic Settings

1. **Show Stock**: Enable/disable stock quantity display
2. **Separator**: Set the separator between variation name and stock quantity (default: " - ")
3. **Zero Stock Text Display**: Choose whether to show zero stock as custom text
4. **Zero Stock Text**: Set the text to display when stock is 0 (default: "Out of Stock")

### Display Examples

- Color: Red - 5
- Size: Large - In Stock
- Style: Modern - Out of Stock

## Frequently Asked Questions

### Where will the stock quantity be displayed?

The stock quantity will be displayed:
- In variation dropdowns on product pages
- Next to selected variation attributes
- In the cart
- In orders

### Can I customize the separator?

Yes, you can set any character or text as the separator in the plugin settings.

### Does it work with unmanaged stock?

Yes, for unmanaged stock it will display "In Stock" or "Out of Stock" based on the product's stock status.

## Support

If you encounter any issues or have suggestions, please create an issue in the GitHub repository.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Created by [3DSTU](https://3dstu.com)

## Changelog

### 1.0.0
- Initial release
- Basic functionality for displaying stock quantities
- Settings page for customization
- Multi-language support 