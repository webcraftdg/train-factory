<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }


    /**
     * create query build with condition and orderby
     *
     * @param  array                      $params
     * @param  array                      $orderBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryFindWithConditions(array $params = [], array $orderBy = ['s.name' => 'ASC']) : QueryBuilder
    {
        $querybuilder = $this->prepareQueryBuilder();
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

    /**
     * prepare query builder
     *
     * @param  bool                       $displayArchived
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function prepareQueryBuilder(bool $displayArchived = false) : QueryBuilder
    {
        $querybuilder = $this->createQueryBuilder('s');
        if ($displayArchived === false) {
            $querybuilder->andWhere('s.dateArchived IS NULL');
        }
        return $querybuilder;
    }
}
