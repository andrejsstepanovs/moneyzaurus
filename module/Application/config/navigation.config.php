<?php

return array(
    'navigation' => array(
        'default' => array(
             array(
                 'label' => 'New',
                 'route' => 'new',
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
        ),
    )
);
