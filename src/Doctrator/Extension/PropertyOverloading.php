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

use Mandango\Mondator\Extension;
use Mandango\Mondator\Definition\Method;
use Mandango\Mondator\Definition\Property;
use Mandango\Inflector;

/**
 * The doctrator PropertyOverloading extension.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class PropertyOverloading extends Extension
{
    /**
     * @inheritdoc
     */
    protected function doClassProcess()
    {
        $this->process__setMethod();
        $this->process__getMethod();
    }

    /*
     * "__set" method
     */
    protected function process__setMethod()
    {
        $method = new Method('public', '__set', '$name, $value', <<<EOF
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
     * "__get" method
     */
    protected function process__getMethod()
    {
        $method = new Method('public', '__get', '$name', <<<EOF
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
}
