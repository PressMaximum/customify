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
                    var title = '';
                    var disable = false;
                    var bubble = '';

                    if ( ! _.isObject( label ) ) {
                        l = label;
                        title = label;
                    } else { // Image select field
                        if ( label.img ) {
                            l = '<img src="'+label.img+'" alt="">';
                        }
                        if ( label.label ) {
                             l += '<span>'+label.label+'</span>';
                            title = label.label;
                        }
                        if ( typeof label.disable !== "undefined" && label.disable ) {
                            disable = true;
                        }

                        if ( typeof label.bubble !== "undefined" ) {
                            bubble = label.bubble;
                        }
                    }
                    #>
                    <p <# if ( disable ) { #> class="input-disabled" <# } #>>
                        <label title="{{ title }}">
                            <input type="radio" <# if ( disable ) { #> disabled="disabled" <#} #> data-name="{{ field.name }}" value="{{ key }}" <# if ( field.value == key ){ #> checked="checked" <# } #> name="{{ uniqueID }}">
                            <span class="label">{{{ l }}}</span>
                            <# if ( bubble ) { #>
                            <span class="bubble">{{{ bubble }}}</span>
                            <# } #>
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