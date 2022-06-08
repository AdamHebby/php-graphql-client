<?php

namespace GraphQL;

use GraphQL\Util\StringLiteralFormatter;

/**
 * Class Variable
 *
 * @package GraphQL
 */
class Variable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $required;

    /**
     * @var null|string|int|float|bool
     */
    protected $defaultValue;

    /**
     * Variable constructor.
     *
     * @param string $name
     * @param string $type
     * @param bool   $isRequired
     * @param null   $defaultValue
     */
    public function __construct(string $name, string $type, bool $isRequired = false, $defaultValue = null)
    {
        $this->name         = $name;
        $this->type         = $type;
        $this->required     = $isRequired;
        $this->defaultValue = $defaultValue;
    }

    /**
     * Get Variable name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get Variable type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Is required?
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Get Variable default value
     *
     * @return mixed
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $varString = "\$$this->name: $this->type";
        if ($this->required) {
            $varString .= '!';
        } elseif (!empty($this->defaultValue)) {
            $varString .= '=' . StringLiteralFormatter::formatValueForRHS($this->defaultValue);
        }

        return $varString;
    }
}