<?php

namespace App\Manager;

/**
 * Class RubriqueManager
 *
 * @package App\Manager
 */
class RubriqueManager extends BaseManager
{

    /**
     * Get All rubrique trees with parent fields
     * return rubrique[]
     * */
    public function getAllWithParent() {
        
        $query = "SELECT r0.*, r1.title as parent_title FROM rubrique r0 
            INNER JOIN rubrique r1 ON r1.id = r0.parent
            ORDER BY parent_title ASC";

        // $query = "
        //     SELECT T2.*
        //     FROM ( 
        //         SELECT 
        //             @r AS _id, 
        //             (SELECT @r := parent FROM rubrique WHERE id = _id) AS parent_id, 
        //             @l := @l + 1 AS lvl 
        //         FROM (SELECT @r := @r+1, @l := 0) vars, rubrique h 
        //         WHERE @r <> 0) T1 
        //     JOIN rubrique T2 ON T1._id = T2.id 
        //     ORDER BY T1.lvl DESC
        // ";

        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->executeQuery();

        return $statement->fetchAll();

    }

    /**
     * Get All rubrique trees struct by parent child
     * return mixed[]
     * */
    public function getAllStructParentChild() {
        
        $query = "
            SELECT r1.id, r1.title, r1.uuid, r1.parent, r2.title as parent_name, r2.code as parent_code, r2.id as parent_id FROM rubrique r1 
            INNER JOIN rubrique r2 ON r2.id = r1.parent 
            ORDER BY r1.parent ASC;";
        
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->executeQuery();
        $rubriques = $statement->fetchAll();
        $result = [];
        foreach($rubriques as $item) {
            $resultItem = [];
            if($item['parent_code'] == 'ROOT' && !array_key_exists($item['id'], $result)) {
                $result[$item['id']] = [];
                $result[$item['id']][] = $item['title'];
                $result[$item['id']][] = $item['uuid'];
            }
            $resultItem['id'] = $item['id'];
            $resultItem['uuid'] = $item['uuid'];
            $resultItem['title'] = $item['title'];
            if(array_key_exists($item['parent_id'], $result)){
                array_push($result[$item['parent_id']], $resultItem);
            }
        }

        return $result;
    }

    /**
     * Get parentable rubrique (level1 and level2: Rubrique and subribrique)
     * return rubrique[]
     */
    public function getParentable() {
        $query = "
            SELECT r1.id, r1.title, r1.uuid, r1.parent, r2.title as parent_name, r2.code as parent_code, r2.id as parent_id FROM rubrique r1 
            LEFT JOIN rubrique r2 ON r2.id = r1.parent 
            WHERE r1.code = 'ROOT' OR r2.code = 'ROOT'
            ORDER BY r1.title ASC;";
        
        $connection = $this->em->getConnection();
        $statement = $connection->prepare($query);
        $statement->executeQuery();
        
        return $statement->fetchAll();
    }
}