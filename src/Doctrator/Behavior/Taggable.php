<?php

/*
 * Copyright 2010 Pablo Díez Pascual <pablodip@gmail.com>
 *
 * This file is part of Doctrator.
 *
 * Doctrator is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Doctrator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Doctrator. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Doctrator\Behavior;

use Mondongo\Mondator\Extension;
use Mondongo\Mondator\Definition\Method;
use Mondongo\Inflector;

/**
 * The doctrator Taggable behavior.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Taggable extends Extension
{
    /**
     * @inheritdoc
     */
    protected function doProcess()
    {
        // tag classes
        $this->newConfigClasses[$this->class.'Tag'] = array(
            'columns' => array(
                'id'   => array('id' => 'auto', 'type' => 'integer'),
                'name' => array('type' => 'string', 'unique' => true),
            ),
        );
        $this->newConfigClasses[$this->class.'Tagging'] = array(
            'columns' => array(
                'id'        => array('id' => 'auto', 'type' => 'integer'),
                'parent_id' => array('type' => 'integer'),
                'tag_id'    => array('type' => 'integer'),
            ),
            'relations' => array(
                'parent' => array('type' => 'ManyToOne', 'targetEntity' => $this->class),
                'tag'    => array('type' => 'ManyToOne', 'targetEntity' => $this->class.'Tag'),
            ),
            'indexes' => array(
                array('columns' => array('parent_id', 'tag_id'), 'unique' => true),
            ),
        );

        // method
        $this->processAddTagsMethod();
        $this->processRemoveTagsMethod();
        $this->processRemoveAllTagsMethod();
        $this->processGetSavedTagsMethod();
        $this->processSetTagsMethod();
        $this->processGetTagsMethod();
        $this->processGetTagHolderMethod();
        $this->processSaveTagsMethod();

        // repository methods
        $this->processRepositoryGetTagsMethod();
        $this->processRepositoryGetTagsWithCountMethod();
    }

    /*
     * "addTags" method
     */
    protected function processAddTagsMethod()
    {
        $method = new Method('public', 'addTags', '$tags', <<<EOF
        \$holder = \$this->getTagHolder();

        foreach (\Doctrator\Behavior\Taggable::explodeAndCleanTags(\$tags) as \$tag) {
            if (false !== \$key = array_search(\$tag, \$holder['remove'])) {
                \$remove =& \$holder['remove'];
                unset(\$remove[\$key]);
            }

            if (in_array(\$tag, \$this->getSavedTags())) {
                continue;
            }

            \$holder['add'][] = \$tag;
        }
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Add tags.
     *
     * @param mixed \$tags The tags.
     *
     * @return void
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "removeTags" method
     */
    protected function processRemoveTagsMethod()
    {
        $method = new Method('public', 'removeTags', '$tags', <<<EOF
        \$holder = \$this->getTagHolder();

        foreach (\Doctrator\Behavior\Taggable::explodeAndCleanTags(\$tags) as \$tag) {
            if (false !== \$key = array_search(\$tag, \$holder['add'])) {
                \$add =& \$holder['add'];
                unset(\$add[\$key]);
            }

            if (!in_array(\$tag, \$this->getSavedTags())) {
                continue;
            }

            \$holder['remove'][] = \$tag;
        }
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Remove tags.
     *
     * @param mixed \$tags The tags.
     *
     * @return void
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "removeAllTags" method
     */
    protected function processRemoveAllTagsMethod()
    {
        $method = new Method('public', 'removeAllTags', '', <<<EOF
        \$holder = \$this->getTagHolder();

        \$holder['add'] = array();
        \$this->removeTags(\$this->getSavedTags());
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Remove all tags.
     *
     * @return void
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "getSavedTags" method
     */
    protected function processGetSavedTagsMethod()
    {
        $method = new Method('public', 'getSavedTags', '', <<<EOF
        \$holder = \$this->getTagHolder();

        if (null === \$holder['saved']) {
            \$holder['saved'] = array();

            \$query = \$this->getEntityManager()->createQuery('SELECT t, ta FROM {$this->class}Tagging t JOIN t.tag ta WHERE t.parent_id = ?1');
            \$query->setParameter(1, \$this->getId());
            foreach (\$query->getResult() as \$tagging) {
                \$holder['saved'][] = \$tagging->getTag()->getName();
            }
        }

        return \$holder['saved'];
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns the saved tags.
     *
     * @return array The saved tags.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "setTags" method
     */
    protected function processSetTagsMethod()
    {
        $method = new Method('public', 'setTags', '$tags', <<<EOF
        \$this->removeAllTags();
        \$this->addTags(\$tags);
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Set the tags.
     *
     * @param mixed \$tags The tags.
     *
     * @return void
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "getTags" method
     */
    protected function processGetTagsMethod()
    {
        $method = new Method('public', 'getTags', '', <<<EOF
        \$holder = \$this->getTagHolder();

        \$add    = \$holder['add'];
        \$remove = \$holder['remove'];
        \$saved  = \$this->getSavedTags();

        return array_values(array_diff(array_merge(\$add, \$saved), \$remove));
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns the tags.
     *
     * @return array The tags.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "getTagHolder" method
     */
    protected function processGetTagHolderMethod()
    {
        $method = new Method('protected', 'getTagHolder', '', <<<EOF
        static \$holder;

        if (null === \$holder) {
            \$holder = new \ArrayObject(array(
                'add'    => array(),
                'remove' => array(),
                'saved'  => null,
            ));
        }

        return \$holder;
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "saveTags" method
     */
    protected function processSaveTagsMethod()
    {
        $method = new Method('public', 'saveTags', '', <<<EOF
        \$holder = \$this->getTagHolder();

        foreach (\$holder['add'] as \$tag) {
            \$query = \$this->getEntityManager()->createQuery('SELECT t FROM {$this->class}Tag t WHERE t.name = ?1');
            \$query->setParameter(1, \$tag);
            if (\$results = \$query->getResult()) {
                \$tagEntity = \$results[0];
            } else {
                \$tagEntity = new \\{$this->class}Tag();
                \$tagEntity->setName(\$tag);
                \$tagEntity->save();
            }

            \$taggingEntity = new \\{$this->class}Tagging();
            \$taggingEntity->setParent(\$this);
            \$taggingEntity->setTag(\$tagEntity);
            \$taggingEntity->save();
        }

        foreach (\$holder['remove'] as \$tag) {
            \$query = \$this->getEntityManager()->createQuery('SELECT t FROM {$this->class}Tag t WHERE t.name = ?1');
            \$query->setParameter(1, \$tag);
            if (\$results = \$query->getResult()) {
                \$query = \$this->getEntityManager()->createQuery('DELETE FROM {$this->class}Tagging t WHERE t.parent_id = ?1 AND t.tag_id = ?2');
                \$query->setMaxResults(1);
                \$query->setParameter(1, \$this->getId());
                \$query->setParameter(2, \$results[0]->getId());
                \$query->execute();
            }
        }

        \$holder['add']    = array();
        \$holder['remove'] = array();
        \$holder['saved']  = null;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Save the tags.
     *
     * @return void
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * repository "getTags" method
     */
    protected function processRepositoryGetTagsMethod()
    {
        $method = new Method('public', 'getTags', '', <<<EOF
        \$tagIds = array();
        foreach (\$this->getEntityManager()->createQuery('SELECT DISTINCT(t.tag_id) FROM {$this->class}Tagging t')->getArrayResult() as \$result) {
            \$tagIds[] = \$result['tag_id'];
        }

        \$tags = array();
        if (\$tagIds) {
            \$query = \$this->getEntityManager()->createQuery('SELECT t FROM {$this->class}Tag t WHERE t.id IN('.implode(', ', \$tagIds).')');
            foreach (\$query->getArrayResult() as \$result) {
                \$tags[] = \$result['name'];
            }
        }

        return \$tags;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns all tags.
     *
     * @return array The tags.
     */
EOF
        );

        $this->definitions['repository_base']->addMethod($method);
    }

    /*
     * repository "getTagsWithCount" method
     */
    protected function processRepositoryGetTagsWithCountMethod()
    {
        $method = new Method('public', 'getTagsWithCount', '$limit = false', <<<EOF
        \$query = 'SELECT t.tag_id, COUNT(t.tag_id) AS total FROM {$this->class}Tagging t GROUP BY t.tag_id ORDER BY total DESC';
        \$query = \$this->getEntityManager()->createQuery(\$query);
        if (\$limit) {
            \$query->setMaxResults(\$limit);
        }

        \$tagIds = array();
        foreach (\$query->getArrayResult() as \$result) {
            \$tagIds[\$result['tag_id']] = \$result['total'];
        }

        \$tags = array();
        if (\$tagIds) {
            \$query = \$this->getEntityManager()->createQuery('SELECT t FROM {$this->class}Tag t WHERE t.id IN('.implode(', ', array_keys(\$tagIds)).')');
            foreach (\$query->getArrayResult() as \$result) {
                \$tags[\$result['name']] = \$tagIds[\$result['id']];
            }
        }

        return \$tags;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns tags with count.
     *
     * @return array The tags with count.
     */
EOF
        );

        $this->definitions['repository_base']->addMethod($method);
    }

    /**
     * Explode tags from a ver.
     *
     * The var can be a string or an array.
     * If the var is a string the tags can be separate by commas.
     *
     * @param string|array $tags The tags.
     *
     * @return array The tags as array.
     *
     * @throws \InvalidArgumentException If the var is not valid.
     */
    static public function explodeTags($tags)
    {
        if (is_string($tags)) {
            $tags = explode(',', $tags);
        } else if (!is_array($tags)) {
            throw new \InvalidArgumentException('The tags are not an array.');
        }

        $tags = array_map('trim', $tags);

        $return = array();
        foreach ($tags as $tag) {
            if ($tag) {
                $return[] = $tag;
            }
        }

        return $return;
    }

    /**
     * Clean a tag.
     *
     * @param string $tag A tag.
     *
     * @return string The tag cleaned.
     */
    static public function cleanTag($tag)
    {
        return trim(str_replace(',', ' ', $tag));
    }

    /**
     * Explode and clean tags.
     *
     * @param mixed $tags A var with tags.
     *
     * @see explodeTags()
     * @see cleanTag()
     */
    static public function explodeAndCleanTags($tags)
    {
        $tags = static::explodeTags($tags);
        foreach ($tags as &$tag) {
            $tag = static::cleanTag($tag);
        }

        return $tags;
    }
}
