<?php

/**
 * This file is part of SilverWare.
 *
 * PHP version >=5.6.0
 *
 * For full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 *
 * @package SilverWare\Google\Maps\Components
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2018 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-google-maps
 */

namespace SilverWare\Google\Maps\Components;

use SilverStripe\Core\Convert;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\ORM\ArrayList;
use SilverWare\Components\BaseComponent;
use SilverWare\Forms\FieldSection;
use SilverWare\Forms\ToggleGroup;
use SilverWare\Google\API\GoogleAPI;
use SilverWare\Google\Maps\Model\Marker;

/**
 * An extension of the base component for a Google map component.
 *
 * @package SilverWare\Google\Maps\Components
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2018 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-google-maps
 */
class GoogleMapComponent extends BaseComponent
{
    /**
     * Define type constants.
     */
    const TYPE_ROADMAP   = 'roadmap';
    const TYPE_SATELLITE = 'satellite';
    const TYPE_HYBRID    = 'hybrid';
    const TYPE_TERRAIN   = 'terrain';
    
    /**
     * Define height constants.
     */
    const HEIGHT_AUTO   = 'auto';
    const HEIGHT_MANUAL = 'manual';
    
    /**
     * Human-readable singular name.
     *
     * @var string
     * @config
     */
    private static $singular_name = 'Google Map Component';
    
    /**
     * Human-readable plural name.
     *
     * @var string
     * @config
     */
    private static $plural_name = 'Google Map Components';
    
    /**
     * Description of this object.
     *
     * @var string
     * @config
     */
    private static $description = 'A component which shows a Google map';
    
    /**
     * Icon file for this object.
     *
     * @var string
     * @config
     */
    private static $icon = 'silverware/google-maps: admin/client/dist/images/icons/GoogleMapComponent.png';
    
    /**
     * Defines the table name to use for this object.
     *
     * @var string
     * @config
     */
    private static $table_name = 'SilverWare_GoogleMapComponent';
    
    /**
     * Defines an ancestor class to hide from the admin interface.
     *
     * @var string
     * @config
     */
    private static $hide_ancestor = BaseComponent::class;
    
    /**
     * Defines the allowed children for this object.
     *
     * @var array|string
     * @config
     */
    private static $allowed_children = 'none';
    
    /**
     * Maps field names to field types for this object.
     *
     * @var array
     * @config
     */
    private static $db = [
        'Zoom' => 'Int',
        'MapType' => 'Varchar(16)',
        'Latitude' => 'Decimal(9,6)',
        'Longitude' => 'Decimal(9,6)',
        'MarkerTitle' => 'Varchar(128)',
        'MarkerLabel' => 'Varchar(8)',
        'MarkerContent' => 'HTMLText',
        'HideMarkerContent' => 'Boolean',
        'HeightMode' => 'Varchar(16)',
        'HeightManual' => 'AbsoluteInt',
        'ShowMarker' => 'Boolean'
    ];
    
    /**
     * Defines the has-many associations for this object.
     *
     * @var array
     * @config
     */
    private static $has_many = [
        'Markers' => Marker::class
    ];
    
    /**
     * Defines the default values for the fields of this object.
     *
     * @var array
     * @config
     */
    private static $defaults = [
        'Zoom' => 8,
        'MapType' => self::TYPE_ROADMAP,
        'HeightMode' => self::HEIGHT_AUTO,
        'ShowMarker' => 0
    ];
    
    /**
     * Maps field and method names to the class names of casting objects.
     *
     * @var array
     * @config
     */
    private static $casting = [
        'MapAttributesHTML' => 'HTMLFragment'
    ];
    
    /**
     * Defines the default pixel height for manual height mode.
     *
     * @var integer
     * @config
     */
    private static $default_pixel_height = 400;
    
