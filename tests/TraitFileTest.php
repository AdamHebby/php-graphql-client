<?php

use GraphQL\SchemaManager\CodeGenerator\CodeFile\TraitFile;

/**
 * Created by PhpStorm.
 * User: mostafa
 * Date: 1/29/19
 * Time: 12:06 AM
 */

class TraitFileTest extends CodeFileTestCase
{
    /**
     * @inheritdoc
     */
    protected static function getExpectedFilesDir()
    {
        return parent::getExpectedFilesDir() . '/traits';
    }

    /**
     * Happy scenario test, create empty trait with just name and write it to file system
     *
     * @throws Exception
     */
    public function testEmptyTrait()
    {
        $fileName = 'EmptyTrait';
        $trait = new TraitFile(static::getGeneratedFilesDir(), $fileName);
        $trait->writeFile();

        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php", $trait->getWritePath());
    }

    /**
     * @throws Exception
     *
     * @depends testEmptyTrait
     */
    public function testTraitWithNamespace()
    {
        $fileName = 'TraitWithNamespace';
        $trait = new TraitFile(static::getGeneratedFilesDir(), $fileName);
        $trait->setNamespace("GraphQL\Test");
        $trait->writeFile();

        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php", $trait->getWritePath());
    }

    /**
     * @throws Exception
     *
     * @depends testEmptyTrait
     */
    public function testTraitWithEmptyNamespace()
    {
        $fileName = 'EmptyTrait';
        $trait = new TraitFile(static::getGeneratedFilesDir(), $fileName);
        $trait->setNamespace('');
        $trait->writeFile();

        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php", $trait->getWritePath());
    }

    /**
     * @throws Exception
     *
     * @depends testEmptyTrait
     */
    public function testTraitWithImports()
    {
        $fileName = 'TraitWithImports';
        $trait = new TraitFile(static::getGeneratedFilesDir(), $fileName);
        $trait->addImport("GraphQL\Query");
        $trait->addImport("GraphQL\Client");
        $trait->writeFile();

        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php", $trait->getWritePath());
    }

    /**
     * @throws Exception
     *
     * @depends testEmptyTrait
     */
    public function testTraitWithEmptyImport()
    {
        $fileName = 'EmptyTrait';
        $trait = new TraitFile(static::getGeneratedFilesDir(), $fileName);
        $trait->addImport("");
        $trait->writeFile();

        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php", $trait->getWritePath());
    }

    /**
     * @throws Exception
     *
     * @depends testTraitWithNamespace
     * @depends testTraitWithImports
     */
    public function testTraitWithNamespaceAndImports()
    {
        $fileName = 'TraitWithNamespaceAndImports';
        $trait = new TraitFile(static::getGeneratedFilesDir(), $fileName);
        $trait->setNamespace("GraphQL\\Test");
        $trait->addImport("GraphQL\\Query");
        $trait->addImport("GraphQL\\Client");
        $trait->writeFile();

        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php", $trait->getWritePath());
    }

    /**
     * @throws Exception
     *
     * @depends testEmptyTrait
     */
    public function testTraitWithProperties()
    {
        $fileName = 'TraitWithProperties';
        $trait = new TraitFile(static::getGeneratedFilesDir(), $fileName);
        $trait->addProperty('property1');
        $trait->addProperty('propertyTwo');
        $trait->addProperty('property_three');
        $trait->writeFile();

        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php" , $trait->getWritePath());

        return $trait;
    }

    /**
     * @throws Exception
     *
     * @depends testTraitWithProperties
     */
    public function testTraitWithEmptyProperty()
    {
        $fileName = 'EmptyTrait';
        $trait = new TraitFile(static::getGeneratedFilesDir(), $fileName);
        $trait->addProperty('');
        $trait->writeFile();

        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php" , $trait->getWritePath());
    }

