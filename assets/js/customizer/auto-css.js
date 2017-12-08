var AutoCSS = window.AutoCSS || null;

( function( $, api ) {

    AutoCSS = function(){
        this.values = {};
        this.devices = [ 'desktop', 'tablet', 'mobile' ];
    };

    AutoCSS.prototype.fonts = {};
    AutoCSS.prototype.subsets = {};
    AutoCSS.prototype.variants = {};
    AutoCSS.prototype.media_queries = {
        all: '%s',
        desktop: '@media screen and (min-width: 64em) { %s }',
        tablet : '@media screen and (max-width: 64em) and (min-width: 35.5em) { %s }',
        mobile: '@media screen and (max-width: 35.5em) { %s }',
    };

    AutoCSS.prototype.css = {
        all: '',
        desktop: '',
        tablet : '',
        mobile: ''
    };

    AutoCSS.prototype.reset = function(){
        this.fonts = {};
        this.subsets = {};
        this.variants = {};
        this.css = {
            all: '',
            desktop: '',
            tablet : '',
            mobile: ''
        };
    };

    AutoCSS.prototype.encodeValue =function( value ){
        return encodeURI( JSON.stringify( value ) )
    };
    AutoCSS.prototype.decodeValue = function( value ){
        return JSON.parse( decodeURI( value ) );
    };
    AutoCSS.prototype.run = function(){
        this.values = api.get();
        this.reset();

       // console.log( 'NEW CUSTOMIZE VALUES', this.values );
        var that = this;
        _.each( Customify_Preview_Config.fields, function( field ){
            if ( field.selector && field.css_format ) {
                switch (field.type) {
                    case 'css_ruler':
                        that.css_ruler(field);
                        break;
                    case 'slider':
                        that.slider(field);
                        break;
                    case 'color':
                        that.color(field);
                        break;
                    case 'text_align':
                    case 'text_align_no_justify':
                        that.text_align( field );
                        break;
                    case 'font':
                        that.font(field);
                        break;
                    default:
                        if ( !_.isUndefined( field.css_format ) && field.css_format === 'background') {
                            that.background(field);
                        } else if (!_.isUndefined( field.css_format ) && field.css_format === 'typography') {
                            that.typography(field);
                        } else {
                            that.maybe_devices_setup( field, 'setup_default' );
                        }
                }
            }

        } );

        var css_code = '';
        var i = 0;
        _.each( that.css, function( code, device ){
            var new_line = '';
            if ( i > 0 ) {
                new_line=  "\r\n\r\n\r\n\r\n\r";
            }
            css_code += new_line + that.media_queries[ device ].replace(/%s/g, code ) + "\r\n";
            i++;
        } );

        var url = that.get_google_fonts_url();
        if ( url ) {
            css_code = "\r\n@import url('"+url+"');\r\n\r\n"+css_code;
        }

        css_code = css_code.trim();
        if ( $( '#customify-style-inline-css' ).length <= 0 ) {
            $( 'head' ).append( "<style id='customify-style-inline-css' type='text/css'></style>" )
        }
        $( '#customify-style-inline-css' ).html( css_code );

      /// console.log( 'CSS' , css_code );
    };


    AutoCSS.prototype.get_setting = function(name, device, key  ){
        if ( _.isUndefined( device ) ) {
            device = 'desktop';
        }
        if ( _.isUndefined( key  ) ) {
            key = false;
        }

        var get_value = null;
        var value;
        var df = false;
        if ( !_.isUndefined( Customify_Preview_Config.fields['setting|'+name ] ) ) {
            var field = Customify_Preview_Config.fields['setting|'+name ];
            df = !_.isUndefined( field.default ) ? field.default : false;
        }

        value = !_.isUndefined( this.values[ name ] ) ? this.values[ name ] : df;
        

        if ( _.isString( value ) ) {
            try {
                var decodeValue = this.decodeValue(value);
                if ( !_.isNull( decodeValue ) ) {
                    value = decodeValue;
                }
            } catch (e) {

            }
        }

        if ( ! key ) {
            if ( device !== 'all' ) {
                if ( _.isObject( value ) && !_.isUndefined( value[ device ] ) ) {
                    get_value =  value[ device ];
                }
            } else {
                get_value = value;
            }
        } else {
            var value_by_key = _.isUndefined( value[ key ] ) ?  value[ key ]: false;
            if ( device !== 'all' && _.isObject( value_by_key ) ) {
                if ( _.isObject( value_by_key ) && !_.isUndefined( value_by_key[ device ] ) ) {
                    get_value =  value_by_key[ device ];
                } else {
                    get_value =  value_by_key;
                }
            } else {
                get_value = value_by_key;
            }
        }

        


        return get_value;
    };

    AutoCSS.prototype.get_google_fonts_url = function(){
        var url = '//fonts.googleapis.com/css?family=';
        var s = '';
        var that = this;
        if ( _.isEmpty( that.fonts ) ) {
            return false;
        }
        _.each ( that.fonts, function( font_name ){
            if ( s ){
                s += '|';
            }
            s += font_name.replace(/\s/g, '+');
            var v = {};
            if ( !_.isUndefined( that.variants[ font_name ] ) ) {

                _.each( that.variants[ font_name ], function( _v ){
                    if ( _v !== 'regular' ) {
                        switch ( _v ) {
                            case 'italic':
                                v[_v] = '400i';
                                break;
                            default:
                                if ( _.isString( _v ) ) {
                                    v[_v] = _v.replace( 'italic', 'i');
                                } else {
                                    v[_v] = _v;
                                }

                        }

                    }
                } )
            }

            if ( ! _.isEmpty( v ) ) {
                s +=  ':'+that.join( v, ',' );
            }

        } );
        url += s;
        if ( ! _.isEmpty( that.subsets ) ) {
            url +='&subset='+that.join( that.subsets , ',' );
        }
        return url;
    };

    AutoCSS.prototype.join = function( object, glue ){

        if( _.isUndefined( glue ) ) {
            glue = '';
        }
        if( _.isArray( object ) ) {
            return object.join( glue );
        }

        if ( !_.isObject( object ) || _.isEmpty( object ) ) {
            return '';
        }

        var array = _.values( object );
        return array.join( glue );

    };

    AutoCSS.prototype.str_value = function( value, format ){
        var find = '{{value}}';
        var reg = new RegExp(find, 'g');
        return format.replace( reg, value );
    };

    AutoCSS.prototype.setup_color = function( value, format ){
        if ( format ) {
            if ( value ) {
                return this.str_value( value, format );
            }
        }
        return false;
    };

    AutoCSS.prototype.setup_slider = function ( value, format ){
        if ( ! _.isObject( value ) ) {
            value = {};
        }
        value = _.defaults( value, {
            unit: 'px',
            value: null
        });

        if ( ! value.unit ) {
            value.unit = 'px';
        }

        var c = '';
        var v = '';

        if ( format ) {
            if ( value.value ) {
                v = value.value + value.unit;
                c = this.str_value( v, format );
            }
        }
        return c;
    };

    AutoCSS.prototype.setup_default = function( value, format ){
        if ( format ) {
            if ( value ) {
                return this.str_value( value, format );
            }
        }
        return false;
    };

    AutoCSS.prototype.setup_css_ruler = function ( value, format ){
        if ( ! _.isObject( value ) ) {
            value = {};
        }
        value = _.defaults( value, {
            unit : '',
            top: '',
            right: '',
            bottom: '',
            left: ''
        });

        if ( ! _.isUndefined( value.unit ) ) {
            value.unit = 'px';
        }

        format = _.defaults( format, {
            top: '',
            right: '',
            bottom: '',
            left: ''
        } );
        var that = this;

        var  code = {};
        _.each( format, function( string, pos ){
            var v = value[ pos ];
            if ( v && string ) {
                if ( string ) {
                    v = v + value['unit'];
                    code[ pos ] = that.str_value( v, string );
                }
            }
        } );

        return that.join( code, "\n\t" );
    };


    AutoCSS.prototype.setup_text_align = function( value, format ) {
        if ( format  ) {
            if ( value ) {
                return this.str_value( value, format );
            }
        }
        return false;
    };

    AutoCSS.prototype.sanitize_color = function ( color ){
       return color;
    };

    AutoCSS.prototype.sanitize_media = function ( value ) {
        if ( ! _.isObject( value ) ) {
            value = {};
        }
        return _.defaults( value, {
            id: null,
            url: null,
            mime: null
        } );
    };

    AutoCSS.prototype.maybe_devices_setup = function( field, call_back, values, no_selector ) {
        var code = '';
        var code_array = {};
        var has_device = false;
        var format = !_.isEmpty( field.css_format ) ? field.css_format : false;
        var that = this;

        if ( _.isUndefined( no_selector ) ) {
            no_selector = false;
        }

        if ( _.isUndefined( values ) ) {
            values = false;
        }

        if ( ! _.isUndefined( field.device_settings ) && field.device_settings ) {
            has_device = true;
            _.each( that.devices, function( device ){
                var value = null;
                if ( _.isEmpty( values ) ) {
                    value = that.get_setting( field.name, device );
                } else {
                    if ( ! _.isUndefined( values[ device ] ) ) {
                        value = values[ device ];
                    }
                }

                var _c = false;
                if ( that[call_back] ){
                    _c = that[ call_back ]( value, format );
                }

                if ( _c ) {
                    code_array[ device ] = _c;
                }
            } );
        } else {
            if ( _.isEmpty( values ) ) {
                values = that.get_setting( field.name );
            }
            if ( that[call_back] ){
                code = that[ call_back ]( values, format );
            }

            code_array.no_devices = code;
        }

        if ( _.isEmpty( code_array ) ) {
           // return false;
        }

        code = '';
        if ( no_selector ) {
            return code_array;
        } else {
            if ( has_device ) {
                _.each( that.devices, function( device ){
                    if ( !_.isUndefined( code_array[ device ] ) ) {
                        var _c = code_array[ device ];
                        if( _c ) {
                            that.css[ device ] += "\r\n"+field.selector+" {\r\n\t"+_c+"\r\n}\r\n" ;
                        }
                    }
                } );
            } else {
                if ( code_array.no_devices ) {
                    that.css.all += "\r\n"+field.selector+"  {\r\n\t"+code_array.no_devices+"\r\n}\r\n";
                }
            }
        }
        return code;
    };

    AutoCSS.prototype.setup_font = function ( value ){
        if( ! _.isObject( value ) ) {
            value = {};
        }
        value = _.defaults( value, {
            font: null,
            type: null,
            variant: null,
            subsets: null,
        });

        if ( ! value.font ) {
            return '';
        }

        if ( value.type == 'google' ){
            this.fonts[ value.font ] = value.font;
            if ( value.variant ) {
                if ( _.isUndefined( this.variants[ value.font ] ) ) {
                    this.variants[ value.font ] = {};
                    if ( _.isString( value.variant )  ) {
                        var vr;
                        vr ={};
                        vr[ value.variant ] =  value.variant;
                        this.variants[ value.font ] = _.extend( this.variants[ value.font ] , vr ) ;
                    } else {
                        this.variants[ value.font ] = _.extend( this.variants[ value.font ] , value.variant ) ;
                    }
                }
            }

            if ( value.subsets ) {
                this.subsets = _.extend( this.subsets, value.subsets ) ;
            }
        }

        return "font-family: \""+value.font+"\";";
    };

    AutoCSS.prototype.font = function( field, values ){
        var code = '';
        var that = this;
        if ( field.device_settings ) {

            _.each( this.devices, function( device ) {
                var value = null;
                if ( _.isEmpty( values ) ) {
                    value = that.get_setting( field.name, device );
                } else {
                    if ( !_.isUndefined( values[ device ] ) ) {
                        device = values[ device ];
                    }
                }
                var _c = that.setup_font( value );
                if ( _c ) {
                    that.css[ device ] = "\r\n"+field.selector+" {\r\n\t"+_c+"\r\n}\r\n";
                    if ( 'desktop' === device ) {
                        code += "\r\n"+field.selector+" {\r\n\t"+_c+"\r\n}";
                    } else {
                        code += "\r\n."+device+" "+field.selector+" {\r\n\t"+_c+"\r\n}\r\n";
                    }
                }
            } );

        } else {
            if ( _.isEmpty( values ) ) {
                values = that.get_setting( field.name );
            }
            code = that.setup_font( values );
            that.css[ 'all' ] += " "+field.selector+"  {\r\n\t"+code+"\r\n}\r\n";
            code += " "+field.selector+"  {\r\n\t"+code+"\r\n}\r\n";
        }

        return code;
    };


    AutoCSS.prototype.css_ruler = function( field ){
        return this.maybe_devices_setup( field, 'setup_css_ruler' );
    };

    AutoCSS.prototype.slider = function( field ){
        return this.maybe_devices_setup( field, 'setup_slider' );
    };

    AutoCSS.prototype.color = function( field ){
        return this.maybe_devices_setup( field, 'setup_color' );
    };

    AutoCSS.prototype.text_align = function( field ){
        return this.maybe_devices_setup( field, 'setup_text_align' );
    };

    AutoCSS.prototype.background = function( field ){
        return this.maybe_devices_setup( field, 'setup_background' );
    };

    AutoCSS.prototype.setup_background = function ( value ) {
        if ( ! _.isObject( value ) ) {
            value = {};
        }
        value =_.defaults( value, {
            color: null,
            image: null,
            position: null,
            cover: null,
            repeat: null,
            attachment: null
        } );

        var css = {};
        var color = this.sanitize_color( value.color );
        if ( color ) {
            css.color = "background-color: "+color+";";
        }

        var image = this.sanitize_media( value.image );

        if ( image.url ) {
            css.image = "background-image: url(\""+image.url+"\");";
        }

        switch ( value.position ) {
            case 'center':
                css.position = 'background-position: center center;';
                break;
            case 'top_left':
                css.position = 'background-position: top left;';
                break;
            case 'top_center':
                css.position = 'background-position: top center;';
                break;
            case 'top_right':
                css.position = 'background-position: top right;';
                break;
            case 'bottom_left':
                css.position = 'background-position: bottom left;';
                break;
            case 'bottom_center':
                css.position = 'background-position: bottom center;';
                break;
            case 'bottom_right':
                css.position = 'background-position: bottom right;';
                break;
            default:
                css.position = 'background-position: center center;';
        }

        switch ( value.repeat ) {
            case 'no-repeat':
                css.repeat = 'background-repeat: no-repeat;';
                break;
            case 'repeat-x':
                css.repeat = 'background-repeat: repeat-x;';
                break;
            case 'repeat-y':
                css.repeat = 'background-repeat: repeat-y;';
                break;
            default:

        }

        switch ( value.attachment ) {
            case 'scroll':
                css.attachment = 'background-attachment: scroll;';
                break;
            case 'fixed':
                css.attachment = 'background-attachment: fixed;';
                break;
            default:
        }

        if ( value.cover ) {
            css.cover = '-webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover;';
            css.attachment = 'background-attachment: fixed;';
        }

        return this.join( css, "\n\t" );
    };

    AutoCSS.prototype.setup_font_style = function ( value ){
        if ( ! _.isObject( value ) ) {
            value = {};
        }

        value = _.defaults( value, {
            b: null,
            i: null,
            u: null,
            s: null,
            t: null
        } );

        var css = {};
        if ( value['b'] ) {
            css['b'] = 'font-weight: bold;';
        }
        if ( value['i'] ) {
            css['i'] = 'font-style: italic;';
        }

        var decoration = {};
        if ( value['u'] ) {
            decoration['underline'] = 'underline';
        }

        if ( value['s'] ) {
            decoration['line-through'] = 'line-through';
        }

        if ( ! _.isEmpty( decoration ) ) {
            css['d'] = 'text-decoration: '+this.join( decoration, ' ' )+';';
        }

        if ( value['t'] ) {
            css['t'] = 'text-transform: uppercase;';
        }

        return this.join( css, "\r\n\t" );
    };

    AutoCSS.prototype.typography = function( field ){
        var values = this.get_setting( field.name, 'all' );

        var that = this;
        if ( ! _.isObject( values ) ) {
            values = {};
        }
        values = _.defaults( values, {
            font: null,
            font_style: null,
            font_size: null,
            line_height: null,
            letter_spacing: null,
            color: null,
        });


        console.log( 'TYPO Values font_size: '+field.name, values.font_size );


        
        var code = {};
        var fields = {};
        var devices_css = {};
        _.each( field.fields, function( f ){
            fields[ f.name ] = f;
        } );

        if ( ! _.isUndefined( fields.font ) ) {
            code.font = this.setup_font( values.font );
        }

        if ( ! _.isUndefined( fields.font_style ) ) {
            code.font_style = this.setup_font_style( values.font_style );
        }

        if ( ! _.isUndefined( fields.font_size ) ) {

            fields.font_size.css_format = 'font-size: {{value}};';
            var font_size_css = this.maybe_devices_setup( fields.font_size, 'setup_slider', values.font_size, true );
            if ( !_.isEmpty( font_size_css ) ) {
                if ( ! _.isUndefined( font_size_css.no_devices ) ) {
                    code.font_size = font_size_css.no_devices;
                } else {
                    _.each( font_size_css, function( _c, device ){
                        if ( device == 'desktop' ) {
                            code.font_size = _c;
                        } else {
                            if ( _.isUndefined( devices_css[ device ] )  ) {
                                devices_css[ device ] = {};
                            }
                            devices_css[ device ]['font_size'] = _c;
                        }
                    } );
                }
            }
        }

        if ( !_.isUndefined( fields.line_height ) ) {
            fields.line_height['css_format'] = 'line-height: {{value}};';
            var line_height_css = this.maybe_devices_setup( fields.line_height , 'setup_slider', values['line_height'], true);
            if ( ! _.isEmpty( line_height_css ) ) {
                if ( ! _.isUndefined( line_height_css['no_devices'] ) ) {
                    code['line_height'] = line_height_css['no_devices'];
                } else {
                    _.each( line_height_css, function( _c, device ) {
                        if ( device == 'desktop' ) {
                            code['line_height'] = _c;
                        } else {
                            if ( _.isUndefined( devices_css[ device ] ) ) {
                                devices_css[ device ] = {};
                            }
                            devices_css[ device ]['line_height'] = _c;
                        }
                    } );

                }
            }
        }

        if (  !_.isUndefined( fields.letter_spacing ) ) {
            fields['letter_spacing']['css_format'] = 'letter-spacing: {{value}};';
            var letter_spacing_cs = this.maybe_devices_setup(fields['letter_spacing'], 'setup_slider', values['letter_spacing'], true);
            if (letter_spacing_cs) {
                if ( !_.isUndefined( letter_spacing_cs['no_devices'] ) ) {
                    code['letter_spacing'] = letter_spacing_cs['no_devices'];
                } else {
                    _.each( letter_spacing_cs, function( _c, device ){
                        if ( device == 'desktop' ) {
                            code['letter_spacing'] = _c;
                        } else {
                            if ( _.isUndefined( devices_css[ device ] ) ) {
                                devices_css[ device ] = {};
                            }
                            devices_css[ device ]['letter_spacing'] = _c;
                        }
                    } );
                }
            }
        }

        if (  !_.isUndefined(fields['color'] ) ) {
            var _c = this.setup_color(values['color'], 'color: {{value}}; text-decoration-color: {{value}};');
            if ( _c ) {
                code['color'] = _c;
            }
        }
        _.each( devices_css, function( els, device ){
            that.css[device] += " "+field['selector']+" {\r\n\t"+that.join( els, "\r\n\t" )+"\r\n}";
        } );

        that.css['all'] += " "+field['selector']+" {\r\n\t"+that.join( code, "\r\n\t" )+"\r\n}";
    };

    //console.log( 'Customify_Preview_Config', Customify_Preview_Config );

    var AutoCSSInit = new AutoCSS();

    api.bind( 'preview-ready', function() {
        AutoCSSInit.run();
    });

    api.bind( 'change', function(){
        //console.log( 'C Change' );
        AutoCSSInit.run();
    } );


} )( jQuery, wp.customize );