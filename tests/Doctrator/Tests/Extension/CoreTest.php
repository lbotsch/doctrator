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
use Model\Category;
use Model\Simple;
use Model\IdentifierStrategyAuto;

class CoreTest extends \Doctrator\Tests\TestCase
{
    public function testRepositoryClass()
    {
        $this->assertSame('Model\ArticleRepository', $this->metadataFactory->getMetadataFor('Model\Article')->customRepositoryClassName);
        $this->assertSame('Model\CategoryRepository', $this->metadataFactory->getMetadataFor('Model\Category')->customRepositoryClassName);
    }

    public function testColumnsMapping()
    {
        $metadata      = $this->metadataFactory->getMetadataFor('Model\Article');
        $fieldMappings = $metadata->fieldMappings;

        // id
        $this->assertTrue(isset($fieldMappings['id']));
        $this->assertSame('integer', $fieldMappings['id']['type']);
        $this->assertTrue($fieldMappings['id']['id']);
        $this->assertSame('id', $fieldMappings['id']['columnName']);

        // title
        $this->assertTrue(isset($fieldMappings['title']));
        $this->assertSame('string', $fieldMappings['title']['type']);
        $this->assertEquals(100, $fieldMappings['title']['length']);
        $this->assertSame('title', $fieldMappings['title']['columnName']);

        // slug
        $this->assertTrue(isset($fieldMappings['slug']));
        $this->assertSame('string', $fieldMappings['slug']['type']);
        $this->assertEquals(110, $fieldMappings['slug']['length']);
        $this->assertTrue($fieldMappings['slug']['unique']);
        $this->assertSame('title_slug', $fieldMappings['slug']['columnName']);

        // content
        $this->assertTrue(isset($fieldMappings['content']));
        $this->assertSame('text', $fieldMappings['content']['type']);

        // source
        $this->assertTrue(isset($fieldMappings['source']));
        $this->assertSame('string', $fieldMappings['source']['type']);
        $this->assertEquals(255, $fieldMappings['source']['length']);

        // is_active
        $this->assertTrue(isset($fieldMappings['is_active']));
        $this->assertSame('boolean', $fieldMappings['is_active']['type']);
        $this->assertTrue($metadata->getReflectionProperty('is_active')->getValue(new Article()));

        // score
        $this->assertTrue(isset($fieldMappings['score']));
        $this->assertSame('decimal', $fieldMappings['score']['type']);
        $this->assertEquals(2, $fieldMappings['score']['precision']);
        $this->assertEquals(4, $fieldMappings['score']['scale']);
    }

    public function testIdentifierStrategyIdentity()
    {
        $metadata = $this->metadataFactory->getMetadataFor('Model\IdentifierStrategyIdentity');

        $this->assertTrue($metadata->isIdGeneratorIdentity());
    }

    public function testIdentifierStrategyNone()
    {
        $metadata = $this->metadataFactory->getMetadataFor('Model\IdentifierStrategyNone');

        $this->assertTrue($metadata->isIdentifierNatural());
    }

    public function testRelationsMapping()
    {
        $articleMetadata  = $this->metadataFactory->getMetadataFor('Model\Article');
        $categoryMetadata = $this->metadataFactory->getMetadataFor('Model\Category');

        $this->assertTrue(isset($articleMetadata->associationMappings['category']));
        $this->assertTrue(isset($categoryMetadata->associationMappings['articles']));
    }

    public function testIndexesMapping()
    {
        $metadata  = $this->metadataFactory->getMetadataFor('Model\Article');

        $this->assertSame(array(
            'is_active_date' => array('columns' => array('is_active', 'date')),
        ), $metadata->table['indexes']);

        $this->assertSame(array(
            'my_slug_index' => array('columns' => array('title_slug')),
        ), $metadata->table['uniqueConstraints']);
    }

