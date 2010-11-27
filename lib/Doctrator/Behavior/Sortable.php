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
 * The doctrator Sortable behavior.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Sortable extends Extension
{
    protected $column;
    protected $columnSetter;
    protected $columnGetter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->addOptions(array(
            'column'       => 'position',
            'new_position' => 'bottom',
        ));
    }

    /**
     * @inheritdoc
     */
    protected function doProcess()
    {
        // new position
        if (!in_array($this->getOption('new_position'), array('top', 'bottom'))) {
            throw new \RuntimeException(sprintf('The new_position "%s" is not valid.', $this->getOption('new_position')));
        }

        // position column
        $this->column       = $this->getOption('column');
        $this->columnSetter = 'set'.Inflector::camelize($this->column);
        $this->columnGetter = 'get'.Inflector::camelize($this->column);

        $this->configClass['columns'][$this->column] = array('type' => 'integer');

        // methods
        $this->processEntityIsFirstMethod();
        $this->processEntityIsLastMethod();
        $this->processEntityGetNextMethod();
        $this->processEntityGetPreviousMethod();
        $this->processEntitySwapWithMethod();
        $this->processEntityMoveUpMethod();
        $this->processEntityMoveDownMethod();
        $this->processRepositoryGetMinPositionMethod();
        $this->processRepositoryGetMaxPositionMethod();

        // events
        $this->processSortableSetPositionEvent();
    }

    /*
     * "isFirst" entity method
     */
    protected function processEntityIsFirstMethod()
    {
        $method = new Method('public', 'isFirst', '', <<<EOF
        return \$this->{$this->columnGetter}() === \$this->getRepository()->getMinPosition();
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns if the entity is the first.
     *
     * @return bool Returns if the entity is the first.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "isLast" entity method
     */
    protected function processEntityIsLastMethod()
    {
        $method = new Method('public', 'isLast', '', <<<EOF
        return \$this->{$this->columnGetter}() === \$this->getRepository()->getMaxPosition();
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns if the entity is the last.
     *
     * @return bool Returns if the entity is the last.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "getNext" entity method
     */
    protected function processEntityGetNextMethod()
    {
        $method = new Method('public', 'getNext', '', <<<EOF
        \$query = 'SELECT s FROM {$this->class} s WHERE s.{$this->column} > ?1 ORDER BY s.{$this->column} ASC';
        \$query = \$this->getEntityManager()->createQuery(\$query);
        \$query->setParameter(1, \$this->{$this->columnGetter}());
        \$query->setMaxResults(1);

        \$results = \$query->getResult();

        return \$results ? \$results[0] : false;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns the next entity.
     *
     * @return mixed The next entity if exists, if not false.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "getPrevious" entity method
     */
    protected function processEntityGetPreviousMethod()
    {
        $method = new Method('public', 'getPrevious', '', <<<EOF
        \$query = 'SELECT s FROM {$this->class} s WHERE s.{$this->column} < ?1 ORDER BY s.{$this->column} DESC';
        \$query = \$this->getEntityManager()->createQuery(\$query);
        \$query->setParameter(1, \$this->{$this->columnGetter}());
        \$query->setMaxResults(1);

        \$results = \$query->getResult();

        return \$results ? \$results[0] : false;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns the previous entity.
     *
     * @return mixed The previous entity if exists, if not false.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "swapWith" entity method
     */
    protected function processEntitySwapWithMethod()
    {
        $method = new Method('public', 'swapWith', '$entity', <<<EOF
        if (!\$entity instanceof \\{$this->class}) {
            throw new \InvalidArgumentException('The entity is not an instance of \\{$this->class}.');
        }

        \$oldPosition = \$this->{$this->columnGetter}();
        \$newPosition = \$entity->{$this->columnGetter}();

        \$em = \$this->getEntityManager();

        \$em->getConnection()->beginTransaction();

        \$query = \$em->createQuery('UPDATE {$this->class} s SET s.{$this->column} = ?1 WHERE s.id = ?2');
        \$query->setParameter(1, \$newPosition);
        \$query->setParameter(2, \$this->getId());
        \$query->execute();
        \$this->refresh();

        \$query = \$em->createQuery('UPDATE {$this->class} s SET s.{$this->column} = ?1 WHERE s.id = ?2');
        \$query->setParameter(1, \$oldPosition);
        \$query->setParameter(2, \$entity->getId());
        \$query->execute();
        \$entity->refresh();

        \$em->getConnection()->commit();
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Swap the position with another entity.
     *
     * @param \\{$this->class} \$entity The entity.
     *
     * @return void
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "moveUp" entity method
     */
    protected function processEntityMoveUpMethod()
    {
        $method = new Method('public', 'moveUp', '', <<<EOF
        if (\$this->isFirst()) {
            throw new \RuntimeException('The entity is the first.');
        }

        \$this->swapWith(\$this->getPrevious());
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Move up the entity.
     *
     * @return void
     *
     * @throws \RuntimeException If the entity is the first.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "moveDown" entity method
     */
    protected function processEntityMoveDownMethod()
    {
        $method = new Method('public', 'moveDown', '', <<<EOF
        if (\$this->isLast()) {
            throw new \RuntimeException('The entity is the last.');
        }

        \$this->swapWith(\$this->getNext());
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Move down the entity.
     *
     * @return void
     *
     * @throws \RuntimeException If the entity is the last.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "getMinPosition" repository method
     */
    protected function processRepositoryGetMinPositionMethod()
    {
        $method = new Method('public', 'getMinPosition', '', <<<EOF
        \$result = \$this->getEntityManager()
            ->createQuery('SELECT MIN(s.{$this->column}) FROM {$this->class} s')
            ->getSingleScalarResult()
        ;

        return \$result ? (int) \$result : null;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns the min position.
     *
     * @return integer The min position.
     */
EOF
        );

        $this->definitions['repository_base']->addMethod($method);
    }

    /*
     * "getMaxPosition" repository method
     */
    protected function processRepositoryGetMaxPositionMethod()
    {
        $method = new Method('public', 'getMaxPosition', '', <<<EOF
        \$result = \$this->getEntityManager()
            ->createQuery('SELECT MAX(s.{$this->column}) FROM {$this->class} s')
            ->getSingleScalarResult()
        ;

        return \$result ? (int) \$result : null;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns the max position.
     *
     * @return integer The max position.
     */
EOF
        );

        $this->definitions['repository_base']->addMethod($method);
    }

    /*
     * sortableSetPosition event
     */
    protected function processSortableSetPositionEvent()
    {
        $positionAsNew = 'top' == $this->getOption('new_position') ? '1' : '$maxPosition + 1';

        $method = new Method('public', 'sortableSetPosition', '', <<<EOF
        \$maxPosition = \$this->getRepository()->getMaxPosition();

        if (\$this->isNew()) {
            \$position = $positionAsNew;
        } else {
            \$changeSet = \$this->getEntityManager()->getUnitOfWork()->getEntityChangeSet(\$this);
            if (!isset(\$changeSet['position'])) {
                return;
            }
            \$oldPosition = \$changeSet['position'][0];
            \$position    = \$changeSet['position'][1];
        }

        \$this->{$this->columnSetter}(\$position);

        // move entities
        if (\$this->isNew()) {
            \$query = 'UPDATE {$this->class} s SET s.{$this->column} = s.{$this->column} + 1 WHERE s.{$this->column} >= ?1';
            \$query = \$this->getEntityManager()->createQuery(\$query);
            \$query->setParameter(1, \$position);
        } else {
            \$sign = \$position > \$oldPosition ? '-' : '+';
            \$query = "UPDATE {$this->class} s SET s.{$this->column} = s.{$this->column} \$sign 1 WHERE s.{$this->column} BETWEEN ?1 AND ?2";
            \$query = \$this->getEntityManager()->createQuery(\$query);
            \$query->setParameter(1, min(\$position, \$oldPosition));
            \$query->setParameter(2, max(\$position, \$oldPosition));
        }

        \$query->execute();
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Set the position.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);

        $this->configClass['events']['prePersist'][] = $method->getName();
        $this->configClass['events']['preUpdate'][]  = $method->getName();
    }
}
