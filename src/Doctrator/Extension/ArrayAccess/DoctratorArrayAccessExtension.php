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

namespace Doctrator\Extension\ArrayAccess;

use Mandango\Mondator\Extension;
use Mandango\Mondator\Definition\Method;
use Mandango\Mondator\Definition\Property;

/**
 * The doctrator ArrayAccess extension.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class DoctratorArrayAccessExtension extends Extension
{
    /**
     * @inheritdoc
     */
    protected function doClassProcess()
    {
        $this->definitions['entity_base']->addInterface('\ArrayAccess');
        $this->processTemplate($this->definitions['entity_base'], 'arrayAccess.php');
    }
}
