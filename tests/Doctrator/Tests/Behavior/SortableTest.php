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

use Model\Simple;
use Model\Sortable;

class SortableTest extends \Doctrator\Tests\TestCase
{
    public function testBase()
    {
        for ($i = 1; $i <= 10; $i++) {
            $entity = new Sortable();
            $entity->setTitle('foo');
            $entity->save();

            $this->assertSame($i, $entity->getPosition());
        }
    }

    public function testEntityIsFirst()
    {
        $entities = array();
        for ($i = 1; $i <= 5; $i++) {
            $entities[$i] = $entity = new Sortable();
            $entity->setTitle('foo');
            $entity->save();
        }

        $this->assertTrue($entities[1]->isFirst());
        $this->assertFalse($entities[2]->isFirst());
        $this->assertFalse($entities[3]->isFirst());
        $this->assertFalse($entities[4]->isFirst());
        $this->assertFalse($entities[5]->isFirst());
    }

    public function testEntityIsLast()
    {
        $entities = array();
        for ($i = 1; $i <= 5; $i++) {
            $entities[$i] = $entity = new Sortable();
            $entity->setTitle('foo');
            $entity->save();
        }

        $this->assertFalse($entities[1]->isLast());
        $this->assertFalse($entities[2]->isLast());
        $this->assertFalse($entities[3]->isLast());
        $this->assertFalse($entities[4]->isLast());
        $this->assertTrue($entities[5]->isLast());
    }

    public function testEntityGetNext()
    {
        $entities = array();
        for ($i = 1; $i <= 5; $i++) {
            $entities[$i] = $entity = new Sortable();
            $entity->setTitle('foo');
            $entity->save();
        }

        $this->assertSame($entities[2], $entities[1]->getNext());
        $this->assertSame($entities[3], $entities[2]->getNext());
        $this->assertSame($entities[4], $entities[3]->getNext());
        $this->assertSame($entities[5], $entities[4]->getNext());
        $this->assertFalse($entities[5]->getNext());
    }

    public function testEntityGetPrevious()
    {
        $entities = array();
        for ($i = 1; $i <= 5; $i++) {
            $entities[$i] = $entity = new Sortable();
            $entity->setTitle('foo');
            $entity->save();
        }

        $this->assertFalse($entities[1]->getPrevious());
        $this->assertSame($entities[1], $entities[2]->getPrevious());
        $this->assertSame($entities[2], $entities[3]->getPrevious());
        $this->assertSame($entities[3], $entities[4]->getPrevious());
        $this->assertSame($entities[4], $entities[5]->getPrevious());
    }

    public function testEntitySwapWith()
    {
        $entities = array();
        for ($i = 1; $i <= 5; $i++) {
            $entities[$i] = $entity = new Sortable();
            $entity->setTitle('foo');
            $entity->save();
        }

        $entities[2]->swapWith($entities[4]);

        foreach ($entities as $entity) {
            $entity->refresh();
        }

        $this->assertSame(1, $entities[1]->getPosition());
        $this->assertSame(2, $entities[4]->getPosition());
        $this->assertSame(3, $entities[3]->getPosition());
        $this->assertSame(4, $entities[2]->getPosition());
        $this->assertSame(5, $entities[5]->getPosition());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEntitySwapWithAnotherClassEntity()
    {
        $sortable = new Sortable();
        $sortable->setTitle('foo');
        $sortable->save();

        $simple = new Simple();
        $simple->setColumn('foo');
        $simple->save();

        $sortable->swapWith($simple);
    }

    public function testEntityMoveUp()
    {
        $entities = array();
        for ($i = 1; $i <= 5; $i++) {
            $entities[$i] = $entity = new Sortable();
            $entity->setTitle('foo');
            $entity->save();
        }

        $entities[3]->moveUp();

        foreach ($entities as $entity) {
            $entity->refresh();
        }

        $this->assertSame(1, $entities[1]->getPosition());
        $this->assertSame(2, $entities[3]->getPosition());
        $this->assertSame(3, $entities[2]->getPosition());
        $this->assertSame(4, $entities[4]->getPosition());
        $this->assertSame(5, $entities[5]->getPosition());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testEntityMoveUpIsFirst()
    {
        $entities = array();
        for ($i = 1; $i <= 5; $i++) {
            $entities[$i] = $entity = new Sortable();
            $entity->setTitle('foo');
            $entity->save();
        }

        $entities[1]->moveUp();
    }

    public function testEntityMoveDown()
    {
        $entities = array();
        for ($i = 1; $i <= 5; $i++) {
            $entities[$i] = $entity = new Sortable();
            $entity->setTitle('foo');
            $entity->save();
        }

        $entities[3]->moveDown();

        foreach ($entities as $entity) {
            $entity->refresh();
        }

        $this->assertSame(1, $entities[1]->getPosition());
        $this->assertSame(2, $entities[2]->getPosition());
        $this->assertSame(3, $entities[4]->getPosition());
        $this->assertSame(4, $entities[3]->getPosition());
        $this->assertSame(5, $entities[5]->getPosition());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testEntityMoveDownIsLast()
    {
        $entities = array();
        for ($i = 1; $i <= 5; $i++) {
            $entities[$i] = $entity = new Sortable();
            $entity->setTitle('foo');
            $entity->save();
        }

        $entities[5]->moveDown();
    }

    public function testRepositoryGetMinPosition()
    {
        $repository = $this->entityManager->getRepository('Model\Sortable');

        $this->assertNull($repository->getMinPosition());

        for ($i = 1; $i <= 5; $i++) {
            $entity = new Sortable();
            $entity->setTitle('foo');
            $entity->save();
        }

        $this->assertSame(1, $repository->getMinPosition());
    }

    public function testRepositoryGetMaxPosition()
    {
        $repository = $this->entityManager->getRepository('Model\Sortable');

        $this->assertNull($repository->getMaxPosition());

        for ($i = 1; $i <= 5; $i++) {
            $entity = new Sortable();
            $entity->setTitle('foo');
            $entity->save();
        }

        $this->assertSame(5, $repository->getMaxPosition());
    }

    public function testSortableSetPositionEventModifyPosition()
    {
        $entities = array();
        for ($i = 1; $i <= 8; $i++) {
            $entities[$i] = $entity = new Sortable();
            $entity->setTitle('foo');
            $entity->save();
        }

        $entities[5]->setPosition(3);
        $entities[5]->save();

        foreach ($entities as $entity) {
            $entity->refresh();
        }

        $this->assertSame(1, $entities[1]->getPosition());
        $this->assertSame(2, $entities[2]->getPosition());
        $this->assertSame(3, $entities[5]->getPosition());
        $this->assertSame(4, $entities[3]->getPosition());
        $this->assertSame(5, $entities[4]->getPosition());
        $this->assertSame(6, $entities[6]->getPosition());
        $this->assertSame(7, $entities[7]->getPosition());
        $this->assertSame(8, $entities[8]->getPosition());

        $entities[1]->setTitle('bar');
        $entities[1]->save();
    }
}
