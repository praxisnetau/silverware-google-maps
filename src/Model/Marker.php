<?php

/**
 * This file is part of SilverWare.
 *
 * PHP version >=5.6.0
 *
 * For full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 *
 * @package SilverWare\Google\Maps\Model
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2018 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-google-maps
 */

namespace SilverWare\Google\Maps\Model;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverWare\Google\Maps\Components\GoogleMapComponent;

/**
 * An extension of the data object class for a map marker.
 *
 * @package SilverWare\Google\Maps\Model
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2018 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-google-maps
 */
class Marker extends DataObject
{
    /**
     * Human-readable singular name.
     *
     * @var string
     * @config
     */
    private static $singular_name = 'Marker';
    
    /**
     * Human-readable plural name.
     *
     * @var string
     * @config
     */
    private static $plural_name = 'Markers';
    
    /**
     * Defines the table name to use for this object.
     *
     * @var string
     * @config
     */
    private static $table_name = 'SilverWare_GoogleMap_Marker';
    
    /**
     * Maps field names to field types for this object.
     *
     * @var array
     * @config
     */
    private static $db = [
        'Title' => 'Varchar(128)',
        'Label' => 'Varchar(8)',
        'Content' => 'HTMLText',
        'Latitude' => 'Decimal(9,6)',
        'Longitude' => 'Decimal(9,6)',
        'HideContent' => 'Boolean',
        'Disabled' => 'Boolean'
    ];
    
    /**
     * Defines the has-one associations for this object.
     *
     * @var array
     * @config
     */
    private static $has_one = [
        'Map' => GoogleMapComponent::class
    ];
    
    /**
     * Defines the default values for the fields of this object.
     *
     * @var array
     * @config
     */
    private static $defaults = [
        'Disabled' => 0,
        'HideContent' => 0
    ];
    
    /**
     * Defines the summary fields of this object.
     *
     * @var array
     * @config
     */
    private static $summary_fields = [
        'Title',
        'Label',
        'Latitude',
        'Longitude',
        'Disabled.Nice'
    ];
    
    /**
     * Answers a list of field objects for the CMS interface.
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        // Create Field List and Tab Set:
        
        $fields = FieldList::create(TabSet::create('Root'));
        
        // Create Main Fields:
        
        $fields->addFieldsToTab(
            'Root.Main',
            [
                TextField::create(
                    'Title',
                    $this->fieldLabel('Title')
                ),
                TextField::create(
                    'Label',
                    $this->fieldLabel('Label')
                ),
                TextField::create(
                    'Latitude',
                    $this->fieldLabel('Latitude')
                ),
                TextField::create(
                    'Longitude',
                    $this->fieldLabel('Longitude')
                ),
                HTMLEditorField::create(
                    'Content',
                    $this->fieldLabel('Content')
                ),
                CheckboxField::create(
                    'HideContent',
                    $this->fieldLabel('HideContent')
                ),
                CheckboxField::create(
                    'Disabled',
                    $this->fieldLabel('Disabled')
                )
            ]
        );
        
        // Extend Field Objects:
        
        $this->extend('updateCMSFields', $fields);
        
        // Answer Field Objects:
        
        return $fields;
    }
    
    /**
     * Answers the labels for the fields of the receiver.
     *
     * @param boolean $includerelations Include labels for relations.
     *
     * @return array
     */
    public function fieldLabels($includerelations = true)
    {
        // Obtain Field Labels (from parent):
        
        $labels = parent::fieldLabels($includerelations);
        
        // Define Field Labels:
        
        $labels['Label'] = _t(__CLASS__ . '.LABEL', 'Label');
        $labels['Latitude'] = _t(__CLASS__ . '.LATITUDE', 'Latitude');
        $labels['Longitude'] = _t(__CLASS__ . '.LONGITUDE', 'Longitude');
        $labels['HideContent'] = _t(__CLASS__ . '.HIDECONTENT', 'Hide content');
        
        $labels['Disabled'] = $labels['Disabled.Nice'] =_t(__CLASS__ . '.DISABLED', 'Disabled');
        
        // Define Relation Labels:
        
        if ($includerelations) {
            $labels['Map'] = _t(__CLASS__ . '.has_one_Map', 'Map');
        }
        
        // Answer Field Labels:
        
        return $labels;
    }
    
    /**
     * Converts the receiver to an array representation.
     *
     * @return array
     */
    public function toMap()
    {
        return [
            'title' => $this->Title,
            'label' => $this->Label,
            'content' => $this->Content,
            'latitude' => $this->Latitude,
            'longitude' => $this->Longitude,
            'showInfoWindow' => $this->ShowInfoWindow
        ];
    }
    
    /**
     * Answers true if the info window is to be shown.
     *
     * @return boolean
     */
    public function getShowInfoWindow()
    {
        return ($this->Content && !$this->HideContent);
    }
}
