<?php
class Customify_Customizer_Control_Radio extends Customify_Customizer_Control_Base {
    static function field_template(){
        echo '<script type="text/html" id="tmpl-field-customify-radio">';
        self::before_field();
        ?>
        <#
        var uniqueID = field.name + ( new Date().getTime() );
        #>
        <?php echo self::field_header(); ?>
        <div class="customify-field-settings-inner">
            <div class="customify-radio-list">
                <# _.each( field.choices, function( label, key ){
                    var l = '';
                    if ( ! _.isObject( label ) ) {
                        l = label;
                    } else {
                        if ( label.img ) {
                            l = '<img src="'+label.img+'" alt="">';
                        }
                        if ( label.label ) {
                            l += '<span>'+label.label+'</span>';
                        }
                    }
                    #>
                    <p>
                        <label><input type="radio" data-name="{{ field.name }}" value="{{ key }}" <# if ( field.value == key ){ #> checked="checked" <# } #> name="{{ uniqueID }}">
                            <span class="label">{{{ l }}}</span>
                        </label>
                    </p>
                <# } ); #>
            </div>
        </div>
        <?php
        self::after_field();
        echo '</script>';
    }
}