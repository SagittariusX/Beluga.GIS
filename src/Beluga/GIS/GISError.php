<?php
/**
 * In this file the class {@see \Beluga\GIS\Exception} is defined.
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


use \Beluga\BelugaError;


/**
 * @since v0.1
 */
class GISError extends BelugaError
{


   // <editor-fold desc="// = = = =   P R I V A T E   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   // The exception type
   private $type;

   // </editor-fold>


   // <editor-fold desc="// = = = =   C L A S S   C O N S T A N T S   = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * An Error depending to an invalid direction.
    */
   const ERROR_TYPE_DIRECTION = 'direction';

   /**
    * An Error depending to an invalid degree value.
    */
   const ERROR_TYPE_DEGREES   = 'degrees';

   /**
    * An Error depending to an invalid minutes value.
    */
   const ERROR_TYPE_MINUTES   = 'minutes';

   /**
    * An Error depending to an invalid seconds value.
    */
   const ERROR_TYPE_SECONDS   = 'seconds';

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Init a new instance.
    *
    * @param string $package      The name of the package that triggers the error.
    * @param string $type         The type (see {@see \Beluga\GIS\GISError}}::ERROR_TYPE* constants)
    * @param string $msg          An optional error message
    * @param mixed  $code         The optional error code
    * @param \Throwable $previous An optional previous error/exception
    */
   public function __construct(
      string $package, string $type, string $msg = null, $code = \E_USER_WARNING, \Throwable $previous = null )
   {

      parent::__construct(
         $package,
         \sprintf( 'Invalid or unknown value for a geo coordinate "%s" element/part!', $type )
            . static::appendMessage( $msg ),
         $code,
         $previous
      );

      $this->type = $type;

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   // <editor-fold desc="// - - -   G E T T E R   - - - - - - - - - - - - - - - - - - - - - -">

   /**
    * Returns the error type.
    *
    * @return string
    */
   public final function getType() : string
   {
      return $this->type;
   }

   // </editor-fold>

   // </editor-fold>


}

