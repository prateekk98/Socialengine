<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Image
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Imagick.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_Image
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Image_Adapter_Imagick extends Engine_Image
{
  /**
   * @var Imagick
   */
  protected $_resource;

  // General

  /**
   * Constructor
   *
   * @param string $file Image to open
   */
  public function __construct($options = array())
  {
    // Check support
    if( !class_exists('Imagick', false) ) {
      throw new Engine_Image_Adapter_Exception('Imagick library is not installed');
    }

    parent::__construct($options);
  }



  // Options

  public function getFile()
  {
    $this->_checkOpenImage();
    return $this->_resource->getImageFilename();
  }

  public function setFile($file)
  {
    $this->_checkOpenImage();
    $this->_resource->setImageFilename($file);
    return $this;
  }

  public function getFormat()
  {
    $this->_checkOpenImage();
    return $this->_resource->getImageFormat();
  }

  public function setFormat($format)
  {
    $this->_checkOpenImage();
    $format = strtoupper($format);
    $this->_resource->setImageFormat($format);
  }

  public function getHeight()
  {
    $this->_checkOpenImage();
    return $this->_resource->getImageHeight();
  }

  public function getWidth()
  {
    $this->_checkOpenImage();
    return $this->_resource->getImageWidth();
  }

  // Actions

  public function create($width, $height, $format = 'png')
  {
    // Destroy first?
    $this->destroy();

    // Create
    $resource = new Imagick();
    $resource->setResolution($width, $height);
    $resource->newImage(100, 100, new ImagickPixel('none'), $format);
    $resource->setImageFormat($format);

    // Save resource
    $this->_resource = $resource;

    return $this;
  }

  /**
   * Open an image
   *
   * @param string $file
   * @return Engine_Image_Adapter_Gd
   * @throws Engine_Image_Adapter_Exception If unable to open
   */
  public function open($file)
  {
    // Destroy first?
    $this->destroy();

    // Open
    $this->_resource = new Imagick();
    $return = $this->_resource->readImage($file);
    if( !$return || !$this->_checkOpenImage(false) ) {
      $this->_resource = null;
      throw new Engine_Image_Adapter_Exception(sprintf('Unable to open image "%s"', $file));
    }
    return $this;
  }

  /**
   * Write current image to a file
   *
   * @param string $file (OPTIONAL) The file to write to. Default: original file
   * @param string $type (OPTIONAL) The output image type. Default: jpeg
   * @return Engine_Image_Adapter_Gd
   * @throws Engine_Image_Adapter_Exception If unable to write
   */
  public function write($file = null, $type = 'jpeg')
  {
    $this->_checkOpenImage();
    if( $this->_isGif() ) {
      $image = $this->_resource->coalesceImages();
      if( !$file ) {
        $file = $this->_resource->getImageFilename();
      }
      if( null !== $this->_quality ) {
        foreach( $image as $frame ) {
          $frame->setImageCompressionQuality($this->_quality);
        }
      }
      $image = $image->deconstructImages();
      $return = $image->writeImages($file, true);
      // Error
      if( !$return ) {
        throw new Engine_Image_Adapter_Exception(sprintf('Unable to write image to file "%s"', $file));
      }
      $this->_resource = $image;
      return $this;
    }
    // Set file type
    if( $type == 'jpg' ) {
      $type = 'jpeg';
    }
    $type = strtoupper($type);
    if( $type !== $this->_resource->getImageFormat() ) {
      $this->_resource->setImageFormat($type);
    }
    // Set quality
    if( null !== $this->_quality ) {
      $this->_resource->setImageCompressionQuality($this->_quality);
    }

      $orientation = $this->_resource->getImageOrientation();
      switch($orientation) {
          case imagick::ORIENTATION_BOTTOMRIGHT:
              $this->_resource->rotateimage('#000', 180);
              break;

          case imagick::ORIENTATION_RIGHTTOP:
              $this->_resource->rotateimage('#000', 90);
              break;

          case imagick::ORIENTATION_LEFTBOTTOM:
              $this->_resource->rotateimage('#000', -90);
              break;
      }

      $this->_resource->setImageOrientation(imagick::ORIENTATION_TOPLEFT);

    // Write
    if( null === $file ) {
      $return = $this->_resource->writeImage();
    } else {
      $return = $this->_resource->writeImage($file);
    }
    // Error
    if( !$return ) {
      if( !$file ) {
        $file = $this->_resource->getImageFilename();
      }
      throw new Engine_Image_Adapter_Exception(sprintf('Unable to write image to file "%s"', $file));
    }
    return $this;
  }

  /**
   * Remove the current image object from memory
   */
  public function destroy()
  {
    if( $this->_checkOpenImage(false) ) {
      $this->_resource->destroy();
    }
    $this->_resource = null;
    return $this;
  }

  /**
   * Output an image to buffer or return as string
   *
   * @param string $type Image format
   * @param boolean $buffer Output or return?
   * @return mixed
   * @throws Engine_Image_Adapter_Exception If unable to output
   */
  public function output($type = 'jpeg', $buffer = false)
  {
    $this->_checkOpenImage();

    if( $this->_isGif() ) {
      if( null !== $this->_quality ) {
        $image = $this->_resource->coalesceImages();
        foreach( $image as $frame ) {
          $frame->setImageCompressionQuality($this->_quality);
        }
        $this->_resource = $image->deconstructImages();
      }
      // Output
      if( $buffer ) {
        return (string) $this->_resource;
      } else {
        echo $this->_resource;
      }
      return $this;
    }

    // Set file type
    if( $type == 'jpg' ) {
      $type = 'jpeg';
    }
    $type = strtoupper($type);
    if( $type !== $this->_resource->getImageFormat() ) {
      $this->_resource->setImageFormat($type);
    }
    // Set quality
    if( null !== $this->_quality ) {
      $this->_resource->setImageCompressionQuality($this->_quality);
    }
    // Output
    if( $buffer ) {
      return (string) $this->_resource;
    } else {
      echo $this->_resource;
    }

    return $this;
  }

  /**
   * Resizes current image to $width and $height. If aspect is set, will fit
   * within boundaries while keeping aspect
   *
   * @param integer $width
   * @param integer $height
   * @param boolean $aspect (OPTIONAL) Default - true
   * @return Engine_Image_Adapter_Gd
   * @throws Engine_Image_Adapter_Exception If unable to resize
   */
  public function resize($width, $height, $aspect = true)
  {
    $this->_checkOpenImage();

    $imgW = $this->_resource->getImageWidth();
    $imgH = $this->_resource->getImageHeight();

    // Keep aspect
    if( $aspect ) {
      list($width, $height) = self::_fitImage($imgW, $imgH, $width, $height);
    }

    // Resize
    if( $this->_isGif() ) {
      return $this->_processForGif('resize', 'Unable to resize image', array(
          'width' => $width,
          'height' => $height,
      ));
    }

    try {
      $return = $this->_resource->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
    } catch( ImagickException $e ) {
      throw new Engine_Image_Adapter_Exception(sprintf('Unable to resize image: %s',
          $e->getMessage()), $e->getCode());
    }

    if( !$return ) {
      throw new Engine_Image_Adapter_Exception('Unable to resize image');
    }

    return $this;
  }

  /**
   * Crop an image
   *
   * @param integer $x
   * @param integer $y
   * @param integer $w
   * @param integer $h
   * @return Engine_Image_Adapter_Gd
   * @throws Engine_Image_Adapter_Exception If unable to crop
   */
  public function crop($x, $y, $w, $h)
  {
    $this->_checkOpenImage();
    if( $this->_isGif() ) {
      return $this->_processForGif('crop', 'Unable to crop image', array(
          'x' => $x,
          'y' => $y,
          'w' => $w,
          'h' => $h,
      ));
    }
    // Crop image
    try {
      $return = $this->_resource->cropImage($w, $h, $x, $y);
    } catch( ImagickException $e ) {
      throw new Engine_Image_Adapter_Exception(sprintf('Unable to crop image: %s',
          $e->getMessage()), $e->getCode());
    }

    if( !$return ) {
      throw new Engine_Image_Adapter_Exception('Unable to crop image');
    }

    return $this;
  }

  /**
   * Resample. Just crop+resize
   *
   * @param integer $srcX
   * @param integer $srcY
   * @param integer $srcW
   * @param integer $srcH
   * @param integer $dstW
   * @param integer $dstH
   * @return Engine_Image_Adapter_Gd
   * @throws Engine_Image_Adapter_Exception If unable to crop
   */
  public function resample($srcX, $srcY, $srcW, $srcH, $dstW, $dstH)
  {
    $this->_checkOpenImage();

    // Resample image
    if( $this->_isGif() ) {
      return $this->_processForGif('resample', 'Unable to resample image', array(
          'srcW' => $srcW,
          'srcY' => $srcY,
          'srcX' => $srcX,
          'srcH' => $srcH,
          'dstW' => $dstW,
          'dstH' => $dstH
      ));
    }

    // Crop
    try {
      $return = $this->_resource->cropImage($srcW, $srcH, $srcX, $srcY);
    } catch( ImagickException $e ) {
      throw new Engine_Image_Adapter_Exception(sprintf('Unable to resample image: %s',
          $e->getMessage()), $e->getCode());
    }

    if( !$return ) {
      throw new Engine_Image_Adapter_Exception('Unable to resample image');
    }

    // Resize
    try {
      $return = $this->_resource->resizeImage($dstW, $dstH, Imagick::FILTER_LANCZOS, 1);
    } catch( ImagickException $e ) {
      throw new Engine_Image_Adapter_Exception(sprintf('Unable to resample image: %s',
          $e->getMessage()), $e->getCode());
    }

    if( !$return ) {
      throw new Engine_Image_Adapter_Exception('Unable to resample image');
    }

    return $this;
  }

  public function rotate($angle)
  {
    $this->_checkOpenImage();

    if( $this->_isGif() ) {
      return $this->_processForGif('rotate', 'Unable to rotate image', array('angle' => $angle));
    }
    // Rotate
    try {
      $return = $this->_resource->rotateImage(new ImagickPixel('none'), $angle);
    } catch( ImagickException $e ) {
      throw new Engine_Image_Adapter_Exception(sprintf('Unable to rotate image: %s',
          $e->getMessage()), $e->getCode());
    }

    return $this;
  }

  public function flip($horizontal = true)
  {
    $this->_checkOpenImage();
    // Flip
    if( $this->_isGif() ) {
      if( $horizontal ) {
        $handler = 'flop';
      } else {
        $handler = 'flip';
      }
      return $this->_processForGif($handler, 'Unable to flip image');
    }

    try {
      if( $horizontal ) {
        $return = $this->_resource->flopImage();
      } else {
        $return = $this->_resource->flipImage();
      }
    } catch( ImagickException $e ) {
      throw new Engine_Image_Adapter_Exception(sprintf('Unable to flip image: %s',
          $e->getMessage()), $e->getCode());
    }

    return $this;
  }

  // Utility

  protected function _checkOpenImage($throw = true)
  {
    if( !($this->_resource instanceof Imagick) ) {
      if( $throw ) {
        throw new Engine_Image_Adapter_Exception('No open image to operate on.');
      } else {
        return false;
      }
    } else {
      return true;
    }
  }

  protected function _isGif()
  {
    $this->_checkOpenImage();
    return $this->_resource->getImageFormat() === 'GIF';
  }

  protected function _processForGif($processHandler, $errorHeading, $options = array())
  {
    $image = $this->_resource->coalesceImages();
    try {
      foreach( $image as $frame ) {
        call_user_func_array(array($this, '_frame' . ucwords($processHandler)), array($frame, $options));
      }
    } catch( ImagickException $e ) {
      throw new Engine_Image_Adapter_Exception(sprintf('%1$s: %2$s', $errorHeading, $e->getMessage()), $e->getCode());
    }
    $this->_resource = $image->deconstructImages();
    return $this;
  }

  protected function _frameResize($frame, $options)
  {
    $frame->resizeImage($options['width'], $options['height'], Imagick::FILTER_LANCZOS, 1);
  }

  protected function _frameCrop($frame, $options)
  {
    $frame->cropImage($options['w'], $options['h'], $options['x'], $options['y']);
  }

  protected function _frameResample($frame, $options)
  {
    $frame->cropImage($options['srcW'], $options['srcH'], $options['srcX'], $options['srcY']);
    $frame->thumbnailImage($options['dstW'], $options['dstH']);
    $frame->setImagePage($options['dstW'], $options['dstH'], 0, 0);
  }

  protected function _frameRotate($frame, $options)
  {
    $frame->rotateImage(new ImagickPixel('none'), $options['angle']);
  }

  protected function _frameFlop($frame)
  {
    $frame->flopImage();
  }

  protected function _frameFlip($frame)
  {
    $frame->flipImage();
  }
}
