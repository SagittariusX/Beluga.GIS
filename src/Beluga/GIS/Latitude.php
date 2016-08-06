<?php
/**
 * In this file the class {@see \Beluga\GIS\Latitude} is defined.
 *
 * @author         SagittariusX <unikado+sag@gmail.com>
 * @copyright  (c) 2016, SagittariusX
 * @package        Beluga
 * @since          2016-08-06
 * @subpackage     GIS
 * @version        0.1.0
 */


declare( strict_types = 1 );


namespace Beluga\GIS;


use \Beluga\{TypeTool, Type};


/**
 * A Latitude : (90° N == 90°) to (90° S == −90°)
 *
 * @since                v0.1.0
 */
class Latitude extends AbstractElement
{


   // <editor-fold desc="// = = = =   P U B L I C   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Init a new instance.
    *
    * @param  string                $direction The direction char ('N' or 'S')
    * @param  integer               $degrees   The degree value (without minutes and seconds)
    * @param  integer|double|string $minutes   The minutes value.
    * @param  integer|string|null   $seconds   The seconds value.
    * @throws \Beluga\GIS\GISError             Is thrown if an parameter it invalid.
    */
   public function __construct( string $direction, $degrees, $minutes, $seconds = null )
   {

      $this->properties[ 'islatitude' ] = true;

      # DIRECTION
      $this->initDirection( $direction );

      # DEGREES
      $this->initDegrees( $degrees );

      # MINUTES + SECONDS
      $this->initMinutes( $minutes, $seconds );

      # DECIMAL
      $this->calcDec();
      
   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Equals the current instance with the defined value.
    *
    * The value can use the following formats:
    *
    * - string: A GPS coordinate latitude string in any known valid format.
    * - double|float: A GPS coordinate latitude as an floating point number.
    * - \SimpleXMLElement: A GPS coordinate latitude as SimpleXMLElement. It can be defined as 'latitude' attribute.
    *   In this case, it must be defined as attribute value as an floating point number. Otherwise it also works if
    *   the attributes 'direction' (char), 'degrees' (integer), 'minutes' (integer|double) and 'seconds' (integer)
    *   are defined.
    * - {@see \Beluga\GIS\Latitude}: ...
    * - {@see \Beluga\GIS\Coordinate}: A coordinate that defines an Latitude
    *
    * @param  string|double|float|\Beluga\GIS\Latitude|\Beluga\GIS\Coordinate $value
    * @return boolean Returns TRUE if $value is equal to current latitude, FALSE otherwise.
    */
   public function equals( $value ) : bool
   {

      // First the value must be converted to an Latitude instance.
      $lng = null;
      if ( ! self::TryParse( $value, $lng ) )
      {
         // Value is of a type that can not be used as latitude
         return false;
      }

      return ( (string) $lng ) === ( (string) $this );

   }

   // </editor-fold>

   
   // <editor-fold desc="// = = = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = =">

   /**
    * Extracts a {@see \Beluga\GIS\Latitude} instance from defined string value and returns it by reference with the
    * $output parameter. The Method returns TRUE on success, FALSE otherwise.
    *
    * @param string                  $str    The string that should be parsed.
    * @param \Beluga\GIS\Latitude|null &$output Returns the resulting Latitude reference, if the method returns TRUE
    * @return boolean
    */
   public static function TryParseString( string $str, &$output ) : bool
   {
      
      if ( ! \is_string( $str ) )
      {
         return false;
      }
      
      if ( TypeTool::IsDecimal( $str, true ) )
      {   # 40.446195 oder -79.948862
         $data = AbstractElement::_DecToDDMS( \doubleval( \str_replace( ',', '.', $str ) ), false );
         try
         {
            $output = new Latitude(
               $data[ 'DIR' ],
               $data[ 'DEG' ],
               $data[ 'MIN' ],
               $data[ 'SEC' ]
            );
         }
         catch ( \Throwable $ex )
         {
            return false;
         }
         return true;
      }
      
      $str = \preg_replace( '~(\s+deg)~i', '°', $str );
      $dir = null;
      
      if ( \preg_match( '~^([NS])(.+)$~i', $str, $m ) )
      {
         $dir = $m[ 1 ];
         $str = \trim( $m[ 2 ] );
      }
      else if ( \preg_match( '~^(.+)([NS])$~i', $str, $m ) )
      {
         $dir = $m[ 2 ];
         $str = \trim( $m[ 1 ] );
      }
      else if ( \preg_match( '~^(-?)\d+°~', $str, $m ) )
      {
         if ( isset( $m[ 1 ] ) && $m[ 1 ] == '-' )
         {
            $dir = 'S';
            $str = \substr( $str, 1 );
         }
         else
         {
            $dir = 'N';
         }
      }
      else
      {
         return false;
      }
      
      if ( \preg_match( '~^(\d{1,3})[°d:]\s*(\d{1,2})[:\'](.+)$~', \trim( $str ), $m ) )
      {
         try
         {
            $output = new Latitude(
               $dir,
               \trim( $m[ 1 ] ),
               \trim( $m[ 2 ] ),
               \rtrim( \trim( $m[ 3 ] ), '"' )
            );
         }
         catch ( \Throwable $ex )
         {
            return false;
         }
         return true;
      }
      
      if ( TypeTool::IsDecimal( $str, true ) )
      {
         $data = AbstractElement::_DecToDDMS( \doubleval( \str_replace( ',', '.', $str ) ), false );
         try
         {
            $output = new Latitude(
               $data[ 'DIR' ],
               $data[ 'DEG' ],
               $data[ 'MIN' ],
               $data[ 'SEC' ]
            );
         }
         catch ( \Throwable $ex )
         {
            return false;
         }
         return true;
      }
      
      if ( \preg_match( '~^(\d{1,3})°\s+([\d.]+)\'?$~', \trim( $str ), $m ) )
      {
         try
         {
            $output = new Latitude(
               $dir,
               \trim( $m[ 1 ] ),
               \doubleval( \trim( $m[ 2 ] ) )
            );
         }
         catch ( \Throwable $ex )
         {
            return false;
         }
         return true;
      }
      
      if ( \preg_match( '~^(\d{1,3})°\s+([\d.]+)"\s+([\d.]+)\'?$~', \trim( $str ), $m ) )
      {
         try
         {
            $output = new Latitude(
               $dir,
               \trim( $m[ 1 ] ),
               \doubleval( \trim( $m[ 2 ] ) ),
               \doubleval( \trim( $m[ 3 ] ) )
            );
         }
         catch ( \Throwable $ex )
         {
            return false;
         }
         return true;
      }
      
      return false;
      
   }

   /**
    * Extracts a {@see \Beluga\GIS\Latitude} instance from defined value and returns it by reference with the
    * $output parameter. The Method returns TRUE on success, FALSE otherwise.
    *
    * @param  string|double|float|\SimpleXMLElement|\Beluga\GIS\Latitude|\Beluga\GIS\Coordinate $value
    * @param  \Beluga\GIS\Latitude|null &$output Returns the resulting Latitude reference, if the method returns TRUE
    * @return boolean
    */
   public static function TryParse( $value, &$output ) : bool
   {
      
      $output = null;
      
      if ( \is_null( $value ) )
      {
         return false;
      }
      
      if ( $value instanceof Latitude )
      {
         $output = $value;
         return true;
      }
      
      if ( $value instanceof Coordinate )
      {
         $output = $value->Latitude;
         return true;
      }
      
      if ( \is_double( $value ) || \is_float( $value ) )
      {
         $data = AbstractElement::_DecToDDMS( $value, false );
         try
         {
            $output = new Latitude(
               $data[ 'DIR' ],
               $data[ 'DEG' ],
               $data[ 'MIN' ],
               $data[ 'SEC' ]
            );
         }
         catch ( \Throwable $ex )
         {
            return false;
         }
         return true;
      }

      $type = new Type( $value );
      if ( ! $type->hasAssociatedString() )
      {
         return false;
      }
      
      return self::TryParseString( $type->getStringValue(), $output );
      
   }

   # </editor-fold>
   

}