    /**
     * Answers a list of field objects for the CMS interface.
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        // Obtain Field Objects (from parent):
        
        $fields = parent::getCMSFields();
        
        // Add Status Message (if exists):
        
        $fields->addStatusMessage($this->getSiteConfig()->getGoogleStatusMessage());
        
        // Create Main Fields:
        
        $fields->addFieldsToTab(
            'Root.Main',
            [
                FieldSection::create(
                    'Coordinates',
                    $this->fieldLabel('Coordinates'),
                    [
                        TextField::create(
                            'Latitude',
                            $this->fieldLabel('Latitude')
                        ),
                        TextField::create(
                            'Longitude',
                            $this->fieldLabel('Longitude')
                        )
                    ]
                ),
                ToggleGroup::create(
                    'ShowMarker',
                    $this->fieldLabel('ShowMarker'),
                    [
                        TextField::create(
                            'MarkerTitle',
                            $this->fieldLabel('MarkerTitle')
                        )->setRightTitle(
                            _t(
                                __CLASS__ . '.MARKERTITLERIGHTTITLE',
                                'If empty, the marker will use the component title.'
                            )
                        ),
                        TextField::create(
                            'MarkerLabel',
                            $this->fieldLabel('MarkerLabel')
                        ),
                        HTMLEditorField::create(
                            'MarkerContent',
                            $this->fieldLabel('MarkerContent')
                        )->setRows(12),
                        CheckboxField::create(
                            'HideMarkerContent',
                            $this->fieldLabel('HideMarkerContent')
                        )
                    ]
                )
            ]
        );
        
        // Insert Markers Tab:
        
        $fields->insertAfter(
            Tab::create(
                'Markers',
                $this->fieldLabel('Markers')
            ),
            'Main'
        );
        
        // Create Markers Field:
        
        $fields->addFieldsToTab(
            'Root.Markers',
            [
                GridField::create(
                    'Markers',
                    $this->fieldLabel('Markers'),
                    $this->Markers(),
                    $config = GridFieldConfig_RecordEditor::create()
                )
            ]
        );
        
        // Create Style Fields:
        
        $fields->addFieldToTab(
            'Root.Style',
            FieldSection::create(
                'MapStyle',
                $this->fieldLabel('MapStyle'),
                [
                    DropdownField::create(
                        'HeightMode',
                        $this->fieldLabel('HeightMode'),
                        $this->getHeightModeOptions()
                    ),
                    TextField::create(
                        'HeightManual',
                        $this->fieldLabel('HeightManual')
                    )
                ]
            )
        );
        
        // Create Options Fields:
        
        $fields->addFieldToTab(
            'Root.Options',
            FieldSection::create(
                'MapOptions',
                $this->fieldLabel('MapOptions'),
                [
                    DropdownField::create(
                        'MapType',
                        $this->fieldLabel('MapType'),
                        $this->getMapTypeOptions()
                    ),
                    DropdownField::create(
                        'Zoom',
                        $this->fieldLabel('Zoom'),
                        $this->getZoomOptions()
                    )
                ]
            )
        );
        
        // Answer Field Objects:
        
        return $fields;
    }
    
    /**
     * Answers a validator for the CMS interface.
     *
     * @return RequiredFields
     */
    public function getCMSValidator()
    {
        return RequiredFields::create([
            'Latitude',
            'Longitude'
        ]);
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
        
        $labels['Zoom'] = _t(__CLASS__ . '.ZOOM', 'Zoom');
        $labels['Markers'] = _t(__CLASS__ . '.MARKERS', 'Markers');
        $labels['MapType'] = _t(__CLASS__ . '.MAPTYPE', 'Map type');
        $labels['Latitude'] = _t(__CLASS__ . '.LATITUDE', 'Latitude');
        $labels['Longitude'] = _t(__CLASS__ . '.LONGITUDE', 'Longitude');
        $labels['MarkerTitle'] = _t(__CLASS__ . '.MARKERTITLE', 'Marker title');
        $labels['MarkerLabel'] = _t(__CLASS__ . '.MARKERLABEL', 'Marker label');
        $labels['MarkerContent'] = _t(__CLASS__ . '.MARKERCONTENT', 'Marker content');
        $labels['ShowMarker'] = _t(__CLASS__ . '.SHOWMARKERFORLOCATION', 'Show marker for location');
        $labels['HideMarkerContent'] = _t(__CLASS__ . '.HIDEMARKERCONTENT', 'Hide marker content');
        $labels['HeightMode'] = _t(__CLASS__ . '.HEIGHTMODE', 'Height mode');
        $labels['HeightManual'] = _t(__CLASS__ . '.HEIGHTMANUALINPIXELS', 'Height manual (in pixels)');
        $labels['Coordinates'] = _t(__CLASS__ . '.COORDINATES', 'Coordinates');
        $labels['MapStyle'] = $labels['MapOptions'] = _t(__CLASS__ . '.MAP', 'Map');
        
        // Define Relation Labels:
        
        if ($includerelations) {
            $labels['Markers'] = _t(__CLASS__ . '.has_many_Markers', 'Markers');
        }
        
        // Answer Field Labels:
        
        return $labels;
    }
    
    /**
     * Answers an array of class names for the HTML template.
     *
     * @return array
     */
    public function getClassNames()
    {
        $classes = parent::getClassNames();
        
        if ($this->HeightMode == self::HEIGHT_AUTO) {
            $classes[] = 'height-auto';
        } else {
            $classes[] = 'height-manual';
        }
        
        return $classes;
    }
    
    /**
     * Answers an array of wrapper class names for the HTML template.
     *
     * @return array
     */
    public function getWrapperClassNames()
    {
        $classes = ['google-map-wrapper'];
        
        $this->extend('updateWrapperClassNames', $classes);
        
        return $classes;
    }
    
    /**
     * Answers an array of map class names for the HTML template.
     *
     * @return array
     */
    public function getMapClassNames()
    {
        $classes = ['google-map'];
        
        $this->extend('updateMapClassNames', $classes);
        
        return $classes;
    }
    
