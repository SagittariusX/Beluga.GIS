<?php
/**
 * In this file the class {@see \Beluga\GIS\Coordinate} is defined.
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


use \Beluga\{ArgumentError, TypeTool, Type};
use \Beluga\GIS\Ext\Ellipsoid;


/**
 * A GPS coordinate.
 *
 * - Latitude : (90° N == 90°) to (90° S == −90°)
 * - Longitude: (180° E == 180°) to (180° W == -180°)
 *
 * @since v0.1.0
 */
class Coordinate
{


   // <editor-fold desc="// = = = =   C L A S S   C O N S T A N T S   = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * FLATTING_WGS84
    */
   const FLATTING_WGS84 = 0.003352811;

   /**
    * ERADIUS_WGS84
    */
   const ERADIUS_WGS84  = 6378.14;

   /**
    * DEG2RAD
    */
   const DEG2RAD        = 0.017453292519943;

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * @var \Beluga\GIS\Longitude
    */
   public $Longitude;

   /**
    * @var \Beluga\GIS\Latitude
    */
   public $Latitude;

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Init a new instance.
    *
    * @param  \Beluga\GIS\Latitude|string|double $latitude
    * @param  \Beluga\GIS\Longitude|string|double $longitude
    * @throws \Beluga\ArgumentError
    */
   public function __construct( $latitude, $longitude )
   {

      if ( ! \is_null( $latitude ) && $latitude instanceof Longitude )
      {
         throw new ArgumentError(
            'latitude',
            $latitude,
            'GIS',
            'Can not use a longitude as latitude!'
         );
      }

      if ( ! \is_null( $longitude ) && $longitude instanceof Latitude )
      {
         throw new ArgumentError(
            'longitude',
            $longitude,
            'GIS',
            'Can not use a latitude as longitude!'
         );
      }

      $lat = null;
      if ( \is_null( $latitude ) )
      {
         $this->Latitude  = $latitude;
      }
      else if ( $latitude instanceof Latitude )
      {
         $this->Latitude  = $latitude;
      }
      else if ( Latitude::TryParse( $latitude, $lat ) )
      {
         $this->Latitude = $lat;
      }
      else
      {
         $this->Latitude = null;
      }

      $lon = null;
      if ( \is_null( $longitude ) )
      {
         $this->Longitude  = $longitude;
      }
      else if ( $longitude instanceof Longitude )
      {
         $this->Longitude  = $longitude;
      }
      else if ( Longitude::TryParse( $longitude, $lon ) )
      {
         $this->Longitude = $lon;
      }
      else
      {
         $this->Longitude = null;
      }

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = =">

   /**
    * Extracts a {@see \Beluga\GIS\Coordinate} instance from defined string value and returns it by reference with the
    * $output parameter. The Method returns TRUE on success, FALSE otherwise.
    *
    * @param  string  $str The string to parse
    * @param  \Beluga\GIS\Coordinate|null &$output Returns the resulting Coordinate reference, if the method returns TRUE
    * @return boolean
    */
   public static function TryParseString( string $str, &$output ) : bool
   {

      if ( empty( $str ) )
      {
         return false;
      }

      if ( false !== ( $res = Converter::ParseUtm2LL( Ellipsoid::TYPE_WGS_84, $str ) ) )
      {   # UTM: 10T E 549142 N 5280803
         try
         {
            $output = new Coordinate( $res[ 'Latitude' ], $res[ 'Longitude' ] );
         }
         catch ( \Throwable $ex )
         {
            return false;
         }
         return true;
      }

      $laStr = null;
      $loStr = null;
      $tmp   = \explode( ', ', $str );

      if ( \count( $tmp ) == 2 )
      {

         $tmp[ 0 ] = \trim( $tmp[ 0 ] );
         $tmp[ 1 ] = \trim( $tmp[ 1 ] );

         if ( \preg_match( '~^([NS].+|.+[NS])$~', $tmp[ 0 ] ) )
         {
            try { $output = new Coordinate( $tmp[ 0 ], \trim( $tmp[ 1 ] ) ); }
            catch ( \Throwable $ex ) { return false; }
            return true;
         }

         if ( \preg_match( '~^([EW].+|.+[EW])$~', $tmp[ 0 ] ) )
         {
            try { $output = new Coordinate( \trim( $tmp[ 1 ] ), $tmp[ 0 ] ); }
            catch ( \Throwable $ex ) { return false; }
            if ( ! $output->isValid() )
            {
               $output = null;
               return false;
            }
            return true;
         }
         else
         {
            $loStr = \trim( $tmp[ 1 ] );
            if ( TypeTool::IsDecimal( $loStr ) )
            {   # 40.446195, -79.948862
               try { $output = new Coordinate( \trim( \rtrim( $tmp[ 0 ], ' ,' ) ), $loStr ); }
               catch ( \Throwable $ex ) { return false; }
               if ( ! $output->isValid() )
               {
                  $output = null;
                  return false;
               }
               return true;
            }
         }

      }

      $tmp = \explode( ' ', $str );

      if ( \count( $tmp ) == 2 )
      {

         if ( \preg_match( '~^([NS].+|.+[NS])$~', $tmp[ 0 ] ) )
         {
            try { $output = new Coordinate( $tmp[ 0 ], \trim( $tmp[ 1 ] ) ); }
            catch ( \Throwable $ex ) { return false; }
            if ( ! $output->isValid() )
            {
               $output = null;
               return false;
            }
            return true;
         }

         if ( \preg_match( '~^([EW].+|.+[EW])$~', $tmp[ 0 ] ) )
         {
            try { $output = new Coordinate( \trim( $tmp[ 1 ] ), $tmp[ 0 ] ); }
            catch ( \Throwable $ex ) { return false; }
            if ( ! $output->isValid() )
            {
               $output = null;
               return false;
            }
            return true;
         }
         else
         {
            $loStr = \trim( $tmp[ 1 ] );
            if ( TypeTool::IsDecimal( $loStr ) )
            {  # 40.446195, -79.948862
               try { $output = new Coordinate( \trim( \rtrim( $tmp[ 0 ], ' ,' ) ), $loStr ); }
               catch ( \Throwable $ex ) { return false; }
               if ( ! $output->isValid() )
               {
                  $output = null;
                  return false;
               }
               return true;
            }
         }

         return false;

      }

      $tmp[ 0 ] = \strtoupper( $tmp[ 0 ] );
      $tc       = \count( $tmp );
      $max      = $tc - 1;

      if ( \preg_match( '~^[NSEW]$~', $tmp[ 0 ] ) )
      {
         switch ( $tmp[ 0 ] )
         {

            case 'N':
            case 'S':
               self::normalizeForNS( $laStr, $loStr, $tc, $max, $tmp );
               try { $output = new Coordinate( $laStr, $loStr ); }
               catch ( \Throwable $ex ) { return false; }
               if ( ! $output->isValid() )
               {
                  $output = null;
                  return false;
               }
               return true;

            default:
               $loStr = $tmp[ 0 ];
               $i     = 1;
               $nc    = \trim( $tmp[ $i ] );
               while ( ( $i < $tc ) && ! \preg_match( '~^[NS]$~', $nc ) )
               {
                  $loStr .= " {$nc}";
                  ++$i;
                  $nc = \trim( $tmp[ $i ] );
               }
               ++$i;
               if ( $i >= $max )
               {
                  return false;
               }
               $laStr = $nc;
               $laStr .= ' ' . \join( ' ', \array_slice( $tmp, $i ) );
               try { $output = new Coordinate( $laStr, $loStr ); }
               catch ( \Throwable $ex ) { return false; }
               if ( ! $output->isValid() )
               {
                  $output = null;
                  return false;
               }
               return true;

         }
      }

      if ( \preg_match( '~^[NSEW]$~', $tmp[ $max ] ) )
      {

         switch ($tmp[$max])
         {

            case 'N':
            case 'S':
               $laStr = $tmp[ $max ];
               $i     = $max - 1;
               $nc    = \trim( $tmp[ $i ] );
               while ( ( $i >= 0 ) && ! \preg_match( '~^[EW]$~', $nc ) )
               {
                  $laStr .= "{$nc} {$laStr}";
                  --$i;
                  $nc = \trim( $tmp[ $i ] );
               }
               --$i;
               if ( $i <= 0 )
               {
                  return false;
               }
               $loStr = $nc;
               $loStr = \join( ' ', \array_slice( $tmp, 0, $i + 1 ) ) . ' ' . $loStr;
               try { $output = new Coordinate( $laStr, $loStr ); }
               catch ( \Throwable $ex ) { return false; }
               if ( ! $output->isValid() )
               {
                  $output = null;
                  return false;
               }
               return true;

            default:
               $loStr = $tmp[ $max ];
               $i     = $max - 1;
               $nc    = \trim( $tmp[ $i ] );
               while ( ( $i >= 0 ) && ! \preg_match( '~^[NS]$~', $nc ) )
               {
                  $loStr .= "{$nc} {$loStr}";
                  --$i;
                  $nc = \trim( $tmp[ $i ] );
               }
               --$i;
               if ( $i <= 0 )
               {
                  return false;
               }
               $laStr = $nc;
               $laStr = \join( ' ', \array_slice( $tmp, 0, $i + 1 ) ) . ' ' . $laStr;
               try { $output = new Coordinate( $laStr, $loStr ); }
               catch ( \Throwable $ex ) { return false; }
               if ( ! $output->isValid() )
               {
                  $output = null;
                  return false;
               }
               return true;

         }

      }

      if ( $tc != 4 )
      {
         return false;
      }

      $tmp[ 0 ] = \preg_replace( '~[^\d-]~', '', $tmp[ 0 ] );
      $tmp[ 1 ] = \preg_replace( '~[^\d.]~', '', $tmp[ 1 ] );
      $tmp[ 2 ] = \preg_replace( '~[^\d-]~', '', $tmp[ 2 ] );
      $tmp[ 3 ] = \preg_replace( '~[^\d.]~', '', $tmp[ 3 ] );

      # 40° 26.7717, -79° 56.93172
      $direction = null;
      if ( $tmp[ 0 ][ 0 ] == '-' )
      {
         $direction = 'S';
         $tmp[ 0 ]  = \substr( $tmp[ 0 ], 1 );
      }
      else
      {
         $direction = 'N';
      }

      $latElement = null;
      try
      {
         $latElement = new Latitude( $direction, \intval( $tmp[ 0 ] ), \doubleval( $tmp[ 1 ] ) );
      }
      catch ( \Throwable $ex ) { return false; }

      if ( $tmp[ 2 ][ 0 ] == '-' )
      {
         $direction = 'W';
         $tmp[ 2 ] = \substr( $tmp[ 2 ], 1 );
      }
      else
      {
         $direction = 'E';
      }

      $lonElement = null;
      try
      {
         $lonElement = new Longitude( $direction, \intval( $tmp[ 2 ] ), \doubleval( $tmp[ 3 ] ) );
      }
      catch ( \Throwable $ex ) { return false; }

      $output = new Coordinate( $latElement, $lonElement );

      if ( ! $output->isValid() )
      {
         $output = null;
         return false;
      }

      return true;

   }

   /**
    * Extracts a {@see \Beluga\GIS\Coordinate} instance from defined value and returns it by reference with the
    * $output parameter. The Method returns TRUE on success, FALSE otherwise.
    *
    * @param  string|double|float|\Beluga\GIS\Coordinate $value The value to parse.
    * @param  \Beluga\GIS\Coordinate|null &$output Returns the resulting Coordinate reference, if the method returns TRUE
    * @return boolean
    */
   public static function TryParse( $value, &$output ) : bool
   {

      if ( empty( $value ) )
      {
         return false;
      }

      if ( $value instanceof Coordinate )
      {
         $output = $value;
         return true;
      }

      $type = new Type( $value );
      if ( ! $type->hasAssociatedString() )
      {
         return false;
      }

      return self::TryParseString( $type->getStringValue(), $output );

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Formats the coordinate UTM specific and return the resulting string.
    *
    * @param  string $ellipsoid The ellipsoid to use (e.g: 'WGS-84') (see \Beluga\GIS\Ext\Ellipsoid::TYPE_* constants)
    * @return string|FALSE
    */
   public function formatUtm( string $ellipsoid = Ellipsoid::TYPE_WGS_84 )
   {

      return Converter::LL2Utm( $ellipsoid, $this->Latitude, $this->Longitude );

   }

   /**
    * Formats the coordinate with floating point numbers.
    *
    * @param  integer $precision The number of required decimal places.
    * @return string Return format is 'Latitude, Longitude' e.g.: '-13.58470058, 52.4788904'
    */
   public function formatDecimal( int $precision = 8 ) : string
   {

      $returnValue = '';

      if ( ! $this->isValid() )
      {
         return $returnValue;
      }

      $returnValue .= $this->Latitude->formatDecimal( $precision )
         .  ', '
         .  $this->Longitude->formatDecimal( $precision );

      return $returnValue;

   }

   /**
    * Returns a element with the DMS (Degrees Minutes Seconds) Format like: N 70° 50' 12" E 15° 12' 47.34".
    *
    * @param  boolean $reverse              Should the direction char placed at the end of the resulting string?
    *                                       e.g. like: 70° 50' 12" N 15° 12' 47.34" E    (default=FALSE)
    * @param  boolean $withoutSpaces        Remove all whitespace characters from resulting string? (default=FALSE)
    * @param  integer $secondsDecimalPlaces Round seconds to decimal places, defined here (default=3)
    * @param  string  $separator            The separator between latitude (first element) and longitude for return
    * @return string
    */
   public function formatDMS(
      bool $reverse = false, bool $withoutSpaces = false, int $secondsDecimalPlaces = 3,
      string $separator = ' ' ) : string
   {

      $returnValue = '';

      if ( ! $this->isValid() )
      {
         return $returnValue;
      }

      $res = $this->Latitude->formatDMS( $reverse, $withoutSpaces, $secondsDecimalPlaces )
         . ( empty( $separator ) ? ' ' : $separator  )
         . $this->Longitude->formatDMS( $reverse, $withoutSpaces, $secondsDecimalPlaces );

      return $res;

   }

   /**
    * Returns a element with the DcMcS (Degrees Colon Minutes Colon Seconds) Format like: N 70:50:12 E 15:12:47.34
    *
    * @param  boolean $withoutSpaces        Remove all whitespace characters from resulting string? (default=FALSE)
    * @param  integer $secondsDecimalPlaces Round seconds to decimal places, defined here (default=3)
    * @param  string  $separator            The separator between latitude (first element) and longitude for return
    * @return string
    */
   public function formatDcMcS(
      bool $withoutSpaces = false, int $secondsDecimalPlaces = 3, string $separator = ' ' ) : string
   {

      if ( ! $this->isValid() )
      {
         return '';
      }

      $res = $this->Latitude->formatDcMcS( $withoutSpaces, $secondsDecimalPlaces )
         . ( empty( $separator ) ? ' ' : $separator  )
         . $this->Longitude->formatDcMcS( $withoutSpaces, $secondsDecimalPlaces );

      return $res;

   }

   /**
    * Returns a element with the DM (Degrees Minutes) Format like: N 70° 50.150037' E 15° 12.761451
    *
    * @param  boolean $showMinuteChar Show the minute character ' (single quote)?
    * @param  integer $minutesDecimalPlaces Round minutes to decimal places, defined here (default=6)
    * @param  string  $separator            The separator between latitude (first element) and longitude for return
    * @return string
    */
   public function formatDM(
      bool $showMinuteChar = true, int $minutesDecimalPlaces = 6, string $separator = ' ' ) : string
   {

      if ( ! $this->isValid() )
      {
         return '';
      }

      $res = $this->Latitude->formatDM( $showMinuteChar, $minutesDecimalPlaces )
         . ( empty( $separator ) ? ' ' : $separator  )
         . $this->Longitude->formatDM( $showMinuteChar, $minutesDecimalPlaces );

      return $res;

   }

   /**
    * Returns a element with the following format: -70° 50.15457, 15° 12.761451
    *
    * @param  boolean $showMinuteChar Show the minute character ' (single quote)?
    * @param  integer $minutesDecimalPlaces Round minutes to decimal places, defined here (default=6)
    * @param  string  $separator            The separator between latitude (first element) and longitude for return
    * @return string
    */
   public function formatWithoutDirection(
      bool $showMinuteChar = false, int $minutesDecimalPlaces = 6, string $separator = ' ' ) : string
   {

      if ( ! $this->isValid() )
      {
         return '';
      }

      $res = $this->Latitude->formatWithoutDirection( $showMinuteChar, $minutesDecimalPlaces )
         . ( empty( $separator ) ? ', ' : $separator  )
         . $this->Longitude->formatWithoutDirection( $showMinuteChar, $minutesDecimalPlaces );

      return $res;

   }

   /**
    * See {@see \Beluga\GIS\Coordinate::formatDMS()}.
    *
    * @return string
    */
   public function __toString()
   {

      return $this->formatDMS();

   }

   /**
    * Format: 51 deg 0' 31.27" N, 13 deg 52' 12.32" E
    *
    * @return string
    */
   public final function formatExifLike() : string
   {

      if ( ! $this->isValid() )
      {
         return '';
      }

      $res = $this->Latitude->formatExifLike()
         . ', '
         . $this->Longitude->formatExifLike();

      return $res;

   }

   /**
    * Calculate the WGS-84 distance from current coordinate to a other one.
    *
    * @param \Beluga\GIS\Coordinate $otherPoint
    * @return integer
    */
   public function calcWGS84DistanceTo( Coordinate $otherPoint )
   {

      if ( ! $this->isValid() )
      {
         return 0;
      }

      $b1 = $this->Latitude->getDecimalValue();
      $l1 = $this->Longitude->getDecimalValue();
      $b2 = $otherPoint->Latitude->getDecimalValue();
      $l2 = $otherPoint->Longitude->getDecimalValue();

      $F = \round( ( 0.0 + $b1 + $b2 ) / 2.0, 8 );
      $G = \round( ( 0.0 + $b1 - $b2 ) / 2.0, 9 );
      $l = \round( (double) ( ( 0.0 + $l1 - $l2 ) / 2.0 ), 8 );
      $F = (double) ( self::DEG2RAD * $F );
      $G = (double) ( self::DEG2RAD * $G );
      $l = (double) ( self::DEG2RAD * $l );
      $S = \round( (double) ( \pow( \sin( $G ), 2 ) * \pow( \cos( $l ), 2 )
         + \pow( \cos( $F ), 2 ) * \pow( \sin( $l ), 2 ) ), 9 );
      $C = \round( (double) ( \pow( \cos( $G ), 2 ) * \pow( \cos( $l ), 2 )
         + \pow( \sin( $F ), 2 ) * \pow( \sin( $l ), 2 ) ), 9 );
      $w = \round( \atan( \sqrt( $S / $C ) ), 12 );
      $D = \round( 2.0 * $w * self::ERADIUS_WGS84, 9 );
      $R = \round( (double) ( \sqrt( $S * $C ) / $w ), 8 );

      $H1  = \round( ( 3.0 * $R - 2.0 ) / ( 2.0 * $C ), 9 );
      $H2  = \round( ( 3.0 * $R + 2.0 ) / ( 2.0 * $S ), 9 );
      $res = $D * ( 2.0 + self::FLATTING_WGS84 * $H1
            * \pow( \sin( $F ), 2 ) * \pow( \cos( $G ), 2 )
            - self::FLATTING_WGS84 * $H2
            * \pow( \cos( $F ), 2 ) * \pow( \sin( $G ), 2 ) );

      return \round( $res, 3 );

   }

   /**
    * Calculate the spherical distance from current coordinate to a other one.
    *
    * @param \Beluga\GIS\Coordinate $otherPoint
    * @return integer
    */
   public function calcSphericalDistanceTo( Coordinate $otherPoint )
   {

      if ( ! $this->isValid() )
      {
         return 0;
      }

      $b1 = $this->Latitude->getDecimalValue();
      $l1 = $this->Longitude->getDecimalValue();
      $b2 = $otherPoint->Latitude->getDecimalValue();
      $l2 = $otherPoint->Longitude->getDecimalValue();

      $rbreite1   = $b1 * self::DEG2RAD;
      $rlaenge1   = $l1 * self::DEG2RAD;
      $rbreite2   = $b2 * self::DEG2RAD;
      $rlaenge2   = $l2 * self::DEG2RAD;
      $rwinkel    = \acos( \sin( $rbreite1 ) * \sin( $rbreite2 ) + \cos( $rbreite1 )
         * \cos( $rbreite2 ) * \cos( \abs( $rlaenge2 - $rlaenge1 ) ) );
      $entfernung = $rwinkel * 6370;

      return \round( $entfernung, 3 );

   }

   /**
    * Returns if the current instance defines a valid coordinate.
    *
    * @return boolean
    */
   public function isValid() : bool
   {

      return
            ! \is_null( $this->Latitude )
         && ! \is_null( $this->Longitude )
         && ( $this->Latitude instanceof Latitude)
         && ( $this->Longitude instanceof Longitude );

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P R I V A T E   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = =">

   private static function normalizeForNS( &$laStr, &$loStr, $tc, $max, array $tmp )
   {

      $laStr = $tmp[ 0 ];
      $i     = 1;
      $nc    = \trim( $tmp[ $i ] );

      while ( ( $i < $tc ) && ! \preg_match( '~^[EW]$~', $nc ) )
      {
         $laStr .= " {$nc}";
         ++$i;
         $nc = \trim( $tmp[ $i ] );
      }

      ++$i;
      if ( $i >= $max )
      {
         return false;
      }

      $loStr = $nc;
      $loStr .= ' ' . \join( ' ', \array_slice( $tmp, $i ) );

      return true;

   }

   // </editor-fold>


}

