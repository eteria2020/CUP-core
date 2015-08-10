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
                        'publiccars' => [
                            'type' => 'Segment',
                            'options' => [
                                'route' => '/publiccars[/:id]',
                                'constraints' => array(
                                    'id'     => '[a-zA-Z0-9_-]+',
                                ),
                                'defaults' => [
                                    'controller' => 'PublicCars'
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
                        'invoices' => [
                            'type' => 'Segment',
                            'options' => [
                                'route' => '/invoices[/:id]',
                                'constraints' => array(
                                    'id'     => '[0-9]+',
                                ),
                                'defaults' => [
                                    'controller' => 'Invoices'
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
            'SharengoCore\Controller\Trips'            => 'SharengoCore\Controller\TripsControllerFactory',
            'SharengoCore\Controller\PublicCars'       => 'SharengoCore\Controller\PublicCarsControllerFactory',
            'SharengoCore\Controller\Invoices'       => 'SharengoCore\Controller\InvoicesControllerFactory'
        ],
    ],

    'service_manager' => [
        'invokables' => [
            'SharengoCore\Service\DatatableQueryBuilder' => 'SharengoCore\Service\DatatableQueryBuilders\Basic',
            'SharengoCore\Service\FreeFaresService' => 'SharengoCore\Service\FreeFaresService',
            'SharengoCore\Service\TripFaresService' => 'SharengoCore\Service\TripFaresService'
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
            'SharengoCore\Service\BonusService'        => 'SharengoCore\Service\BonusServiceFactory',
            'SharengoCore\Service\AccountTripsService' => 'SharengoCore\Service\AccountTripsServiceFactory',
            'SharengoCore\Service\CommandsService'     => 'SharengoCore\Service\CommandsServiceFactory',
            'SharengoCore\Service\Invoices' => 'SharengoCore\Service\InvoicesServiceFactory',
            'SharengoCore\Service\AccountedTripsService' => 'SharengoCore\Service\AccountedTripsServiceFactory',
            'SharengoCore\Service\TripCostService' => 'SharengoCore\Service\TripCostServiceFactory',
            'SharengoCore\Service\FaresService' => 'SharengoCore\Service\FaresServiceFactory',
            'SharengoCore\Service\TripPaymentsService' => 'SharengoCore\Service\TripPaymentsServiceFactory',
            'SharengoCore\Service\SimpleLoggerService' => 'SharengoCore\Service\SimpleLoggerServiceFactory'
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
                array('controller' => 'SharengoCore\Controller\Cars', 'roles' => array('admin', 'callcenter')),
                array('controller' => 'SharengoCore\Controller\PublicCars', 'roles' => array()),
                array('controller' => 'SharengoCore\Controller\Customers', 'roles' => array('admin', 'callcenter')),
                array('controller' => 'SharengoCore\Controller\Pois', 'roles' => array()),
                array('controller' => 'SharengoCore\Controller\Reservations', 'roles' => array('user', 'admin', 'callcenter')),
                array('controller' => 'SharengoCore\Controller\Trips', 'roles' => array('admin', 'callcenter')),
                array('controller' => 'SharengoCore\Controller\Invoices', 'roles' => array('user')),
            ),
        ),
    ),
];
