<?php
/**
 * Repeater text row template.
 *
 * @package eXtended WooCommerce
 * @subpackage Admin
 *
 * @var string $class       Input class.
 * @var string $custom_atts Custom attributes.
 * @var string $name        Input name.
 * @var string $placeholder Input placeholder.
 * @var string $suffix      Input suffix.
 * @var string $type        Input type.
 * @var string $value       Input value.
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="repeater-row row">
    <input
        name="<?php echo \esc_attr( $name ); ?>"
        type="<?php echo \esc_attr( $type ); ?>"
        value="<?php echo \esc_attr( $value ); ?>"
        class="<?php echo \esc_attr( $class ); ?>"
        placeholder="<?php echo \esc_attr( $placeholder ); ?>"
        <?php echo $custom_atts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    ><?php echo \esc_html( $suffix ); ?>
    <button type="button" class="button minus repeater-remove-row">
        <?php \esc_html_e( 'Remove', 'woocommerce' ); ?>
    </button>
</div>
