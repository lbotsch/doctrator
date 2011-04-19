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

use Mandango\Mondator\ClassExtension;
use Mandango\Mondator\Definition\Method;

/**
 * The doctrator Hashable behavior.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Hashable extends ClassExtension
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->addOptions(array(
            'column' => 'hash',
        ));
    }

    /**
     * @inheritdoc
     */
    protected function doConfigClassProcess()
    {
        // column
        $column = $this->getOption('column');
        $this->configClass['columns'][$column] = array('type' => 'string', 'length' => 40, 'unique' => true);

        // event
        $this->configClass['events']['prePersist'][] = 'updateHashableHash';
    }

    /**
     * @inheritdoc
     */
    protected function doClassProcess()
    {
        $column = $this->getOption('column');

        // event
        $method = new Method('public', 'updateHashableHash', '', <<<EOF
        do {
            \$hash = '';
            for (\$i = 1; \$i <= 20; \$i++) {
                \$hash .= sha1(microtime(true).mt_rand(111111, 999999));
            }
            \$hash = sha1(\$hash);

            \$results = static::entityManager()->createQuery("SELECT e.id FROM {$this->class} e WHERE e.$column = '\$hash'")->getArrayResult();
        } while (\$results);

        \$this->set('$column', \$hash);
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }
}
