<?php

namespace App\Entities;

/**
 * @Entity
 * @Table(name="preferences")
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