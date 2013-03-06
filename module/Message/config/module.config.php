<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Message\Controller\Message' => 'Message\Controller\MessageController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'message' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/message[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Message\Controller\Message',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),


    'view_manager' => array(
        'template_path_stack' => array(
            'message' => __DIR__ . '/../view',
        ),
    ),
);