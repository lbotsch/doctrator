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

namespace Doctrator\Tests\Extension;

use Model\Entity\Article;

class EntityArrayAccessTest extends \Doctrator\Tests\TestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testOffsetExists()
    {
        $article = new Article();
        isset($article['title']);
    }

    public function testOffsetSet()
    {
        $article = new Article();
        $article['title'] = 'Doctrator';
        $this->assertSame('Doctrator', $article->getTitle());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOffsetSetNameNotExists()
    {
        $article = new Article();
        $article['no'] = 'Doctrator';
    }

    public function testOffsetGet()
    {
        $article = new Article();
        $article->setTitle('Doctrator');
        $this->assertSame('Doctrator', $article['title']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOffsetGetNameNotExists()
    {
        $article = new Article();
        $article['no'];
    }

    /**
     * @expectedException \LogicException
     */
    public function testOffsetUnset()
    {
        $article = new Article();
        unset($article['title']);
    }
}
