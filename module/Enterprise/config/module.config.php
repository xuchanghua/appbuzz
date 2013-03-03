<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Enterprise\Controller\Enterprise' => 'Enterprise\Controller\EnterpriseController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'enterprise' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/enterprise[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Enterprise\Controller\Enterprise',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),


    'view_manager' => array(
        'template_path_stack' => array(
            'enterprise' => __DIR__ . '/../view',
        ),
    ),
);