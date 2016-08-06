<?php
/**
 * In this file the class {@see \Beluga\GIS\Converter} is defined.
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


use \Beluga\GIS\Ext\Ellipsoid;


/**
 * @ignore
 */
class Converter
{

   
   # <editor-fold desc="= = =   C O N S T A N T S   = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =">
   
   const PI = 3.14159265;
   const FOURTHPI = 0.7853981625;
   const DEG2RAD = 0.0174532925;
   const RAD2DEG = 57.2957795785523;
   
   # </editor-fold>
   
   
   # <editor-fold desc="= = =   P R I V A T E   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = =">

   private function __construct() {}
   
   # </editor-fold>

   
   # <editor-fold desc="= = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = = =">

   /**
    * Converts a latitude + longitude to UTM format.
    *
    * @param  string  $ellipsoid e.g. 'WGS-84' use one of the \Beluga\Gps\Ext\Ellipsoid::TYPE_* constants
    * @param  \Beluga\GIS\AbstractElement|double $lat The latitude
    * @param  \Beluga\GIS\AbstractElement|double $long The longitude.
    * @return string|FALSE
    */
   public static function LL2Utm( string $ellipsoid, $lat, $long )
   {

      if ( ! isset( Ellipsoid::$Ellipsoid[ $ellipsoid ] ) )
      {
         // The defined ellipsoid does not exist.
         return false;
      }

      // Get the longitude decimal value
      $ln = ( ( $long instanceof AbstractElement )
         ? $long->getDecimalValue()
         : ( \is_double( $long ) ? $long : \doubleval( $long ) )
      );
      // Get the latitude decimal value
      $lt = ( ( $lat instanceof AbstractElement )
         ? $lat->getDecimalValue()
         : ( \is_double( $lat ) ? $lat : \doubleval( $lat ) )
      );

      // Init some required parameters
      $longOrigin      = null;
      $eccPrimeSquared = null;
      $longOriginRad   = null;
      $k0              = 0.9996;

      // Doing the calculation
      $a               = Ellipsoid::$Ellipsoid[ $ellipsoid ][ 0 ];
      $eccSquared      = Ellipsoid::$Ellipsoid[ $ellipsoid ][ 1 ];
      $N = null; $T = null; $C = null; $A = null; $M = null;
      $longTemp        = ( $ln + 180.0 ) - \intval( ( $ln + 180 ) / 360 ) * 360 - 180;
      $latRad          = $lt * self::DEG2RAD;
      $longRad         = $longTemp * self::DEG2RAD;
      $zoneNumber      = \intval( ( $longTemp + 180 ) / 6 ) + 1;
      if ( $lt >= 56.0 && $lt < 64.0 && $longTemp >= 3.0 && $longTemp < 12.0 )
      {
         $zoneNumber = 32;
      }
      if ( $lt >= 72.0 && $lt < 84.0 )
      {
         if ( $longTemp >= 0.0  && $longTemp <  9.0 )
         {
            $zoneNumber = 31;
         }
         else if ( $longTemp >= 9.0  && $longTemp < 22.0 )
         {
            $zoneNumber = 33;
         }
         else if ( $longTemp >= 22.0 && $longTemp < 33.0 )
         {
            $zoneNumber = 35;
         }
         else if ( $longTemp >= 33.0 && $longTemp < 42.0 )
         {
            $zoneNumber = 37;
         }
      }
      $longOrigin    = ( $zoneNumber - 1 ) * 6 - 180 + 3;
      $longOriginRad = $longOrigin * self::DEG2RAD;
      $UTMZone       = \sprintf(
         '%d%s',
         $zoneNumber,
         self::UTMLetterDesignator($lt)
      );
      $eccPrimeSquared = ( $eccSquared ) / ( 1 - $eccSquared );
      $N               = $a / \sqrt( 1 - $eccSquared * \sin( $latRad ) * \sin( $latRad ) );
      $T               = \tan( $latRad ) * \tan( $latRad );
      $C               = $eccPrimeSquared * \cos( $latRad ) * \cos( $latRad );
      $A               = \cos( $latRad ) * ( $longRad - $longOriginRad );
      $M               = $a * (
            ( 1 - $eccSquared/4 - 3 * $eccSquared * $eccSquared/64 - 5 * $eccSquared * $eccSquared * $eccSquared / 256 )
            * $latRad
            - (3 * $eccSquared/8 + 3 * $eccSquared * $eccSquared/32 + 45 * $eccSquared * $eccSquared * $eccSquared/1024)
            * \sin( 2 * $latRad )
            + ( 15 * $eccSquared * $eccSquared / 256 + 45 * $eccSquared * $eccSquared * $eccSquared / 1024 )
            * \sin( 4 * $latRad )
            - ( 35 * $eccSquared * $eccSquared * $eccSquared / 3072) * \sin( 6 * $latRad )
         );
      $UTMEasting = (double) ( $k0 * $N * ( $A + ( 1 - $T + $C ) * $A * $A * $A / 6
            + ( 5 - 18 * $T + $T * $T + 72 * $C - 58 * $eccPrimeSquared )
            * $A * $A * $A * $A * $A / 120 ) + 500000.0
      );
      $UTMEasting = \round( $UTMEasting, 0 );
      $UTMNorthing = (double) ( $k0
         * ( $M + $N * \tan( $latRad ) * ( $A * $A / 2 + ( 5 - $T + 9 * $C + 4 * $C * $C )
               * $A * $A * $A * $A / 24 + ( 61 - 58 * $T + $T * $T + 600 * $C - 330 * $eccPrimeSquared )
               * $A * $A * $A * $A * $A * $A / 720 ) )
      );
      if ( $lt < 0 )
      {
         $UTMNorthing += 10000000.0;
      }
      $UTMNorthing = \round( $UTMNorthing, 0 );
      return "{$UTMZone} E {$UTMEasting} N {$UTMNorthing}";
   }

