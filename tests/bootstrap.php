<?php

$config = require(__DIR__.'/config.php');

// autoloader
require($config['mondongo_lib_dir'].'/vendor/symfony/src/Symfony/Component/HttpFoundation/UniversalClassLoader.php');

use Symfony\Component\HttpFoundation\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Doctrator\\Tests' => __DIR__,
    'Doctrator'        => __DIR__.'/../lib',
    'Doctrine\\Common' => $config['doctrine_common_lib_dir'],
    'Doctrine\\DBAL'   => $config['doctrine_dbal_lib_dir'],
    'Doctrine\\ORM'    => $config['doctrine_orm_lib_dir'],
    'Model'            => __DIR__,
    'Mondongo'         => $config['mondongo_lib_dir'],
));
$loader->register();

// mondator
use \Mondongo\Mondator\Mondator;
use \Mondongo\Mondator\Output\Output;

$configClasses = array(
    'Model\\Entity\\Article' => array(
        'columns' => array(
            'id'        => array('id' => 'auto', 'type' => 'integer'),
            'title'     => array('type' => 'string', 'length' => 100),
            'slug'      => array('name' => 'title_slug', 'type' => 'string', 'length' => 110, 'unique' => true),
            'content'   => 'text',
            'source'    => array('type' => 'string', 'length' => 255, 'nullable' => true),
            'is_active' => array('type' => 'boolean', 'default' => true),
            'score'     => array('type' => 'decimal', 'precision' => 2, 'scale' => 4),
            'date'      => 'date',
        ),
        'relations' => array(
            'category' => array('type' => 'ManyToOne', 'targetEntity' => 'Model\\Entity\\Category'),
        ),
        'indexes' => array(
            'my_slug_index'  => array('columns' => array('title_slug'), 'unique' => true),
            'is_active_date' => array('columns' => array('is_active', 'date')),
        ),
    ),
    'Model\\Entity\\Category' => array(
        'columns' => array(
            'id'   => array('id' => 'auto', 'type' => 'integer'),
            'name' => array('type' => 'string', 'length' => 100)
        ),
        'relations' => array(
            'articles' => array('type' => 'OneToMany', 'targetEntity' => 'Model\\Entity\\Article', 'mappedBy' => 'category'),
        ),
    ),
    'Model\\Entity\\Simple' => array(
        'columns' => array(
            'id'      => array('id' => 'auto', 'type' => 'integer'),
            'column'  => array('type' => 'string', 'length' => 255),
            'column2' => array('type' => 'string', 'length' => 255, 'nullable' => true),
        ),
    ),

    /*
     * Specific
     */
    'Model\\Entity\\IdentifierStrategyIdentity' => array(
        'columns' => array(
            'id' => array('id' => array('strategy' => 'identity'), 'type' => 'integer'),
        ),
    ),
    'Model\\Entity\\IdentifierStrategyNone' => array(
        'columns' => array(
            'id' => array('id' => array('strategy' => 'none'), 'type' => 'integer'),
        ),
    ),
    /*
     * Behaviors
     */
    'Model\\Entity\\Hashable' => array(
        'columns' => array(
            'id'    => array('id' => 'auto', 'type' => 'integer'),
            'title' => 'string',
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\\Behavior\\Hashable'),
        ),
    ),
    'Model\\Entity\\Ipable' => array(
        'columns' => array(
            'id'    => array('id' => 'auto', 'type' => 'integer'),
            'title' => 'string',
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\\Behavior\\Ipable'),
        ),
    ),
    'Model\\Entity\\Sluggable' => array(
        'columns' => array(
            'id'    => array('id' => 'auto', 'type' => 'integer'),
            'title' => 'string',
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\\Behavior\\Sluggable', 'options' => array('from_column' => 'title')),
        ),
    ),
    'Model\\Entity\\SluggableUpdate' => array(
        'columns' => array(
            'id'    => array('id' => 'auto', 'type' => 'integer'),
            'title' => 'string',
            'body'  => array('type' => 'text', 'nullable' => true),
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\\Behavior\\Sluggable', 'options' => array('from_column' => 'title', 'update' => true)),
        ),
    ),
    'Model\\Entity\\Sortable' => array(
        'columns' => array(
            'id'    => array('id' => 'auto', 'type' => 'integer'),
            'title' => 'string',
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\\Behavior\\Sortable'),
        ),
    ),
    'Model\\Entity\\Taggable' => array(
        'columns' => array(
            'id'    => array('id' => 'auto', 'type' => 'integer'),
            'title' => 'string',
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\\Behavior\\Taggable'),
        ),
    ),
    'Model\\Entity\\Timestampable' => array(
        'columns' => array(
            'id'    => array('id' => 'auto', 'type' => 'integer'),
            'title' => 'string',
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\\Behavior\\Timestampable'),
        ),
    ),
    'Model\\Entity\\Translatable' => array(
        'columns' => array(
            'id'        => array('id' => 'auto', 'type' => 'integer'),
            'title'     => array('type' => 'string', 'length' => 255),
            'body'      => array('type' => 'text'),
            'date'      => array('type' => 'datetime'),
            'is_active' => array('type' => 'boolean', 'default' => true),
        ),
        'behaviors' => array(
            array('class' => 'Doctrator\\Behavior\\Translatable', 'options' => array('columns' => array('title', 'body'))),
        ),
    ),
);

$mondator = new Mondator();
$mondator->setConfigClasses($configClasses);
$mondator->setExtensions(array(
    new Doctrator\Extension\Core(array(
        'default_entity_output'     => __DIR__.'/Model/Entity',
        'default_repository_output' => __DIR__.'/Model/Repository',
    )),
    new Doctrator\Extension\EntityDataCamelCaseMap(),
    new Doctrator\Extension\EntityArrayAccess(),
    new Doctrator\Extension\EntityPropertyOverloading(),
));
$mondator->process();
