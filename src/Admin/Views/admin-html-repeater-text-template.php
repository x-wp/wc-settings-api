<?php
/**
 * Repeater text field template.
 *
 * @package eXtended WooCommerce
 * @version 3.7.3
 *
 * @var array<string>        $value       Field value.
 * @var array<string,mixed>> $field       Field data.
 * @var array<string>        $custom_atts Custom attributes.
 */

defined( 'ABSPATH' ) || exit;

$def_val    = $field['default'] ?? array();
$field_name = is_scalar( current( $def_val ) ) ? "{$field['field_name']}[]" : $field['field_name'];
$field_desc = WC_Admin_Settings::get_field_description( $field );

?>
<tr valign="top">
    <th scope="row" class="titledesc">
        <label for="<?php echo \esc_attr( $field['id'] ); ?>">
            <?php echo esc_html( $field['title'] ); ?> <?php echo $field_desc['tooltip_html']; // phpcs:ignore ?>
        </label>
    </th>
    <td class="forminp forminp-<?php echo \esc_attr( \sanitize_title( $field['type'] ) ); ?>">
        <div id="<?php echo \esc_attr( $field['id'] ); ?>" class="repeater-rows">
            <?php
            foreach ( $value ?? array() as $row_value ) {
                xwp_get_template(
                    $row_tmpl,
                    array(
                        'class'       => $field['class'],
                        'custom_atts' => $custom_atts,
                        'name'        => $field_name,
                        'placeholder' => $field['placeholder'],
                        'suffix'      => $field['suffix'],
                        'type'        => 'text',
                        'value'       => $row_value,
                    ),
                );
            }
            ?>
        </div>
        <button
            type="button"
            class="button plus repeater-add-row"
            data-tmpl="<?php echo \esc_attr( $field['id'] ); ?>"
            data-name="<?php echo \esc_attr( $field_name ); ?>"
            data-type="text"
            data-value=""
            data-class="<?php echo \esc_attr( $field['class'] ); ?>"
            data-placeholder="<?php echo \esc_attr( $field['placeholder'] ); ?>"
            data-custom_atts="<?php echo \esc_attr( $custom_atts ); ?>"
            data-suffix="<?php echo \esc_attr( $field['suffix'] ); ?>"
        >
            <?php \esc_html_e( 'Add', 'woocommerce' ); ?>
        </button>
    </td>
</tr>
