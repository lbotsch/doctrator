<?php

namespace Model\Base;

/**
 * Base class of the Model\Taggable entity.
 */
abstract class Taggable implements \ArrayAccess
{
    protected $id;

    protected $title;

    static protected $dataCamelCaseMap = array(
        'id' => 'Id',
        'title' => 'Title',
    );

    /**
     * Add tags.
     *
     * @param mixed $tags The tags.
     *
     * @return void
     */
    public function addTags($tags)
    {
        $holder = $this->getTagHolder();

        foreach (\Doctrator\Behavior\Taggable::explodeAndCleanTags($tags) as $tag) {
            if (false !== $key = array_search($tag, $holder['remove'])) {
                $remove =& $holder['remove'];
                unset($remove[$key]);
            }

            if (in_array($tag, $this->getSavedTags())) {
                continue;
            }

            $holder['add'][] = $tag;
        }
    }

    /**
     * Remove tags.
     *
     * @param mixed $tags The tags.
     *
     * @return void
     */
    public function removeTags($tags)
    {
        $holder = $this->getTagHolder();

        foreach (\Doctrator\Behavior\Taggable::explodeAndCleanTags($tags) as $tag) {
            if (false !== $key = array_search($tag, $holder['add'])) {
                $add =& $holder['add'];
                unset($add[$key]);
            }

            if (!in_array($tag, $this->getSavedTags())) {
                continue;
            }

            $holder['remove'][] = $tag;
        }
    }

    /**
     * Remove all tags.
     *
     * @return void
     */
    public function removeAllTags()
    {
        $holder = $this->getTagHolder();

        $holder['add'] = array();
        $this->removeTags($this->getSavedTags());
    }

    /**
     * Returns the saved tags.
     *
     * @return array The saved tags.
     */
    public function getSavedTags()
    {
        $holder = $this->getTagHolder();

        if (null === $holder['saved']) {
            $holder['saved'] = array();

            $query = $this->getEntityManager()->createQuery('SELECT t, ta FROM Model\TaggableTagging t JOIN t.tag ta WHERE t.parent_id = ?1');
            $query->setParameter(1, $this->getId());
            foreach ($query->getResult() as $tagging) {
                $holder['saved'][] = $tagging->getTag()->getName();
            }
        }

        return $holder['saved'];
    }

    /**
     * Set the tags.
     *
     * @param mixed $tags The tags.
     *
     * @return void
     */
    public function setTags($tags)
    {
        $this->removeAllTags();
        $this->addTags($tags);
    }

    /**
     * Returns the tags.
     *
     * @return array The tags.
     */
    public function getTags()
    {
        $holder = $this->getTagHolder();

        $add    = $holder['add'];
        $remove = $holder['remove'];
        $saved  = $this->getSavedTags();

        return array_values(array_diff(array_merge($add, $saved), $remove));
    }

    protected function getTagHolder()
    {
        static $holder;

        if (null === $holder) {
            $holder = new \ArrayObject(array(
                'add'    => array(),
                'remove' => array(),
                'saved'  => null,
            ));
        }

        return $holder;
    }

    /**
     * Save the tags.
     *
     * @return void
     */
    public function saveTags()
    {
        $holder = $this->getTagHolder();

        foreach ($holder['add'] as $tag) {
            $query = $this->getEntityManager()->createQuery('SELECT t FROM Model\TaggableTag t WHERE t.name = ?1');
            $query->setParameter(1, $tag);
            if ($results = $query->getResult()) {
                $tagEntity = $results[0];
            } else {
                $tagEntity = new \Model\TaggableTag();
                $tagEntity->setName($tag);
                $tagEntity->save();
            }

            $taggingEntity = new \Model\TaggableTagging();
            $taggingEntity->setParent($this);
            $taggingEntity->setTag($tagEntity);
            $taggingEntity->save();
        }

        foreach ($holder['remove'] as $tag) {
            $query = $this->getEntityManager()->createQuery('SELECT t FROM Model\TaggableTag t WHERE t.name = ?1');
            $query->setParameter(1, $tag);
            if ($results = $query->getResult()) {
                $query = $this->getEntityManager()->createQuery('DELETE FROM Model\TaggableTagging t WHERE t.parent_id = ?1 AND t.tag_id = ?2');
                $query->setMaxResults(1);
                $query->setParameter(1, $this->getId());
                $query->setParameter(2, $results[0]->getId());
                $query->execute();
            }
        }

        $holder['add']    = array();
        $holder['remove'] = array();
        $holder['saved']  = null;
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
        return $this->getEntityManager()->getRepository('Model\Taggable');
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
        $metadata->setCustomRepositoryClass('Model\TaggableRepository');
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