<?php

namespace Model\Base;

/**
 * Base class of the Model\Timestampable entity.
 */
abstract class Timestampable implements \ArrayAccess
{
    protected $id;

    protected $title;

    protected $created_at;

    protected $updated_at;

    static protected $dataCamelCaseMap = array(
        'id' => 'Id',
        'title' => 'Title',
        'created_at' => 'CreatedAt',
        'updated_at' => 'UpdatedAt',
    );

    public function updateTimestampableCreated()
    {
        $this->setCreatedAt(new \DateTime());
    }

    public function updateTimestampableUpdated()
    {
        $this->setUpdatedAt(new \DateTime());
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
     * Set the created_at column value.
     *
     * @param mixed $value The column value.
     */
    public function setCreatedAt($value)
    {
        $this->created_at = $value;
    }

    /**
     * Returns the created_at column value.
     *
     * @return mixed The column value.
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set the updated_at column value.
     *
     * @param mixed $value The column value.
     */
    public function setUpdatedAt($value)
    {
        $this->updated_at = $value;
    }

    /**
     * Returns the updated_at column value.
     *
     * @return mixed The column value.
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
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
        if ('created_at' == $name) {
            return $this->setCreatedAt($value);
        }
        if ('updated_at' == $name) {
            return $this->setUpdatedAt($value);
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
        if ('created_at' == $name) {
            return $this->getCreatedAt();
        }
        if ('updated_at' == $name) {
            return $this->getUpdatedAt();
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
        if (isset($array['created_at'])) {
            $this->setCreatedAt($array['created_at']);
        }
        if (isset($array['updated_at'])) {
            $this->setUpdatedAt($array['updated_at']);
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
        $array['created_at'] = $this->getCreatedAt();
        $array['updated_at'] = $this->getUpdatedAt();

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
        return $this->getEntityManager()->getRepository('Model\Timestampable');
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
        $metadata->setCustomRepositoryClass('Model\TimestampableRepository');
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
            'fieldName' => 'created_at',
            'type' => 'datetime',
            'nullable' => true,
        ));
        $metadata->mapField(array(
            'fieldName' => 'updated_at',
            'type' => 'datetime',
            'nullable' => true,
        ));
        $metadata->setLifecycleCallbacks(array(
            'prePersist' => array(
                0 => 'prePersist',
                1 => 'updateTimestampableCreated',
            ),
            'postPersist' => array(
                0 => 'postPersist',
            ),
            'preUpdate' => array(
                0 => 'preUpdate',
                1 => 'updateTimestampableUpdated',
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