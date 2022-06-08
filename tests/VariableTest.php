<?php

namespace GraphQL\Tests;

use GraphQL\Variable;
use PHPUnit\Framework\TestCase;

/**
 * Class VariableTest
 *
 * @package GraphQL\Tests
 */
class VariableTest extends TestCase
{
    /**
     * @covers \GraphQL\Variable::__construct
     * @covers \GraphQL\Variable::__toString
     * @covers \GraphQL\Variable::getName
     * @covers \GraphQL\Variable::getType
     * @covers \GraphQL\Variable::isRequired
     * @covers \GraphQL\Variable::getDefaultValue
     */
    public function testCreateVariable()
    {
        $variable = new Variable('var', 'String');
        $this->assertEquals('$var: String', (string) $variable);

        $this->assertEquals('var', $variable->getName());
        $this->assertEquals('String', $variable->getType());
        $this->assertFalse($variable->isRequired());
        $this->assertNull($variable->getDefaultValue());
    }

    /**
     * @depends testCreateVariable
     *
     * @covers \GraphQL\Variable::__construct
     * @covers \GraphQL\Variable::__toString
     * @covers \GraphQL\Variable::isRequired
     */
    public function testCreateRequiredVariable()
    {
        $variable = new Variable('var', 'String', true);
        $this->assertEquals('$var: String!', (string) $variable);

        $this->assertTrue($variable->isRequired());
    }

    /**
     * @depends testCreateRequiredVariable
     *
     * @covers \GraphQL\Variable::__construct
     * @covers \GraphQL\Variable::__toString
     * @covers \GraphQL\Variable::getDefaultValue
     */
    public function testRequiredVariableWithDefaultValueDoesNothing()
    {
        $variable = new Variable('var', 'String', true, 'def');
        $this->assertEquals('$var: String!', (string) $variable);
        $this->assertEquals('def', $variable->getDefaultValue());
    }

    /**
     * @depends testCreateVariable
     *
     * @covers \GraphQL\Variable::__construct
     * @covers \GraphQL\Variable::__toString
     * @covers \GraphQL\Variable::getDefaultValue
     */
    public function testOptionalVariableWithDefaultValue()
    {
        $variable = new Variable('var', 'String', false, 'def');
        $this->assertEquals('$var: String="def"', (string) $variable);
        $this->assertEquals('def', $variable->getDefaultValue());

        $variable = new Variable('var', 'String', false, '4');
        $this->assertEquals('$var: String="4"', (string) $variable);
        $this->assertEquals('4', $variable->getDefaultValue());

        $variable = new Variable('var', 'Int', false, 4);
        $this->assertEquals('$var: Int=4', (string) $variable);
        $this->assertEquals(4, $variable->getDefaultValue());

        $variable = new Variable('var', 'Boolean', false, true);
        $this->assertEquals('$var: Boolean=true', (string) $variable);
        $this->assertEquals(true, $variable->getDefaultValue());
    }
}