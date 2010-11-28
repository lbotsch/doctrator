<?php

namespace Model\Base;

/**
 * Base class of the Model\Article entity.
 */
abstract class Article implements \ArrayAccess
{
    protected $id;

    protected $title;

    protected $slug;

    protected $content;

    protected $source;

    protected $is_active = true;

    protected $score;

    protected $date;

    protected $category;

    static protected $dataCamelCaseMap = array(
        'id' => 'Id',
        'title' => 'Title',
        'slug' => 'Slug',
        'content' => 'Content',
        'source' => 'Source',
        'is_active' => 'IsActive',
        'score' => 'Score',
        'date' => 'Date',
        'category' => 'Category',
    );

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
     * Set the slug column value.
     *
     * @param mixed $value The column value.
     */
    public function setSlug($value)
    {
        $this->slug = $value;
    }

    /**
     * Returns the slug column value.
     *
     * @return mixed The column value.
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the content column value.
     *
     * @param mixed $value The column value.
     */
    public function setContent($value)
    {
        $this->content = $value;
    }

    /**
     * Returns the content column value.
     *
     * @return mixed The column value.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the source column value.
     *
     * @param mixed $value The column value.
     */
    public function setSource($value)
    {
        $this->source = $value;
    }

    /**
     * Returns the source column value.
     *
     * @return mixed The column value.
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set the is_active column value.
     *
     * @param mixed $value The column value.
     */
    public function setIsActive($value)
    {
        $this->is_active = $value;
    }

    /**
     * Returns the is_active column value.
     *
     * @return mixed The column value.
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set the score column value.
     *
     * @param mixed $value The column value.
     */
    public function setScore($value)
    {
        $this->score = $value;
    }

    /**
     * Returns the score column value.
     *
     * @return mixed The column value.
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set the date column value.
     *
     * @param mixed $value The column value.
     */
    public function setDate($value)
    {
        $this->date = $value;
    }

    /**
     * Returns the date column value.
     *
     * @return mixed The column value.
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set the category relation value.
     *
     * @param mixed $value The relation value.
     */
    public function setCategory($value)
    {
        $this->category = $value;
    }

    /**
     * Returns the category relation value.
     *
     * @return mixed The relation value.
     */
    public function getCategory()
    {
        return $this->category;
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
        if ('slug' == $name) {
            return $this->setSlug($value);
        }
        if ('content' == $name) {
            return $this->setContent($value);
        }
        if ('source' == $name) {
            return $this->setSource($value);
        }
        if ('is_active' == $name) {
            return $this->setIsActive($value);
        }
        if ('score' == $name) {
            return $this->setScore($value);
        }
        if ('date' == $name) {
            return $this->setDate($value);
        }
        if ('category' == $name) {
            return $this->setCategory($value);
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
        if ('slug' == $name) {
            return $this->getSlug();
        }
        if ('content' == $name) {
            return $this->getContent();
        }
        if ('source' == $name) {
            return $this->getSource();
        }
        if ('is_active' == $name) {
            return $this->getIsActive();
        }
        if ('score' == $name) {
            return $this->getScore();
        }
        if ('date' == $name) {
            return $this->getDate();
        }
        if ('category' == $name) {
            return $this->getCategory($value);
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
        if (isset($array['slug'])) {
            $this->setSlug($array['slug']);
        }
        if (isset($array['content'])) {
            $this->setContent($array['content']);
        }
        if (isset($array['source'])) {
            $this->setSource($array['source']);
        }
        if (isset($array['is_active'])) {
            $this->setIsActive($array['is_active']);
        }
        if (isset($array['score'])) {
            $this->setScore($array['score']);
        }
        if (isset($array['date'])) {
            $this->setDate($array['date']);
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
        $array['slug'] = $this->getSlug();
        $array['content'] = $this->getContent();
        $array['source'] = $this->getSource();
        $array['is_active'] = $this->getIsActive();
        $array['score'] = $this->getScore();
        $array['date'] = $this->getDate();

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
        return $this->getEntityManager()->getRepository('Model\Article');
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
        $metadata->setCustomRepositoryClass('Model\ArticleRepository');
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_AUTO);
        $metadata->mapField(array(
            'fieldName' => 'id',
            'type' => 'integer',
            'id' => true,
        ));
        $metadata->mapField(array(
            'fieldName' => 'title',
            'type' => 'string',
            'length' => 100,
        ));
        $metadata->mapField(array(
            'fieldName' => 'slug',
            'type' => 'string',
            'length' => 110,
            'unique' => true,
            'columnName' => 'title_slug',
        ));
        $metadata->mapField(array(
            'fieldName' => 'content',
            'type' => 'text',
        ));
        $metadata->mapField(array(
            'fieldName' => 'source',
            'type' => 'string',
            'length' => 255,
            'nullable' => true,
        ));
        $metadata->mapField(array(
            'fieldName' => 'is_active',
            'type' => 'boolean',
        ));
        $metadata->mapField(array(
            'fieldName' => 'score',
            'type' => 'decimal',
            'precision' => 2,
            'scale' => 4,
        ));
        $metadata->mapField(array(
            'fieldName' => 'date',
            'type' => 'date',
        ));
        $metadata->mapManyToOne(array(
            'fieldName' => 'category',
            'targetEntity' => 'Model\\Category',
            'cascade' => array(
                0 => 'persist',
                1 => 'remove',
            ),
        ));
        $metadata->table['uniqueConstraints']['my_slug_index'] = array(
            'columns' => array(
                0 => 'title_slug',
            ),
        );
        $metadata->table['indexes']['is_active_date'] = array(
            'columns' => array(
                0 => 'is_active',
                1 => 'date',
            ),
        );
        $metadata->setLifecycleCallbacks(array(
            'prePersist' => array(
                0 => 'prePersist',
            ),
            'postPersist' => array(
                0 => 'postPersist',
            ),
            'preUpdate' => array(
                0 => 'preUpdate',
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