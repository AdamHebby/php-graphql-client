<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GraphQL\Mutation;
use GraphQL\RawObject;

// Create the GraphQL mutation
$gql = (new Mutation('createCompany'))
    ->setArguments(['companyObject' => new RawObject('{name: "Trial Company", employees: 200}')])
    ->setSelectionSet(
        [
            '_id',
            'name',
            'serialNumber',
        ]
    );
