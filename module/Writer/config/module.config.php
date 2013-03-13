<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Writer\Controller\Writer' => 'Writer\Controller\WriterController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'writer' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/writer[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Writer\Controller\Writer',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),


    'view_manager' => array(
        'template_path_stack' => array(
            'writer' => __DIR__ . '/../view',
        ),
    ),
);