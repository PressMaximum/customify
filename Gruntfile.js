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
            option: {
                sourceMap: true
            },
            dist: {
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
    grunt.loadNpmTasks( 'grunt-sass' );
    grunt.loadNpmTasks( 'grunt-contrib-cssmin' );


    // Register tasks
    grunt.registerTask('default', [
        'watch',
        'css'
    ]);
    grunt.registerTask( 'css', [
        'sass',
        'postcss',
        //'cssmin'
    ]);

};