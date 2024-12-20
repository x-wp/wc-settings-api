<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing

namespace XWC\Admin;

/**
 * Outputs the image select field in WooCommerce settings API forms
 */
final class Repeater_Text_Settings_Field extends Custom_Field {
    protected function get_name(): string {
        return 'repeater_text';
    }

    protected function get_type(): string {
        return 'settings';
    }

    public function output_field( array $field ) {
        $value       = \wc_string_to_array( $field['value'] ?? '' );
        $custom_atts = array();

        foreach ( ( $field['custom_attributes'] ?? array() ) as $att_key => $att_val ) {
            $custom_atts[] = \sprintf( '%s="%s"', \esc_attr( $att_key ), \esc_attr( $att_val ) );
        }

        \xwp_get_template(
            __DIR__ . '/Views/admin-html-repeater-text-template.php',
            array(
                'custom_atts' => $custom_atts,
                'field'       => $field,
                'value'       => $value,
            ),
        );
    }

    protected function get_css(): string {
        return <<<'CSS'
        .repeater-rows .repeater-row {
            margin-bottom: 10px;
        }
        .repeater-row .repeater-remove-row {
            color: #d00;
            border-color: #d00;
        }
        CSS;
    }

    protected function get_js(): string {
        return <<<'JS'
        jQuery(function($){
            var rptField = {
                template: window.wp.template('xwc-repeater-text'),

                init: function() {
                    $('.repeater-add-row').on('click', (e) => this.addRow(e));
                    $('.repeater-rows').on('click', '.repeater-remove-row', (e) => this.removeRow(e));
                },

                addRow: function(e) {
                    var {tmpl, ...data} = $(e.target).data();

                    $('#'+tmpl).append(this.template(data));
                },

                removeRow: function(e) {
                    $(e.target).closest('.row').remove();
                }
            };

            rptField.init();
        });
        JS;
    }

    public function output_js() {
        if ( ! $this->did_output ) {
            return;
        }

        parent::output_js();

        include __DIR__ . '/Views/admin-js-repeater-text-row-template.php';
    }

    protected function sanitize( mixed $value, array $option, mixed $raw_value ): mixed {
        return \wc_string_to_array( $raw_value );
    }
}
