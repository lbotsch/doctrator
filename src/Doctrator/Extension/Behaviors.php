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
 * The doctrator Behaviors extension.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Behaviors extends Extension
{
    /**
     * @inheritdoc
     */
    protected function setup()
    {
        $this->addOptions(array(
            'default_behaviors' => array(),
        ));
    }

    /**
     * @inheritdoc
     */
    protected function doNewClassExtensionsProcess()
    {
        // default behaviors
        foreach ($this->getOption('default_behaviors') as $behavior) {
            $this->newClassExtensions[] = $this->createClassExtensionFromArray($behavior);
        }

        // behaviors
        if (isset($this->configClass['behaviors'])) {
            foreach ($this->configClass['behaviors'] as $behavior) {
                $this->newClassExtensions[] = $this->createClassExtensionFromArray($behavior);
            }
        }
    }
}
