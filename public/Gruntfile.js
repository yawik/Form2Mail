module.exports = function(grunt) {

  var targetDir = grunt.config.get('targetDir');
  var moduleDir = targetDir + "/modules/Form2Mail";
  
  grunt.config.merge({
    less: {
      form2mail: {
        options: {
          compress: true,
          optimization: 2
        },
        files: [
            {
              dest: targetDir + "/modules/Form2Mail/layout.css",
              src: moduleDir + "/less/layout.less"
            }
          ]
        
      }
    }
  });

  grunt.registerTask('yawik:form2mail', ['less:form2mail']);
};

