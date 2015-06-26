<?php

namespace SharengoCore;

return [
    'service_manager' => [
        'invokables' => [
            'SharengoCore\Service\DatatableQueryBuilder' => 'SharengoCore\Service\DatatableQueryBuilders\Basic'
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
            'SharengoCore\Service\AccountTripsService' => 'SharengoCore\Service\AccountTripsServiceFactory'
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
];
