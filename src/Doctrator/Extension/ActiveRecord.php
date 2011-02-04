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

namespace Doctrator\Extension;

use Mondongo\Mondator\Extension;
use Mondongo\Mondator\Definition\Method;
use Mondongo\Mondator\Definition\Property;
use Mondongo\Inflector;

/**
 * The doctrator ActiveRecord extension.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class ActiveRecord extends Extension
{
    /**
     * @inheritdoc
     */
    protected function doClassProcess()
    {
        $this->processEntityManagerMethod();
        $this->processCheckEntityManagerIsClearMethod();
        $this->processRepositoryMethod();
        $this->processQueryBuilderMethod();
        $this->processIsNewMethod();
        $this->processCheckIsNewMethod();
        $this->processCheckIsNotNewMethod();
        $this->processIsModifiedMethod();
        $this->processCheckIsModifiedMethod();
        $this->processCheckIsNotModifiedMethod();
        $this->processGetModifiedMethod();
        $this->processRefreshMethod();
        $this->processChangeSetMethod();

        $this->processSaveMethod();
        $this->processDeleteMethod();
    }

    /*
     * "entityManager" method
     */
    protected function processEntityManagerMethod()
    {
        $method = new Method('public', 'entityManager', '', <<<EOF
        return \Doctrator\EntityManagerContainer::getEntityManager();
EOF
        );
        $method->setIsStatic(true);
        $method->setDocComment(<<<EOF
    /**
     * Returns the entity manager.
     *
     * @return \Doctrine\ORM\EntityManager The entity manager.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "checkEntityManagerIsClear" method
     */
    protected function processCheckEntityManagerIsClearMethod()
    {
        $method = new Method('public', 'checkEntityManagerIsClear', '', <<<EOF
        static \$reflection;

        \$unitOfWork = static::entityManager()->getUnitOfWork();

        if (null === \$reflection) {
            \$reflection = new \ReflectionProperty(get_class(\$unitOfWork), 'scheduledForDirtyCheck');
            \$reflection->setAccessible(true);
        }

        if (\$unitOfWork->hasPendingInsertions() || \$reflection->getValue(\$unitOfWork) || \$unitOfWork->getScheduledEntityDeletions()) {
            throw new \RuntimeException('The entity manager is not clear.');
        }
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Check if the entity manager is clear.
     *
     * @return void
     *
     * @throws \RuntimeException If the entity manager is not clear.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "repository" method
     */
    protected function processRepositoryMethod()
    {
        $method = new Method('public', 'repository', '', <<<EOF
        return static::entityManager()->getRepository('{$this->class}');
EOF
        );
        $method->setIsStatic(true);
        $method->setDocComment(<<<EOF
    /**
     * Returns the repository.
     *
     * @return \Doctrine\ORM\EntityRepository The repository.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "queryBuilder" method
     */
    protected function processQueryBuilderMethod()
    {
        $method = new Method('public', 'queryBuilder', '$alias', <<<EOF
        return static::repository()->createQueryBuilder(\$alias);
EOF
        );
        $method->setIsStatic(true);
        $method->setDocComment(<<<EOF
    /**
     * Create a query builder for this entity name.
     *
     * @param string \$alias The alias.
     *
     * @return \Doctrine\ORM\QueryBuilder A query builder
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "isNew" method
     */
    protected function processIsNewMethod()
    {
        $method = new Method('public', 'isNew', '', <<<EOF
        return !static::entityManager()->getUnitOfWork()->isInIdentityMap(\$this);
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns if the entity is new.
     *
     * @return bool If the entity is new.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "checkIsNew" method
     */
    protected function processCheckIsNewMethod()
    {
        $method = new Method('public', 'checkIsNew', '', <<<EOF
        if (!\$this->isNew()) {
            throw new \RuntimeException('The entity is not new.');
        }
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Throws an \RuntimeException if the entity is not new.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "checkIsNotNew" method
     */
    protected function processCheckIsNotNewMethod()
    {
        $method = new Method('public', 'checkIsNotNew', '', <<<EOF
        if (\$this->isNew()) {
            throw new \RuntimeException('The entity is new.');
        }
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Throws an \RuntimeException if the entity is new.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "isModified" method
     */
    protected function processIsModifiedMethod()
    {
        $method = new Method('public', 'isModified', '', <<<EOF
        return (bool) count(\$this->getModified());
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns if the entity is modified.
     *
     * @return bool If the entity is modified.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "checkIsModified" method
     */
    protected function processCheckIsModifiedMethod()
    {
        $method = new Method('public', 'checkIsModified', '', <<<EOF
        if (!\$this->isModified()) {
            throw new \RuntimeException('The entity is not modified.');
        }
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Throws an \RuntimeException if the entity is not modified.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "checkIsNotModified" method
     */
    protected function processCheckIsNotModifiedMethod()
    {
        $method = new Method('public', 'checkIsNotModified', '', <<<EOF
        if (\$this->isModified()) {
            throw new \RuntimeException('The entity is modified.');
        }
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Throws an \RuntimeException if the entity is modified.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "getModified" method
     */
    protected function processGetModifiedMethod()
    {
        $method = new Method('public', 'getModified', '', <<<EOF
        if (\$this->isNew()) {
            return array();
        }

        \$originalData = static::entityManager()->getUnitOfWork()->getOriginalEntityData(\$this);

        return array_diff(\$originalData, \$this->toArray());
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns the entity modifications
     *
     * @return array The entity modifications.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "refresh" method
     */
    protected function processRefreshMethod()
    {
        $method = new Method('public', 'refresh', '', <<<EOF
        static::entityManager()->getUnitOfWork()->refresh(\$this);
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Refresh the entity from the database.
     *
     * @return void
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "changeSet" method.
     */
    protected function processChangeSetMethod()
    {
        $method = new Method('public', 'changeSet', '', <<<EOF
        return static::entityManager()->getUnitOfWork()->getEntityChangeSet(\$this);
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns the change set of the entity.
     *
     * @return array The change set.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "save" active record method
     */
    protected function processSaveMethod()
    {
        $method = new Method('public', 'save', '', <<<EOF
        \$this->checkEntityManagerIsClear();

        \$em = static::entityManager();

        \$em->persist(\$this);
        \$em->flush();
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Save the entity.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "delete" active record method
     */
    protected function processDeleteMethod()
    {
        $method = new Method('public', 'delete', '', <<<EOF
        \$this->checkEntityManagerIsClear();

        \$em = static::entityManager();

        \$em->remove(\$this);
        \$em->flush();
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Delete the entity.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }
}