    public function testRelationsInitCollections()
    {
        $category = new Category();

        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $category->getArticles());
    }

    public function testColumnsSettersAndGetters()
    {
        $article = new Article();

        $article->setTitle('My Title');
        $this->assertSame('My Title', $article->getTitle());

        $article->setIsActive(false);
        $this->assertFalse($article->getIsActive());
    }

    public function testRelationsSettersAndGetters()
    {
        $article  = new Article();
        $category = new Category();

        $article->setCategory($category);
        $this->assertSame($category, $article->getCategory());
    }

    public function testSet()
    {
        $article = new Article();
        $article->set('title', 'Doctrator');
        $article->set('content', 'My Content');

        $this->assertSame('Doctrator', $article->getTitle());
        $this->assertSame('My Content', $article->getContent());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetDataDoesNotExists()
    {
        $article = new Article();
        $article->set('no', 'foo');
    }

    public function testGet()
    {
        $article = new Article();
        $article->setTitle('foo');
        $article->setContent('bar');

        $this->assertSame('foo', $article->get('title'));
        $this->assertSame('bar', $article->get('content'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetDataDoesNotExists()
    {
        $article = new Article();
        $article->get('no');
    }

    public function testFromArray()
    {
        $article = new Article();
        $article->fromArray(array(
            'title'   => 'foo',
            'content' => 'bar',
        ));

        $this->assertSame('foo', $article->getTitle());
        $this->assertSame('bar', $article->getContent());
    }

    public function testToArray()
    {
        $article = new Article();
        $article->setTitle('foo');
        $article->setContent('bar');

        $this->assertSame(array(
            'id'      => null,
            'title'   => 'foo',
            'slug'    => null,
            'content' => 'bar',
            'source'  => null,
            'is_active' => true,
            'score'     => null,
            'date'      => null,
        ), $article->toArray());
    }

    public function testEntityManager()
    {
        $this->assertSame($this->entityManager, Article::entityManager());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCheckEntityManagerIsClearPendingInserts()
    {
        $simple = new Simple();
        $simple->setColumn('foo');
        $this->entityManager->persist($simple);

        $simple->checkEntityManagerIsClear();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCheckEntityManagerIsClearPendingUpdates()
    {
        $simple = new Simple();
        $simple->setColumn('foo');
        $simple->save();
        $simple->setColumn('bar');
        $this->entityManager->persist($simple);

        $simple->checkEntityManagerIsClear();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCheckEntityManagerIsClearPendingDeletes()
    {
        $simple = new Simple();
        $simple->setColumn('foo');
        $simple->save();
        $this->entityManager->remove($simple);

        $simple->checkEntityManagerIsClear();
    }

    public function testRepository()
    {
        $this->assertSame($this->entityManager->getRepository('Model\Article'), Article::repository());
    }

    public function testQueryBuilder()
    {
        $this->assertEquals(Article::repository()->createQueryBuilder('alias'), Article::queryBuilder('alias'));
    }

    public function testIsNew()
    {
        $simple = new Simple();
        $simple->setColumn('foo');

        $this->assertTrue($simple->isNew());

        $simple->save();

        $this->assertFalse($simple->isNew());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCheckIsNew()
    {
        $simple = new Simple();
        $simple->setColumn('foo');
        $simple->save();
        $simple->checkIsNew();
    }

    public function testCheckIsNewOk()
    {
        $simple = new Simple();
        $simple->setColumn('foo');
        $simple->checkIsNew();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCheckIsNotNew()
    {
        $simple = new Simple();
        $simple->setColumn('foo');
        $simple->checkIsNotNew();
    }

    public function testCheckIsNotNewOk()
    {
        $simple = new Simple();
        $simple->setColumn('foo');
        $simple->save();
        $simple->checkIsNotNew();
    }

    public function testIsModifiedGetModified()
    {
        $simple = new Simple();
        $simple->setColumn('foo');
        $this->assertFalse($simple->isModified());
        $this->assertSame(array(), $simple->getModified());

        $simple->save();
        $simple->setColumn2('bar');
        $this->assertTrue($simple->isModified());
        $this->assertSame(array('column2' => null), $simple->getModified());
        $simple->setColumn('foobar');
        $simple->setColumn2('barfoo');
        $this->assertTrue($simple->isModified());
        $this->assertSame(array('column' => 'foo', 'column2' => null), $simple->getModified());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCheckIsModified()
    {
        $simple = new Simple();
        $simple->checkIsModified();
    }

    public function testCheckIsModifiedOk()
    {
        $simple = new Simple();
        $simple->setColumn('foo');
        $simple->save();
        $simple->setColumn('bar');
        $simple->checkIsModified();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCheckIsNotModified()
    {
        $simple = new Simple();
        $simple->setColumn('foo');
        $simple->save();
        $simple->setColumn('bar');
        $simple->checkIsNotModified();
    }

    public function testCheckIsNotModifiedOk()
    {
        $simple = new Simple();
        $simple->setColumn('foo');
        $simple->save();
        $simple->checkIsNotModified();
    }

    public function testRefresh()
    {
        $simple = new Simple();
        $simple->setColumn('foo');
        $simple->save();

        $simple->setColumn('bar');
        $simple->refresh();

        $this->assertSame('foo', $simple->getColumn());
    }

    public function testSave()
    {
        $simple = new Simple();
        $simple->setColumn('foo');
        $simple->save();

        $this->assertTrue(is_int($simple->getId()));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSaveCheckClearEntityManager()
    {
        $simple1 = new Simple();
        $simple1->setColumn('foo');
        $simple1->save();

        $simple2 = new Simple();
        $simple2->setColumn('bar');
        $this->entityManager->persist($simple2);

        $simple1->setColumn('foobar');
        $simple1->save();
    }

    public function testDelete()
    {
        $simple = new Simple();
        $simple->setColumn('foo');
        $this->entityManager->persist($simple);
        $this->entityManager->flush();
        $simple->delete();

        $this->assertNull($simple->getId());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDeleteCheckClearEntityManager()
    {
        $simple1 = new Simple();
        $simple1->setColumn('foo');
        $simple1->save();

        $simple2 = new Simple();
        $simple2->setColumn('bar');
        $this->entityManager->persist($simple2);

        $simple1->delete();
    }
}
