<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GraphQL\QueryBuilder\QueryBuilder;

// Create the GraphQL query
$builder = (new QueryBuilder('pokemon'))
    ->setArgument('name', 'Pikachu')
    ->selectField('id')
    ->selectField('number')
    ->selectField('name')
    ->selectField(
        (new QueryBuilder('attacks'))
            ->selectField(
                (new QueryBuilder('special'))
                    ->selectField('name')
                    ->selectField('type')
                    ->selectField('damage')
            )
    )
    ->selectField(
        (new QueryBuilder('evolutions'))
            ->selectField('id')
            ->selectField('name')
            ->selectField('number')
            ->selectField(
                (new QueryBuilder('attacks'))
                    ->selectField(
                        (new QueryBuilder('fast'))
                            ->selectField('name')
                            ->selectField('type')
                            ->selectField('damage')
                    )
            )
    );