    /**
     * @param TraitFile $trait
     *
     * @depends clone testTraitWithProperties
     */
    public function testTraitWithDuplicateProperties(TraitFile $trait)
    {
        // Adding the same property again
        $trait->addProperty('property1');

        $fileName = $trait->getFileName();
        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php" , $trait->getWritePath());
    }

    /**
     * @throws Exception
     *
     * @depends testTraitWithProperties
     */
    public function testTraitWithPropertiesAndValues()
    {
        $fileName = 'TraitWithPropertiesAndValues';
        $trait = new TraitFile(static::getGeneratedFilesDir(), $fileName);
        $trait->addProperty('propertyOne', null);
        $trait->addProperty('propertyTwo', 2);
        $trait->addProperty('propertyThree', 'three');
        $trait->addProperty('propertyFour', false);
        $trait->addProperty('propertyFive', true);
        $trait->writeFile();

        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php" , $trait->getWritePath());
    }

    /**
     * @throws Exception
     *
     * @depends testEmptyTrait
     */
    public function testTraitWithOneMethod()
    {
        $fileName = 'TraitWithOneMethod';
        $trait = new TraitFile(static::getGeneratedFilesDir(), $fileName);
        $trait->addMethod('public function testTheTrait() {
    print "test!";
    die();
}'
        );
        $trait->writeFile();

        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php" , $trait->getWritePath());
    }

    /**
     * @throws Exception
     *
     * @depends testTraitWithOneMethod
     */
    public function testTraitWithMultipleMethods()
    {
        $fileName = 'TraitWithMultipleMethods';
        $trait = new TraitFile(static::getGeneratedFilesDir(), $fileName);
        $trait->addMethod('public function testTheTrait() {
    $this->innerTest();
    die();
}'
        );
        $trait->addMethod('private function innerTest() {
    print "test!";
    return 0;
}'
        );
        $trait->writeFile();

        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php" , $trait->getWritePath());
    }

    /**
     * @throws Exception
     *
     * @depends testEmptyTrait
     */
    public function testTraitWithEmptyMethod()
    {
        $fileName = 'EmptyTrait';
        $trait = new TraitFile(static::getGeneratedFilesDir(), $fileName);
        $trait->addMethod('');
        $trait->writeFile();

        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php" , $trait->getWritePath());
    }

    /**
     * @throws Exception
     *
     * @depends testTraitWithProperties
     * @depends testTraitWithMultipleMethods
     */
    public function testTraitWithPropertiesAndMethods()
    {
        $fileName = 'TraitWithPropertiesAndMethods';
        $trait = new TraitFile(static::getGeneratedFilesDir(), $fileName);
        $trait->addProperty('propOne');
        $trait->addProperty('propTwo', true);
        $trait->addMethod('public function getProperties() {
    return [$this->propOne, $this->propTwo];
}'
        );
        $trait->addMethod('public function clearProperties() {
    $this->propOne = 1;
    $this->propTwo = 2;
}'
        );
        $trait->writeFile();

        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php" , $trait->getWritePath());
    }

    /**
     * @throws Exception
     *
     * @depends testTraitWithNamespaceAndImports
     * @depends testTraitWithPropertiesAndMethods
     */
    public function testTraitWithEverything()
    {
        $fileName = 'TraitWithEverything';
        $trait = new TraitFile(static::getGeneratedFilesDir(), $fileName);
        $trait->setNamespace("GraphQL\\Test");
        $trait->addImport("GraphQL\\Query");
        $trait->addImport("GraphQL\\Client");
        $trait->addProperty('propOne');
        $trait->addProperty('propTwo', true);
        $trait->addMethod('public function getProperties() {
    return [$this->propOne, $this->propTwo];
}'
        );
        $trait->addMethod('public function clearProperties() {
    $this->propOne = 1;
    $this->propTwo = 2;
}'
        );
        $trait->writeFile();

        $this->assertFileEquals(static::getExpectedFilesDir() . "/$fileName.php", $trait->getWritePath());
    }
}