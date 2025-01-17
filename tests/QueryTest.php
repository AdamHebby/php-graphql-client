<?php

namespace GraphQL\Tests;

use GraphQL\Exception\ArgumentException;
use GraphQL\Exception\InvalidSelectionException;
use GraphQL\Exception\InvalidVariableException;
use GraphQL\InlineFragment;
use GraphQL\Query;
use GraphQL\RawObject;
use GraphQL\Variable;
use PHPUnit\Framework\TestCase;

/**
 * Class QueryTest
 */
class QueryTest extends TestCase
{
    /**
     * @covers \GraphQL\Query::__ToString
     * @covers \GraphQL\Query::__construct
     *
     * @return Query
     */
    public function testConvertsToString()
    {
        $query = new Query('Object');
        $this->assertIsString((string) $query, 'Failed to convert query to string');

        return $query;
    }

    /**
     * @depends testConvertsToString
     *
     * @covers \GraphQL\Query::constructArguments
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testEmptyArguments(Query $query)
    {
        $this->assertStringNotContainsString("()", (string) $query, 'Query has empty arguments list');

        return $query;
    }

    /**
     * @covers \GraphQL\Query::__toString
     * @covers \GraphQL\Query::getFieldName
     * @covers \GraphQL\FieldTrait::constructSelectionSet
     */
    public function testQueryWithoutFieldName()
    {
        $query = new Query();

        $this->assertEquals(
            "query",
            (string) $query
        );

        $query->setSelectionSet(
            [
                (new Query('Object'))
                    ->setSelectionSet(['one']),
                (new Query('Another'))
                    ->setSelectionSet(['two'])
            ]
        );

        $this->assertEquals(
            "query {
Object {
one
}
Another {
two
}
}",
            (string) $query
        );

