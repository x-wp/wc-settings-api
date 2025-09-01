<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing

namespace XWC\Admin;

/**
 * Outputs the image select field in WooCommerce settings API forms
 */
class Repeater_Text_Settings_Field extends Custom_Field {
    protected function get_name(): string {
        return 'repeater_text';
    }

    protected function get_type(): string {
        return 'settings';
    }

    public function output_field( array $field ) {
        $value       = $this->get_value( $field );
        $custom_atts = array();

        foreach ( ( $field['custom_attributes'] ?? array() ) as $att_key => $att_val ) {
            if ( ! $att_val ) {
                continue;
            }

            $custom_atts[ $att_key ] = $att_val;
        }

        \xwp_get_template(
            $this->get_field_template(),
            array(
                'custom_atts' => \wc_implode_html_attributes( $custom_atts ),
                'field'       => $field,
                'row_tmpl'    => $this->get_row_template(),
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
        return \sprintf(
            <<<'JS'
            jQuery(function($){
                var rptField = {
                    template: window.wp.template('%s'),

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
            JS,
            $this->get_row_template_id(),
        );
    }

    public function output_js() {
        if ( ! $this->did_output ) {
            return;
        }

        parent::output_js();

        \printf(
            '%s%s id="tmpl-%s" type="text/html" class="repeater-tmpl">',
            '<',
            'script',
            \esc_attr( $this->get_row_template_id() ),
        );

        \xwp_get_template(
            $this->get_row_template(),
            array(
                'class'       => '{{data.class}}',
                'custom_atts' => '{{data.custom_atts}}',
                'name'        => '{{data.name}}',
                'placeholder' => '{{data.placeholder}}',
                'suffix'      => '{{data.suffix}}',
                'type'        => '{{data.type}}',
                'value'       => '{{data.value}}',
            ),
        );

        echo '</%>';
    }

    /**
     * Get the field value
     *
     * @param  array{value?: mixed, default?: mixed} $field The field data.
     * @return mixed
     */
    protected function get_value( array $field ): mixed {
        return \wc_string_to_array( $field['value'] ?? '' );
    }

    protected function sanitize( mixed $value, array $option, mixed $raw_value ): mixed {
        return \wc_string_to_array( $raw_value );
    }

    /**
     * Get the field template path
     *
     * @return string
     */
    protected function get_field_template(): string {
        return __DIR__ . '/Views/admin-html-repeater-text-template.php';
    }

    /**
     * Get the HTML row template path
     *
     * @return string
     */
    protected function get_row_template(): string {
        return __DIR__ . '/Views/admin-html-repeater-text-row-template.php';
    }

    /**
     * Get the JS row template path
     *
     * @return string
     */
    protected function get_js_row_template(): string {
        return __DIR__ . '/Views/admin-js-repeater-text-row-template.php';
    }

    /**
     * Get the JS row template ID
     *
     * @return string
     */
    protected function get_row_template_id(): string {
        return 'xwc-repeater-text';
    }
}
