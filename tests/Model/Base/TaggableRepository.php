<?php

namespace Model\Base;

/**
 * Base class of the repository of the Model\Taggable entity.
 */
abstract class TaggableRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Returns all tags.
     *
     * @return array The tags.
     */
    public function getTags()
    {
        $tagIds = array();
        foreach ($this->getEntityManager()->createQuery('SELECT DISTINCT(t.tag_id) FROM Model\TaggableTagging t')->getArrayResult() as $result) {
            $tagIds[] = $result['tag_id'];
        }

        $tags = array();
        if ($tagIds) {
            $query = $this->getEntityManager()->createQuery('SELECT t FROM Model\TaggableTag t WHERE t.id IN('.implode(', ', $tagIds).')');
            foreach ($query->getArrayResult() as $result) {
                $tags[] = $result['name'];
            }
        }

        return $tags;
    }

    /**
     * Returns tags with count.
     *
     * @return array The tags with count.
     */
    public function getTagsWithCount($limit = false)
    {
        $query = 'SELECT t.tag_id, COUNT(t.tag_id) AS total FROM Model\TaggableTagging t GROUP BY t.tag_id ORDER BY total DESC';
        $query = $this->getEntityManager()->createQuery($query);
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $tagIds = array();
        foreach ($query->getArrayResult() as $result) {
            $tagIds[$result['tag_id']] = $result['total'];
        }

        $tags = array();
        if ($tagIds) {
            $query = $this->getEntityManager()->createQuery('SELECT t FROM Model\TaggableTag t WHERE t.id IN('.implode(', ', array_keys($tagIds)).')');
            foreach ($query->getArrayResult() as $result) {
                $tags[$result['name']] = $tagIds[$result['id']];
            }
        }

        return $tags;
    }
}