<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Evaluate\Controller\Evaluate' => 'Evaluate\Controller\EvaluateController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'evaluate' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/evaluate[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evaluate\Controller\Evaluate',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),


    'view_manager' => array(
        'template_path_stack' => array(
            'evaluate' => __DIR__ . '/../view',
        ),
        'template_map' => array(//配置分页控件模板路径
            'pagination/search' => __DIR__ . '/../view/pagination/search.phtml',
        ),
    ),
);