<?php
/**
 * In this file the class {@see \Beluga\GIS\Ext\Ellipsoid} is defined.
 *
 * @author         SagittariusX <unikado+sag@gmail.com>
 * @copyright  (c) 2016, SagittariusX
 * @package        Beluga
 * @since          2016-08-06
 * @subpackage     GIS\Ext
 * @version        0.1.0
 */


namespace Beluga\GIS\Ext;


if ( ! defined( 'PI' ) )       {   define ( 'PI',       3.14159265 );   }
if ( ! defined( 'FOURTHPI' ) ) {   define ( 'FOURTHPI', PI / 4 );       }
if ( ! defined( 'deg2rad' ) )  {   define ( 'deg2rad',  PI / 180 );     }
if ( ! defined( 'rad2deg' ) )  {   define ( 'rad2deg',  180.0 / PI );   }


/**
 * @since v0.1
 */
class Ellipsoid
{


   # <editor-fold desc="= = =   C O N S T A N T S   = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =">

   const TYPE_AIRY = 'Airy';
   const TYPE_AUSTRALIAN_NATIONAL = 'Australian National';
   const TYPE_BESSEL_1841 = 'Bessel 1841';
   const TYPE_BESSEL_1841_NAMIBIA = 'Bessel 1841 (Nambia) ';
   const TYPE_CLARKE_1866 = 'Clarke 1866';
   const TYPE_CLARKE_1880 = 'Clarke 1880';
   const TYPE_EVEREST = 'Everest';
   const TYPE_FISCHER_1960_MERCURY = 'Fischer 1960 (Mercury) ';
   const TYPE_FISCHER_1968 = 'Fischer 1968';
   const TYPE_GRS_1967 = 'GRS 1967';
   const TYPE_GRS_1980 = 'GRS 1980';
   const TYPE_HELMERT_1906 = 'Helmert 1906';
   const TYPE_HOUGH = 'Hough';
   const TYPE_INTERNATIONAL = 'International';
   const TYPE_KRASSOVSKY = 'Krassovsky';
   const TYPE_AIRY_MODIFIED = 'Modified Airy';
   const TYPE_EVEREST_MODIFIED = 'Modified Everest';
   const TYPE_FISCHER_1960_MODIFIED = 'Modified Fischer 1960';
   const TYPE_SOUTH_AMERICAN_1969 = 'South American 1969';
   const TYPE_WGS_60 = 'WGS 60';
   const TYPE_WGS_66 = 'WGS 66';
   const TYPE_WGS_72 = 'WGS-72';
   const TYPE_WGS_84 = 'WGS-84';

   # </editor-fold>


   # <editor-fold desc="= = =   P U B L I C   S T A T I C   F I E L D S   = = = = = = = = = = = = = = = = = = =">

   public static $Ellipsoid = array();

   # </editor-fold>

}


# radius, ecc
Ellipsoid::$Ellipsoid = [
   Ellipsoid::TYPE_AIRY                  => [ 6377563.0, 0.00667054  ],
   Ellipsoid::TYPE_AUSTRALIAN_NATIONAL   => [ 6378160.0, 0.006694542 ],
   Ellipsoid::TYPE_BESSEL_1841           => [ 6377397.0, 0.006674372 ],
   Ellipsoid::TYPE_BESSEL_1841_NAMIBIA   => [ 6377484.0, 0.006674372 ],
   Ellipsoid::TYPE_CLARKE_1866           => [ 6378206.0, 0.006768658 ],
   Ellipsoid::TYPE_CLARKE_1880           => [ 6378249.0, 0.006803511 ],
   Ellipsoid::TYPE_EVEREST               => [ 6377276.0, 0.006637847 ],
   Ellipsoid::TYPE_FISCHER_1960_MERCURY  => [ 6378166.0, 0.006693422 ],
   Ellipsoid::TYPE_FISCHER_1968          => [ 6378150.0, 0.006693422 ],
   Ellipsoid::TYPE_GRS_1967              => [ 6378160.0, 0.006694605 ],
   Ellipsoid::TYPE_GRS_1980              => [ 6378137.0, 0.00669438  ],
   Ellipsoid::TYPE_HELMERT_1906          => [ 6378200.0, 0.006693422 ],
   Ellipsoid::TYPE_HOUGH                 => [ 6378270.0, 0.00672267  ],
   Ellipsoid::TYPE_INTERNATIONAL         => [ 6378388.0, 0.00672267  ],
   Ellipsoid::TYPE_KRASSOVSKY            => [ 6378245.0, 0.006693422 ],
   Ellipsoid::TYPE_AIRY_MODIFIED         => [ 6377340.0, 0.00667054  ],
   Ellipsoid::TYPE_EVEREST_MODIFIED      => [ 6377304.0, 0.006637847 ],
   Ellipsoid::TYPE_FISCHER_1960_MODIFIED => [ 6378155.0, 0.006693422 ],
   Ellipsoid::TYPE_SOUTH_AMERICAN_1969   => [ 6378160.0, 0.006694542 ],
   Ellipsoid::TYPE_WGS_60                => [ 6378165.0, 0.006693422 ],
   Ellipsoid::TYPE_WGS_66                => [ 6378145.0, 0.006694542 ],
   Ellipsoid::TYPE_WGS_72                => [ 6378135.0, 0.006694318 ],
   Ellipsoid::TYPE_WGS_84                => [ 6378137.0, 0.00669438  ]
];

