<?php

class _Beacon_Customizer_Layout_Builder {
    static $_instance;
    function __construct()
    {

        add_action( 'customize_controls_enqueue_scripts', array( $this, 'scripts' ) );
        add_action( 'customize_controls_print_footer_scripts', array( $this, 'template' ) );
    }

    function scripts(){
        wp_enqueue_script( 'jquery-ui-resizable' );
        wp_enqueue_script( '_beacon-customizer-builder', get_template_directory_uri() . '/assets/js/customizer/builder.js', array( 'customize-controls', 'jquery-ui-resizable' ), false, true );
    }

    static function get_instance(){
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance ;
    }

    function template(){
        ?>
        <div class="_beacon--customize-builder">
            <div class="_beacon--cb-inner">
                <div class="_beacon--cb-header">
                    <div class="_beacon--cb-devices-switcher">
                        <a href="#"><?php _e( 'Desktop', '_beacon' ); ?></a>
                        <a href="#"><?php _e( 'Tablet', '_beacon' ); ?></a>
                        <a href="#"><?php _e( 'Mobile', '_beacon' ); ?></a>
                    </div>
                    <div class="_beacon--cb-actions">
                        <a href="#"><?php _e( 'Settings', '_beacon' ); ?></a>
                        <a href="#"><?php _e( 'Templates', '_beacon' ); ?></a>
                        <a href="#"><?php _e( 'Close', '_beacon' ); ?></a>
                    </div>
                </div>

                <div class="_beacon--cb-body">
                    <div class="_beacon--row-top _beacon--cb-row">
                        <a class="_beacon--cb-row-settings" href="#">set</a>
                        <div class="_beacon--row-inner">
                            <div class="_beacon--cb-items">

                                <div class="_beacon--cb-item">
                                    <span class="resize-left"><--</span>
                                    Item 1
                                    <span class="resize-left">--></span>
                                </div>

                                <div class="_beacon--cb-item">
                                    <span class="resize-left"><--</span>
                                    Item 2
                                    <span class="resize-left">--></span>
                                </div>

                                <div class="_beacon--cb-item">
                                    <span class="resize-left"><--</span>
                                    Item 3
                                    <span class="resize-left">--></span>
                                </div>

                                <div class="_beacon--cb-item">
                                    <span class="resize-left"><--</span>
                                    Item 4
                                    <span class="resize-left">--></span>
                                </div>


                            </div>
                        </div>
                    </div>
                    <div class="_beacon--row-main _beacon--cb-row">
                        <a class="_beacon--cb-row-settings" href="#">set</a>
                        <div class="_beacon--row-inner">
                            <div class="_beacon--cb-items"></div>
                        </div>
                    </div>
                    <div class="_beacon--row-bottom _beacon--cb-row">
                        <a class="_beacon--cb-row-settings" href="#">set</a>
                        <div class="_beacon--row-inner">
                            <div class="_beacon--cb-items"></div>
                        </div>
                    </div>
                </div>

                <div class="_beacon--cb-footer">

                </div>

            </div>

        </div>
        <?php
    }



}

new _Beacon_Customizer_Layout_Builder();