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
            'default_output' => null,
        ));
    }

    /**
     * @inheritdoc
     */
    protected function doConfigClassProcess()
    {
        // init mapping
        $this->initMapping();
    }

    /**
     * @inheritdoc
     */
    protected function doClassProcess()
    {
        $this->loadMetadataCode[$this->class] = array();

        // definitions and outputs
        $this->processInitDefinitionsAndOutputs();

        // mapping
        $this->processChangeTrackingPolicyMapping();
        $this->processTableNameMapping();
        $this->processCustomRepositoryClassMapping();
        $this->processColumnsMapping();
        $this->processAssociationsMapping();
        $this->processIndexesMapping();
        $this->processEventsMapping();

        // associations init collections (constructor)
        $this->processAssociationsInitCollections();

        // columns setters && getters
        $this->processColumnsSettersAndGetters();

        // associations setters && getters
        $this->processAssociationsSettersAndGetters();

        // set && get
        $this->processSetMethod();
        $this->processGetMethod();

        // fromArray && toArray
        $this->processFromArrayMethod();
        $this->processToArrayMethod();

        // loadMetadata method
        $this->processLoadMetadataMethod();
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

        // associations
        if (!isset($this->configClass['one_to_one'])) {
            $this->configClass['one_to_one'] = array();
        }
        if (!isset($this->configClass['one_to_many'])) {
            $this->configClass['one_to_many'] = array();
        }
        if (!isset($this->configClass['many_to_one'])) {
            $this->configClass['many_to_one'] = array();
        }
        if (!isset($this->configClass['many_to_many'])) {
            $this->configClass['many_to_many'] = array();
        }

        // indexes
        if (!isset($this->configClass['indexes'])) {
            $this->configClass['indexes'] = array();
        }

        // events
        $this->configClass['events'] = array(
            'prePersist'  => array(),
            'postPersist' => array(),
            'preUpdate'   => array(),
            'postUpdate'  => array(),
            'preRemove'   => array(),
            'postRemove'  => array(),
            'onFlush'     => array(),
            'postLoad'    => array(),
        );
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
     * Associations mapping.
     */
    protected function processAssociationsMapping()
    {
        foreach ($this->mergeAssociations() as $name => $association) {
            if (!isset($association['class'])) {
                throw new \RuntimeException(sprintf('The association "%s" of the class "%s" does not have class.', $name, $this->class));
            }

            // property
            $property = new Property('protected', $name, null);
            $this->definitions['entity_base']->addProperty($property);

            /*
             * mapping
             */
            $mapping = array(
                'fieldName'    => $name,
                'targetEntity' => $association['class'],
                'cascade'      => array('persist', 'remove'),
            );

            if (isset($association['fetch'])) {
                $mapping['fetch'] = constant('Doctrine\ORM\Mapping\ClassMetadata::FETCH_'.$association['fetch']);
            }

            // one_to_one
            if ('one_to_one' == $association['type']) {
                // inverse
                if (isset($association['mapped'])) {
                    $mapping['mappedBy'] = $association['mappedBy'];
                }
                // owning
                else {
                    if (isset($association['inversed'])) {
                        $mapping['inversed'] = $association['inversedBy'];
                    }
                }
            }
            // one_to_many
            if ('one_to_many' == $association['type']) {
                if (!isset($association['mapped'])) {
                    throw new \RuntimeException('The association "%s" of the class "%s" is one_to_many and does not have mapped.', $name, $this->class);
                }
                $mapping['mappedBy'] = $association['mapped'];
            }
            // many_to_one
            if ('many_to_one' == $association['type']) {
                if (isset($association['inversed'])) {
                    $mapping['inversed'] = $association['inversed'];
                }
            }
            // many_to_many
            if ('many_to_many' == $association['type']) {
                if (isset($association['mapped'])) {
                    $mapping['mapped'] = $association['mapped'];
                } else if (isset($association['join_table'])) {
                    if (isset($association['inversed'])) {
                        $mapping['inversedBy'] = $association['inversed'];
                    }
                }
            }

            $typeCamelCase = Inflector::camelize($association['type']);
            $mapping = Dumper::exportArray($mapping, 12);

            $this->loadMetadataCode[$this->class][] = <<<EOF
        \$metadata->map{$typeCamelCase}($mapping);
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
     * Associations setters and getters.
     */
    protected function processAssociationsSettersAndGetters()
    {
        foreach ($this->mergeAssociations() as $name => $association) {
            // setter
            $method = new Method('public', 'set'.Inflector::camelize($name), '$value', <<<EOF
        \$this->$name = \$value;
EOF
            );
            $method->setDocComment(<<<EOF
    /**
     * Set the $name association value.
     *
     * @param mixed \$value The association value.
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
     * Returns the $name association value.
     *
     * @return mixed The association value.
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
        // associations
        foreach ($this->mergeAssociations() as $name => $association) {
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
        // associations
        foreach ($this->mergeAssociations() as $name => $association) {
            $getter = 'get'.Inflector::camelize($name);
            $code .= <<<EOF
        if ('$name' == \$name) {
            return \$this->$getter();
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
            $gets[] = <<<EOF
        \$array['$name'] = \$this->get('$name');
EOF;
        }

        // associations
        foreach ($this->mergeAssociations() as $name => $association) {
            if (isset($association['mapped'])) {
                continue;
            }

            if (in_array($association['type'], array('one_to_one', 'many_to_one'))) {
                $gets[] = <<<EOF
        if (\$withAssociations) {
            \$array['$name'] = \$this->get('$name') ? \$this->get('$name')->toArray(\$withAssociations) : null;
        }
EOF;
            } else {
                $gets[] = <<<EOF
        if (\$withAssociations) {
            \$array['$name'] = array();
            foreach (\$this->get('$name') as \$key => \$value) {
                \$array['$name'][\$key] = \$value->toArray(\$withAssociations);
            }
        }
EOF;
            }
        }

        $gets = implode("\n", $gets);

        // method
        $method = new Method('public', 'toArray', '$withAssociations = true', <<<EOF
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
     * Associations init collections.
     */
    protected function processAssociationsInitCollections()
    {
        $collections = array();
        foreach ($this->mergeAssociations() as $name => $association) {
            if (
                'many_to_many' == $association['type']
                ||
                ('one_to_many' == $association['type'] && isset($association['mapped']))
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

    protected function mergeAssociations()
    {
        $associations = array();
        foreach (array('one_to_one', 'one_to_many', 'many_to_one', 'many_to_many') as $type) {
            foreach ($this->configClass[$type] as $name => $association) {
                $associations[$name] = array_merge($association, array('type' => $type));
            }
        }

        return $associations;
    }
}
