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
 * The doctrator ArrayAccess extension.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class ArrayAccess extends Extension
{
    /**
     * @inheritdoc
     */
    protected function doClassProcess()
    {
        $this->definitions['entity_base']->addInterface('\ArrayAccess');

        $this->processOffsetExistsMethod();
        $this->processOffsetSetMethod();
        $this->processOffsetGetMethod();
        $this->processOffsetUnsetMethod();
    }

    /*
     * "offsetExists" method
     */
    protected function processOffsetExistsMethod()
    {
        $method = new Method('public', 'offsetExists', '$name', <<<EOF
        throw new \LogicException('You cannot check if data exists in the entity.');
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Throws an \LogicException because you cannot check if data exists.
     *
     * @throws \LogicException
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "offsetSet" method
     */
    protected function processOffsetSetMethod()
    {
        $method = new Method('public', 'offsetSet', '$name, $value', <<<EOF
        return \$this->set(\$name, \$value);
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Set data in the entity.
     *
     * @param string \$name  The data name.
     * @param mixed  \$value The value.
     *
     * @see set()
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "offsetGet" method
     */
    protected function processOffsetGetMethod()
    {
        $method = new Method('public', 'offsetGet', '$name', <<<EOF
        return \$this->get(\$name);
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Returns data of the entity.
     *
     * @param string \$name The data name.
     *
     * @return mixed Some data.
     *
     * @see get()
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }

    /*
     * "offsetUnset" method
     */
    protected function processOffsetUnsetMethod()
    {
        $method = new Method('public', 'offsetUnset', '$name', <<<EOF
        throw new \LogicException('You cannot unset data in the entity.');
EOF
        );
        $method->setDocComment(<<<EOF
    /**
     * Throws a \LogicException because you cannot unset data in the entity.
     *
     * @throws \LogicException
     */
EOF
        );

        $this->definitions['entity_base']->addMethod($method);
    }
}
