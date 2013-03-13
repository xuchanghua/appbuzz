<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Topic\Controller\Topic' => 'Topic\Controller\TopicController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'topic' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/topic[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Topic\Controller\Topic',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),


    'view_manager' => array(
        'template_path_stack' => array(
            'topic' => __DIR__ . '/../view',
        ),
    ),
);