<?php
return array(
    'modules' => array(
        'Admin',
        'Album',
        'Application',
        'Article',
        'Attachment',
        'Credit',
	    'Enterprise',
        'Evaluate',
        'Interview',
	    'Media',
        'Message',
        'Monitor',
        'Newshelper',
        'Newspub',
        'Product',
        'Topic',
        'User',
        'Writer',
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
