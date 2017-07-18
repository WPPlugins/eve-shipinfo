<?php

class EVEShipInfo_Collection_Ship_Attribute
{
    const ERROR_UNKNOWN_ATTRIBUTE = 17001;
    
    const ERROR_SHIP_DOES_NOT_HAVE_ATTRIBUTE = 17002;
    
    protected $rawData;
    
    protected $name;
    
   /**
    * @var EVEShipInfo_Collection
    */
    protected $collection;
    
   /**
    * @var EVEShipInfo_Collection_Ship
    */
    protected $ship;
    
   /**
    * @var EVEShipInfo
    */
    protected $plugin;
    
    protected static $globalData;
    
    public function __construct(EVEShipInfo_Collection_Ship $ship, $name)
    {
        $this->collection = $ship->getCollection();
        $this->plugin = $this->collection->getPlugin();
        $this->ship = $ship;
        $this->name = $name;
        
        $this->register();
        
        $values = $this->plugin->dbFetch(
            "SELECT
                *
            FROM
                ".$this->plugin->getTableName('ships_attributes')."
            WHERE
                typeID=%d
            AND
                attributeID=%d",
            array(
                $this->ship->getID(),
                self::$globalData[$name]['attributeID']
            )
        );
        
        if(empty($values)) {
            throw new EVEShipInfo_Exception(
                'Ship has no such attribute:'.sprintf(
                    'The ship [%s] does not have the [%s] attribute.',
                    $ship->getName(),
                    $name
                ), 
                sprintf(
                    'The ship [%s] does not have the [%s] attribute.',
                    $ship->getName(),
                    $name
                ), 
                self::ERROR_SHIP_DOES_NOT_HAVE_ATTRIBUTE
            );
        }
        
        $this->rawData = array_merge(self::$globalData[$name], $values);
    }
    
    public function getID()
    {
        return $this->rawData['attributeID'];
    }
    
    protected function register()
    {
        if(isset(self::$globalData[$this->name])) {
            return;
        }
        
        $global = $this->plugin->dbFetch(
            "SELECT
                *
            FROM
                ".$this->plugin->getTableName('attributes')."
            WHERE
                attributeName=%s",
            array(
                $this->name
            )
        );
             
	    if(empty($global)) {
            throw new EVEShipInfo_Exception(
                'Unknown attribute ['.$this->name.'].',
                'The attribute ['.$this->name.'] cannot be found in the database.',
                self::ERROR_UNKNOWN_ATTRIBUTE
            );
        }
         
        self::$globalData[$this->name] = $global;
    }
    
    public function getName()
    {
        return $this->name;
    }

    protected function getProperty($name)
    {
        if(isset($this->rawData[$name])) {
            return $this->rawData[$name];
        }
        
        return null;
    }
    
    protected $unitName;
    
    public function getUnitName()
    {
        if(!isset($this->unitName)) {
            // unit names are in english, so to support translations
            // we translate the native strings locally.
            $this->unitName = $this->translateNativeString(
                $this->getProperty('displayName')
            );
        }
        
        return $this->unitName;
    }
    
    public function getCategoryName()
    {
        return $this->getProperty('categoryName');
    }
    
    public function getIconID()
    {
        return $this->getProperty('iconID');
    }
    
    public function getValue($pretty=false)
    {
        $int = $this->getProperty('valueInt');
        $float = $this->getProperty('valueFloat');
        $value = $int;

        if($float > $value) {
            $value = $float;
        }
        
        if($pretty) {
            $tokens = explode('.', $value);
            if(isset($tokens[1])) {
                return number_format($value, 2);
            }
            
            return number_format($value);
        }
        
        return $value;
    }
    
   /**
    * Retrieves the attribute's value as a string, with
    * the units name if any.
    * 
    * Example: 500 m/s
    * 
    * @return string
    */
    public function getValuePretty()
    {
        $result = $this->getValue(true);
        
        $units = $this->getUnitName();
        if(!empty($units)) {
            $result .= ' ' . $units;
        }
        
        return $result;
    }
    
    public function __toString()
    {
        $value = $this->getValue();
        return $value;
    }
    
    protected static $stringTranslations;
    
    protected function translateNativeString($string)
    {
        if(!isset(self::$stringTranslations)) {
            self::$stringTranslations = array(
                'HP' => __('HP', 'eve-shipinfo'),
                'MW' => __('MW', 'eve-shipinfo'),
                'm/sec' => __('M/Sec', 'eve-shipinfo'),
                'tf' => __('TF', 'eve-shipinfo'),
                'm' => __('M', 'eve-shipinfo'),
                's' => __('S', 'eve-shipinfo'),
                'GJ' => __('GJ', 'eve-shipinfo'),
                'mm' => __('MM', 'eve-shipinfo'),
                'm3' => __('M3', 'eve-shipinfo'),
                'Mbit/sec' => __('MB/S', 'eve-shipinfo')
            );
        }
        
        if(isset(self::$stringTranslations[$string])) {
            return self::$stringTranslations[$string];
        }
        
        return $string;
    }
}