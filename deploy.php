<?php
namespace Deployer;

require 'recipe/zend_framework.php';

// Project name
set('application', 'Form2Mail');

// Project repository
set('repository', 'git clone https://gitlab-ci-token:'.$_ENV['GITLAB_TOKEN'].'@gitlab.cross-solution.de/Personalwerk/form2mail.git');

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', [
   'shared',
]);

// Writable dirs by web server 
add('writable_dirs', []);

set('default_stage', 'prod');

// Hosts

host('jobs-deutschland.de')
    ->user('yawik')
    ->stage('prod')
    ->multiplexing(false) 
    ->set('deploy_path', '/var/www/production');   
    
before('deploy:symlink', 'deploy:build');

task('deploy:build', function () {
    run('cd {{release_path}}/test/sandbox && rm -R config/autoload var && ln -s ../../../../shared/shared/var/ && cd config && ln -s ../../../../../shared/shared/config/autoload && cd ../public && ln -s ../../../../../shared/test/sandbox/public/.htaccess');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

