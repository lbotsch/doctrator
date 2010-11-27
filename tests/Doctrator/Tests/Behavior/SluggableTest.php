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

namespace Doctrator\Tests\Behavior;

use Model\Entity\Sluggable;
use Model\Entity\SluggableUpdate;

class SluggableTest extends \Doctrator\Tests\TestCase
{
    public function testSluggable()
    {
        $entities = array();

        $entities[1] = new Sluggable();
        $entities[1]->setTitle(' Testing Sluggable Extensión ');
        $entities[1]->save();

        $this->assertSame('testing-sluggable-extension', $entities[1]->getSlug());

        $entities[2] = new Sluggable();
        $entities[2]->setTitle(' Testing Sluggable Extensión ');
        $entities[2]->save();

        $this->assertSame('testing-sluggable-extension-2', $entities[2]->getSlug());
    }

    public function testSluggableUpdate()
    {
        $entity = new SluggableUpdate();
        $entity->setTitle('Primer título');
        $entity->save();

        $this->assertSame('primer-titulo', $entity->getSlug());

        $entity->setBody('foo');
        $entity->save();

        $this->assertSame('primer-titulo', $entity->getSlug());

        $entity->setTitle('Actualizando título');
        $entity->save();

        $this->assertSame('actualizando-titulo', $entity->getSlug());
    }
}
