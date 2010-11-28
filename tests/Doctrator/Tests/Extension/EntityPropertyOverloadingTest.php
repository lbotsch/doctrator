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

use Model\Article;

class EntityPropertyOverloadingTest extends \Doctrator\Tests\TestCase
{
    public function test__set()
    {
        $article = new Article();
        $article->title = 'Doctrator';
        $this->assertSame('Doctrator', $article->getTitle());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test__setNameNotExists()
    {
        $article = new Article();
        $article->no = 'Doctrator';
    }

    public function test__get()
    {
        $article = new Article();
        $article->setTitle('Doctrator');
        $this->assertSame('Doctrator', $article->title);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test__getNameNotExists()
    {
        $article = new Article();
        $article->no;
    }
}
