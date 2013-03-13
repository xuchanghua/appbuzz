<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Interview\Controller\Interview' => 'Interview\Controller\InterviewController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'interview' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/interview[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Interview\Controller\Interview',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),


    'view_manager' => array(
        'template_path_stack' => array(
            'interview' => __DIR__ . '/../view',
        ),
    ),
);