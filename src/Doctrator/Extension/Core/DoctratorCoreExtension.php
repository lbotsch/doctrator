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

namespace Doctrator\Extension\Core;

use Mandango\Mondator\Extension;
use Mandango\Mondator\Definition;
use Mandango\Mondator\Definition\Method;
use Mandango\Mondator\Definition\Property;
use Mandango\Mondator\Output;
use Mandango\Mondator\Dumper;

/**
 * The doctrator Core extension.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class DoctratorCoreExtension extends Extension
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

        // templates
        $this->processTemplate($this->definitions['entity_base'], 'core.php');

        // loadMetadata method
        $this->processLoadMetadataMethod();

        return;

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
        if (!isset($this->configClass['oneToOne'])) {
            $this->configClass['oneToOne'] = array();
        }
        if (!isset($this->configClass['oneToMany'])) {
            $this->configClass['oneToMany'] = array();
        }
        if (!isset($this->configClass['manyToOne'])) {
            $this->configClass['manyToOne'] = array();
        }
        if (!isset($this->configClass['manyToMany'])) {
            $this->configClass['manyToMany'] = array();
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
        // classes
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

        // dir
        $dir = $this->getOption('default_output');
        if (isset($this->configClass['output'])) {
            $dir = $this->configClass['output'];
        }
        if (!$dir) {
            throw new \RuntimeException(sprintf('The class "%s" does not have output.', $this->class));
        }

        // entity
        $this->definitions['entity'] = $definition = new Definition($classes['entity'], new Output($dir));
        $definition->setParentClass('\\'.$classes['entity_base']);
        $definition->setDocComment(<<<EOF
/**
 * {$this->class} entity.
 */
EOF
        );

        // entity_base
        $this->definitions['entity_base'] = $definition = new Definition($classes['entity_base'], new Output($dir.'/Base', true));
        $definition->setIsAbstract(true);
        $definition->setDocComment(<<<EOF
/**
 * Base class of the {$this->class} entity.
 */
EOF
        );

        // repository
        $this->definitions['repository'] = $definition = new Definition($classes['repository'], new Output($dir));
        $definition->setParentClass('\\'.$classes['repository_base']);
        $definition->setDocComment(<<<EOF
/**
 * Repository of the {$this->class} entity.
 */
EOF
        );

        // repository_base
        $this->definitions['repository_base'] = $definition = new Definition($classes['repository_base'], new Output($dir.'/Base', true));
        $definition->setIsAbstract(true);
        $definition->setParentClass('\\Doctrine\\ORM\\EntityRepository');
        $definition->setDocComment(<<<EOF
/**
 * Base class of the repository of the {$this->class} entity.
 */
EOF
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

            // oneToOne
            if ('oneToOne' == $association['type']) {
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
            // oneToMany
            if ('oneToMany' == $association['type']) {
                if (!isset($association['mapped'])) {
                    throw new \RuntimeException('The association "%s" of the class "%s" is oneToMany and does not have mapped.', $name, $this->class);
                }
                $mapping['mappedBy'] = $association['mapped'];
            }
            // manyToOne
            if ('manyToOne' == $association['type']) {
                if (isset($association['inversed'])) {
                    $mapping['inversed'] = $association['inversed'];
                }
            }
            // manyToMany
            if ('manyToMany' == $association['type']) {
                if (isset($association['mapped'])) {
                    $mapping['mapped'] = $association['mapped'];
                } else if (isset($association['join_table'])) {
                    if (isset($association['inversed'])) {
                        $mapping['inversedBy'] = $association['inversed'];
                    }
                }
            }

            $typeCamelized = ucfirst($association['type']);
            $mapping = Dumper::exportArray($mapping, 12);

            $this->loadMetadataCode[$this->class][] = <<<EOF
        \$metadata->map{$typeCamelized}($mapping);
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
        foreach (array('oneToOne', 'oneToMany', 'manyToOne', 'manyToMany') as $type) {
            foreach ($this->configClass[$type] as $name => $association) {
                $associations[$name] = array_merge($association, array('type' => $type));
            }
        }

        return $associations;
    }

    protected function configureTwig(\Twig_Environment $twig)
    {
        $twig->addFilter('ucfirst', new \Twig_Filter_Function('ucfirst'));
    }
}
