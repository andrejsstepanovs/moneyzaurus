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
                 'label' => 'Pies',
                 'route' => 'pie',
                 'a_params' => array(
                    'data-transition' => 'slide',
                    'data-icon'       => 'arrow-d',
                 )
             ),
             array(
                 'label' => 'Chart',
                 'route' => 'chart',
                 'data-transition' => 'slide',
                 'a_params' => array(
                    'data-transition' => 'slide',
                    'data-icon'       => 'arrow-u',
                 )
             ),
             array(
                 'label' => 'List',
                 'route' => 'list',
                 'data-transition' => 'slide',
                 'a_params' => array(
                    'data-transition' => 'slide',
                    'data-icon'       => 'search',
                 )
             ),
             array(
                 'label' => 'Profile',
                 'route' => 'user',
                 'data-transition' => 'slide',
                 'a_params' => array(
                    'data-transition' => 'slide',
                    'data-icon'       => 'search',
                 )
             ),
        ),
    )
);
