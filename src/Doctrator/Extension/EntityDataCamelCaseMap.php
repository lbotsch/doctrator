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
 * The doctrator EntityDataCamelCaseMap extension.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class EntityDataCamelCaseMap extends Extension
{
    /**
     * @inheritdoc
     */
    protected function doClassProcess()
    {
        /*
         * Property.
         */
        $dataCamelCaseMap = array();

        // columns
        foreach ($this->configClass['columns'] as $name => $column) {
            $dataCamelCaseMap[$name] = Inflector::camelize($name);
        }

        // relations
        foreach ($this->configClass['relations'] as $name => $relation) {
            $dataCamelCaseMap[$name] = Inflector::camelize($name);
        }

        $property = new Property('protected', 'dataCamelCaseMap', $dataCamelCaseMap);
        $property->setIsStatic(true);

        $this->definitions['entity_base']->addProperty($property);

        /*
         * Method.
         */
        $method = new Method('public', 'getDataCamelCaseMap', '', <<<EOF
        return static::\$dataCamelCaseMap;
EOF
        );
        $method->setIsStatic(true);
        $method->setDocComment(<<<EOF
    /**
     * Returns the data CamelCase map.
     *
     * @return array The data CamelCase map.
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }
}
