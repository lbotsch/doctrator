<?php

namespace Model\Base;

/**
 * Base class of the Model\Sortable entity.
 */
abstract class Sortable implements \ArrayAccess
{
    protected $id;

    protected $title;

    protected $position;

    static protected $dataCamelCaseMap = array(
        'id' => 'Id',
        'title' => 'Title',
        'position' => 'Position',
    );

    /**
     * Returns if the entity is the first.
     *
     * @return bool Returns if the entity is the first.
     */
    public function isFirst()
    {
        return $this->getPosition() === $this->getRepository()->getMinPosition();
    }

    /**
     * Returns if the entity is the last.
     *
     * @return bool Returns if the entity is the last.
     */
    public function isLast()
    {
        return $this->getPosition() === $this->getRepository()->getMaxPosition();
    }

    /**
     * Returns the next entity.
     *
     * @return mixed The next entity if exists, if not false.
     */
    public function getNext()
    {
        $query = 'SELECT s FROM Model\Sortable s WHERE s.position > ?1 ORDER BY s.position ASC';
        $query = $this->getEntityManager()->createQuery($query);
        $query->setParameter(1, $this->getPosition());
        $query->setMaxResults(1);

        $results = $query->getResult();

        return $results ? $results[0] : false;
    }

    /**
     * Returns the previous entity.
     *
     * @return mixed The previous entity if exists, if not false.
     */
    public function getPrevious()
    {
        $query = 'SELECT s FROM Model\Sortable s WHERE s.position < ?1 ORDER BY s.position DESC';
        $query = $this->getEntityManager()->createQuery($query);
        $query->setParameter(1, $this->getPosition());
        $query->setMaxResults(1);

        $results = $query->getResult();

        return $results ? $results[0] : false;
    }

    /**
     * Swap the position with another entity.
     *
     * @param \Model\Sortable $entity The entity.
     *
     * @return void
     */
    public function swapWith($entity)
    {
        if (!$entity instanceof \Model\Sortable) {
            throw new \InvalidArgumentException('The entity is not an instance of \Model\Sortable.');
        }

        $oldPosition = $this->getPosition();
        $newPosition = $entity->getPosition();

        $em = $this->getEntityManager();

        $em->getConnection()->beginTransaction();

        $query = $em->createQuery('UPDATE Model\Sortable s SET s.position = ?1 WHERE s.id = ?2');
        $query->setParameter(1, $newPosition);
        $query->setParameter(2, $this->getId());
        $query->execute();
        $this->refresh();

        $query = $em->createQuery('UPDATE Model\Sortable s SET s.position = ?1 WHERE s.id = ?2');
        $query->setParameter(1, $oldPosition);
        $query->setParameter(2, $entity->getId());
        $query->execute();
        $entity->refresh();

        $em->getConnection()->commit();
    }

    /**
     * Move up the entity.
     *
     * @return void
     *
     * @throws \RuntimeException If the entity is the first.
     */
    public function moveUp()
    {
        if ($this->isFirst()) {
            throw new \RuntimeException('The entity is the first.');
        }

        $this->swapWith($this->getPrevious());
    }

    /**
     * Move down the entity.
     *
     * @return void
     *
     * @throws \RuntimeException If the entity is the last.
     */
    public function moveDown()
    {
        if ($this->isLast()) {
            throw new \RuntimeException('The entity is the last.');
        }

        $this->swapWith($this->getNext());
    }

    /**
     * Set the position.
     */
    public function sortableSetPosition()
    {
        $maxPosition = $this->getRepository()->getMaxPosition();

        if ($this->isNew()) {
            $position = $maxPosition + 1;
        } else {
            $changeSet = $this->getEntityManager()->getUnitOfWork()->getEntityChangeSet($this);
            if (!isset($changeSet['position'])) {
                return;
            }
            $oldPosition = $changeSet['position'][0];
            $position    = $changeSet['position'][1];
        }

        $this->setPosition($position);

        // move entities
        if ($this->isNew()) {
            $query = 'UPDATE Model\Sortable s SET s.position = s.position + 1 WHERE s.position >= ?1';
            $query = $this->getEntityManager()->createQuery($query);
            $query->setParameter(1, $position);
        } else {
            $sign = $position > $oldPosition ? '-' : '+';
            $query = "UPDATE Model\Sortable s SET s.position = s.position $sign 1 WHERE s.position BETWEEN ?1 AND ?2";
            $query = $this->getEntityManager()->createQuery($query);
            $query->setParameter(1, min($position, $oldPosition));
            $query->setParameter(2, max($position, $oldPosition));
        }

        $query->execute();
    }

