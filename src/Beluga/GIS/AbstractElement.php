<?php
/**
 * In this file the class {@see \Beluga\GIS\AbstractElement} is defined.
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


use \Beluga\DynamicProperties\ExplicitGetter;
use \Beluga\TypeTool;


/**
 * The abstract base class of a latitude or longitude.
 *
 * @property-read string  $direction    The direction character. (Latitude: N or S, Longitude: E or W)
 * @property-read integer $degrees      The element degrees part. Always positive and always in realation to
 *                                      Direction, Minutes and Seconds. (Latitude: 0-90°, Longitude: 0-180°)
 * @property-read integer $minutes      The minute part integer.
 * @property-read double  $seconds      The seconds part.
 * @property      double  $decimalValue The element decimal value representation. can be positive or negative value
 * @since         v0.1.0
 */
abstract class AbstractElement extends ExplicitGetter
{

   
   // <editor-fold desc="// = = = =   P R O T E C T E D   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * All instance properties (direction, degrees, minutes, seconds, decimal, islatitude)
    *
    * - <b>direction</b>: The direction character. (Latitude: N or S, Longitude: E or W)
    * - <b>degrees</b>: The element degrees part. Always positive and always in relation to Direction, Minutes
    *   and Seconds. (Latitude: 0-90°, Longitude: 0-180°)
    * - <b>minutes</b>: The minute part integer.
    * - <b>seconds</b>: The seconds part double/float.
    * - <b>decimal</b>: The element decimal value representation. can be positive or negative value
    * - <b>islatitude</b>: Defines if the extending element represents a latitude or longitude.
    *
    * @var array
    */
   protected $properties = array(
      'direction'  => '',
      'degrees'    => 0,
      'minutes'    => 0,
      'seconds'    => 0,
      'decimal'    => 0,
      'islatitude' => false
   );

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   
   // <editor-fold desc="// - - -   G E T T E R   - - - - - - - - - - - - - - - - - - - - - -">

   /**
    * Returns the element decimal value representation. can be positive or negative value
    *
    * @return float
    */
   public final function getDecimalValue() : float
   {

      return $this->properties[ 'decimal' ];

   }

   /**
    * Returns the element degrees part. Always positive and always in relation to Direction, Minutes and Seconds.
    *
    * @return integer
    */
   public final function getDegrees() : int
   {

      return $this->properties[ 'degrees' ];

   }

   /**
    * Returns the direction character. (Latitude: N or S, Longitude: E or W)
    *
    * @return string
    */
   public final function getDirection() : string
   {

      return $this->properties[ 'direction' ];

   }

   /**
    * Returns the minute part integer.
    *
    * @return integer
    */
   public final function getMinutes() : int
   {

      return $this->properties[ 'minutes' ];

   }

   /**
    * Returns the seconds part.
    *
    * @return float
    */
   public final function getSeconds() : float
   {

      return $this->properties[ 'seconds' ];

   }

   // </editor-fold>

   
   // <editor-fold desc="// - - -   S E T T E R   - - - - - - - - - - - - - - - - - - - - - -">

