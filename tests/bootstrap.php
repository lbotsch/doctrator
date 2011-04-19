<?php

$config = require(__DIR__.'/config.php');

// autoloader
require($config['symfony_src_dir'].'/Symfony/Component/ClassLoader/UniversalClassLoader.php');

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces($namespaces = array(
    'Doctrator\Tests'   => __DIR__,
    'Doctrator'         => __DIR__.'/../src',
    'Doctrine\Common'   => $config['doctrine_common_lib_dir'],
    'Doctrine\DBAL'     => $config['doctrine_dbal_lib_dir'],
    'Doctrine\ORM'      => $config['doctrine_orm_lib_dir'],
    'Model'             => __DIR__,
    'Mandango\Mondator' => $config['mondator_src_dir'],
));
$loader->registerPrefixes(array(
    'Twig_' => $config['twig_lib_dir'],
));
$loader->register();

// mondator
use \Mandango\Mondator\Mondator;
use \Mandango\Mondator\Output\Output;

$configClasses = array(
    'Model\Article' => array(
        'columns' => array(
            'id'       => array('id' => 'auto', 'type' => 'integer'),
            'title'    => array('type' => 'string', 'length' => 100),
            'slug'     => array('name' => 'title_slug', 'type' => 'string', 'length' => 110, 'unique' => true),
            'content'  => 'text',
            'source'   => array('type' => 'string', 'length' => 255, 'nullable' => true),
            'isActive' => array('type' => 'boolean', 'default' => true),
            'score'    => array('type' => 'decimal', 'precision' => 2, 'scale' => 4),
            'date'     => 'date',
        ),
        'manyToOne' => array(
            'category' => array('class' => 'Model\Category', 'inversed' => 'articles'),
        ),
        'indexes' => array(
            'mySlugIndex'  => array('columns' => array('title_slug'), 'unique' => true),
            'isActiveDate' => array('columns' => array('isActive', 'date')),
        ),
    ),
    'Model\Category' => array(
        'columns' => array(
            'id'   => array('id' => 'auto', 'type' => 'integer'),
            'name' => array('type' => 'string', 'length' => 100)
        ),
        'oneToMany' => array(
            'articles' => array('class' => 'Model\Article', 'mapped' => 'category'),
        ),
    ),
    'Model\Simple' => array(
        'columns' => array(
            'id'      => array('id' => 'auto', 'type' => 'integer'),
            'column'  => array('type' => 'string', 'length' => 255),
            'column2' => array('type' => 'string', 'length' => 255, 'nullable' => true),
        ),
    ),
    /*
     * One
     */
    'Model\Student' => array(
        'columns' => array(
            'id'   => array('id' => 'auto', 'type' => 'integer'),
            'name' => array('type' => 'string', 'length' => 255),
        ),
        'manyToOne' => array(
            'school' => array('class' => 'Model\School', 'inversed' => 'students'),
        ),
    ),
    'Model\School' => array(
        'columns' => array(
            'id'   => array('id' => 'auto', 'type' => 'integer'),
            'name' => array('type' => 'string', 'length' => 255),
        ),
        'manyToOne' => array(
            'students' => array('class' => 'Model\Student', 'mapped' => 'school'),
        ),
    ),
    /*
     * Many
     */
    'Model\Person' => array(
        'columns' => array(
            'id'   => array('id' => 'auto', 'type' => 'integer'),
            'name' => array('type' => 'string', 'length' => 255),
        ),
        'manyToMany' => array(
            'likings' => array('class' => 'Model\Liking', 'inversed' => 'persons'),
        ),
    ),
    'Model\Liking' => array(
        'columns' => array(
            'id'   => array('id' => 'auto', 'type' => 'integer'),
            'name' => array('type' => 'string', 'length' => 255),
        ),
        'manyToMany' => array(
            'persons' => array('class' => 'Model\Person', 'mapped' => 'likings'),
        ),
    ),
    /*
     * Specific
     */
    'Model\IdentifierStrategyIdentity' => array(
        'columns' => array(
            'id' => array('id' => array('strategy' => 'identity'), 'type' => 'integer'),
        ),
    ),
    'Model\IdentifierStrategyNone' => array(
        'columns' => array(
            'id' => array('id' => array('strategy' => 'none'), 'type' => 'integer'),
        ),
    ),
    /*
     * Behaviors
     */
    'Model\Hashable' => array(
        'columns' => array(
            'id'    => array('id' => 'auto', 'type' => 'integer'),
            'title' => 'string',
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\Behavior\Hashable'),
        ),
    ),
    'Model\Ipable' => array(
        'columns' => array(
            'id'    => array('id' => 'auto', 'type' => 'integer'),
            'title' => 'string',
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\Behavior\Ipable'),
        ),
    ),
    'Model\Sluggable' => array(
        'columns' => array(
            'id'    => array('id' => 'auto', 'type' => 'integer'),
            'title' => 'string',
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\Behavior\Sluggable', 'options' => array('from_column' => 'title')),
        ),
    ),
    'Model\SluggableUpdate' => array(
        'columns' => array(
            'id'    => array('id' => 'auto', 'type' => 'integer'),
            'title' => 'string',
            'body'  => array('type' => 'text', 'nullable' => true),
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\Behavior\Sluggable', 'options' => array('from_column' => 'title', 'update' => true)),
        ),
    ),
    'Model\Sortable' => array(
        'columns' => array(
            'id'    => array('id' => 'auto', 'type' => 'integer'),
            'title' => 'string',
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\Behavior\Sortable'),
        ),
    ),
    'Model\Taggable' => array(
        'columns' => array(
            'id'    => array('id' => 'auto', 'type' => 'integer'),
            'title' => 'string',
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\Behavior\Taggable'),
        ),
    ),
    'Model\Timestampable' => array(
        'columns' => array(
            'id'    => array('id' => 'auto', 'type' => 'integer'),
            'title' => 'string',
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\Behavior\Timestampable'),
        ),
    ),
    'Model\Translatable' => array(
        'columns' => array(
            'id'       => array('id' => 'auto', 'type' => 'integer'),
            'title'    => array('type' => 'string', 'length' => 255),
            'body'     => array('type' => 'text'),
            'date'     => array('type' => 'datetime'),
            'isActive' => array('type' => 'boolean', 'default' => true),
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\Behavior\Translatable', 'options' => array('columns' => array('title', 'body'))),
        ),
    ),
);

$mondator = new Mondator();
$mondator->setConfigClasses($configClasses);
$mondator->setExtensions(array(
    new Doctrator\Extension\Core\DoctratorCoreExtension(array(
        'default_output' => __DIR__.'/Model',
    )),
    new Doctrator\Extension\ActiveRecord\DoctratorActiveRecordExtension(),
    new Doctrator\Extension\ArrayAccess\DoctratorArrayAccessExtension(),
    new Doctrator\Extension\PropertyOverloading\DoctratorPropertyOverloadingExtension(),
    new Doctrator\Extension\Behaviors\DoctratorBehaviorsExtension(),
));
$mondator->process();

/*
 * Not ActiveRecord
 */
$mondator = new Mondator();
$mondator->setConfigClasses($configClasses);
$mondator->setExtensions(array(
    new Doctrator\Extension\Core\DoctratorCoreExtension(array(
        'default_output' => __DIR__.'/ModelNotAR'
    )),
));
$mondator->process();