    /**
     * Constructor.
     */
    public function __construct()
    {

    }

    /**
     * Set the id column value.
     *
     * @param mixed $value The column value.
     */
    public function setId($value)
    {
        $this->id = $value;
    }

    /**
     * Returns the id column value.
     *
     * @return mixed The column value.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the title column value.
     *
     * @param mixed $value The column value.
     */
    public function setTitle($value)
    {
        $this->title = $value;
    }

    /**
     * Returns the title column value.
     *
     * @return mixed The column value.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the position column value.
     *
     * @param mixed $value The column value.
     */
    public function setPosition($value)
    {
        $this->position = $value;
    }

    /**
     * Returns the position column value.
     *
     * @return mixed The column value.
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set data by name.
     *
     * @param string $name  The data name.
     * @param mixed  $value The value.
     *
     * @throws \InvalidArgumentException If the data does not exists.
     */
    public function set($name, $value)
    {
        if ('id' == $name) {
            return $this->setId($value);
        }
        if ('title' == $name) {
            return $this->setTitle($value);
        }
        if ('position' == $name) {
            return $this->setPosition($value);
        }

        throw new \InvalidArgumentException(sprintf('The data "%s" does not exists.', $name));
    }

    /**
     * Get data by name.
     *
     * @param string $name  The data name.
     *
     * @return mixed The data.
     *
     * @throws \InvalidArgumentException If the data does not exists.
     */
    public function get($name)
    {
        if ('id' == $name) {
            return $this->getId();
        }
        if ('title' == $name) {
            return $this->getTitle();
        }
        if ('position' == $name) {
            return $this->getPosition();
        }

        throw new \InvalidArgumentException(sprintf('The data "%s" does not exists.', $name));
    }

    /**
     * Import data from an array.
     *
     * @param array $array An array.
     *
     * @return void
     */
    public function fromArray($array)
    {
        if (isset($array['id'])) {
            $this->setId($array['id']);
        }
        if (isset($array['title'])) {
            $this->setTitle($array['title']);
        }
        if (isset($array['position'])) {
            $this->setPosition($array['position']);
        }
    }

    /**
     * Export the data to an array.
     *
     * @return array An array with the data.
     */
    public function toArray()
    {
        $array = array();

        $array['id'] = $this->getId();
        $array['title'] = $this->getTitle();
        $array['position'] = $this->getPosition();

        return $array;
    }

    /**
     * Returns the entity manager.
     *
     * @return \Doctrine\ORM\EntityManager The entity manager.
     */
    public function getEntityManager()
    {
        return \Doctrator\EntityManagerContainer::getEntityManager();
    }

    /**
     * Check if the entity manager is clear.
     *
     * @return void
     *
     * @throws \RuntimeException If the entity manager is not clear.
     */
    public function checkClearEntityManager()
    {
        static $reflection;

        $unitOfWork = $this->getEntityManager()->getUnitOfWork();

        if (null === $reflection) {
            $reflection = new \ReflectionProperty(get_class($unitOfWork), 'scheduledForDirtyCheck');
            $reflection->setAccessible(true);
        }

        if ($unitOfWork->hasPendingInsertions() || $reflection->getValue($unitOfWork) || $unitOfWork->getScheduledEntityDeletions()) {
            throw new \RuntimeException('The entity manager is not clear.');
        }
    }

    /**
     * Returns the repository.
     *
     * @return \Doctrine\ORM\EntityRepository The repository.
     */
    public function getRepository()
    {
        return $this->getEntityManager()->getRepository('Model\Sortable');
    }