   /**
    * @param  double|float|int|string $decimalValue
    * @return bool
    */
   public final function setDecimalValue( float $decimalValue ) : bool
   {

      if ( ! \is_double( $decimalValue ) )
      {
         // Its not a double value => convert is
         $decimalValue = \doubleval( $decimalValue );
      }

      // Define the min and max allowed values
      $min = $this->properties[ 'islatitude' ] ? -90 : -180;
      $max = $this->properties[ 'islatitude' ] ? 90  : 180;

      if ( $decimalValue > $max )
      {
         // Bad value (bigger than allowed)
         return false;
      }

      if ( $decimalValue < 0 )
      {
         if ( $decimalValue < $min )
         {
            // Bad value (lower than allowed)
            return false;
         }
         // Convert it to a always positive value
         $decimalValue = \abs( $decimalValue );
      }

      // Assign the value
      $this->properties[ 'decimal' ] = $decimalValue;

      // Getting the required other informations
      $data = self::_DecToDDMS( $decimalValue, ! $this->properties[ 'islatitude' ] );

      // Assign the other information to corresponding fields
      $this->properties[ 'direction' ] = $data[ 'DIR' ];
      $this->properties[ 'degrees' ]   = $data[ 'DEG' ];
      $this->properties[ 'minutes' ]   = $data[ 'MIN' ];
      $this->properties[ 'seconds' ]   = $data[ 'SEC' ];

      // All finished successful. Return TRUE
      return true;

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   O T H E R   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = =">

   /**
    * Returns a element with the DMS (Degrees Minutes Seconds) Format like: N 70° 50' 12"
    *
    * @param  boolean $reverse              Should the direction char placed at the end of the resulting string?
    *                                       e.g. like: 70° 50' 12" N    (default=FALSE)
    * @param  boolean $withoutSpaces        Remove all whitespace characters from resulting string? (default=FALSE)
    * @param  integer $secondsDecimalPlaces Round seconds to decimal places, defined here (default=3)
    * @return string
    */
   public final function formatDMS(
      bool $reverse = false, bool $withoutSpaces = false, int $secondsDecimalPlaces = 3 ) : string
   {

      if ( ! $reverse )
      {

         // Doing the forward way
         if ( $withoutSpaces )
         {
            return "{$this->properties[ 'direction' ]}{$this->properties[ 'degrees' ]}°{$this->properties[ 'minutes' ]}'"
                 . \round( $this->properties[ 'seconds' ], $secondsDecimalPlaces ) . '"';
         }

         return "{$this->properties[ 'direction' ]} {$this->properties[ 'degrees' ]}° {$this->properties[ 'minutes' ]}' "
              . \round( $this->properties[ 'seconds' ], $secondsDecimalPlaces ) . '"';

      }

      // Doing the backward way
      if ( $withoutSpaces )
      {
         return "{$this->properties[ 'degrees' ]}°{$this->properties[ 'minutes' ]}'"
              . \round( $this->properties[ 'seconds' ], $secondsDecimalPlaces )
              . "\"{$this->properties[ 'direction' ]}";
      }

      return "{$this->properties[ 'degrees' ]}° {$this->properties[ 'minutes' ]}' "
           . \round( $this->properties[ 'seconds' ], $secondsDecimalPlaces )
           . "\" {$this->properties[ 'direction' ]}";

   }

   /**
    * Returns a element with the DcMcS (Degrees Colon Minutes Colon Seconds) Format like: N 70:50:12
    *
    * @param  boolean $withoutSpaces        Remove all whitespace characters from resulting string? (default=FALSE)
    * @param  integer $secondsDecimalPlaces Round seconds to decimalplaces, defined here (default=3)
    * @return string
    */
   public final function formatDcMcS( bool $withoutSpaces = false, int $secondsDecimalPlaces = 3 ) : string
   {

      if ( $withoutSpaces )
      {
         return "{$this->properties[ 'direction' ]}{$this->properties[ 'degrees' ]}:{$this->properties[ 'minutes' ]}:"
              . \round( $this->properties[ 'seconds' ], $secondsDecimalPlaces );
      }

      return "{$this->properties[ 'direction' ]} {$this->properties[ 'degrees' ]}:{$this->properties[ 'minutes' ]}:"
           . \round( $this->properties[ 'seconds' ], $secondsDecimalPlaces );

   }

   /**
    * Returns a element with the DM (Degrees Minutes) Format like: N 70° 50.150037'
    *
    * @param  boolean $showMinuteChar Show the minute character ' (single quote)?
    * @param  integer $minutesDecimalPlaces Round minutes to decimal places, defined here (default=6)
    * @return string
    */
   public final function formatDM( bool $showMinuteChar = true, int $minutesDecimalPlaces = 6 ) : string
   {

      // Direction Degrees°
      $returnValue = "{$this->properties[ 'direction' ]} {$this->properties[ 'degrees' ]}° ";

      // Calculate the minutes value
      $minutes = \doubleval( 0.0 + $this->properties[ 'minutes' ] + ( $this->properties[ 'seconds' ] / 60 ) );

      // Append the minutes to the returning string
      $returnValue .= \round( $minutes, $minutesDecimalPlaces )
                   .  ( $showMinuteChar ? "'" : '' );

      // Return the resulting string
      return $returnValue;

   }

   /**
    * Returns a element with the following format: -70° 50.15457
    *
    * @param  boolean $showMinuteChar Show the minute character ' (single quote)?
    * @param  integer $minutesDecimalPlaces Round minutes to decimal places, defined here (default=6)
    * @return string
    */
   public final function formatWithoutDirection( bool $showMinuteChar = true, int $minutesDecimalPlaces = 6 ) : string
   {

      $firstChar = '';

      if ( ( $this->properties[ 'direction' ] == 'S' ) || ( $this->properties[ 'direction' ] == 'W' ) )
      {
         // Define a negative - (minus) prefix by need.
         $firstChar = '-';
      }

      // {FirstChar}Degress°
      $returnValue = "{$firstChar}{$this->properties[ 'degrees' ]}° ";

      // Calculate the minutes value
      $minutes = \doubleval( 0.0 + $this->properties[ 'minutes' ] + ( $this->properties[ 'seconds' ] / 60 ) );

      // Append the minutes
      $returnValue .= \round( $minutes, $minutesDecimalPlaces )
                   .  ( $showMinuteChar ? "'" : '' );

      return $returnValue;

   }

   /**
    * Returns the decimal value, rounded to defined $precision.
    *
    * @param  integer $precision
    * @return double
    */
   public final function formatDecimal( int $precision = 8 ) : float
   {

      return \round( $this->properties[ 'decimal' ], $precision );

   }

   /**
    * Format: 51 deg 0' 31.27" N
    *
    * @return string
    */
   public final function formatExifLike() : string
   {

      return "{$this->properties[ 'degrees' ]} deg {$this->properties[ 'minutes' ]}' "
           . \str_replace( ',', '.', \strval( \round( $this->properties[ 'seconds' ], 3 ) ) )
           . "\" {$this->properties[ 'direction' ]}";

   }

   /**
    * See {@see \Beluga\GIS\AbstractElement::formatDMS()}.
    *
    * @return string
    */
   public function __toString()
   {

      return $this->formatDMS();

   }

   # </editor-fold>


   // </editor-fold>

   
   // <editor-fold desc="// = = = =   P R O T E C T E D   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Changes the current direction value.
    *
    * @param  string $direction
    * @throws \Beluga\GIS\GISError If the direction does not match the based latitude or longitude element.
    */
   protected function initDirection( string $direction )
   {

      // Convert the direction to UPPER case
      $direction = \strtoupper( \trim( $direction ) );

      if ( \strlen( $direction ) != 1 )
      {
         // Bad Format
         throw new GISError(
            GISError::ERROR_TYPE_DIRECTION,
            \sprintf( "'%s' is not allowed.", $direction )
         );
      }

      if ( 'O' == $direction )
      {
         // Convert O to E
         $direction = 'E';
      }

      // Define the allowed 2 direction values
      $allowedDirections = $this->properties[ 'islatitude' ] ? array( 'N', 'S' ) : array( 'E', 'W' );

      if ( ! \in_array( $direction, $allowedDirections ) )
      {
         // Not allowed value
         throw new GISError(
            GISError::ERROR_TYPE_DIRECTION,
            \sprintf(
               "'%s' isnt allowed. Please use '%s'",
               $direction,
               \join( "' or '", $allowedDirections )
            )
         );
      }

      $this->properties[ 'direction' ] = $direction;

   }

   /**
    * Changes the current degrees value.
    *
    * @param  integer $degrees
    * @throws \Beluga\GIS\GISError If the direction is defined outside the allowed range, depending to Direction.
    */
   protected function initDegrees( $degrees )
   {

      if ( ! \is_int( $degrees ) )
      {
         // Convert to integer
         $degrees = \ltrim( $degrees, '0' );
         if ( \strlen( $degrees ) < 1 )
         {
            $degrees = '0';
         }
         $degrees = \intval( $degrees );
      }

      // Define the min + max allowed values
      $min = $this->properties[ 'islatitude' ] ? -90 : -180;
      $max = $this->properties[ 'islatitude' ] ? 90  : 180;

      $dir = $this->properties[ 'islatitude' ]
         ? 'north/south latitude'
         : 'east/west longitude';

      if ( $degrees < 0 )
      {
         if ( $degrees < $min )
         {
            throw new GISError(
               GISError::ERROR_TYPE_DEGREES,
               \sprintf(
                  '%s° is out of allowed range 0-%s for a %s.',
                  $degrees,
                  $max,
                  $dir
               )
            );
         }
         $degrees = \abs( $degrees );
      }

      if ( $degrees > $max )
      {
         throw new GISError(
            GISError::ERROR_TYPE_DEGREES,
            \sprintf(
               '%s° is out of allowed range 0-%s for a %s.',
               $degrees,
               $max,
               $dir
            )
         );
      }

      $this->properties[ 'degrees' ] = $degrees;

   }

   /**
    * Changes the current minutes value.
    *
    * @param  double $minutes
    * @param  double $seconds
    * @throws \Beluga\GIS\GISError  If the minutes is wrong.
    */
   protected function initMinutes( $minutes, $seconds )
   {

      if ( ! TypeTool::IsDecimal( $minutes ) )
      {
         throw new GISError(
            GISError::ERROR_TYPE_MINUTES,
            \sprintf( "'%s' is not of required decimal number format.", $minutes )
         );
      }

      $this->extractTime( $minutes, $seconds );

   }

   /**
    * @param  double $minutes
    * @param  double $seconds
    * @return boolean
    */
   protected function extractTime( $minutes, $seconds )
   {

      if ( \is_null( $seconds ) || $seconds < 1 )
      {

         if ( \is_int( $minutes ) )
         {
            $this->properties[ 'minutes' ] = $minutes;
            $this->properties[ 'seconds' ] = 0.0;
            return true;
         }

         if ( \is_double( $minutes ) )
         {
            $tmp = \explode( '.', \str_replace( ',', '.', (string) $minutes ), 2 );
            if ( \count( $tmp ) == 0 )
            {
               $this->properties[ 'minutes' ] = 0;
               $this->properties[ 'seconds' ] = 0.0;
               return true;
            }
            if ( \count( $tmp ) == 1 )
            {
               $this->properties[ 'minutes' ] = \intval( $minutes );
               $this->properties[ 'seconds' ] = 0.0;
               return true;
            }
            $this->properties[ 'minutes' ] = \intval( $tmp[ 0 ] );
            $sec            = \doubleval( '0.' . $tmp[ 1 ] );
            $this->properties[ 'seconds' ] = \round( 60.0 * $sec, 8 );
            return true;
         }

         $tmp = \explode( '.', \str_replace( ',', '.', (string) $minutes ), 2 );

         if ( \count( $tmp ) == 0 )
         {
            $this->properties[ 'minutes' ] = 0;
            $this->properties[ 'seconds' ] = 0.0;
            return true;
         }

         if ( \count( $tmp ) == 1 )
         {
            $this->properties[ 'minutes' ] = \intval( $tmp[ 0 ] );
            $this->properties[ 'seconds' ] = 0.0;
            return true;
         }

         $this->properties[ 'minutes' ] = \intval( $tmp[ 0 ] );
         $sec            = \doubleval( '0.' . $tmp[ 1 ] );
         $this->properties[ 'seconds' ] = \round( 60.0 * $sec, 6 );

         return true;

      }

      if ( \is_string( $seconds ) )
      {
         $seconds = \doubleval($seconds);
      }

      $this->properties[ 'seconds' ] = \round( $seconds, 8 );

      if ( \is_int( $minutes ) )
      {
         $this->properties[ 'minutes' ] = $minutes;
         return true;
      }

      $min = \ltrim( (string) $minutes, '0.' );

      if ( \strlen( $min ) < 1 )
      {
         $this->properties[ 'minutes' ] = 0;
      }
      else
      {
         $this->properties[ 'minutes' ] = \intval( $minutes );
      }

      return true;

   }

   /**
    * …
    */
   protected function calcDec()
   {

      $totalSeconds = ( 60.0 * $this->properties[ 'minutes' ] ) + $this->properties[ 'seconds' ];
      $fractPart    = (float) ( $totalSeconds / 3600.0 );
      $res          = $this->properties[ 'degrees' ] + $fractPart;

      if ( $this->properties[ 'direction' ] == 'E' || $this->properties[ 'direction' ] == 'N' )
      {
         $this->properties[ 'decimal' ] = $res;
      }
      else
      {
         $this->properties[ 'decimal' ] = -$res;
      }

   }

   // </editor-fold>

   
   // <editor-fold desc="// = = = =   P R I V A T E   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = =">

   private static function extractDec( $value ) : array
   {

      $tmp = \explode( '.', \str_replace( ',', '.', (string) $value ), 2 );

      if ( \count( $tmp ) < 1 )
      {
         return array( 0, 0 );
      }

      if ( \count( $tmp ) < 2 )
      {
         return array( \intval( $value ), 0 );
      }

      if ( \strlen( $tmp[ 0 ] ) < 1 )
      {
         $tmp[ 0 ] = 0;
      }
      else
      {
         $tmp[ 0 ] = \intval( $tmp[ 0 ] );
      }

      if ( \strlen( $tmp[ 1 ] ) < 1 )
      {
         $tmp[ 1 ] = 0;
      }
      else
      {
         $tmp[ 1 ] = \intval( $tmp[ 1 ] );
      }

      return $tmp;

   }

   // </editor-fold>

   
   // <editor-fold desc="// = = = =   P R O T E C T E D   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = =">

   protected static function _DecToDDMS( $degfloat, $isLongi = false )
   {

      if ( ! \is_double( $degfloat ) )
      {
         // $degfloat must be of type double/float
         $degfloat = \doubleval( $degfloat );
      }

      // Remember if $degfloat is negative
      $isNegative = ( $degfloat < 0 );

      // Init the result array
      $res        = array( 'DIR' => '', 'DEG' => 0, 'MIN' => 0,'SEC' => 0.0 );
      if ( $isNegative )
      {
         $res[ 'DIR' ] = $isLongi ? 'W' : 'S';
         $degfloat     = \abs( $degfloat );
      }
      else
      {
         $res[ 'DIR' ] = $isLongi ? 'E' : 'N';
      }

      $degfloatStr  = self::extractDec( $degfloat );
      $res[ 'DEG' ] = $degfloatStr[ 0 ];
      $minfloat     = 60.0 * ( $degfloat - $res[ 'DEG' ] );
      $minfloatStr  = self::extractDec( $minfloat );
      $res[ 'MIN' ] = $minfloatStr[ 0 ];
      $secfloat     = 60.0 * ( $minfloat - $res[ 'MIN' ] );
      $secfloat     = \round( $secfloat, 5 );
      if ( $secfloat == 60.0 )
      {
         ++$res[ 'MIN' ];
         $secfloat = 0.0;
      }
      while ( $res[ 'MIN' ] >= 60 )
      {
         $res[ 'DEG' ]++;
         $res[ 'MIN' ] = $res[ 'MIN' ] - 60;
      }
      $res[ 'SEC' ] = $secfloat;
      return $res;
   }

   // </editor-fold>
   

}

