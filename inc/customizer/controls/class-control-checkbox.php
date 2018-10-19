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

                <span class="onoffswitch">
                    <input type="checkbox" class="onoffswitch-checkbox customify-input" <# if ( field.value == 1 ){ #> checked="checked" <# } #> data-name="{{ field.name }}" value="1">
                    <span class="onoffswitch-label">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </span>
                </span>

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