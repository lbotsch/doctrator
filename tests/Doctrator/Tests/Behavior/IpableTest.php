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

use Model\Entity\Ipable;

class IpableTest extends \Doctrator\Tests\TestCase
{
    public function testIpable()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.0.1';

        $entity = new Ipable();
        $entity->setTitle('inserting');
        $entity->save();

        $this->assertSame('192.168.0.1', $entity->getCreatedFrom());
        $this->assertNull($entity->getUpdatedFrom());

        $_SERVER['REMOTE_ADDR'] = '192.168.0.100';

        $entity->setTitle('updating');
        $entity->save();

        $this->assertSame('192.168.0.100', $entity->getUpdatedFrom());
        $this->assertSame('192.168.0.1', $entity->getCreatedFrom());
    }
}
