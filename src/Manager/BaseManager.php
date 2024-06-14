<?php

namespace App\Manager;


use Doctrine\Common\Proxy\Proxy;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * Class BaseManager
 *
 * @package AppBundle\Manager
 * @author  Joelio
 */
abstract class BaseManager
{
    /** @var \Doctrine\ORM\EntityManager $em */
    protected $em;

    /** @var mixed $class */
    protected $class;

    /** @var \Doctrine\ORM\EntityRepository $repository */
    protected $repository;

    /**
     * @param EntityManager $em
     * @param mixed         $class
     */
    public function __construct(EntityManager $em, $class)
    {
        $this->em         = $em;
        $this->repository = $em->getRepository($class);
        $this->class      = $class;
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return mixed
     */
    public function createObject()
    {
        $class = $this->getClass();

        return new $class();
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $id
     *
     * @return bool|Proxy|null|object
     * @throws ORMException
     */
    public function getReference($id)
    {
        return $this->em->getReference($this->class, $id);
    }

    /**
     * @param mixed $id
     *
     * @return null|object
     */
    public function load($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     *
     * @return object|null
     */
    public function loadOneBy(array $criteria, array $orderBy = null)
    {
        return $this->repository->findOneBy($criteria, $orderBy);
    }

    /**
     * @param array      $criteria
     * @param array|null $order
     *
     * @return array
     */
    public function loadBy(array $criteria, array $order = null)
    {
        return $this->repository->findBy($criteria, $order);
    }

    /**
     * @return array
     */
    public function loadAll()
    {
        return $this->repository->findAll();
    }

    /**
     * @param mixed $entity
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function persist($entity)
    {
        $this->em->persist($entity);
    }

    /**
     * @param null $entity
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush($entity = null)
    {
        $this->em->flush($entity);
    }

    /**
     * @param mixed $entity
     * @param bool  $flush
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save($entity, $flush = true)
    {
        $this->em->persist($entity);

        if (true === $flush) {
            $this->em->flush($entity);
        }
    }

    /**
     * @param mixed $entity
     *
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     */
    public function delete($entity)
    {
        $this->em->remove($entity);
        $this->em->flush($entity);
    }

    /**
     * @param null $entityName
     *
     * @throws \Doctrine\Persistence\Mapping\MappingException
     */
    public function clear($entityName = null)
    {
        $this->em->clear($entityName);
    }

    /**
     * @param null $entityName
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function refresh($entityName = null)
    {
        $this->em->refresh($entityName);
    }

    /**
     * Get SQL from query
     *
     * @param QueryBilderDql $query
     *
     * @return int
     * @author Yosef Kaminskyi
     */
    public function getFullSQL($query)
    {
        $sql        = $query->getSql();
        $paramsList = $this->getListParamsByDql($query->getDql());
        $paramsArr  = $this->getParamsArray($query->getParameters());
        $fullSql    = '';
        for ($i = 0; $i < strlen($sql); $i ++) {
            if ($sql[$i] == '?') {
                $nameParam = array_shift($paramsList);

                if (is_string($paramsArr[$nameParam])) {
                    $fullSql .= '"' . addslashes($paramsArr[$nameParam]) . '"';
                } elseif (is_array($paramsArr[$nameParam])) {
                    $sqlArr = '';
                    foreach ($paramsArr[$nameParam] as $var) {
                        if (!empty($sqlArr)) {
                            $sqlArr .= ',';
                        }

                        if (is_string($var)) {
                            $sqlArr .= '"' . addslashes($var) . '"';
                        } else {
                            $sqlArr .= $var;
                        }
                    }
                    $fullSql .= $sqlArr;
                } elseif (is_object($paramsArr[$nameParam])) {
                    switch (get_class($paramsArr[$nameParam])) {
                        case 'DateTime':
                            $fullSql .= "'" . $paramsArr[$nameParam]->format('Y-m-d H:i:s') . "'";
                            break;
                        default:
                            $fullSql .= $paramsArr[$nameParam]->getId();
                    }

                } else {
                    $fullSql .= $paramsArr[$nameParam];
                }

            } else {
                $fullSql .= $sql[$i];
            }
        }

        return $fullSql;
    }

    /**
     * Get query params list
     *
     * @param Doctrine\ORM\Query\Parameter $paramObj
     *
     * @return array
     * @author Yosef Kaminskyi <yosefk@spotoption.com>
     */
    protected function getParamsArray($paramObj)
    {
        $parameters = [];
        foreach ($paramObj as $val) {
            /* @var $val Doctrine\ORM\Query\Parameter */
            $parameters[$val->getName()] = $val->getValue();
        }

        return $parameters;
    }

    /**
     * @param $dql
     *
     * @return array
     */
    public function getListParamsByDql($dql)
    {
        $parsedDql = preg_split("/:/", $dql);
        $length    = count($parsedDql);
        $parmeters = [];
        for ($i = 1; $i < $length; $i ++) {
            if (ctype_alpha($parsedDql[$i][0])) {
                $param       = (preg_split("/[' ' )]/", $parsedDql[$i]));
                $parmeters[] = $param[0];
            }
        }

        return $parmeters;
    }
}