   /**
    * Converts a UTM formatted GPS coordinate (e.g.: 10T E 549142 N 5280803) to a latitude and longitude and
    * returns as {@see \Beluga\GIS\Latitude} and {@see \Beluga\GIS\Longitude} instances inside a array with the keys
    * 'Longitude' and 'Latitude'.
    *
    * Attention! If you want to determine the parameters of this method not only manually from a string, so
    * you can also use the {@see \Beluga\GIS\Converter::ParseUtm2LL()} method.
    *
    * @param  string  $ellipsoid   See the \Beluga\GIS\Ext\Ellipsoid::TYPE_* constants.
    * @param  integer $UTMNorthing The northing UTM definition
    * @param  integer $UTMEasting  The easting UTM definition
    * @param  string  $UTMZone     The UTM zone. (e.g.: 10T)
    * @return array|FALSE
    */
   public static function Utm2LL( string $ellipsoid, int $UTMNorthing, int $UTMEasting, string $UTMZone )
   {
      $k0 = 0.9996;
      if ( ! isset( Ellipsoid::$Ellipsoid[ $ellipsoid ] ) )
      {
         return false;
      }
      $a                  = Ellipsoid::$Ellipsoid[ $ellipsoid ][ 0 ];
      $eccSquared         = Ellipsoid::$Ellipsoid[ $ellipsoid ][ 1 ];
      $e1                 = ( 1 - \sqrt( 1 - $eccSquared ) ) / ( 1 + \sqrt( 1 - $eccSquared ) );
      $x                  = $UTMEasting - 500000.0;
      $y                  = $UTMNorthing;
      #$NorthernHemisphere = 0;
      $zoneNumber         = \intval( \substr( $UTMZone, 0, -1 ) );
      $ZoneLetter         = \strtoupper( $UTMZone[ \strlen( $UTMZone ) - 1 ] );
      $NorthernHemisphere = ( $ZoneLetter == 'N' );
      if ( $NorthernHemisphere )
      {
         $y -= 10000000.0;
      }
      $longOrigin         = ( $zoneNumber - 1 ) * 6 - 180 + 3;
      $eccPrimeSquared    = ( $eccSquared ) / ( 1 - $eccSquared );
      $M                  = $y / $k0;
      $mu                 = $M / ( $a *
            ( 1 - $eccSquared / 4 - 3 * $eccSquared * $eccSquared / 64 - 5 *
               $eccSquared * $eccSquared * $eccSquared / 256 )
         );
      $phi1Rad = $mu + ( 3 * $e1 / 2 - 27 * $e1 * $e1 * $e1 / 32 ) * \sin( 2 * $mu )
         + ( 21 * $e1 * $e1 / 16 - 55 * $e1 * $e1 * $e1 * $e1 / 32 )
         * \sin( 4 * $mu ) + ( 151 * $e1 * $e1 * $e1 / 96 ) * \sin( 6 * $mu );
      #$phi1 = $phi1Rad * self::RAD2DEG;
      $N1 = $a / \sqrt( 1 - $eccSquared * \sin( $phi1Rad ) * \sin( $phi1Rad ) );
      $T1 = \tan( $phi1Rad ) * \tan( $phi1Rad );
      $C1 = $eccPrimeSquared * \cos( $phi1Rad ) * \cos( $phi1Rad );
      $R1 = $a * ( 1 - $eccSquared ) / \pow( 1 - $eccSquared * \sin( $phi1Rad ) * \sin( $phi1Rad ), 1.5 );
      $D  = $x / ( $N1 * $k0 );
      $Lat = $phi1Rad
         - ( $N1 * \tan( $phi1Rad ) / $R1 )
         * ( $D * $D / 2 - ( 5 + 3 * $T1 + 10 * $C1 - 4 * $C1 * $C1 - 9 * $eccPrimeSquared )
            * $D * $D * $D * $D / 24
            + ( 61 + 90 * $T1 + 298 * $C1 + 45 * $T1 * $T1 - 252 * $eccPrimeSquared - 3 * $C1 * $C1 )
            * $D * $D * $D * $D * $D * $D / 720
         );
      $Lat = $Lat * static::RAD2DEG;
      $Long = (
            $D - ( 1 + 2 * $T1 + $C1 ) * $D * $D * $D / 6
            + ( 5 - 2 * $C1 + 28 * $T1 - 3 * $C1 * $C1 + 8 * $eccPrimeSquared + 24 * $T1 * $T1 )
            * $D * $D * $D * $D * $D / 120
         ) / \cos( $phi1Rad );
      $Long = $longOrigin + $Long * static::RAD2DEG;
      if ( ! Longitude::TryParse( $Long, $_lo )
        || ! Latitude::TryParse( $Lat, $_la ) )
      {
         return false;
      }
      $res = [
         'Longitude' => $_lo,
         'Latitude'  => $_la
      ];
      return $res;
   }

