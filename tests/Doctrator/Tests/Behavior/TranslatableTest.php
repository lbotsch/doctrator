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

use Doctrine\ORM\Query;
use Model\Translatable;

class TranslatableTest extends \Doctrator\Tests\TestCase
{
    public function testTranslatableMapping()
    {
        $translatable = $this->metadataFactory->getMetadataFor('Model\Translatable');
        $translation  = $this->metadataFactory->getMetadataFor('Model\TranslatableTranslation');

        $this->assertFalse(isset($translatable->fieldMappings['title']));
        $this->assertFalse(isset($translatable->fieldMappings['body']));

        $this->assertTrue(isset($translation->fieldMappings['title']));
        $this->assertTrue(isset($translation->fieldMappings['body']));
    }

    public function testTranslatable()
    {
        $entity = new Translatable();

        $enTranslation = $entity->translation('en');
        $this->assertInstanceOf('\Model\TranslatableTranslation', $enTranslation);
        $this->assertSame($enTranslation, $entity->translation('en'));
        $this->assertSame('en', $enTranslation->getLocale());

        $esTranslation = $entity->translation('es');
        $this->assertInstanceOf('\Model\TranslatableTranslation', $esTranslation);
        $this->assertSame($esTranslation, $entity->translation('es'));
        $this->assertSame('es', $esTranslation->getLocale());

        $entity->setDate(new \DateTime());
        $enTranslation->setTitle('english title');
        $enTranslation->setBody('english body');
        $esTranslation->setTitle('spanish title');
        $esTranslation->setBody('spanish body');

        $entity->save();

        $this->assertEquals(array(
            'id'        => $entity->getId(),
            'date'      => $entity->getDate(),
            'is_active' => true,
            'translations' => array(
                array(
                    'id'     => $enTranslation->getId(),
                    'locale' => 'en',
                    'title'  => 'english title',
                    'body'   => 'english body',
                ),
                array(
                    'id'     => $esTranslation->getId(),
                    'locale' => 'es',
                    'title'  => 'spanish title',
                    'body'   => 'spanish body',
                ),
            ),
        ), $this->entityManager->createQuery('SELECT t, tr FROM Model\Translatable t LEFT JOIN t.translations tr')->getSingleResult(Query::HYDRATE_ARRAY));
    }
}
