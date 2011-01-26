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
 * The doctrator Timestampable behavior.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Timestampable extends Extension
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->addOptions(array(
            'created_enabled' => true,
            'created_column'  => 'created_at',
            'updated_enabled' => true,
            'updated_column'  => 'updated_at',
        ));
    }

    /**
     * @inheritdoc
     */
    protected function doProcess()
    {
        /*
         * Created.
         */
        if ($this->getOption('created_enabled')) {
            // column
            $column = $this->getOption('created_column');
            $this->configClass['columns'][$column] = array('type' => 'datetime', 'nullable' => true);

            // event
            $columnSetter = 'set'.Inflector::camelize($column);

            $method = new Method('public', 'updateTimestampableCreated', '', <<<EOF
        \$this->$columnSetter(new \DateTime());
EOF
            );

            $this->definitions['entity_base']->addMethod($method);

            $this->configClass['events']['prePersist'][] = $method->getName();
        }

        /*
         * Updated.
         */
        if ($this->getOption('updated_enabled')) {
            // column
            $column = $this->getOption('updated_column');
            $this->configClass['columns'][$column] = array('type' => 'datetime', 'nullable' => true);

            // event
            $columnSetter = 'set'.Inflector::camelize($column);

            $method = new Method('public', 'updateTimestampableUpdated', '', <<<EOF
        \$this->$columnSetter(new \DateTime());
EOF
            );

            $this->definitions['entity_base']->addMethod($method);

            $this->configClass['events']['preUpdate'][] = $method->getName();
        }
    }
}