        $this->assertEmpty($query->getFieldName());
    }

    /**
     * @depends testConvertsToString
     *
     * @covers \GraphQL\Query::generateSignature
     * @covers \GraphQL\Query::__toString
     * @covers \GraphQL\Query::getAlias
     */
    public function testQueryWithAlias()
    {
        $query = (new Query('Object', 'ObjectAlias'))
            ->setSelectionSet([
                'one'
            ]);

        $this->assertEquals(
            "query {
ObjectAlias: Object {
one
}
}",
            (string) $query
        );

        $this->assertEquals('ObjectAlias', $query->getAlias());
    }

    /**
     * @depends testConvertsToString
     *
     * @covers \GraphQL\Query::setAlias
     * @covers \GraphQL\Query::generateSignature
     * @covers \GraphQL\Query::__toString
     * @covers \GraphQL\Query::getAlias
     */
    public function testQueryWithSetAlias()
    {
        $query = (new Query('Object'))
            ->setAlias('ObjectAlias')
            ->setSelectionSet([
                'one'
            ]);

        $this->assertEquals(
            "query {
ObjectAlias: Object {
one
}
}",
            (string) $query
        );

        $this->assertEquals('ObjectAlias', $query->getAlias());
    }

    /**
     * @depends testConvertsToString
     *
     * @covers \GraphQL\Query::generateSignature
     * @covers \GraphQL\Query::setOperationName
     * @covers \GraphQL\Query::getOperationName
     * @covers \GraphQL\Query::__toString
     */
    public function testQueryWithOperationName()
    {
        $query = (new Query('Object'))
            ->setOperationName('retrieveObject');
        $this->assertEquals(
'query retrieveObject {
Object
}',
            (string) $query
        );

        $this->assertEquals('retrieveObject', $query->getOperationName());
    }

    /**
     * @depends testQueryWithoutFieldName
     * @depends testQueryWithOperationName
     *
     * @covers \GraphQL\Query::generateSignature
     * @covers \GraphQL\Query::setOperationName
     * @covers \GraphQL\Query::getOperationName
     * @covers \GraphQL\Query::__toString
     */
    public function testQueryWithOperationNameAndOperationType()
    {
        $query = (new Query())
            ->setOperationName('retrieveObject')
            ->setSelectionSet([new Query('Object')]);
        $this->assertEquals(
            'query retrieveObject {
Object
}',
            (string) $query
        );

        $this->assertEquals('retrieveObject', $query->getOperationName());
    }

    /**
     * @depends testQueryWithOperationName
     *
     * @covers \GraphQL\Query::generateSignature
     * @covers \GraphQL\Query::setOperationName
     * @covers \GraphQL\Query::getOperationName
     * @covers \GraphQL\Query::__toString
     */
    public function testQueryWithOperationNameInSecondLevelDoesNothing()
    {
        $query = (new Query('Object'))
            ->setOperationName('retrieveObject')
            ->setSelectionSet([(new Query('Nested'))->setOperationName('opName')]);
        $this->assertEquals(
            'query retrieveObject {
Object {
Nested
}
}',
            (string) $query
        );

        $this->assertEquals('retrieveObject', $query->getOperationName());
    }

    /**
     * @covers \GraphQL\Query::setVariables
     * @covers \GraphQL\Exception\InvalidVariableException
     */
    public function testSetVariablesWithoutVariableObjects()
    {
        $this->expectException(InvalidVariableException::class);
        (new Query('Object'))->setVariables(['one', 'two']);
    }

    /**
     * @depends testConvertsToString
     *
     * @covers \GraphQL\Query::setVariables
     * @covers \GraphQL\Query::getVariables
     * @covers \GraphQL\Query::generateSignature
     * @covers \GraphQL\Query::constructVariables
     * @covers \GraphQL\Query::__toString
     */
    public function testQueryWithOneVariable()
    {
        $query = (new Query('Object'))
            ->setVariables([new Variable('var', 'String')]);
        $this->assertEquals(
            'query($var: String) {
Object
}',
            (string) $query
        );

        $this->assertEquals([new Variable('var', 'String')], $query->getVariables());
    }

    /**
     * @depends testQueryWithOneVariable
     *
     * @covers \GraphQL\Query::setVariables
     * @covers \GraphQL\Query::getVariables
     * @covers \GraphQL\Query::generateSignature
     * @covers \GraphQL\Query::constructVariables
     * @covers \GraphQL\Query::__toString
     */
    public function testQueryWithMultipleVariables()
    {
        $query = (new Query('Object'))
            ->setVariables([new Variable('var', 'String'), new Variable('intVar', 'Int', false, 4)]);
        $this->assertEquals(
            'query($var: String $intVar: Int=4) {
Object
}',
            (string) $query
        );

        $this->assertEquals(
            [new Variable('var', 'String'), new Variable('intVar', 'Int', false, 4)],
            $query->getVariables()
        );
    }

    /**
     * @depends testConvertsToString
     *
     * @covers \GraphQL\Query::__toString
     * @covers \GraphQL\Query::getVariables
     */
    public function testQueryWithVariablesInSecondLevelDoesNothing()
    {
        $query = (new Query('Object'))
            ->setVariables([new Variable('var', 'String'), new Variable('intVar', 'Int', false, 4)])
            ->setSelectionSet([(new Query('Nested'))])
            ->setVariables([new Variable('var', 'String'), new Variable('intVar', 'Int', false, 4)]);
        $this->assertEquals(
            'query($var: String $intVar: Int=4) {
Object {
Nested
}
}',
            (string) $query
        );

        $this->assertEquals(
            [new Variable('var', 'String'), new Variable('intVar', 'Int', false, 4)],
            $query->getVariables()
        );
    }

    /**
     * @depends testQueryWithMultipleVariables
     * @depends testQueryWithOperationName
     *
     * @covers \GraphQL\Query::generateSignature
     * @covers \GraphQL\Query::getOperationName
     * @covers \GraphQL\Query::getVariables
     * @covers \GraphQL\Query::__toString
     */
    public function testQueryWithOperationNameAndVariables()
    {
        $query = (new Query('Object'))
            ->setOperationName('retrieveObject')
            ->setVariables([new Variable('var', 'String')]);
        $this->assertEquals(
            'query retrieveObject($var: String) {
Object
}',
            (string) $query
        );

        $this->assertEquals('retrieveObject', $query->getOperationName());
        $this->assertEquals([new Variable('var', 'String')], $query->getVariables());
    }

    /**
     * @depends clone testEmptyArguments
     *
     * @covers \GraphQL\Query::__toString
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testEmptyQuery(Query $query)
    {
        $this->assertEquals(
            "query {
Object
}",
            (string) $query,
            'Incorrect empty query string'
        );

        return $query;
    }

    /**
     * @depends clone testEmptyArguments
     *
     * @covers \GraphQL\Exception\ArgumentException
     * @covers \GraphQL\Query::setArguments
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testArgumentWithoutName(Query $query)
    {
        $this->expectException(ArgumentException::class);
        $query->setArguments(['val']);

        return $query;
    }

    /**
     * @depends clone testEmptyArguments
     *
     * @covers \GraphQL\Query::setArguments
     * @covers \GraphQL\Query::getArguments
     * @covers \GraphQL\Query::constructArguments
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testStringArgumentValue(Query $query)
    {
        $query->setArguments(['arg1' => 'value']);
        $this->assertEquals(
            "query {
Object(arg1: \"value\")
}",
            (string) $query,
            'Query has improperly formatted parameter list'
        );

        $this->assertEquals(['arg1' => 'value'], $query->getArguments());

        return $query;
    }

    /**
     * @depends clone testEmptyArguments
     *
     * @covers \GraphQL\Query::setArguments
     * @covers \GraphQL\Query::getArguments
     * @covers \GraphQL\Query::constructArguments
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testIntegerArgumentValue(Query $query)
    {
        $query->setArguments(['arg1' => 23]);
        $this->assertEquals(
            "query {
Object(arg1: 23)
}",
            (string) $query
        );

        $this->assertEquals(['arg1' => 23], $query->getArguments());

        return $query;
    }

    /**
     * @depends clone testEmptyArguments

     * @covers \GraphQL\Query::setArguments
     * @covers \GraphQL\Query::getArguments
     * @covers \GraphQL\Query::constructArguments
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testBooleanArgumentValue(Query $query)
    {
        $query->setArguments(['arg1' => true]);
        $this->assertEquals(
            "query {
Object(arg1: true)
}",
            (string) $query
        );

        $this->assertEquals(['arg1' => true], $query->getArguments());

        return $query;
    }

    /**
     * @depends clone testEmptyArguments
     *
     * @covers  \GraphQL\Query::setArguments
     * @covers  \GraphQL\Query::getArguments
     * @covers  \GraphQL\Query::constructArguments
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testNullArgumentValue(Query $query)
    {
        $query->setArguments(['arg1' => null]);
        $this->assertEquals(
            "query {
Object(arg1: null)
}"
            , (string) $query
        );

        $this->assertEquals(['arg1' => null], $query->getArguments());

        return $query;
    }

    /**
     * @depends clone testEmptyArguments
     *
     * @covers \GraphQL\Query::setArguments
     * @covers \GraphQL\Query::getArguments
     * @covers \GraphQL\Query::constructArguments
     *
     * @param  Query $query
     *
     * @return Query
     */
    public function testArrayIntegerArgumentValue(Query $query)
    {
        $query->setArguments(['arg1' => [1, 2, 3]]);
        $this->assertEquals(
            "query {
Object(arg1: [1, 2, 3])
}",
            (string) $query
        );

        $this->assertEquals(['arg1' => [1, 2, 3]], $query->getArguments());

        return $query;
    }

    /**
     * @depends clone testEmptyArguments
     *
     * @covers  \GraphQL\Query::setArguments
     * @covers  \GraphQL\Query::getArguments
     * @covers  \GraphQL\Query::constructArguments
     * @covers  \GraphQL\RawObject::__toString
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testJsonObjectArgumentValue(Query $query)
    {
        $query->setArguments(['obj' => new RawObject('{json_string_array: ["json value"]}')]);
        $this->assertEquals(
            "query {
Object(obj: {json_string_array: [\"json value\"]})
}"
            , (string) $query
        );

        $this->assertEquals(
            ['obj' => new RawObject('{json_string_array: ["json value"]}')],
            $query->getArguments()
        );

        return $query;
    }

    /**
     * @depends clone testEmptyArguments
     *
     * @covers \GraphQL\Query::setArguments
     * @covers \GraphQL\Query::getArguments
     * @covers \GraphQL\Query::constructArguments
     *
     * @param  Query $query
     *
     * @return Query
     */
    public function testArrayStringArgumentValue(Query $query)
    {
        $query->setArguments(['arg1' => ['one', 'two', 'three']]);
        $this->assertEquals(
            "query {
Object(arg1: [\"one\", \"two\", \"three\"])
}",
            (string) $query
        );

        $this->assertEquals(
            ['arg1' => ['one', 'two', 'three']],
            $query->getArguments()
        );

        return $query;
    }

    /**
     * @depends clone testStringArgumentValue
     * @depends testIntegerArgumentValue
     * @depends testBooleanArgumentValue
     *
     * @covers \GraphQL\Query::setArguments
     * @covers \GraphQL\Query::getArguments
     * @covers \GraphQL\Query::constructArguments
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testTwoOrMoreArguments(Query $query)
    {
        $query->setArguments(['arg1' => 'val1', 'arg2' => 2, 'arg3' => true]);
        $this->assertEquals(
            "query {
Object(arg1: \"val1\" arg2: 2 arg3: true)
}",
            (string) $query,
            'Query has improperly formatted parameter list'
        );

        $this->assertEquals(
            ['arg1' => 'val1', 'arg2' => 2, 'arg3' => true],
            $query->getArguments()
        );

        return $query;
    }

    /**
     * @depends testStringArgumentValue
     *
     * @covers \GraphQL\Query::setArguments
     * @covers \GraphQL\Query::constructArguments
     */
    public function testStringWrappingWorks()
    {
        // TODO: Remove this in v1.0 release
        $queryWrapped = new Query('Object');
        $queryWrapped->setArguments(['arg1' => '"val"']);

        $queryNotWrapped = new Query('Object');
        $queryNotWrapped->setArguments(['arg1' => 'val']);

        $this->assertEquals((string) $queryWrapped, (string) $queryWrapped);
    }

    /**
     * @depends clone testEmptyQuery
     *
     * @covers \GraphQL\Query::setSelectionSet
     * @covers \GraphQL\FieldTrait::constructSelectionSet
     * @covers \GraphQL\Query::getSelectionSet
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testSingleSelectionField(Query $query)
    {
        $query->setSelectionSet(['field1']);
        $this->assertEquals(
            "query {
Object {
field1
}
}",
            (string) $query,
            'Query has improperly formatted selection set'
        );

        $this->assertEquals(['field1'], $query->getSelectionSet());

        return $query;
    }

    /**
     * @depends clone testEmptyQuery
     *
     * @covers \GraphQL\Query::setSelectionSet
     * @covers \GraphQL\Query::getSelectionSet
     * @covers \GraphQL\FieldTrait::constructSelectionSet
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testTwoOrMoreSelectionFields(Query $query)
    {
        $query->setSelectionSet(['field1', 'field2']);
        $this->assertEquals(
            "query {
Object {
field1
field2
}
}",
            (string) $query,
            'Query has improperly formatted selection set'
        );

        $this->assertEquals(['field1', 'field2'], $query->getSelectionSet());

        return $query;
    }

    /**
     * @depends clone testEmptyQuery
     *
     * @covers \GraphQL\Exception\InvalidSelectionException
     * @covers \GraphQL\Query::setSelectionSet
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testSelectNonStringValues(Query $query)
    {
        $this->expectException(InvalidSelectionException::class);
        $query->setSelectionSet([true, 1.5]);

        return $query;
    }

    /**
     * @depends clone testEmptyQuery
     *
     * @coversNothing
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testOneLevelQuery(Query $query)
    {
        $query->setSelectionSet(['field1', 'field2']);
        $query->setArguments(['arg1' => 'val1', 'arg2' => 'val2']);
        $this->assertEquals(
            "query {
Object(arg1: \"val1\" arg2: \"val2\") {
field1
field2
}
}",
            (string) $query,
            'One level query not formatted correctly'
        );

        $this->assertEquals(['field1', 'field2'], $query->getSelectionSet());
        $this->assertEquals(['arg1' => 'val1', 'arg2' => 'val2'], $query->getArguments());

        return $query;
    }

    /**
     * @depends clone testOneLevelQuery
     *
     * @covers \GraphQL\FieldTrait::constructSelectionSet
     * @covers \GraphQL\Query::setAsNested
     * @covers \GraphQL\Query::isNested
     * @covers \GraphQL\Query::getSelectionSet
     * @covers \GraphQL\Query::getFieldName
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testTwoLevelQueryDoesNotContainWordQuery(Query $query)
    {
        $query->setSelectionSet(
            [
                'field1',
                'field2',
                (new Query('Object2'))
                    ->setSelectionSet(['field3'])
            ]
        );
        $this->assertStringNotContainsString(
            "\nquery {",
            (string) $query,
            'Nested query contains "query" word'
        );

        $this->assertTrue($query->getSelectionSet()[2]->isNested());

        $this->assertCount(3, $query->getSelectionSet());
        $this->assertEquals('field1', $query->getSelectionSet()[0]);
        $this->assertEquals('field2', $query->getSelectionSet()[1]);
        $this->assertEquals('Object2', $query->getSelectionSet()[2]->getFieldName());

        return $query;
    }

    /**
     * @depends clone testTwoLevelQueryDoesNotContainWordQuery
     *
     * @covers \GraphQL\Query::setAsNested
     * @covers \GraphQL\Query::getSelectionSet
     * @covers \GraphQL\Query::getFieldName
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testTwoLevelQuery(Query $query)
    {
        $query->setSelectionSet(
            [
                'field1',
                'field2',
                (new Query('Object2'))
                    ->setSelectionSet(['field3'])
            ]
        );
        $this->assertEquals(
            "query {
Object(arg1: \"val1\" arg2: \"val2\") {
field1
field2
Object2 {
field3
}
}
}",
            (string) $query,
            'Two level query not formatted correctly'
        );

        $this->assertCount(3, $query->getSelectionSet());
        $this->assertEquals('field1', $query->getSelectionSet()[0]);
        $this->assertEquals('field2', $query->getSelectionSet()[1]);
        $this->assertEquals('Object2', $query->getSelectionSet()[2]->getFieldName());

        return $query;
    }

    /**
     * @depends clone testTwoLevelQueryDoesNotContainWordQuery
     *
     * @param Query $query
     *
     * @return Query
     */
    public function testTwoLevelQueryWithInlineFragment(Query $query)
    {
        $query->setSelectionSet(
            [
                'field1',
                (new InlineFragment('Object'))
                    ->setSelectionSet(
                        [
                            'fragment_field1',
                            'fragment_field2',
                        ]
                    ),
            ]
        );
        $this->assertEquals(
            'query {
Object(arg1: "val1" arg2: "val2") {
field1
... on Object {
fragment_field1
fragment_field2
}
}
}',
            (string) $query
        );

        $this->assertCount(2, $query->getSelectionSet());
        $this->assertEquals('field1', $query->getSelectionSet()[0]);
        $this->assertInstanceOf(InlineFragment::class, $query->getSelectionSet()[1]);

        return $query;
    }
}
