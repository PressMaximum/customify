module.exports = function( grunt ) {
    'use strict';

    grunt.initConfig({

        // Autoprefixer.
        postcss: {
            options: {
                processors: [
                    require( 'autoprefixer' )({
                        browsers: [
                            '> 0.1%',
                            'ie 8',
                            'ie 9'
                        ]
                    })
                ]
            },
            dist: {
                src: [
                    'style.css',
                    'assets/css/admin/customizer/customizer.css'
                ]
            }
        },

        // SASS
        sass: {
            dist: {
                options: {
                    style: 'expanded'
                },

                files: {
                    'style.css': 'assets/sass/site/style.scss',
                    'assets/css/admin/customizer/customizer.css': 'assets/sass/admin/customizer/customizer.scss'
                }
            }
        },

        // Minify all css files.
        cssmin: {
            main: {
                files: {
                    'style.css': ['style.css']
                }
            }
        },

        // Watch changes for assets.
        watch: {
            css: {
                files: [
                    'assets/sass/site/*.scss',
                    'assets/sass/site/**/*.scss',
                    'assets/sass/admin/*.scss',
                    'assets/sass/admin/**/*.scss'
                ],
                tasks: [
                    'sass',
                    'css'
                ]
            }
        },

    });

    // Load NPM tasks to be used here
    grunt.loadNpmTasks( 'grunt-contrib-watch' );
    grunt.loadNpmTasks( 'grunt-postcss' );
    grunt.loadNpmTasks('grunt-contrib-sass');
    //grunt.loadNpmTasks( 'grunt-sass' );
    grunt.loadNpmTasks( 'grunt-contrib-cssmin' );


    // Register tasks
    grunt.registerTask('default', [
        'watch',
        'css'
    ]);
    grunt.registerTask( 'css', [
        'sass',
        //'postcss',
        //'cssmin'
    ]);



    // Update google Fonts
    grunt.registerTask('google-fonts', function () {
        var done = this.async();
        var request = require('request');
        var fs = require('fs');

        request('https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyDN4eR6IPflX0QhU1UOOHjv71-2KY3BQwA', function (error, response, body) {

            if (response && response.statusCode == 200) {

                var fonts = {};
                JSON.parse(body).items.forEach( function( font ){
                    fonts[font.family] =  {
                        family: font.family,
                        category: font.category,
                        variants: font.variants,
                        subsets: font.subsets
                    };
                } );

                fs.writeFile('assets/fonts/google-fonts.json', JSON.stringify(fonts, undefined, 4), function (err) {
                    if (! err ) {
                        console.log("Google Fonts Updated!");
                    }
                });
            }

        });

    });


};