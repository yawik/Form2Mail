<?php
namespace Deployer;

require 'recipe/zend_framework.php';

// Project name
set('application', 'Form2Mail');

// Project repository
set('repository', 'git@gitlab.cross-solution.de:Personalwerk/form2mail.git');

// Shared files/dirs between deploys 
add('shared_files', ['/var/www/production/current/shared/test/sandbox/public/.htaccess']);
add('shared_dirs', [
   '/www/production/current/shared/config',
   '/var/www/production/current/shared/var/cache',
   '/var/www/production/current/shared/var/log'
]);

// Writable dirs by web server 
add('writable_dirs', [
    '/var/www/production/current/shared/var/cache',
    '/var/www/production/current/shared/var/log'
    ]);

set('default_stage', 'prod');

// Hosts

host('jobs-deutschland.de')
    ->user('yawik')
    ->stage('prod')
    ->multiplexing(false) 
    ->set('deploy_path', '/var/www/production');   
    
before('deploy:symlink', 'deploy:build');

task('deploy:build', function () {
    run('cd {{release_path}}/test/sandbox && rm -R config/autoload var && ln -s ../../../../shared/shared/var/ && cd config && ln -s ../../../../../shared/shared/config/autoload');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

