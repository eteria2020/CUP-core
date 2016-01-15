<?php

namespace SharengoCore;

return [

    'router' => [
        'routes' => [
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
                            'constraints' => [
                                'id'     => '[a-zA-Z0-9_-]+',
                            ],
                            'defaults' => [
                                'controller' => 'Cars'
                            ]
                        ]
                    ],
                    'publiccars' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/publiccars[/:id]',
                            'constraints' => [
                                'id'     => '[a-zA-Z0-9_-]+',
                            ],
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
                            'constraints' => [
                                'id'     => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => 'Reservations'
                            ]
                        ]
                    ],
                    'trips' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/trips[/:id]',
                            'constraints' => [
                                'id'     => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => 'Trips'
                            ]
                        ]
                    ],
                    'invoices' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/invoices[/:id]',
                            'constraints' => [
                                'id'     => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => 'Invoices'
                            ]
                        ]
                    ],
                    'fleets' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/fleets[/:id]',
                            'constraints' => [
                                'id'     => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => 'Fleets'
                            ]
                        ]
                    ],
                    'municipalities' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/municipalities[/:province]',
                            'defaults' => [
                                'controller' => 'Municipalities',
                                'action' => 'activeMunicipalities'
                            ]
                        ]
                    ]
                ],
            ],
            'pdf' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/pdf',
                    'defaults' => [
                        '__NAMESPACE__' => 'SharengoCore\Controller',
                        'controller' => 'Pdf',
                        'action'     => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'invoices' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/invoices[/:id]',
                            'defaults' => [
                                'action' => 'index',
                            ]
                        ]
                    ],
                ]
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            'SharengoCore\Controller\Cars'             => 'SharengoCore\Controller\CarsControllerFactory',
            'SharengoCore\Controller\Customers'        => 'SharengoCore\Controller\CustomersControllerFactory',
            'SharengoCore\Controller\Pois'             => 'SharengoCore\Controller\PoisControllerFactory',
            'SharengoCore\Controller\Reservations'     => 'SharengoCore\Controller\ReservationsControllerFactory',
            'SharengoCore\Controller\Trips'            => 'SharengoCore\Controller\TripsControllerFactory',
            'SharengoCore\Controller\PublicCars'       => 'SharengoCore\Controller\PublicCarsControllerFactory',
            'SharengoCore\Controller\Fleets'           => 'SharengoCore\Controller\FleetsControllerFactory',
            'SharengoCore\Controller\Invoices' => 'SharengoCore\Controller\InvoicesControllerFactory',
            'SharengoCore\Controller\Pdf' => 'SharengoCore\Controller\PdfControllerFactory',
            'SharengoCore\Controller\CustomersBonusPackages' => 'SharengoCore\Controller\CustomersBonusPackagesControllerFactory',
            'SharengoCore\Controller\Municipalities' => 'SharengoCore\Controller\MunicipalitiesControllerFactory',
        ],
    ],

    'service_manager' => [
        'invokables' => [
            'SharengoCore\Service\DatatableQueryBuilder' => 'SharengoCore\Service\DatatableQueryBuilders\Basic',
            'SharengoCore\Service\FreeFaresService' => 'SharengoCore\Service\FreeFaresService',
            'SharengoCore\Service\TripFaresService' => 'SharengoCore\Service\TripFaresService',
            'SharengoCore\Service\LocationService' => 'SharengoCore\Service\LocationService'
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
            'SharengoCore\Service\EmailService' => 'SharengoCore\Service\EmailServiceFactory',
            'SharengoCore\Service\TripPaymentsService' => 'SharengoCore\Service\TripPaymentsServiceFactory',
            'SharengoCore\Service\SimpleLoggerService' => 'SharengoCore\Service\SimpleLoggerServiceFactory',
            'SharengoCore\Service\TripPaymentsService' => 'SharengoCore\Service\TripPaymentsServiceFactory',
            'SharengoCore\Service\SimpleLoggerService' => 'SharengoCore\Service\SimpleLoggerServiceFactory',
            'SharengoCore\Service\EmailService' => 'SharengoCore\Service\EmailServiceFactory',
            'SharengoCore\Service\TripCostComputerService' => 'SharengoCore\Service\TripCostComputerServiceFactory',
            'SharengoCore\Service\PaymentsService' => 'SharengoCore\Service\PaymentsServiceFactory',
            'SharengoCore\Service\TripPaymentTriesService' => 'SharengoCore\Service\TripPaymentTriesServiceFactory',
            'SharengoCore\Service\EditTripsService' => 'SharengoCore\Service\EditTripsServiceFactory',
            'SharengoCore\Service\ExtraPaymentsService' => 'SharengoCore\Service\ExtraPaymentsServiceFactory',
            'SharengoCore\Service\PenaltiesService' => 'SharengoCore\Service\PenaltiesServiceFactory',
            'SharengoCore\Listener\PaymentEmailListener' => 'SharengoCore\Listener\PaymentEmailListenerFactory',
            'SharengoCore\Listener\NotifyCustomerPayListener' => 'SharengoCore\Listener\NotifyCustomerPayListenerFactory',
            'SharengoCore\Service\DisableContractService' => 'SharengoCore\Service\DisableContractServiceFactory',
            'SharengoCore\Listener\DisableContractListener' => 'SharengoCore\Listener\DisableContractListenerFactory',
            'SharengoCore\Service\EventsService' => 'SharengoCore\Service\EventsServiceFactory',
            'SharengoCore\Service\FleetService' => 'SharengoCore\Service\FleetServiceFactory',
            'SharengoCore\Service\ExtraPaymentsSearchService' => 'SharengoCore\Service\ExtraPaymentsSearchServiceFactory',
            'SharengoCore\Service\BonusPackagesService' => 'SharengoCore\Service\CustomersBonusPackagesServiceFactory',
            'SharengoCore\Service\BuyCustomerBonusPackage' => 'SharengoCore\Service\BuyCustomerBonusPackageFactory',
            'SharengoCore\Service\BonusPackagePaymentService' => 'SharengoCore\Service\BonusPackagePaymentServiceFactory',
            'SharengoCore\Service\RecapService' => 'SharengoCore\Service\RecapServiceFactory',
            'SharengoCore\Service\CustomerNoteService' => 'SharengoCore\Service\CustomerNoteServiceFactory',
            'SharengoCore\Service\ConfigurationsService' => 'SharengoCore\Service\ConfigurationsServiceFactory',
            'SharengoCore\Service\ZonesService' => 'SharengoCore\Service\ZonesServiceFactory',
            'SharengoCore\Service\CustomerDeactivationService' => 'SharengoCore\Service\CustomerDeactivationServiceFactory',
            'SharengoCore\Service\MunicipalitiesService' => 'SharengoCore\Service\MunicipalitiesServiceFactory',
            'SharengoCore\Service\CsvService' => 'SharengoCore\Service\CsvServiceFactory'
        ],
        'shared' => [
            'SharengoCore\Service\TripCostComputerService' => false,
            'SharengoCore\Service\DatatableService' => false,
        ]
    ],

    'doctrine'        => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity']
            ],
            'orm_default'             => [
                'class'   => 'Doctrine\ORM\Mapping\Driver\DriverChain',
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ]
            ],
        ],
    ],

    'bjyauthorize' => [
        'guards' => [
            'BjyAuthorize\Guard\Controller' => [
                ['controller' => 'SharengoCore\Controller\Cars', 'roles' => ['admin', 'callcenter']],
                ['controller' => 'SharengoCore\Controller\PublicCars', 'roles' => []],
                ['controller' => 'SharengoCore\Controller\Customers', 'roles' => ['admin', 'callcenter']],
                ['controller' => 'SharengoCore\Controller\Pois', 'roles' => []],
                ['controller' => 'SharengoCore\Controller\Reservations', 'roles' => ['user', 'admin', 'callcenter']],
                ['controller' => 'SharengoCore\Controller\Trips', 'roles' => ['admin', 'callcenter', 'user']],
                ['controller' => 'SharengoCore\Controller\Fleets', 'roles' => []],
                ['controller' => 'SharengoCore\Controller\Invoices', 'roles' => ['user']],
                ['controller' => 'SharengoCore\Controller\Pdf', 'roles' => ['user', 'admin']],
                ['controller' => 'SharengoCore\Controller\CustomersBonusPackages', 'roles' => ['user', 'admin']],
                ['controller' => 'SharengoCore\Controller\Municipalities', 'roles' => []],
            ],
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'layout/pdf-layout' => __DIR__ . '/../view/layout/layout_pdf.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'invoice' => [
        'template_version' => '4',
        'subscription_amount' => 1000,
        'iva_percentage' => 22
    ],

    'asset_manager' => [
        'resolver_configs' => [
            'paths' => [
                __DIR__ . '/../public',
            ]
        ]
    ],

    'mvlabs-snappy' => [
        'pdf' => [
           'binary'  => __DIR__ . '/../../../../vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64',
           'options' => [],
        ]
    ],

    'simple-logger' => [
        'environment' => 'production'
    ]

];
