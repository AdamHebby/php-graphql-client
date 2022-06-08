<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GraphQL\Query;

// Create the GraphQL query
$gql = (new Query('pokemon'))
    ->setArguments(['name' => 'Pikachu'])
    ->setSelectionSet(
        [
            'id',
            'number',
            'name',
            (new Query('attacks'))
                ->setSelectionSet(
                    [
                        (new Query('special'))
                            ->setSelectionSet(
                                [
                                    'name',
                                    'type',
                                    'damage',
                                ]
                            )
                    ]

                ),
            (new Query('evolutions'))
                ->setSelectionSet(
                    [
                        'id',
                        'number',
                        'name',
                        (new Query('attacks'))
                            ->setSelectionSet(
                                [
                                    (new Query('fast'))
                                        ->setSelectionSet(
                                            [
                                                'name',
                                                'type',
                                                'damage',
                                            ]
                                        )
                                ]
                            )
                    ]
                )
        ]
    );
