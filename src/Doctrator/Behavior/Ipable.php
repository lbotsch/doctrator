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
use Mandango\Inflector;

/**
 * The doctrator Ipable behavior.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Ipable extends ClassExtension
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->addOptions(array(
            'created_enabled' => true,
            'created_column'  => 'created_from',
            'updated_enabled' => true,
            'updated_column'  => 'updated_from',
            'get_ip_callable' => array('\Doctrator\Behavior\Ipable', 'getIp'),
        ));
    }

    /**
     * @inheritdoc
     */
    protected function doConfigClassProcess()
    {
        // created
        if ($this->getOption('created_enabled')) {
            $column = $this->getOption('created_column');
            $this->configClass['columns'][$column] = array('type' => 'string', 'length' => 50, 'nullable' => true);

            $this->configClass['events']['prePersist'][] = 'updateIpableCreated';
        }

        // updated
        if ($this->getOption('updated_enabled')) {
            $column = $this->getOption('updated_column');
            $this->configClass['columns'][$column] = array('type' => 'string', 'length' => 50, 'nullable' => true);

            $this->configClass['events']['preUpdate'][] = 'updateIpableUpdated';
        }
    }

    /**
     * @inheritdoc
     */
    protected function doClassProcess()
    {
        /*
         * Created.
         */
        if ($this->getOption('created_enabled')) {
            // column
            $column = $this->getOption('created_column');

            // event
            $columnSetter  = 'set'.Inflector::camelize($column);
            $getIpCallable = $this->getIpCallableAsString();

            $method = new Method('public', 'updateIpableCreated', '', <<<EOF
        \$this->$columnSetter(call_user_func($getIpCallable));
EOF
            );

            $this->definitions['entity_base']->addMethod($method);
        }

        /*
         * Updated.
         */
        if ($this->getOption('updated_enabled')) {
            // column
            $column = $this->getOption('updated_column');

            // event
            $columnSetter  = 'set'.Inflector::camelize($column);
            $getIpCallable = $this->getIpCallableAsString();

            $method = new Method('public', 'updateIpableUpdated', '', <<<EOF
        \$this->$columnSetter(call_user_func($getIpCallable));
EOF
            );

            $this->definitions['entity_base']->addMethod($method);
        }
    }

    protected function getIpCallableAsString()
    {
        $getIpCallable = $this->getOption('get_ip_callable');
        if (is_array($getIpCallable)) {
            $getIpCallable = sprintf("array('%s', '%s')", $getIpCallable[0], $getIpCallable[1]);
        } else {
            $getIpCallable = "'$getIpCallable'";
        }

        return $getIpCallable;
    }

    /**
     * Returns the IP from $_SERVER['REMOTE_ADDR'] if exists, or 127.0.0.1 if it does not exists.
     *
     * @return string The IP.
     */
    static public function getIp()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    }
}
