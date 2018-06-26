/**
 * Gruntfile for running jshint (javascript error checking tool) and minifying js files.
 *
 * This file configures tasks to be run by Grunt
 * http://gruntjs.com/ for the current plugin.
 *
 *
 * Requirements:
 * -------------
 * nodejs, npm, grunt-cli.
 *
 * Installation:
 * -------------
 * node and npm: instructions at http://nodejs.org/
 *
 * grunt-cli: `[sudo] npm install -g grunt-cli`
 *
 * node dependencies: run `npm install` in the root directory.
 *
 * Usage:
 * ------
 * Call tasks from the plugin root directory. Default behaviour
 * (calling only `grunt`) is to run the watch task detailed below.
 *
 *
 * Porcelain tasks:
 * ----------------
 * The nice user interface intended for everyday use. Provide a
 * high level of automation and convenience for specific use-cases.
 *
 *
 * grunt amd     Create the Asynchronous Module Definition JavaScript files.  See: MDL-49046.
 *               Done here as core Gruntfile.js currently *nix only.
 *
 * Plumbing tasks & targets:
 * -------------------------
 * Lower level tasks encapsulating a specific piece of functionality
 * but usually only useful when called in combination with another.
 *
 *
 *
 * @package blocks
 * @subpackage news_slider
 * @author M Solanki - {@link https://moodle.org/user/profile.php?id=2227655}
 * @author Based on code originally written by Gareth J Barnard, Joby Harding, Bas Brands, David Scotson and many other contributors.
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

module.exports = function(grunt) { // jshint ignore:line

    // Import modules.
    var path = require('path'); // jshint ignore:line

    // Theme Bootstrap constants.
    var MOODLEURLPREFIX = grunt.option('urlprefix') || '',
        THEMEDIR        = path.basename(path.resolve('.'));

    // PHP strings for exec task.
    var moodleroot = path.dirname(path.dirname(__dirname)), // jshint ignore:line
        dirrootopt = grunt.option('dirroot') || process.env.MOODLE_DIR || ''; // jshint ignore:line

    // Allow user to explicitly define Moodle root dir.
    if ('' !== dirrootopt) {
        moodleroot = path.resolve(dirrootopt);
    }

    // Production / development.
    var build = grunt.option('build') || 'd'; // Development for 'watch' task.

    if ((build != 'p') && (build != 'd')) {
        build = 'p';
        console.log('-build switch only accepts \'p\' for production or \'d\' for development,');
        console.log('e.g. -build=p or -build=d.  Defaulting to development.');
    }

    var PWD = process.cwd(); // jshint ignore:line

    var decachephp = '../../admin/cli/purge_caches.php';

    grunt.initConfig({
        exec: {
            decache: {
                cmd: 'php "' + decachephp + '"',
                callback: function(error) {
                    // Warning: Be careful when executing this task.  It may give
                    // file permission errors accessing Moodle because of the directory permissions 
                    // for configured Moodledata directory if this is run as root.
                    // The exec process will output error messages.
                    // Just add one to confirm success.
                    if (!error) {
                        grunt.log.writeln("Moodle theme cache reset.");
                    }
                }
            }
        },
        watch: {
            // Unused at present.
        },
        jshint: {
            options: {jshintrc: moodleroot + '/.jshintrc'},
            files: ['amd/src/*.js']
        },
        uglify: {
            options: {
                preserveComments: 'some'
            },
            dynamic_mappings: {
                files: grunt.file.expandMapping(
                    ['**/src/*.js', '!**/node_modules/**'],
                    '',
                    {
                        cwd: PWD,
                        rename: function(destBase, destPath) {
                            destPath = destPath.replace('src', 'build');
                            destPath = destPath.replace('.js', '.min.js');
                            destPath = path.resolve(PWD, destPath);
                            return destPath;
                        }
                    }
                )
            }
        }
    });

    // Load core tasks.
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint');

    // Register tasks.
    grunt.registerTask("default", ["jshint", "uglify"]);
    grunt.registerTask("amd", ["jshint", "uglify"]);
};
