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

namespace Doctrator\Driver;

use Doctrine\ORM\Mapping\Driver\StaticPHPDriver;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * The doctrator driver.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class DoctratorDriver extends StaticPHPDriver
{
    protected $_fileExtension = '.php';

    protected $_classNames;

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, ClassMetadataInfo $metadata)
    {
        if (false !== stripos($className, 'Base')) {
            $metadata->isMappedSuperclass = true;
        } else {
            parent::loadMetadataForClass($className, $metadata);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isTransient($className)
    {
        return parent::isTransient($className) || false !== stripos($className, 'Base');
    }
}
