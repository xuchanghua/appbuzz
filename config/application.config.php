<?php
return array(
    'modules' => array(
        'Application',
        'Admin',
        'Album',
	    'Enterprise',
	    'Media',
	    'User',
        'Message',
        'Newspub',
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            'config/autoload/{,*.}{global,local}.php',
        ),
        'module_paths' => array(
            './module',
            './vendor',
        ),
    ),
);