    /**
     * Returns if the entity is new.
     *
     * @return bool If the entity is new.
     */
    public function isNew()
    {
        return !$this->getEntityManager()->getUnitOfWork()->isInIdentityMap($this);
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

        $originalData = $this->getEntityManager()->getUnitOfWork()->getOriginalEntityData($this);

        return array_diff($originalData, $this->toArray());
    }

    /**
     * Refresh the entity from the database.
     *
     * @return void
     */
    public function refresh()
    {
        $this->getEntityManager()->getUnitOfWork()->refresh($this);
    }

    /**
     * Save the entity.
     */
    public function save()
    {
        $this->checkClearEntityManager();

        $em = $this->getEntityManager();

        $em->persist($this);
        $em->flush();
    }

    /**
     * Delete the entity.
     */
    public function delete()
    {
        $this->checkClearEntityManager();

        $em = $this->getEntityManager();

        $em->remove($this);
        $em->flush();
    }

    /**
     * The prePersist event.
     */
    public function prePersist()
    {

    }

    /**
     * The postPersist event.
     */
    public function postPersist()
    {

    }

    /**
     * The preUpdate event.
     */
    public function preUpdate()
    {

    }

    /**
     * The postUpdate event.
     */
    public function postUpdate()
    {

    }

    /**
     * The preRemove event.
     */
    public function preRemove()
    {

    }

    /**
     * The postRemove event.
     */
    public function postRemove()
    {

    }

    /**
     * The onFlush event.
     */
    public function onFlush()
    {

    }

    /**
     * The postLoad event.
     */
    public function postLoad()
    {

    }

    /**
     * Load the metadata.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata The metadata class.
     */
    static public function loadMetadata(\Doctrine\ORM\Mapping\ClassMetadata $metadata)
    {
        $metadata->setChangeTrackingPolicy(\Doctrine\ORM\Mapping\ClassMetadata::CHANGETRACKING_DEFERRED_EXPLICIT);
        $metadata->setCustomRepositoryClass('Model\SortableRepository');
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_AUTO);
        $metadata->mapField(array(
            'fieldName' => 'id',
            'type' => 'integer',
            'id' => true,
        ));
        $metadata->mapField(array(
            'fieldName' => 'title',
            'type' => 'string',
        ));
        $metadata->mapField(array(
            'fieldName' => 'position',
            'type' => 'integer',
        ));
        $metadata->setLifecycleCallbacks(array(
            'prePersist' => array(
                0 => 'prePersist',
                1 => 'sortableSetPosition',
            ),
            'postPersist' => array(
                0 => 'postPersist',
            ),
            'preUpdate' => array(
                0 => 'preUpdate',
                1 => 'sortableSetPosition',
            ),
            'postUpdate' => array(
                0 => 'postUpdate',
            ),
            'preRemove' => array(
                0 => 'preRemove',
            ),
            'postRemove' => array(
                0 => 'postRemove',
            ),
            'onFlush' => array(
                0 => 'onFlush',
            ),
            'postLoad' => array(
                0 => 'postLoad',
            ),
        ));
    }

    /**
     * Returns the data CamelCase map.
     *
     * @return array The data CamelCase map.
     */
    static public function getDataCamelCaseMap()
    {
        return static::$dataCamelCaseMap;
    }

    /**
     * Throws an \LogicException because you cannot check if data exists.
     *
     * @throws \LogicException
     */
    public function offsetExists($name)
    {
        throw new \LogicException('You cannot check if data exists in the entity.');
    }

    /**
     * Set data in the entity.
     *
     * @param string $name  The data name.
     * @param mixed  $value The value.
     *
     * @see set()
     */
    public function offsetSet($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Returns data of the entity.
     *
     * @param string $name The data name.
     *
     * @return mixed Some data.
     *
     * @see get()
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * Throws a \LogicException because you cannot unset data in the entity.
     *
     * @throws \LogicException
     */
    public function offsetUnset($name)
    {
        throw new \LogicException('You cannot unset data in the entity.');
    }

    /**
     * Set data in the entity.
     *
     * @param string $name  The data name.
     * @param mixed  $value The value.
     *
     * @see set()
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Returns data of the entity.
     *
     * @param string $name The data name.
     *
     * @return mixed Some data.
     *
     * @see get()
     */
    public function __get($name)
    {
        return $this->get($name);
    }
}