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

use Mondongo\Mondator\ClassExtension;
use Mondongo\Mondator\Definition\Method;

/**
 * The doctrator Translatable behavior.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Translatable extends ClassExtension
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->addRequiredOption('columns');
    }

    /**
     * @inheritdoc
     */
    public function getNewConfigClasses($class, \ArrayObject $configClass)
    {
        $newConfigClasses = array();

        // %class%Translation
        $translationConfigClass = array(
            'columns' => array(
                'id'     => array('id' => 'auto', 'type' => 'integer'),
                'locale' => array('type' => 'string', 'length' => 7),
            ),
            'relations' => array(
                'parent' => array('type' => 'ManyToOne', 'targetEntity' => $class, 'inversedBy' => 'translations'),
            ),
        );

        $configClassColumns = $configClass['columns'];
        foreach ($this->getOption('columns') as $column) {
            if (!isset($configClassColumns[$column])) {
                throw new \RuntimeException(sprintf('The column "%s" of the class "%s" does not exists.', $column, $class));
            }
            $translationConfigClass['columns'][$column] = $configClassColumns[$column];

            unset($configClassColumns[$column]);
        }
        $configClass['columns'] = $configClassColumns;

        $newConfigClasses[$class.'Translation'] = $translationConfigClass;

        // relation
        $configClass['relations']['translations'] = array(
            'type'         => 'OneToMany',
            'targetEntity' => $class.'Translation',
            'mappedBy'     => 'parent',
        );

        return $newConfigClasses;
    }

    /**
     * @inheritdoc
     */
    protected function doClassProcess()
    {
        // "translation" method
        $method = new Method('public', 'translation', '$locale', <<<EOF
        foreach (\$this->getTranslations() as \$translation) {
            if (\$translation->getLocale() == \$locale) {
                return \$translation;
            }
        }

        \$translation = new \\{$this->class}Translation();
        \$translation->setParent(\$this);
        \$translation->setLocale(\$locale);

        \$this->getTranslations()->add(\$translation);

        return \$translation;
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns a translation entity by locale.
     *
     * @param string \$locale The locale.
     *
     * @return \{$this->class}Translation The translation entity.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }
}
