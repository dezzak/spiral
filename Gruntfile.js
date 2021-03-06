module.exports = function(grunt) {
  require('time-grunt')(grunt);

  require('load-grunt-tasks')(grunt);

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    watch: {
      options: {
        spawn: false,
      },
      application: {
        files: ['**/*.php'],
        tasks: [
          'phplint',
          'phpcs'
        ]
      }
    },
    phplint: {
      options: {
        swapPath: '/tmp'
      },
      application: [
        './src/**/*.php',
        './tests/**/*.php'
      ]
    },
    phpcs: {
      options: {
        bin: './vendor/bin/phpcs',
        standard: 'PSR2'
      },
      application: {
        dir: [
          './src',
          './tests'
        ]
      }
    },
    phpmd: {
      options: {
        bin: './vendor/bin/phpmd',
        rulesets: 'unusedcode,naming,design,controversial,codesize',
        reportFormat: 'text'
      },
      application: {
        dir: './src'
      }
    },
    phpcpd: {
      options: {
        bin: './vendor/bin/phpcpd',
        quiet: false,
        ignoreExitCode: true
      },
      application: {
        dir: './src'
      }
    },
    phpunit: {
      options: {
        bin: './vendor/bin/phpunit',
        coverage: true
      },
      application: {
        configuration: './phpunit.xml'
      }
    }
  });

  grunt.registerTask('check', ['phplint', 'phpcs', 'phpmd']);
  grunt.registerTask('test', ['phpunit']);
  grunt.registerTask('default', ['check', 'phpcpd', 'test']);
};
