<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\Reader\MapFactory;
use Migration\Reader\Map;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;

/**
 * Class InitialData
 */
class InitialData
{
    /**
     * [attribute_id => attributeData]
     * @var array
     */
    protected $attributes;

    /**
     * @var array;
     */
    protected $attributeSets;

    /**
     * @var array;
     */
    protected $attributeGroups;

    /**
     * @var array;
     */
    protected $entityTypes;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param MapFactory $mapFactory
     * @param Source $source
     * @param Destination $destination
     * @param Helper $helper
     */
    public function __construct(MapFactory $mapFactory, Source $source, Destination $destination, Helper $helper)
    {
        $this->map = $mapFactory->create('eav_map_file');
        $this->source = $source;
        $this->destination = $destination;
        $this->helper = $helper;
    }

    /**
     * Load EAV data before migration
     *
     * @return void
     */
    public function init()
    {
        $this->initAttributeSets();
        $this->initAttributeGroups();
        $this->initAttributes();
        $this->initEntityTypes();
    }

    /**
     * Load all entity types
     *
     * @return void
     */
    protected function initEntityTypes()
    {
        if ($this->entityTypes === null) {
            $sourceRecords = $this->helper->getSourceRecords('eav_entity_type');
            foreach ($sourceRecords as $record) {
                $this->entityTypes['source']['entity_type_id'][$record['entity_type_id']] = $record;
                $this->entityTypes['source']['entity_type_code'][$record['entity_type_code']] = $record;
            }
            $destinationRecords = $this->helper->getDestinationRecords('eav_entity_type');
            foreach ($destinationRecords as $record) {
                $this->entityTypes['dest']['entity_type_id'][$record['entity_type_id']] = $record;
                $this->entityTypes['dest']['entity_type_code'][$record['entity_type_code']] = $record;
            }
        }
    }

    /**
     * Load all attributes from source and destination
     *
     * @return void
     */
    protected function initAttributes()
    {
        if ($this->attributes === null) {
            $sourceDocument = 'eav_attribute';

            foreach ($this->helper->getSourceRecords($sourceDocument, ['attribute_id']) as $id => $record) {
                $this->attributes['source'][$id] = $record;
            }

            $destinationRecords = $this->helper->getDestinationRecords(
                $sourceDocument,
                ['entity_type_id', 'attribute_code']
            );
            foreach ($destinationRecords as $id => $record) {
                $this->attributes['dest'][$id] = $record;
            }
        }
    }

    /**
     * Load attribute sets data before migration
     * @return void
     */
    protected function initAttributeSets()
    {
        $this->attributeSets['dest'] = $this->helper->getDestinationRecords(
            'eav_attribute_set',
            ['attribute_set_id']
        );
    }

    /**
     * Load attribute group data before migration
     *
     * @return void
     */
    protected function initAttributeGroups()
    {
        $this->attributeGroups['dest'] = $this->helper->getDestinationRecords(
            'eav_attribute_group',
            ['attribute_set_id', 'attribute_group_name']
        );
    }

    /**
     * @codeCoverageIgnore
     * @param string $type
     * @param string $keyField
     * @return array
     */
    public function getEntityTypes($type, $keyField = 'entity_type_code')
    {
        return $keyField ? $this->entityTypes[$type][$keyField] : $this->entityTypes[$type];
    }

    /**
     * @codeCoverageIgnoreStart
     * @param string $type
     * @return mixed
     */
    public function getAttributes($type)
    {
        return $this->attributes[$type];
    }

    /**
     * @param string $type
     * @return array
     */
    public function getAttributeSets($type)
    {
        return $this->attributeSets[$type];
    }

    /**
     * @param string $type
     * @return array
     */
    public function getAttributeGroups($type)
    {
        return $this->attributeGroups[$type];
    }
}
