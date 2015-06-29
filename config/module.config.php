<?php

namespace SharengoCore;

return [

    'router' => array(
            'routes' => array(
                'core' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/core',
                    'defaults' => [
                        '__NAMESPACE__' => 'SharengoCore\Controller',
                        'controller' => 'Cars',
                        ]
                    ],
                    'may_terminate' => true,
                    'child_routes' => [

                        'cars' => [
                            'type' => 'Segment',
                            'options' => [
                                'route' => '/cars[/:id]',
                                'constraints' => array(
                                    'id'     => '[a-zA-Z0-9_-]+',
                                ),
                                'defaults' => [
                                    'controller' => 'Cars'
                                ]
                            ]
                        ],
                        'pois' => [
                            'type' => 'Literal',
                            'options' => [
                                'route' => '/pois',
                                'defaults' => [
                                    'controller' => 'Pois'
                                ]
                            ]
                        ],
                        'users' => [
                            'type' => 'Literal',
                            'options' => [
                                'route' => '/users',
                                'defaults' => [
                                    'controller' => 'Customers'
                                ]
                            ]
                        ],
                        'reservations' => [
                            'type' => 'Segment',
                            'options' => [
                                'route' => '/reservations[/:id]',
                                'constraints' => array(
                                    'id'     => '[0-9]+',
                                ),
                                'defaults' => [
                                    'controller' => 'Reservations'
                                ]
                            ]
                        ],
                        'trips' => [
                            'type' => 'Segment',
                            'options' => [
                                'route' => '/trips[/:id]',
                                'constraints' => array(
                                    'id'     => '[0-9]+',
                                ),
                                'defaults' => [
                                    'controller' => 'Trips'
                                ]
                            ]
                        ],
                    ],
                ],
            ),
        ),
    'controllers' => [
        'factories' => [
            'SharengoCore\Controller\Cars'             => 'SharengoCore\Controller\CarsControllerFactory',
            'SharengoCore\Controller\Customers'        => 'SharengoCore\Controller\CustomersControllerFactory',
            'SharengoCore\Controller\Pois'             => 'SharengoCore\Controller\PoisControllerFactory',
            'SharengoCore\Controller\Reservations'     => 'SharengoCore\Controller\ReservationsControllerFactory',
            'SharengoCore\Controller\Trips'            => 'SharengoCore\Controller\TripsControllerFactory'
        ],
    ],

    'service_manager' => [
        'invokables' => [
            'SharengoCore\Service\DatatableQueryBuilder' => 'SharengoCore\Service\DatatableQueryBuilders\Basic',
        ],
        'factories' => [
            'SharengoCore\Service\CustomersService'    => 'SharengoCore\Service\CustomersServiceFactory',
            'SharengoCore\Service\CountriesService'    => 'SharengoCore\Service\CountriesServiceFactory',
            'SharengoCore\Service\ProvincesService'    => 'SharengoCore\Service\ProvincesServiceFactory',
            'SharengoCore\Service\UsersService'        => 'SharengoCore\Service\UsersServiceFactory',
            'SharengoCore\Service\AuthorityService'    => 'SharengoCore\Service\AuthorityServiceFactory',
            'SharengoCore\Service\TripsService'        => 'SharengoCore\Service\TripsServiceFactory',
            'SharengoCore\Service\DatatableService'    => 'SharengoCore\Service\DatatableServiceFactory',
            'SharengoCore\Service\CarsService'         => 'SharengoCore\Service\CarsServiceFactory',
            'SharengoCore\Service\PoisService'         => 'SharengoCore\Service\PoisServiceFactory',
            'SharengoCore\Service\ReservationsService' => 'SharengoCore\Service\ReservationsServiceFactory',
            'SharengoCore\Service\PromoCodesService'   => 'SharengoCore\Service\PromoCodesServiceFactory',
            'SharengoCore\Service\CardsService'        => 'SharengoCore\Service\CardsServiceFactory',
            'SharengoCore\Service\CommandsService'     => 'SharengoCore\Service\CommandsServiceFactory',
        ]
    ],
    'doctrine'        => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
            ],
            'orm_default'             => [
                'class'   => 'Doctrine\ORM\Mapping\Driver\DriverChain',
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ]
            ],
        ],
    ],
    'bjyauthorize' => array(
        'guards' => array(
            'BjyAuthorize\Guard\Controller' => array(
                array('controller' => 'SharengoCore\Controller\Cars', 'roles' => array()),
                array('controller' => 'SharengoCore\Controller\Customers', 'roles' => array('admin')),
                array('controller' => 'SharengoCore\Controller\Pois', 'roles' => array()),
                array('controller' => 'SharengoCore\Controller\Reservations', 'roles' => array('admin')),
                array('controller' => 'SharengoCore\Controller\Trips', 'roles' => array('admin')),
            ),
        ),
    ),
];
