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

use Doctrator\Behavior\Taggable as TaggableBehavior;
use Model\Taggable;

class TaggableTest extends \Doctrator\Tests\TestCase
{
    public function testBase()
    {
        $entity = new Taggable();
        $entity->setTitle('foo');
        $entity->save();
    }

    public function testAddTags()
    {
        $entity = new Taggable();
        $entity->setTitle('foo');
        $entity->save();

        $entity->addTags('foo, bar');
        $entity->addTags(array('foobar', 'barfoo'));
        $this->assertSame(array('foo', 'bar', 'foobar', 'barfoo'), $entity->getTags());
        $this->assertSame('0', $this->getNb());
        $entity->saveTags();
        $this->assertSame('4', $this->getNb());
    }

    public function testRemoveTags()
    {
        $entity = new Taggable();
        $entity->setTitle('foo');
        $entity->save();

        $entity->addTags('foo, bar, foobar, barfoo');
        $entity->saveTags();

        $entity->removeTags('bar, foobar');
        $this->assertSame(array('foo', 'barfoo'), $entity->getTags());
        $this->assertSame('4', $this->getNb());
        $entity->saveTags();
        $this->assertSame('2', $this->getNb());
    }

    public function testRemoveAllTags()
    {
        $entity = new Taggable();
        $entity->setTitle('foo');
        $entity->save();

        $entity->addTags('foo, bar');
        $entity->saveTags();
        $entity->addTags('foobar, barfoo');

        $entity->removeAllTags();
        $this->assertSame(array(), $entity->getTags());
        $this->assertSame('2', $this->getNb());
        $entity->saveTags();
        $this->assertSame('0', $this->getNb());
    }

    public function testSaveTags()
    {
        $entity = new Taggable();
        $entity->setTitle('foo');
        $entity->save();

        $this->assertSame(array(), $entity->getSavedTags());
        $entity->addTags('foo, bar');
        $this->assertSame(array(), $entity->getSavedTags());
        $entity->saveTags();
        $this->assertSame(array('foo', 'bar'), $entity->getSavedTags());
        $entity->addTags('foobar, barfoo');
        $this->assertSame(array('foo', 'bar'), $entity->getSavedTags());
        $entity->saveTags();
        $this->assertSame(array('foo', 'bar', 'foobar', 'barfoo'), $entity->getSavedTags());
    }

    public function testSetTags()
    {
        $entity = new Taggable();
        $entity->setTitle('foo');
        $entity->save();

        $entity->addTags('foo, bar');
        $entity->saveTags();
        $entity->addTags('foobar, barfoo');

        $entity->setTags('doctrator, doctrine2');
        $this->assertSame(array('doctrator', 'doctrine2'), $entity->getTags());
    }

    protected function getNb()
    {
        return $this->entityManager->createQuery('SELECT COUNT(t.id) FROM Model\TaggableTagging t')->getSingleScalarResult();
    }

    public function testRepositoryGetTags()
    {
        $entity1 = new Taggable();
        $entity1->setTitle('foo');
        $entity1->save();
        $entity1->setTags('foo, bar, foobar');
        $entity1->saveTags();

        $entity2 = new Taggable();
        $entity2->setTitle('foo');
        $entity2->save();
        $entity2->setTags('foo, foobar, barfoo');
        $entity2->saveTags();

        $this->assertSame(array('foo', 'bar', 'foobar', 'barfoo'), Taggable::repository()->getTags());
    }

    public function testRepositoryGetTagsWithCount()
    {
        $tags = array(
            'foo, bar',
            'foo, bar, foobar',
            'foo, bar, foobar, barfoo',
            'foo, bar',
        );
        foreach ($tags as $tag) {
            $entity = new Taggable();
            $entity->setTitle('foo');
            $entity->save();
            $entity->setTags($tag);
            $entity->saveTags();
        }

        $this->assertSame(array(
            'foo'    => '4',
            'bar'    => '4',
            'foobar' => '2',
            'barfoo' => '1',
        ), Taggable::repository()->getTagsWithCount());

        $this->assertSame(array(
            'foo'    => '4',
            'bar'    => '4',
            'foobar' => '2',
        ), Taggable::repository()->getTagsWithCount(3));
    }

    public function testExplodeTags()
    {
        $this->assertSame(array('foo', 'bar'), TaggableBehavior::explodeTags('foo,bar'));
        $this->assertSame(array('foo', 'bar'), TaggableBehavior::explodeTags(array('foo', 'bar')));

        $this->assertSame(array('foo', 'bar'), TaggableBehavior::explodeTags(' foo, bar '));
        $this->assertSame(array('foo', 'bar'), TaggableBehavior::explodeTags(array(' foo', ' bar ')));

        $this->assertSame(array('foo', 'bar'), TaggableBehavior::explodeTags('foo,,bar'));
        $this->assertSame(array('foo', 'bar'), TaggableBehavior::explodeTags(array('foo', 'bar', '')));

        $this->assertSame(array('foo'), TaggableBehavior::explodeTags('foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExplodeTagsNotStringNorArray()
    {
        TaggableBehavior::explodeTags(true);
    }

    public function testCleanTag()
    {
        $this->assertSame('foo', TaggableBehavior::cleanTag(' foo '));
        $this->assertSame('foo bar', TaggableBehavior::cleanTag('foo,bar'));

        $this->assertSame('foo bar', TaggableBehavior::cleanTag(' foo,bar '));
    }

    public function testExplodeAndCleanTags()
    {
        $this->assertSame(array('foo bar', 'bar foo'), TaggableBehavior::explodeAndCleanTags(' foo bar, bar foo '));
    }
}
