<?php

namespace Model\Base;

/**
 * Base class of the repository of the Model\Sortable entity.
 */
abstract class SortableRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Returns the min position.
     *
     * @return integer The min position.
     */
    public function getMinPosition()
    {
        $result = $this->getEntityManager()
            ->createQuery('SELECT MIN(s.position) FROM Model\Sortable s')
            ->getSingleScalarResult()
        ;

        return $result ? (int) $result : null;
    }

    /**
     * Returns the max position.
     *
     * @return integer The max position.
     */
    public function getMaxPosition()
    {
        $result = $this->getEntityManager()
            ->createQuery('SELECT MAX(s.position) FROM Model\Sortable s')
            ->getSingleScalarResult()
        ;

        return $result ? (int) $result : null;
    }
}