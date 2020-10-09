<?php

namespace Deployer;

set('keep_releases', 3);
set( 'mysql_user', 'mypathshala' );
set( 'mysql_password', 'Uttam@2018' );

$module = 'user';

$config_root = '/var/app';
$config_path = '/var/app/config';
$git_path = 'git@bitbucket.org:uttam_s/pathshala-config.git';


$module_env_full_path = $config_path . '/' .  $module  . '/' . '.env';

task('confirm', function () {
    if (! askConfirmation('Are you sure you want to deploy to production?')) {
        write('Ok, quitting.');
        die;
    }
})->onStage('prod2');


task('update:env', function () use ($module_env_full_path, $git_path, $config_path, $config_root) {
    if (! askConfirmation('Are you sure you want to update env file in production?')) {
        writeln('Ok, Not changing the env file.');
    } else {
         if (! run("if [ -d $config_path ]; then echo exists; fi;") == 'exists') {
             writeln($config_path .' not present. Creating directory and downloading config');
             run("mkdir -p $config_root && cd $config_root && rm -rf * && git clone $git_path config");
         }

        if (! run("if [ -d $config_path ]; then echo exists; fi;") == 'exists') {
            die;
        }

        writeln('updating config repo');
        run("cd $config_path && git pull");

        writeln('Replacing environment file in {{deploy_path}}/shared');
        run("cp $module_env_full_path {{deploy_path}}/shared");
    }

})->onStage('prod2');

