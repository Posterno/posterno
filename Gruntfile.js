module.exports = function (grunt) {

	// Load multiple grunt tasks using globbing patterns
	require('load-grunt-tasks')(grunt);

	// Project configuration.
	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		// Setting folder templates.
		dirs: {
			css: 'assets/css',
			fonts: 'assets/font',
			images: 'assets/imgs',
			js: 'assets/js'
		},

		sass: {
			frontend: {
				options: {
					sourcemap: 'none'
				},
				files: [{
					expand: true,
					cwd: '<%= dirs.css %>/sass/frontend/',
					src: ['*.scss'],
					dest: '<%= dirs.css %>/frontend/',
					ext: '.css'
				}]
			},
			admin: {
				options: {
					sourcemap: 'none'
				},
				files: [{
					expand: true,
					cwd: '<%= dirs.css %>/sass/admin/',
					src: ['*.scss'],
					dest: '<%= dirs.css %>/admin/',
					ext: '.css'
				}]
			},
		},

		cssmin: {
			options: {
				mergeIntoShorthands: false,
			},
			admin: {
				files: [{
					expand: true,
					cwd: '<%= dirs.css %>/admin/',
					src: ['*.css', '!*.min.css'],
					dest: '<%= dirs.css %>/admin/',
					ext: '.min.css'
				}]
			},
			frontend: {
				files: [{
					expand: true,
					cwd: '<%= dirs.css %>/frontend/',
					src: ['*.css', '!*.min.css'],
					dest: '<%= dirs.css %>/frontend/',
					ext: '.min.css'
				}]
			}
		},

		uglify: {
			options: {
				ie8: true,
				parse: {
					strict: false
				},
				output: {
					comments: /@license|@preserve|^!/
				}
			},
			admin: {
				files: [{
					expand: true,
					cwd: '<%= dirs.js %>/src/admin/',
					src: [
						'*.js',
						'!*.min.js'
					],
					dest: '<%= dirs.js %>/admin/',
					ext: '.min.js'
				}]
			},
			frontend: {
				files: [{
					expand: true,
					cwd: '<%= dirs.js %>/src/frontend/',
					src: [
						'*.js',
						'!*.min.js'
					],
					dest: '<%= dirs.js %>/frontend/',
					ext: '.min.js'
				}]
			},
		},

		checktextdomain: {
			options: {
				text_domain: 'posterno',
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,3,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d',
					' __ngettext:1,2,3d',
					'__ngettext_noop:1,2,3d',
					'_c:1,2d',
					'_nc:1,2,4c,5d'
				]
			},
			files: {
				src: [
					'**/*.php', // Include all files
					'!node_modules/**', // Exclude node_modules/
					'!build/**', // Exclude build/
					'!vendor/**',
				],
				expand: true
			}
		},

		addtextdomain: {
			target: {
				files: {
					src: [
						'*.php',
						'**/*.php',
						'!node_modules/**',
						'!tests/**',
						'!vendor/**',
						'!src/**',
						'!.sass-cache/**',
						'!public/**',
						'!assets/**'
					]
				}
			}
		},

		makepot: {
			target: {
				options: {
					domainPath: '/languages/', // Where to save the POT file.
					exclude: ['build/.*', 'vendor/.*', 'node_modules/.*'],
					mainFile: 'posterno.php', // Main project file.
					potFilename: 'posterno.pot', // Name of the POT file.
					potHeaders: {
						poedit: true, // Includes common Poedit headers.
						'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
					},
					type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
					updateTimestamp: true, // Whether the POT-Creation-Date should be updated without other changes.
					processPot: function (pot, options) {
						pot.headers['report-msgid-bugs-to'] = 'https://posterno.com/';
						pot.headers['last-translator'] = 'WP-Translations (http://wp-translations.org/)';
						pot.headers['language-team'] = 'WP-Translations <wpt@wp-translations.org>';
						pot.headers['language'] = 'en_US';
						var translation, // Exclude meta data from pot.
							excluded_meta = [
								'Plugin Name of the plugin/theme',
								'Plugin URI of the plugin/theme',
								'Author of the plugin/theme',
								'Author URI of the plugin/theme'
							];
						for (translation in pot.translations['']) {
							if ('undefined' !== typeof pot.translations[''][translation].comments.extracted) {
								if (excluded_meta.indexOf(pot.translations[''][translation].comments.extracted) >= 0) {
									console.log('Excluded meta: ' + pot.translations[''][translation].comments.extracted);
									delete pot.translations[''][translation];
								}
							}
						}
						return pot;
					}
				}
			}
		},

		// Clean up build directory
		clean: {
			main: ['build/**'],
			composer: ['build/<%= pkg.version %>/vendor/nikic/fast-route/test']
		},

		// Copy the plugin into the build directory
		copy: {
			main: {
				src: [
					'assets/**',
					'includes/**',
					'languages/**',
					'templates/**',
					'vendor/**',
					'dist/**',
					'!dist/favicon.ico',
					'!dist/index.html',
					'*.php',
					'*.txt'
				],
				dest: 'build/<%= pkg.version %>/'
			}
		},

		// Compress build directory into <name>.zip and <name>-<version>.zip
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './build/<%= pkg.name %>.zip'
				},
				expand: true,
				cwd: 'build/<%= pkg.name %>/',
				src: ['**/*'],
				dest: '<%= pkg.name %>/'
			}
		},

		watch: {
			css: {
				files: [
					'<%= dirs.css %>/sass/admin/*.scss',
					'<%= dirs.css %>/sass/frontend/*.scss'
				],
				tasks: ['sass', 'cssmin'],
			},
			js: {
				files: [
					'<%= dirs.js %>/src/admin/*.js',
					'<%= dirs.js %>/src/frontend/*.js',
				],
				tasks: ['uglify'],
				options: {
					debounceDelay: 500
				}
			}
		},

	});

	grunt.loadNpmTasks('grunt-contrib-watch');

	// Build task(s).
	grunt.registerTask('build', ['cssmin', 'uglify', 'force:checktextdomain', 'makepot', 'clean', 'copy', 'clean:composer', 'compress']);

};
