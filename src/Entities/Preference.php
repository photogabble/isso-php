<?php

namespace App\Entities;

/**
 * @Entity
 * @Table(name="preferences")
 * @Entity(repositoryClass="App\Repositories\Preferences")
 */
class Preference
{
    /**
     * @var string
     * @Id @Column(type="string")
     */
    private $key;

    /**
     * @var string
     * @Column(type="string")
     */
    private $value;

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key)
    {
        $this->key = $key;
    }
}