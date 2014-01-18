<?php

return array(
    'navigation' => array(
        'default' => array(
             array(
                 'label' => 'New',
                 'route' => 'transaction',
                 'a_params' => array(
                    'data-transition' => 'slide',
                    'data-icon'       => 'plus',
                 )
             ),
             array(
                 'label' => 'Pie',
                 'route' => 'pie',
                 'a_params' => array(
                    'data-transition' => 'slide',
                    'data-icon'       => 'eye',
                 )
             ),
//             array(
//                 'label' => 'Chart',
//                 'route' => 'chart',
//                 'data-transition' => 'slide',
//                 'a_params' => array(
//                    'data-transition' => 'slide',
//                    'data-icon'       => 'arrow-u',
//                 )
//             ),
             array(
                 'label' => 'List',
                 'route' => 'list',
                 'data-transition' => 'slide',
                 'a_params' => array(
                    'data-transition' => 'slide',
                    'data-icon'       => 'grid',
                 )
             ),
             array(
                 'label' => 'Profile',
                 'route' => 'user',
                 'data-transition' => 'slide',
                 'a_params' => array(
                    'data-transition' => 'slide',
                    'data-icon'       => 'user',
                    'data-ajax'       => 'false'
                 )
             ),
        ),
    )
);
