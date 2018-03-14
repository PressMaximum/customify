<?php
class Customify_Customizer_Control_Checkbox extends Customify_Customizer_Control_Base {
    static function field_template(){
        ?>
        <script type="text/html" id="tmpl-field-customify-checkbox">
        <?php
        self::before_field();
        ?>
        <?php echo self::field_header(); ?>
        <div class="customify-field-settings-inner">
            <label>
                <input type="checkbox" class="customify-input" <# if ( field.value == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}" value="1">
                {{{ field.checkbox_label }}}
            </label>
        </div>
        <?php
        self::after_field();
        ?>
        </script>
        <?php
    }
}