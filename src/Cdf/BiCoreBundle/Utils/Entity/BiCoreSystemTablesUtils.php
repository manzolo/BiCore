<?php

namespace Cdf\BiCoreBundle\Utils\Entity;

use Cdf\BiCoreBundle\Entity\Ruoli;
use Doctrine\ORM\EntityManager;

class BiCoreSystemTablesUtils
{
    /* @var $em EntityManager */
    private $em;
    private $entities = array();

    public function __construct($em)
    {
        $this->em = $em;
        $this->entities[Ruoli::class] = array('priority' => 10);
        $this->entities["Cdf\BiCoreBundle\Entity\Operatori"] = array('priority' => 50);
        $this->entities["Cdf\BiCoreBundle\Entity\Permessi"] = array('priority' => 100);
        $this->entities["Cdf\BiCoreBundle\Entity\Storicomodifiche"] = array('priority' => 110);
        $this->entities["Cdf\BiCoreBundle\Entity\Colonnetabelle"] = array('priority' => 120);
        $this->entities["Cdf\BiCoreBundle\Entity\Opzionitabelle"] = array('priority' => 150);
        $this->entities["Cdf\BiCoreBundle\Entity\Menuapplicazione"] = array('priority' => 160);

        $this->countEntitiesRows();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function countEntitiesRows()
    {
        $records = $this->entities;
        foreach ($records as $entity => $detail) {
            $qb = $this->em;
            $numrows = $qb->createQueryBuilder()
                    ->select('count(table.id)')
                    ->from($entity, 'table')
                    ->getQuery()
                    ->getSingleScalarResult();
            $this->entities[$entity]['rows'] = $numrows;
        }
    }

    public function getSystemEntities()
    {
        return $this->entities;
    }

    public function getDefaultDataSystemEntities()
    {
        return $this->entities;
    }
}
