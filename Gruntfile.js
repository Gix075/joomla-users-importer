module.exports = function(grunt) {

  // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        meta: {
            banner: '/*! \n' +
                    ' * ************************************************************************************ \n' +
                    ' *  <%= pkg.title %> | <%= pkg.description %> \n'+
                    ' *  Version <%= pkg.version %> - Date: <%= grunt.template.today("dd/mm/yyyy") %> \n' +
                    ' *  HomePage: <%= pkg.homepage %> \n'+
                    ' * ************************************************************************************ \n' +
                    '*/ \n',
            bannerHtml: '<!-- <%= pkg.title %> (v.<%= pkg.version %>) | <%= pkg.description %> -->'
        },
        clean: {
            dist: {
                src: ["<%= pkg.distDir %>"]
            }
        },
        
        'string-replace': {
            dist: {
                files: {
                    
                    '<%= pkg.distDir %>/php/class.usersimporter.php':'<%= pkg.devDir %>/php/class.usersimporter.php',
                    '<%= pkg.distDir %>/php/ws.usersimporter.php':'<%= pkg.devDir %>/php/ws.usersimporter.php',
                    
                    '<%= pkg.distDir %>/assets/css/style.css':'<%= pkg.devDir %>/assets/css/style.css',
                    '<%= pkg.distDir %>/assets/js/users-importer.js':'<%= pkg.devDir %>/assets/js/users-importer.js',
                    
                    '<%= pkg.distDir %>/index.html':'<%= pkg.devDir %>/index.html'
                },
                options: {
                    replacements: [
                        {
                            pattern: '//{BANNER}',
                            replacement: '<%= meta.banner %>'
                        },
                        {
                            pattern: '/*{BANNER}*/',
                            replacement: '<%= meta.banner %>'
                        },
                        {
                            pattern: '<!-- {BANNER} -->',
                            replacement: '<%= meta.bannerHtml %>'
                        }
                    ]
                }
            }
        },
                
        copy: {
            dist: {
                files: [
                    {
                    	src: '<%= pkg.devDir %>/docs.html', 
                    	dest: '<%= pkg.distDir %>/docs.html'
                    },
                    {
                        expand: true, 
                        cwd: '<%= pkg.devDir %>/csv/', 
                        src: ['*'],
                        dest: '<%= pkg.distDir %>/csv/'
                    },
                    {
                        expand: true, 
                        cwd: '<%= pkg.devDir %>/logs/', 
                        src: ['*.txt'],
                        dest: '<%= pkg.distDir %>/logs/'
                    }
                ]
            }
        }

    });


    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-string-replace');

    // Default task(s).
    grunt.registerTask('default', [
        'clean:dist',
        'string-replace:dist',
        'copy:dist'
    ]);

};