    /**
     * Answers an array of attributes for the map element.
     *
     * @return array
     */
    public function getMapAttributes()
    {
        // Create Attributes Array:
        
        $attributes = [
            'id' => $this->MapID,
            'class' => $this->MapClass
        ];
        
        // Extend Attributes Array:
        
        $this->extend('updateMapAttributes', $attributes);
        
        // Merge Data Attributes:
        
        $attributes = array_merge($attributes, $this->getMapDataAttributes());
        
        // Answer Attributes Array:
        
        return $attributes;
    }
    
    /**
     * Answers an array of data attributes for the map element.
     *
     * @return array
     */
    public function getMapDataAttributes()
    {
        $attributes = [
            'data-zoom' => $this->Zoom,
            'data-map-type' => $this->MapType,
            'data-latitude' => $this->Latitude,
            'data-longitude' => $this->Longitude,
            'data-markers' => $this->MarkersJSON
        ];
        
        $this->extend('updateMapDataAttributes', $attributes);
        
        return $attributes;
    }
    
    /**
     * Answers a list of all markers for the map.
     *
     * @return ArrayList
     */
    public function getAllMarkers()
    {
        $markers = ArrayList::create();
        
        $markers->merge($this->Markers()->filter('Disabled', 0));
        
        if ($this->ShowMarker) {
            $markers->push($this->getLocationMarker());
        }
        
        return $markers;
    }
    
    /**
     * Answers a map marker for the map location.
     *
     * @return Marker
     */
    public function getLocationMarker()
    {
        $marker = Marker::create([
            'Title' => $this->LocationMarkerTitle,
            'Label' => $this->LocationMarkerLabel,
            'Content' => $this->LocationMarkerContent,
            'Latitude' => $this->Latitude,
            'Longitude' => $this->Longitude,
            'HideContent' => $this->HideMarkerContent
        ]);
        
        return $marker;
    }
    
    /**
     * Answers the title for the location marker.
     *
     * @return string
     */
    public function getLocationMarkerTitle()
    {
        return $this->MarkerTitle ? $this->MarkerTitle : $this->Title;
    }
    
    /**
     * Answers the label for the location marker.
     *
     * @return string
     */
    public function getLocationMarkerLabel()
    {
        return $this->MarkerLabel ?: $this->MarkerLabel;
    }
    
    /**
     * Answers the content for the location marker.
     *
     * @return string
     */
    public function getLocationMarkerContent()
    {
        return $this->MarkerContent ?: $this->MarkerContent;
    }
    
    /**
     * Answers a JSON-encoded string containing the markers for the map.
     *
     * @return string
     */
    public function getMarkersJSON()
    {
        $markers = [];
        
        foreach ($this->getAllMarkers() as $marker) {
            $markers[] = $marker->toMap();
        }
        
        return Convert::array2json($markers);
    }
    
    /**
     * Answers a string of attributes for the map element.
     *
     * @return string
     */
    public function getMapAttributesHTML()
    {
        return $this->getAttributesHTML($this->getMapAttributes());
    }
    
    /**
     * Answers the map element ID for the template.
     *
     * @return string
     */
    public function getMapID()
    {
        return sprintf('%s_Map', $this->getHTMLID());
    }
    
    /**
     * Answers the manual height CSS.
     *
     * @return string
     */
    public function getHeightManualCSS()
    {
        $height = $this->HeightManual ? $this->HeightManual : $this->config()->default_pixel_height;
        
        return sprintf('%dpx', $height);
    }
    
    /**
     * Answers true if the height mode is set to manual.
     *
     * @return boolean
     */
    public function isManualHeightMode()
    {
        return ($this->HeightMode == self::HEIGHT_MANUAL);
    }
    
    /**
     * Answers true if the object is disabled within the template.
     *
     * @return boolean
     */
    public function isDisabled()
    {
        if (!GoogleAPI::singleton()->hasAPIKey()) {
            return true;
        }
        
        return parent::isDisabled();
    }
    
    /**
     * Answers an array of options for the height mode field.
     *
     * @return array
     */
    public function getHeightModeOptions()
    {
        return [
            self::HEIGHT_AUTO => _t(__CLASS__ . '.AUTO', 'Auto'),
            self::HEIGHT_MANUAL => _t(__CLASS__ . '.MANUAL', 'Manual')
        ];
    }
    
    /**
     * Answers an array of options for the map type field.
     *
     * @return array
     */
    public function getMapTypeOptions()
    {
        return [
            self::TYPE_ROADMAP => _t(__CLASS__ . '.ROADMAP', 'Roadmap'),
            self::TYPE_SATELLITE => _t(__CLASS__ . '.SATELLITE', 'Satellite'),
            self::TYPE_HYBRID => _t(__CLASS__ . '.HYBRID', 'Hybrid'),
            self::TYPE_TERRAIN => _t(__CLASS__ . '.TERRAIN', 'Terrain')
        ];
    }
    
    /**
     * Answers an array of options for the zoom field.
     *
     * @return array
     */
    public function getZoomOptions()
    {
        return ArrayLib::valuekey(range(1, 20));
    }
}