   /**
    * Converts a UTM formatted GPS coordinate (e.g.: 10T E 549142 N 5280803) to a latitude and longitude and
    * returns as {@see \Beluga\GIS\Latitude} and {@see \Beluga\GIS\Longitude} instances inside a array with the keys
    * 'Longitude' and 'Latitude'.
    *
    * @param  string  $ellipsoid   See the \Beluga\GIS\Ext\Ellipsoid::TYPE_* constants.
    * @param  string  $utmDefinition The UTM formatted coordinate definition.
    * @return array|bool|FALSE
    */
   public static function ParseUtm2LL( string $ellipsoid, string $utmDefinition )
   {

      $m = null;
      if ( ! \preg_match( '~^(\d+[A-Z])\s+E\s+(\d+)\s+N\s+(\d+)$~', $utmDefinition, $m ) )
      {
         return false;
      }

      $easting  = \intval( $m[ 2 ] );
      $northing = \intval( $m[ 3 ] );

      return self::Utm2LL( $ellipsoid, $northing, $easting, $m[ 1 ] );

   }

   # </editor-fold>


   # <editor-fold desc="= = =   P R I V A T E   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = =">

   private static function UTMLetterDesignator( $Lat )
   {
      if ( ( 84  >= $Lat ) && ( $Lat >=  72 ) ) { return 'X'; }
      if ( ( 72  >  $Lat ) && ( $Lat >=  64 ) ) { return 'W'; }
      if ( ( 64  >  $Lat ) && ( $Lat >=  56 ) ) { return 'V'; }
      if ( ( 56  >  $Lat ) && ( $Lat >=  48 ) ) { return 'U'; }
      if ( ( 48  >  $Lat ) && ( $Lat >=  40 ) ) { return 'T'; }
      if ( ( 40  >  $Lat ) && ( $Lat >=  32 ) ) { return 'S'; }
      if ( ( 32  >  $Lat ) && ( $Lat >=  24 ) ) { return 'R'; }
      if ( ( 24  >  $Lat ) && ( $Lat >=  16 ) ) { return 'Q'; }
      if ( ( 16  >  $Lat ) && ( $Lat >=   8 ) ) { return 'P'; }
      if ( ( 8   >  $Lat ) && ( $Lat >=   0 ) ) { return 'N'; }
      if ( ( 0   >  $Lat ) && ( $Lat >=  -8 ) ) { return 'M'; }
      if ( ( -8  >  $Lat ) && ( $Lat >= -16 ) ) { return 'L'; }
      if ( ( -16 >  $Lat ) && ( $Lat >= -24 ) ) { return 'K'; }
      if ( ( -24 >  $Lat ) && ( $Lat >= -32 ) ) { return 'J'; }
      if ( ( -32 >  $Lat ) && ( $Lat >= -40 ) ) { return 'H'; }
      if ( ( -40 >  $Lat ) && ( $Lat >= -48 ) ) { return 'G'; }
      if ( ( -48 >  $Lat ) && ( $Lat >= -56 ) ) { return 'F'; }
      if ( ( -56 >  $Lat ) && ( $Lat >= -64 ) ) { return 'E'; }
      if ( ( -64 >  $Lat ) && ( $Lat >= -72 ) ) { return 'D'; }
      if ( ( -72 >  $Lat ) && ( $Lat >= -80 ) ) { return 'C'; }
      return 'Z';
   }

   # </editor-fold>


}

