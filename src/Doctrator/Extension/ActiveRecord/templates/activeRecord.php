<?php

    /**
     * Returns the entity manager.
     *
     * @return \Doctrine\ORM\EntityManager The entity manager.
     */
    static public function entityManager()
    {
        return \Doctrator\EntityManagerContainer::getEntityManager();
    }

    /**
     * Returns the repository.
     *
     * @return \Doctrine\ORM\EntityRepository The repository.
     */
    static public function repository()
    {
        return static::entityManager()->getRepository('{{ class }}');
    }

    /**
     * Create a query builder for this entity name.
     *
     * @param string $alias The alias.
     *
     * @return \Doctrine\ORM\QueryBuilder A query builder
     */
    static public function queryBuilder($alias)
    {
        return static::repository()->createQueryBuilder($alias);
    }

    /**
     * Check if the entity manager is clear.
     *
     * @return void
     *
     * @throws \RuntimeException If the entity manager is not clear.
     */
    public function checkEntityManagerIsClear()
    {
        static $reflection;

        $unitOfWork = static::entityManager()->getUnitOfWork();

        if (null === $reflection) {
            $reflection = new \ReflectionProperty(get_class($unitOfWork), 'scheduledForDirtyCheck');
            $reflection->setAccessible(true);
        }

        if ($unitOfWork->hasPendingInsertions() || $reflection->getValue($unitOfWork) || $unitOfWork->getScheduledEntityDeletions()) {
            throw new \RuntimeException('The entity manager is not clear.');
        }
    }

    /**
     * Returns if the entity is new.
     *
     * @return bool If the entity is new.
     */
    public function isNew()
    {
        return !static::entityManager()->getUnitOfWork()->isInIdentityMap($this);
    }

    /**
     * Throws an \RuntimeException if the entity is not new.
     */
    public function checkIsNew()
    {
        if (!$this->isNew()) {
            throw new \RuntimeException('The entity is not new.');
        }
    }

    /**
     * Throws an \RuntimeException if the entity is new.
     */
    public function checkIsNotNew()
    {
        if ($this->isNew()) {
            throw new \RuntimeException('The entity is new.');
        }
    }

    /**
     * Returns if the entity is modified.
     *
     * @return bool If the entity is modified.
     */
    public function isModified()
    {
        return (bool) count($this->getModified());
    }

    /**
     * Throws an \RuntimeException if the entity is not modified.
     */
    public function checkIsModified()
    {
        if (!$this->isModified()) {
            throw new \RuntimeException('The entity is not modified.');
        }
    }

    /**
     * Throws an \RuntimeException if the entity is modified.
     */
    public function checkIsNotModified()
    {
        if ($this->isModified()) {
            throw new \RuntimeException('The entity is modified.');
        }
    }

    /**
     * Returns the entity modifications
     *
     * @return array The entity modifications.
     */
    public function getModified()
    {
        if ($this->isNew()) {
            return array();
        }

        $originalData = static::entityManager()->getUnitOfWork()->getOriginalEntityData($this);

        return array_diff($originalData, $this->toArray());
    }

    /**
     * Refresh the entity from the database.
     *
     * @return void
     */
    public function refresh()
    {
        static::entityManager()->getUnitOfWork()->refresh($this);
    }

    /**
     * Returns the change set of the entity.
     *
     * @return array The change set.
     */
    public function changeSet()
    {
        return static::entityManager()->getUnitOfWork()->getEntityChangeSet($this);
    }

    /**
     * Save the entity.
     */
    public function save()
    {
        $this->checkEntityManagerIsClear();

        $em = static::entityManager();

        $em->persist($this);
        $em->flush();
    }

    /**
     * Delete the entity.
     */
    public function delete()
    {
        $this->checkEntityManagerIsClear();

        $em = static::entityManager();

        $em->remove($this);
        $em->flush();
    }
