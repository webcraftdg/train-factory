<?php

namespace App\Repository;

use App\Entity\Exercise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Exercise>
 */
class ExerciseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exercise::class);
    }

    /**
     * create query build with condition and orderby
     *
     * @param  array                      $params
     * @param  array                      $orderBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryFindWithConditions(array $params = [], array $orderBy = ['e.name' => 'ASC']) : QueryBuilder
    {
        $querybuilder = $this->createQueryBuilder('e');
        foreach($params as $field => $value) {
            $conditionalAttribute = ':'.$field;
            $querybuilder->andWhere($field.'='.$conditionalAttribute)
            ->setParameter($field, $value);
        }
        foreach($orderBy as $field => $order) {
            $querybuilder->addOrderBy($field, $order);
        }
        return $querybuilder;
    }
}
