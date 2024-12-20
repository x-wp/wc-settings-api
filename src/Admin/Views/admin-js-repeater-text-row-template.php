<?php
/**
 * Repeater text row template.
 *
 * @package eXtended WooCommerce
 * @subpackage Admin
 */

defined( 'ABSPATH' ) || exit;

?>
<script id="tmpl-xwc-repeater-text" type="text/html" class="repeater-tmpl">
    <div class="repeater-row row">
        <input
            name="{{ data.name }}"
            type="{{ data.type }}"
            value="{{ data.value }}"
            class="{{ data.class }}"
            placeholder="{{ data.placeholder }}"
            {{ data.custom_atts }}
        >{{ data.suffix }}
        <button type="button" class="button minus repeater-remove-row">
            <?php \esc_html_e( 'Remove', 'woocommerce' ); ?>
        </button>
    </div>
</script>
