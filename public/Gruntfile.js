module.exports = function(grunt) {
    var targetDir = grunt.config.get('targetDir');
    var nodeModulesPath = grunt.config.get('nodeModulesPath');

    grunt.config.merge({
        less: {
            form2mail: {
                options: {
                    compress: false,
                    modifyVars: {
                        "fa-font-path": "/dist/fonts",
                        "flag-icon-css-path": "/dist/flags"
                    }
                },
                files: [
                    {
                        src: [
                            "./node_modules/select2/dist/css/select2.min.css",
                            "./node_modules/pnotify/dist/pnotify.css",
                            "./node_modules/pnotify/dist/pnotify.buttons.css",
                            "./node_modules/bootsrap3-dialog/dist/css/bootstrap-dialog.css",
                            targetDir+"/modules/Form2Mail/less/layout.less"
                        ],
                        dest: targetDir+"/modules/Form2Mail/dist/layout.css"
                    }
                ]
            },
            jobs: {
                files: [
                    {
                        src: "./view/jobs/templates/less/job.less",
                        dest: "./view/jobs/templates/job.css"
                    }
                ]
            }
        },
        cssmin: {
            aviation: {
                files: [
                    {
                        dest: targetDir+'/modules/Form2Mail/dist/layout.min.css',
                        src: targetDir+'/modules/Form2Mail/dist/layout.css'
                    }
                ]
            }
        }
    });

    grunt.registerTask('yawik:form2mail',['copy','less','less:job','concat','uglify','cssmin']);
};