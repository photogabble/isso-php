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
}