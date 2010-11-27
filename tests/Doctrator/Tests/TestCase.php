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

namespace Doctrator\Tests;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrator\Driver\DoctratorDriver;
use Doctrator\EntityManagerContainer;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $entityManager;

    protected $eventManager;

    protected $metadataFactory;

    public function setUp()
    {
        if (!$this->entityManager) {
            EntityManagerContainer::clearEntityManager();

            $configuration = new Configuration();
            $configuration->setMetadataDriverImpl(new DoctratorDriver(__DIR__.'/../../Model/Entity'));
            $configuration->setProxyDir(__DIR__.'/../../Proxy');
            $configuration->setProxyNamespace('Proxy');
            $configuration->setAutoGenerateProxyClasses(true);

            $this->entityManager = EntityManager::create(array(
                'driver' => 'pdo_sqlite',
                'path'   => ':memory:',
            ), $configuration);

            // event manager
            $this->eventManager = $this->entityManager->getEventManager();

            // metadata factory
            $this->metadataFactory = $this->entityManager->getMetadataFactory();

            // create schema
            $schemaTool = new SchemaTool($this->entityManager);
            $schemaTool->createSchema($this->metadataFactory->getAllMetadata());

            // entity manager container
            EntityManagerContainer::setEntityManager($this->entityManager);
        }
    }
}
