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
            options: {
                precision: 10
            },
            dist: {
                options: {
                    style: 'expanded'
                },

                files: [
                    {
                    'style.css': 'assets/sass/site/style.scss',
                    'assets/css/admin/customizer/customizer.css': 'assets/sass/admin/customizer/customizer.scss'
                    },
                    {
                        expand: true,
                        cwd: 'assets/sass/site/compatibility',
                        src: '*.scss',
                        dest: 'assets/css/compatibility',
                        ext: '.css'
                    }
                ]
            }
        },

        // Minified all css files.
        cssmin: {
            target: {
                files: [
                    // Base style
                    {
                        expand: true,
                        cwd: '',
                        src: ['*.css', '!*.min.css'],
                        dest: '',
                        ext: '.min.css'
                    },

                    // Customizer style
                    {
                        expand: true,
                        cwd: 'assets/css/admin/customizer',
                        src: ['*.css', '!*.min.css'],
                        dest: 'assets/css/admin/customizer',
                        ext: '.min.css'
                    },

                    // Compatibility style
                    {
                        expand: true,
                        cwd: 'assets/css/compatibility',
                        src: ['*.css', '!*.min.css'],
                        dest: 'assets/css/compatibility',
                        ext: '.min.css'
                    }
                ]
            }
        },

        uglify: {
            my_target: {
                files: [
                    {
                        'assets/js/theme.min.js': ['assets/js/theme.js']
                    },
                    {
                        expand: true,
                        cwd: '.',
                        src: ['assets/js/compatibility/*.js', '!assets/js/compatibility/*.min.js'],
                        dest: '.',
                        rename: function (dst, src) {
                            // To keep the source js files and make new files as `*.min.js`:
                            return dst + '/' + src.replace('.js', '.min.js');
                        }
                    },
                    {
                        expand: true,
                        cwd: '.',
                        src: ['assets/js/customizer/*.js', '!assets/js/customizer/*.min.js'],
                        dest: '.',
                        rename: function (dst, src) {
                            // To keep the source js files and make new files as `*.min.js`:
                            return dst + '/' + src.replace('.js', '.min.js');
                        }
                    }
                ]
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
                    //'sass',
                    'css'
                ]
            },
            scripts: {
                files: [
                    'assets/js/*.js',
                    'assets/js/compatibility/*.js',
                    'assets/js/customizer/*.js'
                ],
                tasks: ['uglify']
            }
        }

    });

    // Load NPM tasks to be used here
    grunt.loadNpmTasks( 'grunt-contrib-watch' );
    grunt.loadNpmTasks( 'grunt-postcss' );
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
    grunt.loadNpmTasks('grunt-contrib-uglify');


    // Register tasks
    grunt.registerTask('default', [
        'watch',
        'css'
    ]);
    grunt.registerTask( 'css', [
        'sass'
        //'postcss',
        //'cssmin'
    ]);

    grunt.registerTask('before-release', [
        'css',
        'postcss',
        'cssmin',
        'uglify'
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