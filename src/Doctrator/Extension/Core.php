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
use Mondongo\Mondator\Definition\Definition;
use Mondongo\Mondator\Definition\Method;
use Mondongo\Mondator\Definition\Property;
use Mondongo\Mondator\Output\Output;
use Mondongo\Mondator\Dumper;
use Mondongo\Inflector;

/**
 * The doctrator Core extension.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Core extends Extension
{
    protected $loadMetadataCode = array();

    /**
     * @inheritdoc
     */
    protected function setup()
    {
        $this->addOptions(array(
            'default_output'    => null,
            'active_record'     => true,
            'default_behaviors' => array(),
        ));
    }

    /**
     * @inheritdoc
     */
    public function getNewClassExtensions($class, \ArrayObject $configClass)
    {
        $classExtensions = array();

        // default behaviors
        foreach ($this->getOption('default_behaviors') as $behavior) {
            $classExtensions[] = $this->createClassExtensionFromArray($behavior);
        }

        // behaviors
        if (isset($configClass['behaviors'])) {
            foreach ($configClass['behaviors'] as $behavior) {
                $classExtensions[] = $this->createClassExtensionFromArray($behavior);
            }
        }

        return $classExtensions;
    }

    /**
     * @inheritdoc
     */
    protected function doClassProcess()
    {
        $this->loadMetadataCode[$this->class] = array();

        // definitions and outputs
        $this->processInitDefinitionsAndOutputs();

        // init mapping
        $this->initMapping();
    }

    /**
     * @inheritdoc
     */
    protected function doReverseClassProcess()
    {
        // mapping
        $this->processChangeTrackingPolicyMapping();
        $this->processTableNameMapping();
        $this->processCustomRepositoryClassMapping();
        $this->processColumnsMapping();
        $this->processRelationsMapping();
        $this->processIndexesMapping();
        $this->processEventsMapping();

        // relations init collections (constructor)
        $this->processRelationsInitCollections();

        // columns setters && getters
        $this->processColumnsSettersAndGetters();

        // relations setters && getters
        $this->processRelationsSettersAndGetters();

        // set && get
        $this->processSetMethod();
        $this->processGetMethod();

        // fromArray && toArray
        $this->processFromArrayMethod();
        $this->processToArrayMethod();

        // general methods
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

        if ($this->getOption('active_record')) {
            $this->processActiveRecordSaveMethod();
            $this->processActiveRecordDeleteMethod();
        } else {
            $this->processNotActiveRecordPersistMethod();
            $this->processNotActiveRecordRemoveMethod();
            $this->processNotActiveRecordSaveMethod();
            $this->processNotActiveRecordDeleteMethod();
        }

        // events methods
        $this->processEventsMethods();

        // loadMetadata method
        $this->processLoadMetadataMethod();
    }

    /*
     * Init Definitions and Outputs.
     */
    protected function processInitDefinitionsAndOutputs()
    {
        /*
         * Classes.
         */
        $classes = array('entity' => $this->class);
        if (false !== $pos = strrpos($classes['entity'], '\\')) {
            $entityNamespace = substr($classes['entity'], 0, $pos);
            $entityClassName = substr($classes['entity'], $pos + 1);
            $classes['entity_base']     = $entityNamespace.'\\Base\\'.$entityClassName;
            $classes['repository']      = $entityNamespace.'\\'.$entityClassName.'Repository';
            $classes['repository_base'] = $entityNamespace.'\\Base\\'.$entityClassName.'Repository';
        } else {
            $classes['entity_base']     = 'Base'.$classes['entity'];
            $classes['repository']      = $classes['entity'].'Repository';
            $classes['repository_base'] = 'Base'.$classes['entity'].'Repository';
        }

        /*
         * Definitions
         */

        // entity
        $this->definitions['entity'] = $definition = new Definition($classes['entity']);
        $definition->setParentClass('\\'.$classes['entity_base']);
        $definition->setDocComment(<<<EOF
/**
 * {$this->class} entity.
 */
EOF
        );

        // entity_base
        $this->definitions['entity_base'] = $definition = new Definition($classes['entity_base']);
        $definition->setIsAbstract(true);
        $definition->setDocComment(<<<EOF
/**
 * Base class of the {$this->class} entity.
 */
EOF
        );

        // repository
        $this->definitions['repository'] = $definition = new Definition($classes['repository']);
        $definition->setParentClass('\\'.$classes['repository_base']);
        $definition->setDocComment(<<<EOF
/**
 * Repository of the {$this->class} entity.
 */
EOF
        );

        // repository_base
        $this->definitions['repository_base'] = $definition = new Definition($classes['repository_base']);
        $definition->setIsAbstract(true);
        $definition->setParentClass('\\Doctrine\\ORM\\EntityRepository');
        $definition->setDocComment(<<<EOF
/**
 * Base class of the repository of the {$this->class} entity.
 */
EOF
        );

        /*
         * Outputs
         */

        $dir = $this->getOption('default_output');
        if (isset($this->configClass['output'])) {
            $dir = $this->configClass['output'];
        }
        if (!$dir) {
            throw new \RuntimeException(sprintf('The class "%s" does not have output.', $this->class));
        }

        // entity
        $this->outputs['entity'] = new Output($dir);

        // entity_base
        $this->outputs['entity_base'] = new Output($this->outputs['entity']->getDir().'/Base', true);

        // repository
        $this->outputs['repository'] = new Output($dir);

        // repository_base
        $this->outputs['repository_base'] = new Output($this->outputs['repository']->getDir().'/Base', true);
    }

    /*
     * Init mapping.
     */
    protected function initMapping()
    {
        // table
        if (!isset($this->configClass['table'])) {
            $this->configClass['table'] = null;
        }

        // columns
        if (!isset($this->configClass['columns'])) {
            $this->configClass['columns'] = array();
        }

        // relations
        if (!isset($this->configClass['relations'])) {
            $this->configClass['relations'] = array();
        }

        // indexes
        if (!isset($this->configClass['indexes'])) {
            $this->configClass['indexes'] = array();
        }

        // events
        $this->configClass['events'] = array(
            'prePersist'  => array('prePersist'),
            'postPersist' => array('postPersist'),
            'preUpdate'   => array('preUpdate'),
            'postUpdate'  => array('postUpdate'),
            'preRemove'   => array('preRemove'),
            'postRemove'  => array('postRemove'),
            'onFlush'     => array('onFlush'),
            'postLoad'    => array('postLoad'),
        );
    }

    /*
     * Change tracking policy mapping
     */
    protected function processChangeTrackingPolicyMapping()
    {
        $this->loadMetadataCode[$this->class][] = <<<EOF
        \$metadata->setChangeTrackingPolicy(\Doctrine\ORM\Mapping\ClassMetadata::CHANGETRACKING_DEFERRED_EXPLICIT);
EOF;
    }

    /*
     * Table name mapping.
     */
    protected function processTableNameMapping()
    {
        if ($this->configClass['table']) {
            $this->loadMetadataCode[$this->class][] = <<<EOF
        \$metadata->setTableName('{$this->configClass['table']}');
EOF;
        }
    }

    /*
     * Custom repository class mapping.
     */
    protected function processCustomRepositoryClassMapping()
    {
        $repositoryClass = $this->definitions['repository']->getClass();

        $this->loadMetadataCode[$this->class][] = <<<EOF
        \$metadata->setCustomRepositoryClass('$repositoryClass');
EOF;
    }

    /*
     * Columns mapping.
     */
    protected function processColumnsMapping()
    {
        foreach ($this->configClass['columns'] as $name => &$column) {
            if (is_string($column)) {
                $column = array('type' => $column);
            } else {
                if (!is_array($column)) {
                    throw new \RuntimeException(sprintf('The column "%s" is not an string or array.', $name));
                }
                if (!isset($column['type'])) {
                    throw new \RuntimeException(sprintf('The column "%s" does not have type.', $name));
                }
            }

            /*
             * Mapping
             */

            // column
            $fieldMapping = array('fieldName' => $name);
            foreach (array(
                'type',
                'length',
                'precision',
                'scale',
                'nullable',
                'unique',
                'options',
                'columnDefinition'
            ) as $attribute) {
                if (isset($column[$attribute])) {
                    $fieldMapping[$attribute] = $column[$attribute];
                }
            }
            if (isset($column['name'])) {
                $fieldMapping['columnName'] = $column['name'];
            }

            // identifier
            if (isset($column['id'])) {
                // field
                $fieldMapping['id'] = true;

                // check
                if (is_string($column['id'])) {
                    $column['id'] = array('strategy' => $column['id']);
                } else {
                    if (!is_array($column['id'])) {
                        throw new \RuntimeException(sprintf('The identifier "%s" is not an array or an string.', $name));
                    }
                    if (!isset($column['id']['strategy'])) {
                        throw new \RuntimeException(sprintf('The identifier "%s" does not have strategy.', $name));
                    }
                }

                // strategy
                $strategyUpper = strtoupper($column['id']['strategy']);
                $this->loadMetadataCode[$this->class][] = <<<EOF
        \$metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_$strategyUpper);
EOF
                ;

                // sequence
                if ('sequence' == $column['id']['strategy']) {
                    if (!isset($column['id']['sequence'])) {
                        $column['id']['sequence'] = array();
                    }

                    $sequenceMapping = array();
                    if (isset($column['id']['sequence']['name'])) {
                        $sequenceMapping['sequenceName'] = $column['id']['sequence']['name'];
                    }
                    if (isset($column['id']['sequence']['allocationSize'])) {
                        $sequenceMapping['sequenceName'] = $column['id']['sequence']['allocationSize'];
                    }
                    if (isset($column['id']['sequence']['initialValue'])) {
                        $sequenceMapping['sequenceName'] = $column['id']['sequence']['initialValue'];
                    }

                    $sequenceMapping = Dumper::exportArray($sequenceMapping, 12);

                    $this->loadMetadataCode[$this->class][] = <<<EOF
        \$metadata->setSequenceGeneratorDefinition($sequenceMapping);
EOF;
                }
            }

            // map field
            $fieldMapping = Dumper::exportArray($fieldMapping, 12);

            $this->loadMetadataCode[$this->class][] = <<<EOF
        \$metadata->mapField($fieldMapping);
EOF;

            /*
             * Property
             */
            $property = new Property('protected', $name, null);

            // default
            if (isset($column['default'])) {
                $property->setValue($column['default']);
            }

            $this->definitions['entity_base']->addProperty($property);
        }
    }

    /*
     * Columns setters and getters.
     */
    protected function processColumnsSettersAndGetters()
    {
        foreach ($this->configClass['columns'] as $name => $column) {
            // setter
            $method = new Method('public', 'set'.Inflector::camelize($name), '$value', <<<EOF
        \$this->$name = \$value;
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Set the $name column value.
     *
     * @param mixed \$value The column value.
     */
EOF
            );
            $this->definitions['entity_base']->addMethod($method);

            // getter
            $method = new Method('public', 'get'.Inflector::camelize($name), '', <<<EOF
        return \$this->$name;
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Returns the $name column value.
     *
     * @return mixed The column value.
     */
EOF
            );
            $this->definitions['entity_base']->addMethod($method);
        }
    }

    /*
     * Relations mapping.
     */
    protected function processRelationsMapping()
    {
        foreach ($this->configClass['relations'] as $name => &$relation) {
            if (!isset($relation['type'])) {
                throw new \RuntimeException(sprintf('The relation "%s" of the class "%s" does not have type.', $name, $this->class));
            }

            if (!in_array($relation['type'], array('OneToOne', 'OneToMany', 'ManyToOne', 'ManyToMany'))) {
                throw new \RuntimeException(sprintf('The type "%s" relation "%s" of the class "%s" is not valid.', $relation['type'], $name, $this->class));
            }

            if (!isset($relation['targetEntity'])) {
                throw new \RuntimeException(sprintf('The relation "%s" of the class "%s" does not have targetEntity.', $name, $this->class));
            }

            // property
            $property = new Property('protected', $name, null);
            $this->definitions['entity_base']->addProperty($property);

            /*
             * mapping
             */
            $mapping = array(
                'fieldName'    => $name,
                'targetEntity' => $relation['targetEntity'],
            );

            if (isset($relation['fetch'])) {
                $mapping['fetch'] = constant('Doctrine\ORM\Mapping\ClassMetadata::FETCH_'.$relation['fetch']);
            }

            // FIXME: make customizable
            $relation['cascade'] = array('persist', 'remove');

            if (isset($relation['cascade'])) {
                $mapping['cascade'] = $relation['cascade'];
            }

            // OneToOne
            if ('OneToOne' == $relation['type']) {
                // inverse
                if (isset($relation['mappedBy'])) {
                    $mapping['mappedBy'] = $relation['mappedBy'];
                }
                // owning
                else {
                    if (isset($relation['inversedBy'])) {
                        $mapping['inversedBy'] = $relation['inversedBy'];
                    }
                }
            }
            // OneToMany
            if ('OneToMany' == $relation['type']) {
                if (!isset($relation['mappedBy'])) {
                    throw new \RuntimeException('The relation "%s" of the class "%s" is OneToMany and does not have mappedBy.', $name, $this->class);
                }
                $mapping['mappedBy'] = $relation['mappedBy'];
            }
            // ManyToOne
            if ('ManyToOne' == $relation['type']) {
                if (isset($relation['inversedBy'])) {
                    $mapping['inversedBy'] = $relation['inversedBy'];
                }
            }
            // ManyToMany
            if ('ManyToMany' == $relation['type']) {
                if (isset($relation['mappedBy'])) {
                    $mapping['mappedBy'] = $relation['mappedBy'];
                } else if (isset($relation['joinTable'])) {
                    if (isset($relation['inversedBy'])) {
                        $mapping['inversedBy'] = $relation['inversedBy'];
                    }
                }
            }

            $mapping = Dumper::exportArray($mapping, 12);

            $this->loadMetadataCode[$this->class][] = <<<EOF
        \$metadata->map{$relation['type']}($mapping);
EOF;

        }
    }

    /*
     * Indexes mapping.
     */
    protected function processIndexesMapping()
    {
        foreach ($this->configClass['indexes'] as $name => &$index) {
            if (!isset($index['columns']) || !is_array($index['columns'])) {
                throw new \RuntimeException(sprintf('The columns of the index "%s" of the class "%s" is not valid.', $index['name'], $this->class));
            }

            $indexType    = isset($index['unique']) && $index['unique'] ? 'uniqueConstraints' : 'indexes';
            $indexMapping = Dumper::exportArray(array('columns' => $index['columns']), 12);

            $this->loadMetadataCode[$this->class][] = <<<EOF
        \$metadata->table['$indexType']['$name'] = $indexMapping;
EOF;
        }
    }

    /*
     * Events mapping.
     */
    protected function processEventsMapping()
    {
        if ($this->configClass['events']) {
            $events = Dumper::exportArray($this->configClass['events'], 12);
            $this->loadMetadataCode[$this->class][] = <<<EOF
        \$metadata->setLifecycleCallbacks($events);
EOF;
        }
    }

    /*
     * Relations setters and getters.
     */
    protected function processRelationsSettersAndGetters()
    {
        foreach ($this->configClass['relations'] as $name => $relation) {
            // setter
            $method = new Method('public', 'set'.Inflector::camelize($name), '$value', <<<EOF
        \$this->$name = \$value;
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Set the $name relation value.
     *
     * @param mixed \$value The relation value.
     */
EOF
            );
            $this->definitions['entity_base']->addMethod($method);

            // getter
            $method = new Method('public', 'get'.Inflector::camelize($name), '', <<<EOF
        return \$this->$name;
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Returns the $name relation value.
     *
     * @return mixed The relation value.
     */
EOF
            );
            $this->definitions['entity_base']->addMethod($method);
        }
    }

    /*
     * "set" method
     */
    protected function processSetMethod()
    {
        $code = '';
        // columns
        foreach ($this->configClass['columns'] as $name => $column) {
            $setter = 'set'.Inflector::camelize($name);
            $code .= <<<EOF
        if ('$name' == \$name) {
            return \$this->$setter(\$value);
        }

EOF;
        }
        // relations
        foreach ($this->configClass['relations'] as $name => $relation) {
            $setter = 'set'.Inflector::camelize($name);
            $code .= <<<EOF
        if ('$name' == \$name) {
            return \$this->$setter(\$value);
        }

EOF;
        }
        // exception
        $code .= <<<EOF

        throw new \InvalidArgumentException(sprintf('The data "%s" does not exists.', \$name));
EOF;

        $method = new Method('public', 'set', '$name, $value', $code);
        $method->setDocComment(<<<EOF
    /**
     * Set data by name.
     *
     * @param string \$name  The data name.
     * @param mixed  \$value The value.
     *
     * @throws \InvalidArgumentException If the data does not exists.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "get" method
     */
    protected function processGetMethod()
    {
        $code = '';
        // columns
        foreach ($this->configClass['columns'] as $name => $column) {
            $getter = 'get'.Inflector::camelize($name);
            $code .= <<<EOF
        if ('$name' == \$name) {
            return \$this->$getter();
        }

EOF;
        }
        // relations
        foreach ($this->configClass['relations'] as $name => $relation) {
            $getter = 'get'.Inflector::camelize($name);
            $code .= <<<EOF
        if ('$name' == \$name) {
            return \$this->$getter(\$value);
        }

EOF;
        }
        // exception
        $code .= <<<EOF

        throw new \InvalidArgumentException(sprintf('The data "%s" does not exists.', \$name));
EOF;

        $method = new Method('public', 'get', '$name', $code);
        $method->setDocComment(<<<EOF
    /**
     * Get data by name.
     *
     * @param string \$name  The data name.
     *
     * @return mixed The data.
     *
     * @throws \InvalidArgumentException If the data does not exists.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "fromArray" method.
     */
    public function processFromArrayMethod()
    {
        $sets = '';

        // columns
        foreach ($this->configClass['columns'] as $name => $columns) {
            $setter = 'set'.Inflector::camelize($name);
            $sets[] = <<<EOF
        if (isset(\$array['$name'])) {
            \$this->$setter(\$array['$name']);
        }
EOF;
        }

        $method = new Method('public', 'fromArray', '$array', implode("\n", $sets));
        $method->setDocComment(<<<EOF
    /**
     * Import data from an array.
     *
     * @param array \$array An array.
     *
     * @return void
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "toArray" method
     */
    protected function processToArrayMethod()
    {
        $gets = array();

        // columns
        foreach ($this->configClass['columns'] as $name => $column) {
            $getter = 'get'.Inflector::camelize($name);
            $gets[] = <<<EOF
        \$array['$name'] = \$this->$getter();
EOF;
        }

        $gets = implode("\n", $gets);

        // method
        $method = new Method('public', 'toArray', '', <<<EOF
        \$array = array();

$gets

        return \$array;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Export the data to an array.
     *
     * @return array An array with the data.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * Relations init collections.
     */
    protected function processRelationsInitCollections()
    {
        $collections = array();
        foreach ($this->configClass['relations'] as $name => $relation) {
            if (
                'ManyToMany' == $relation['type']
                ||
                ('OneToMany' == $relation['type'] && isset($relation['mappedBy']))
            ) {
                $collections[] = <<<EOF
        \$this->$name = new \Doctrine\Common\Collections\ArrayCollection();
EOF;
            }
        }

        $method = new Method('public', '__construct', '', implode("\n", $collections));
        $method->setDocComment(<<<EOF
    /**
     * Constructor.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
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
     * "save" active record method
     */
    protected function processActiveRecordSaveMethod()
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
    protected function processActiveRecordDeleteMethod()
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

    /*
     * "persist" not active record method
     */
    protected function processNotActiveRecordPersistMethod()
    {
        $method = new Method('public', 'persist', '', <<<EOF
        static::entityManager()->persist(\$this);
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Persist the entity (the same that \$em->persist(\$entity);)
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "remove" not active record method
     */
    protected function processNotActiveRecordRemoveMethod()
    {
        $method = new Method('public', 'persist', '', <<<EOF
        static::entityManager()->remove(\$this);
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Remove the entity (the same that \$em->remove(\$entity);)
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "save" not active record method
     */
    protected function processNotActiveRecordSaveMethod()
    {
        $method = new Method('public', 'save', '', <<<EOF
        \$this->persist();
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Save the entity (equivalent to the ->persist() method)
     *
     * This is the not ActiveRecord implementation of Doctrator.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "delete" not active record method
     */
    protected function processNotActiveRecordDeleteMethod()
    {
        $method = new Method('public', 'delete', '', <<<EOF
        \$this->remove();
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Delete the entity (equivalent to the ->remove() method)
     *
     * This is the not ActiveRecord implementation of Doctrator.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * events methods
     */
    protected function processEventsMethods()
    {
        foreach ($this->configClass['events'] as $event => $callbacks) {
            $method = new Method('public', $event, '', '');
            $method->setDocComment(<<<EOF
    /**
     * The $event event.
     */
EOF
            );

            $this->definitions['entity_base']->addMethod($method);
        }
    }

    /*
     * "loadMetadata" method
     */
    protected function processLoadMetadataMethod()
    {
        $method = new Method('public', 'loadMetadata', '\\Doctrine\\ORM\\Mapping\\ClassMetadata $metadata', implode("\n", $this->loadMetadataCode[$this->class]));
        $method->setIsStatic(true);
        $method->setDocComment(<<<EOF
    /**
     * Load the metadata.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata \$metadata The metadata class.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }
}
