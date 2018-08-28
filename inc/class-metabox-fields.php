<?php

class Customify_Form_Fields {

    private $fields = array();
    private $tabs = array();
    private $values = array();
    private $using_tabs = true;
    private $group_name = 'customify_page_settings';

	function parse_args( $args ){
		if ( ! is_array( $args ) ) {
			$args = array();
		}
		$args = wp_parse_args( $args, array(
			'title' => '',
			'type' => 'text',
			'tab' => 'text',
			'name' => '',
			'description' => '',
			'default' => null,
			'show_default' => false,
			'default_label' => false,
			'placeholder' => '',
			'choices' => array()
		) );
		$args['type'] = sanitize_text_field( $args['type'] );
		return $args;
	}

	function get_all_fields(){
	    return $this->fields;
    }

	function get_all_tabs(){
		return $this->tabs;
	}

	function using_tabs( $using ){
		$this->using_tabs = $using;
	}

	function set_values( $values ){
        $this->values = $values;
    }

    function get_submitted_values(){
	    $data = $this->group_name && isset( $_REQUEST[ $this->group_name ] ) ? $_REQUEST[ $this->group_name  ] : $_REQUEST;
	    $data = wp_unslash( $data );

	    $submitted_data =  array();
        foreach ( $this->fields as $field ) {
	        if ( $field['type'] == 'multiple_checkbox' ) {
		        foreach ( ( array ) $field['choices'] as $key => $label ) {
			        $value = isset( $data[ $key ] ) ? $data[ $key ] : null;
			        $submitted_data[ $key ] = $value;
                }
	        } else if ( $field['name'] ) {
                $value = isset( $data[ $field['name'] ] ) ? $data[ $field['name'] ] : null;
	            $submitted_data[ $field['name'] ] = $value;
            }
        }

        return $submitted_data;
    }

    function add_tab( $tab_id, $args ){
	    $args = wp_parse_args( $args, array(
            'title' => '',
            'icon' => '',
        ) );
	    $args['_id'] = $tab_id;

	    $this->tabs[ $tab_id ] = $args;
    }

	function add_field( $args ){
		$this->fields[] = $this->parse_args( $args );
    }

    private function render_fields( $tab_id  = false ){
	    foreach ( $this->fields as $field ) {
	        if( $tab_id ) {
	            if ( $tab_id != $field['tab'] ) {
	                continue;
                }
            }

		    $cb = apply_filters( 'customify_render_field_cb', false, $field );
	        $_in_class = false;
		    if ( ! is_callable( $cb ) ) {
			    if ( method_exists( $this, 'field_'.$field['type'] ) ) {
				    $cb =  array( $this, 'field_'.$field['type'] );
				    $_in_class = true;
			    }
		    }

		    ob_start();

		    if ( $cb ) {
			    call_user_func_array( $cb, array( $field ) );
		    }

		    $content = ob_get_clean();

		    if ( $content ) {
			    $this->before_field( $field );
			    if ( $_in_class ) {
				    $this->field_label( $field );
                }
			    echo '<div class="customify-mt-field-inner">'.$content.'</div>'; // WPCS: XSS OK.
			    $this->after_field( $field );
		    }
	    }
    }

	function is_valid_url( $url ) {

		// Must start with http:// or https://.
		if ( 0 !== strpos( $url, 'http://' ) && 0 !== strpos( $url, 'https://' ) ) {
			return false;
		}

		// Must pass validation.
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		return true;
	}

    private function render_tabs(){
	    if ( $this->using_tabs && ! empty( $this->tabs ) ) {
            echo '<div class="customify-mt-tabs">';
                echo '<ul class="customify-mt-tabs-list">';
                    $i = 0;
                    foreach( $this->tabs as $id => $tab ) {
                        $icon = '';
	                    $class = ' customify-mt-tab-cont';
	                    if ( $i == 0 ) {
		                    $class.= ' active ';
	                    }
                        if ( $this->is_valid_url( $tab['icon'] ) ) {
	                        $icon = '<img alt="" src="'.esc_url( $tab['icon'] ).'"/>';
                        } elseif ( $tab['icon'] ) {
	                        $icon = '<i class="'.esc_attr( $tab['icon'] ).'"></i>';
                        }

                        echo '<li class="li-'.esc_attr( $id ).$class.'"><a href="#" data-tab-id="'.esc_attr( $id ).'">'.$icon.esc_html( $tab['title'] ).'</a></li>';
                        $i++;
                    }
                echo '</ul>';

                echo '<div class="customify-mt-tab-contents">';
                $i = 0;
                foreach( $this->tabs as $id => $tab ) {
                    $class = 'customify-mt-tab-cont';
                    if ( $i == 0 ) {
	                    $class.= ' active ';
                    }
                    echo '<div class="'.$class.'" data-tab-id="'.esc_attr( $id ).'">';
                    $this->render_fields( $id );
                    echo '</div>';
	                $i ++ ;
                }
                echo '</div>';


            echo '</div>';
        }
    }

