module.exports = function( grunt ) {
    'use strict';
    var pkgInfo = grunt.file.readJSON('package.json');
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

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
                    'assets/css/admin/customizer/customizer.css',
                    'assets/css/admin/admin.css',
                    'assets/css/admin/dashboard.css'
                ]
            }
        },

        rtlcss: {
            options: {
                // rtlcss options
                config: {
                    preserveComments: true,
                    greedy: true
                },
                // generate source maps
                map: false
            },
            dist: {
                files: [
                    { // Front end compatibility
                        expand: true,
                        cwd: '',
                        src: [
                            '*.css',
                            '!*.min.css',
                            '!*-rtl.css'
                        ],
                        dest: '',
                        ext: '-rtl.css'
                    },
                    { // Front end compatibility
                        expand: true,
                        cwd: 'assets/css/compatibility',
                        src: [
                            '*.css',
                            '!*.min.css',
                            '!*-rtl.css'
                        ],
                        dest: 'assets/css/compatibility',
                        ext: '-rtl.css'
                    }
                ]
            }
        },

        // SASS
        sass: {
            options: {
                precision: 10,
                sourcemap: 'auto'
            },
            dist: {
                options: {
                    style: 'expanded'
                },

                files: [
                    {
                    'style.css': 'assets/sass/site/style.scss',
                    'assets/css/admin/customizer/customizer.css': 'assets/sass/admin/customizer/customizer.scss',
                    'assets/css/admin/metabox.css': 'assets/sass/admin/metabox.scss',
                    'assets/css/admin/dashboard.css': 'assets/sass/admin/dashboard.scss'
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
                    },
                    // Editor style
                    {
                        expand: true,
                        cwd: 'assets/css/admin',
                        src: ['*.css', '!*.min.css'],
                        dest: 'assets/css/admin',
                        ext: '.min.css'
                    }
                ]
            }
        },

        uglify: {
            my_target: {
                files: [
                    {
                        'assets/js/theme.min.js': ['assets/js/theme.js'],
                        'assets/js/jquery.fitvids.min.js': ['assets/js/jquery.fitvids.js']
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
                    },
                    {
                        expand: true,
                        cwd: '.',
                        src: ['assets/js/admin/*.js', '!assets/js/admin/*.min.js'],
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
                    'sass',
                    'css'
                ]
            }
            /*
            ,
            scripts: {
                files: [
                    'assets/js/*.js',
                    'assets/js/compatibility/*.js',
                    'assets/js/customizer/*.js'
                ],
                tasks: ['uglify']
            }
            */
        },

        copy: {
            main: {
                options: {
                    mode: true
                },
                src: [
                    '**',
                    '!node_modules/**',
                    '!build/**',
                    '!css/sourcemap/**',
                    '!.git/**',
                    '!bin/**',
                    '!.gitlab-ci.yml',
                    '!bin/**',
                    '!tests/**',
                    '!phpunit.xml.dist',
                    '!*.sh',
                    '!*.map',
                    '!Gruntfile.js',
                    '!package.json',
                    '!.gitignore',
                    '!phpunit.xml',
                    '!README.md',
                    '!sass/**',
                    '!codesniffer.ruleset.xml',
                    '!vendor/**',
                    '!composer.json',
                    '!composer.lock',
                    '!package-lock.json',
                    '!phpcs.xml.dist'
                ],
                dest: 'customify/'
            }
        },

        compress: {
            main: {
                options: {
                    archive: 'customify-' + pkgInfo.version + '.zip',
                    mode: 'zip'
                },
                files: [
                    {
                        src: [
                            './customify/**'
                        ]

                    }
                ]
            }
        },

        clean: {
            main: ["customify"],
            zip: ["*.zip"]

        },

        makepot: {
            target: {
                options: {
                    domainPath: '/',
                    potFilename: 'languages/customify.pot',
                    potHeaders: {
                        poedit: true,
                        'x-poedit-keywordslist': true
                    },
                    type: 'wp-theme',
                    updateTimestamp: true
                }
            }
        },

        addtextdomain: {
            options: {
                textdomain: 'customify'
            },
            target: {
                files: {
                    src: [
                        '*.php',
                        '**/*.php',
                        '!node_modules/**',
                        '!php-tests/**',
                        '!bin/**',
                    ]
                }
            }
        },

        bumpup: {
            options: {
                updateProps: {
                    pkg: 'package.json'
                }
            },
            file: 'package.json'
        },

        replace: {
            theme_main: {
                src: ['style.css', 'assets/sass/site/style.scss'],
                overwrite: true,
                replacements: [
                    {
                        from: /Version: \bv?(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?(?:\+[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\b/g,
                        to: 'Version: <%= pkg.version %>'
                    }
                ]
            }
        }


    });

    // Load NPM tasks to be used here
    grunt.loadNpmTasks( 'grunt-contrib-watch' );
    grunt.loadNpmTasks( 'grunt-postcss' );
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-rtlcss');

    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-wp-i18n');
    grunt.loadNpmTasks('grunt-bumpup');
    grunt.loadNpmTasks('grunt-text-replace');


    // Register tasks
    grunt.registerTask('default', [
        'watch',
        'css'
    ]);
    grunt.registerTask( 'css', [
        'sass'
        //'rtlcss'
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

    // To release new version just runt 2 commands below
    // Re create everything: grunt release --ver=<version_number>
    // Zip file installable: grunt zipfile

    grunt.registerTask('zipfile', ['clean:zip', 'copy:main', 'compress:main', 'clean:main']);
    grunt.registerTask('release', function (ver) {
        var newVersion = grunt.option('ver');
        if (newVersion) {
            // Replace new version
            newVersion = newVersion ? newVersion : 'patch';
            grunt.task.run('bumpup:' + newVersion);
            grunt.task.run('replace');

            // i18n
            grunt.task.run(['addtextdomain', 'makepot']);
            // re create css file and min
            grunt.task.run([ 'css', 'postcss', 'uglify', 'rtlcss', 'cssmin' ]);
        }
    });

    grunt.registerTask('re-css', function (ver) {
        grunt.task.run([ 'css', 'postcss', 'uglify', 'rtlcss', 'cssmin' ]);
    });



};