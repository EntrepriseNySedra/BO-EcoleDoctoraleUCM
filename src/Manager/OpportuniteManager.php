<?php

namespace App\Manager;

/**
 * Class OpportuniteManager
 *
 * @package App\Manager
 */
use Doctrine\ORM\EntityManagerInterface;

class OpportuniteManager extends BaseManager
{
   /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getAllWithParent() {
        $query = "SELECT * FROM opportunite;
        ";
    
        $connection = $this->em->getConnection();
        $statement = $connection->executeQuery($query);
    
        return $statement->fetchAllAssociative(); // Use fetchAllAssociative for associative arrays
    }
    

}