    function render(){
        $this->render_tabs();
    }

    function before_field( $args ){
        echo "<div class=\"customify-mt-field field-type-{$args['type']}\">";
    }

    function after_field( $args ){
        echo '</div>';
    }

    function get_name( $args ){
	    $key = is_array( $args ) ? $args['name'] : $args;
        if ( $this->group_name ) {
            return $this->group_name."[$key]";
        }
        return $args['name'];
    }

    function get_value( $args ){
	    $key = is_array( $args ) ? $args['name'] : $args;
        if ( isset( $this->values[ $key ] ) ) {
            return $this->values[ $key ];
        }
        return is_array( $args ) ?  $args['default'] : null;
    }

    private function get_filed_id( $args ){
	    $key = is_array( $args ) ? $args['name'] : $args;
        return 'customify-mt-field-'. $key;
    }

    function field_label( $args ){
	    if ( $args['title'] ) {
		    ?>
            <label class="customify-mt-field-label" for="<?php echo esc_attr( $this->get_filed_id( $args ) ); ?>"><?php echo $args['title']; // WPCS: XSS OK. ?></label>
		    <?php
	    }
    }

	private function field_text( $args ){
		?>
        <input type="text" id="<?php echo esc_attr( $this->get_filed_id( $args ) ); ?>" name="<?php echo esc_attr( $this->get_name( $args ) ); ?>" value="<?php echo esc_attr( $this->get_value( $args ) ); ?>" class="widefat">
		<?php
	}
	private function field_textarea( $args ){
		?>
        <textarea rows="5" id="<?php echo esc_attr( $this->get_filed_id( $args ) ); ?>" name="<?php echo esc_attr( $this->get_name( $args ) ); ?>" class="widefat"><?php echo esc_textarea( $this->get_value( $args ) ); ?></textarea>
		<?php
	}

	private function field_select( $args ){
		?>
        <select id="customify-met-field-<?php echo esc_attr( $args['name'] ); ?>" name="<?php echo esc_attr( $this->get_name( $args ) ); ?>">
            <?php if ( $args['show_default'] ){ ?>
            <option value=""><?php echo $args['default_label'] ? $args['default_label'] : __( 'Default', 'customify' ); ?></option>
            <?php } ?>
			<?php foreach( $args['choices'] as $k => $label ) { ?>
                <option <?php selected( $this->get_value( $args ),  $k ); ?> value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
			<?php } ?>
        </select>
		<?php
	}
	private function field_multiple_checkbox( $args ){
		?>
        <?php foreach( $args['choices'] as $k => $label ) { ?>
           <p class="field-p"><label><input type="checkbox" name="<?php echo esc_attr( $this->get_name( $k ) ); ?>" <?php checked( $this->get_value( $k ),  1 ); ?> value="1"/> <?php echo $label; // WPCS: XSS OK.  ?></label></p>
        <?php } ?>
		<?php
	}

	private function field_checkbox( $args ){
		$args = wp_parse_args( $args, array(
            'checkbox_label' => ''
        ) )
		?>
        <label><input type="checkbox" id="<?php echo esc_attr( $this->get_filed_id( $args ) ); ?>" name="<?php echo esc_attr( $this->get_name( $args ) ); ?>" <?php checked( $this->get_value( $args ),  1 ); ?> value="1"/> <?php echo $args['checkbox_label']; // WPCS: XSS OK.  ?></label>
		<?php
	}

	function field_image( $args ){
	    $value = $this->get_value( $args );
		$image = wp_parse_args( $value, array(
			'url' => '',
			'id' => '',
		) );

		$args =  wp_parse_args( $args, array(
            'select_label' => __( 'Select image', 'customify-pro' ),
            'remove_label' => __( 'Remove', 'customify-pro' ),
        ) );

		$img = Customify()->get_media( $image );
	    ?>
        <span class="customify-mt-media <?php echo ( $img ) ? 'attachment-added': 'no-attachment'; ?>">
            <span class="customify-image-preview">
                <?php if ( $img ) {
                    echo '<img src="'.esc_url( $img ).'" alt=""/>';
                } ?>
            </span>
            <a class="customify--add" href="#"><?php echo $args['select_label'];// WPCS: XSS OK  ?></a>
            <a class="customify--remove" href="#"><?php echo $args['remove_label'];// WPCS: XSS OK  ?></a>
            <input type="hidden"  name="<?php echo esc_attr( $this->get_name( $args ) ); ?>[id]" value="<?php echo esc_attr( $image['id'] ); ?>" class="widefat attachment-id">
            <input type="hidden"  name="<?php echo esc_attr( $this->get_name( $args ) ); ?>[url]" value="<?php echo esc_attr( $image['url'] ); ?>" class="widefat attachment-url">
        </span>
        <?php
    }


}