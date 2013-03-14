<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Newspub\Controller\Newspub' => 'Newspub\Controller\NewspubController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'newspub' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/newspub[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Newspub\Controller\Newspub',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),


    'view_manager' => array(
        'template_path_stack' => array(
            'newspub' => __DIR__ . '/../view',
        ),
        'template_map' => array(//配置分页控件模板路径
            'pagination/search' => __DIR__ . '/../view/pagination/search.phtml',
        ),
    ),
);