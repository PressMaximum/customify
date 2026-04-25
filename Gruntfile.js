module.exports = function (grunt) {
    'use strict';
    var pkgInfo = grunt.file.readJSON('package.json');
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        copy: {
            main: {
                options: {
                    mode: true
                },
                src: [
                    '**',
                    '!node_modules/**',
                    '!src/**',
                    '!css/sourcemap/**',
                    '!css/admin/*.map',
                    '!css/admin/customizer/*.map',
                    '!css/compatibility/*.map',
                    '!.git/**',
                    '!.claude/**',
                    '!bin/**',
                    '!.gitlab-ci.yml',
                    '!bin/**',
                    '!tests/**',
                    '!phpunit.xml.dist',
                    '!*.sh',
                    '!*.map',
                    '!*.css.map',
                    '!Gruntfile.js',
                    '!webpack.config.js',
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
                    '!phpcs.xml.dist',
                    '!phpcs.xml'
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
                src: ['style.css'],
                overwrite: true,
                replacements: [
                    {
                        from: /Version: \bv?(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?(?:\+[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\b/g,
                        to: 'Version: <%= pkg.version %>'
                    }
                ]
            }
        },

        phpcs: {
            application: {
                src: [
                    '*.php',
                    '**/*.php',
                    '!node_modules/**',
                    '!php-tests/**',
                    '!bin/**',
                ]
            },
            options: {
                bin: "phpcs",
                standard: 'phpcs.xml'
                //standard: 'WordPress'
            }
        },

        phpcbf: {
            options: {
                bin: 'phpcbf'
            },
            files: {
                src: [
                    '*.php',
                    '**/*.php',
                    '!node_modules/**',
                    '!php-tests/**',
                    '!bin/**',
                ]
            },
        },

    });


    // Load NPM tasks to be used here
    grunt.loadNpmTasks('grunt-contrib-watch');
    // grunt.loadNpmTasks('grunt-postcss');
    // grunt.loadNpmTasks('grunt-contrib-sass');
    // grunt.loadNpmTasks('grunt-contrib-cssmin');
    // grunt.loadNpmTasks('grunt-contrib-uglify');
    // grunt.loadNpmTasks('grunt-rtlcss');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-wp-i18n');
    grunt.loadNpmTasks('grunt-bumpup');
    grunt.loadNpmTasks('grunt-text-replace');

    grunt.loadNpmTasks('grunt-phpcs');
    grunt.loadNpmTasks('grunt-phpcbf');


    // Register tasks
    grunt.registerTask('default', [
        'watch',
        // 'css'
    ]);

    // Update google Fonts
    grunt.registerTask('google-fonts', function () {
        const done = this.async();
        const request = require('request');
        const fs = require('fs');

        request('https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyDN4eR6IPflX0QhU1UOOHjv71-2KY3BQwA', function (error, response, body) {

            if (response && response.statusCode == 200) {

                var fonts = {};
                JSON.parse(body).items.forEach(function (font) {
                    fonts[font.family] = {
                        family: font.family,
                        category: font.category,
                        variants: font.variants,
                        subsets: font.subsets
                    };
                });

                fs.writeFile('assets/fonts/google-fonts.json', JSON.stringify(fonts, undefined, 4), function (err) {
                    if (!err) {
                        console.log("Google Fonts Updated!");
                    }
                });
            }

        });

    });

    // Run `npm run build` from within grunt so the shipped zip always contains
    // a fresh build/ directory (both .min and non-min assets).
    grunt.registerTask('npm-build', 'Run `npm run build` to rebuild webpack assets.', function () {
        var done = this.async();
        var execSync = require('child_process').execSync;
        grunt.log.writeln('Running: npm run build');
        try {
            execSync('npm run build', { stdio: 'inherit' });
            done();
        } catch (err) {
            grunt.log.error('npm run build failed: ' + err.message);
            done(false);
        }
    });

    // To release new version just runt 2 commands below
    // Re create everything: grunt release --ver=<version_number>
    // Zip file installable: grunt zipfile

    grunt.registerTask('zipfile', ['clean:zip', 'npm-build', 'copy:main', 'compress:main', 'clean:main']);
    grunt.registerTask('release', function () {
        var newVersion = pkgInfo.version
        if (newVersion) {
            // Replace new version
            newVersion = newVersion ? newVersion : 'patch';
            grunt.task.run('bumpup:' + newVersion);
            grunt.task.run('replace');

            // i18n
            grunt.task.run(['zipfile']);
        }
    });

    

};
