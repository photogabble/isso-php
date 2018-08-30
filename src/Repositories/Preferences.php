<?php

namespace App\Repositories;

use App\Entities\Preference;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;

class Preferences extends EntityRepository
{

    private $defaults = [];

    /**
     * Preferences constructor.
     *
     * @param $em
     * @param Mapping\ClassMetadata $class
     * @throws \Exception
     */
    public function __construct($em, Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);

        $this->defaults = [
            'session-key' => bin2hex(random_bytes(24)),
        ];

        $added = [];

        foreach ($this->defaults as $key => $value) {
            if (!$this->get($key)) {
                array_push($added, $this->set($key, $value));
            }
        }

        if (count($added) > 0) {
            $this->getEntityManager()->flush($added);
        }
    }

    /**
     * Port of isso python isso.db.preferences.get
     * @see https://github.com/posativ/isso/blob/master/isso/db/preferences.py#L25
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        try{
            return $this->createQueryBuilder('c')
                ->select('c.value')
                ->where('c.key = :key')
                ->setParameters([
                    'key' => $key,
                ])
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Port of isso python isso.db.preferences.set
     * @see https://github.com/posativ/isso/blob/master/isso/db/preferences.py#L34
     * @param string $key
     * @param mixed $value
     * @return Preference
     * @throws \Doctrine\ORM\ORMException
     */
    public function set(string $key, $value): Preference
    {
        $entity = new Preference();
        $entity->setKey($key);
        $entity->setValue($value);
        $this->getEntityManager()->persist($entity);
        return $entity;
    }

}