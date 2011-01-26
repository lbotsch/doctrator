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

namespace Doctrator;

use Doctrine\ORM\EntityManager;

/**
 * A container for the entity manager.
 *
 * @package Doctrator
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class EntityManagerContainer
{
    static protected $entityManager;

    /**
     * Set the entity manager.
     *
     * @param \Doctrine\ORM\EntityManager $entityManager The entity manager.
     *
     * @return void
     */
    static public function setEntityManager(EntityManager $entityManager)
    {
        static::$entityManager = $entityManager;
    }

    /**
     * Returns the entity manager.
     *
     * @return \Doctrine\ORM\EntityManager The entity manager.
     */
    static public function getEntityManager()
    {
        return static::$entityManager;
    }

    /**
     * Clear the entity manager.
     *
     * @return void
     */
    static public function clearEntityManager()
    {
        static::$entityManager = null;
    }
}
