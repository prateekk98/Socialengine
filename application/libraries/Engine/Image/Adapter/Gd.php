<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Image
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Gd.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_Image
 * @copyright  Copyright 2006-2020 Webligo Developments 
 * @license    http://www.socialengine.com/license/
 */
class Engine_Image_Adapter_Gd extends Engine_Image
{
  /**
   * Image format support
   *
   * @var array
   */
  protected static $_support;
  protected $_file;
  protected $_format;
  protected $_height;
  protected $_width;
  protected $_fileContent;
  protected $_originalFramesMeta = array();
  protected $_meta = array();
  protected $_frames = array();

  // Methods

  /**
   * Constructor
   *
   * @param string $file Image to open
   */
  public function __construct($options = array())
  {
    // Check support
    if( !function_exists('gd_info') ) {
      throw new Engine_Image_Adapter_Exception('GD library is not installed');
    }
    parent::__construct($options);
  }

  // Options

  public function getFile()
  {
    return $this->_file;
  }

  public function setFile($file)
  {
    $this->_file = $file;
    return $this;
  }

  public function getFormat()
  {
    return $this->_format;
  }

  public function setFormat($format)
  {
    $format = strtolower($format);
    self::_isSupported($format);
    $this->_format = $format;
    return $this;
  }

  public function getHeight()
  {
    return $this->_height;
  }

  public function getWidth()
  {
    return $this->_width;
  }

  // Actions

  public function create($width, $height)
  {
    // Check if we can create the image
    self::_isSafeToOpen($width, $height);

    // Create image
    $resource = imagecreatetruecolor($width, $height);

    if( !$resource ) {
      throw new Engine_Image_Adapter_Exception("Unable to create image");
    }

    // Assign info
    $this->_width = $width;
    $this->_height = $height;
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
    // Set file
    $this->_file = $file;

    // Get image info
    $info = @getimagesize($file);
    if( !$info ) {
      throw new Engine_Image_Adapter_Exception(sprintf("File \"%s\" is not an image or does not exist", $file));
    }

    // Check if we can open the file
    self::_isSafeToOpen($info[0], $info[1]);

    // Detect type
    $type = ltrim(strrchr('.', $file), '.');
    if( !$type ) {
      $type = self::image_type_to_extension($info[2], false);
    }
    $type = strtolower($type);

    // Set information
    $this->_format = $type;
    $this->_width = $info[0];
    $this->_height = $info[1];

    // Check support
    self::_isSupported($type);
    $function = 'imagecreatefrom' . $type;
    if( !function_exists($function) ) {
      throw new Engine_Image_Adapter_Exception(sprintf('Image type "%s" is not supported', $type));
    }

    if( $this->_isAnimatedGif() ) {
      $this->_openAnimated();
    } else {
      $this->_open();
    }
    return $this;
  }

  protected function _open()
  {
    $type = $this->_format;
    $file = $this->_file;
    // Open
    $function = 'imagecreatefrom' . $type;
    $this->_resource = $function($file);
    if( !$this->_checkOpenImage(false) ) {
      throw new Engine_Image_Adapter_Exception("Unable to open image");
    }
  }

  protected function _openAnimated()
  {
    $gif = new GIF_Decoder($this->_getFileContent());
    $this->_originalFramesMeta = $gif->GIFGetFramesMeta();
    if( count($this->_originalFramesMeta) <= 0 ) {
      return false;
    }
    $this->_meta = array(
      'delays' => $gif->GIFGetDelays(),
      'loops' => $gif->GIFGetLoop(),
      'disposal' => $gif->GIFGetDisposal(),
      'tr' => $gif->GIFGetTransparentR(),
      'tg' => $gif->GIFGetTransparentG(),
      'tb' => $gif->GIFGetTransparentB(),
      'trans' => (0 == $gif->GIFGetTransparentI() ? false : true)
    );
    $this->_frames = $gif->GIFGetFrames();
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
  public function write($file = null)
  {
    // If no file specified, write to existing file
    if( null === $file ) {
      if( null === $this->_file ) {
        throw new Engine_Image_Adapter_Exception("No file to write specified.");
      }
      $file = $this->_file;
    }

    // Get output format
    $outputFormat = $this->_format;
    if( $this->_isAnimatedGif() ) {
      $this->_writeAnimated($file);
      return $this;
    }
    // Check support
    $function = 'image' . $outputFormat;
    if( !function_exists($function) ) {
      throw new Engine_Image_Adapter_Exception(sprintf('Image type "%s" is not supported', $outputFormat));
    }

    // Apply quality
    $quality = null;
    if( is_int($this->_quality) && $this->_quality >= 0 && $this->_quality <= 100 ) {
      $quality = $this->_quality;
    }

    // Write
    if( $function == 'imagejpeg' && null !== $quality ) {
      $result = $function($this->_resource, $file, $quality);
    } elseif( $function == 'imagepng' && null !== $quality ) {
      $result = $function($this->_resource, $file, round(abs(($quality - 100) / 11.111111)));
    } else {
      $result = $function($this->_resource, $file);
    }

    // Check result
    if( !$result ) {
      throw new Engine_Image_Adapter_Exception(sprintf("Unable to write image to file %s", $file));
    }

    return $this;
  }

  protected function _writeAnimated($file)
  {
    if( count($this->_frames) > 0 ) {
      $frames = array();
      foreach( $this->_frames as $nf ) {
        if( !is_resource($nf) ) {
          continue;
        }
        ob_start();
        imagegif($nf);
        $gifdata = ob_get_clean();
        array_push($frames, $gifdata);
        imagedestroy($nf);
      }
      $gifmerge = new GIF_Encoder(
        $frames, $this->_meta['delays'], $this->_meta['loops'], $this->_meta['disposal'], $this->_meta['tr'], $this->_meta['tg'], $this->_meta['tb'], 'bin'
      );
      $result = false === fwrite(fopen($file, 'wb'), $gifmerge->GetAnimation()) ? false : true;
    }
    return $this;
  }

  /**
   * Remove the current image object from memory
   */
  public function destroy()
  {
    if( $this->_isAnimatedGif() ) {
      foreach( $this->_frames as $nf ) {
        if( !is_resource($nf) ) {
          continue;
        }
        imagedestroy($nf);
      }
      return $this;
    }
    if( is_resource($this->_resource) ) {
      imagedestroy($this->_resource);
    }
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
  public function output($buffer = false)
  {
    $this->_checkOpenImage();

    // Get output format
    $outputFormat = $this->_format;
    // Check support
    $function = 'image' . $outputFormat;
    if( !function_exists($function) ) {
      throw new Engine_Image_Adapter_Exception(sprintf('Image type "%s" is not supported', $outputFormat));
    }

    // Open buffer
    if( $buffer ) {
      ob_start();
    }

    // Apply quality
    $quality = null;
    if( is_int($this->_quality) && $this->_quality >= 0 && $this->_quality <= 100 ) {
      $quality = $this->_quality;
    }

    // Write
    if( $function == 'imagejpeg' && null !== $quality ) {
      $result = $function($this->_resource, null, $quality);
    } elseif( $function == 'imagepng' && null !== $quality ) {
      $result = $function($this->_resource, null, round(abs(($quality - 100) / 11.111111)));
    } else {
      $result = $function($this->_resource, null);
    }

    // Check result
    if( !$result ) {
      if( $buffer ) {
        ob_end_clean();
      }
      throw new Engine_Image_Adapter_Exception("Unable to output image");
    }

    // Finish
    if( $buffer ) {
      return ob_get_clean();
    } else {
      return $this;
    }
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

    $imgW = $this->_width;
    $imgH = $this->_height;

    // Keep aspect
    if( $aspect ) {
      list($width, $height) = self::_fitImage($imgW, $imgH, $width, $height);
    }

    // Create new temporary image
    self::_isSafeToOpen($width, $height);
    if( $this->_isAnimatedGif() ) {
      $this->_resizeAnimated($width, $height);
    } else {
      $this->_resize($width, $height);
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

    // Create new temporary image and resize
    self::_isSafeToOpen($w, $h);
    if( $this->_isAnimatedGif() ) {
      $this->_cropAnimated($x, $y, $w, $h);
    } else {
      $this->_crop($x, $y, $w, $h);
    }


    return $this;
  }

  protected function _cropAnimated($cropX, $cropY, $cropWidth, $cropHeight)
  {
    $bg = null;
    $fullWidth = $this->_width;
    $fullHeight = $this->_height;
    $newFrames = array();
    $originalFrames = $this->_frames;
    foreach( $originalFrames as $k => $v ) {
      $frame = @imagecreatefromstring($v);
      if( !is_resource($frame) )
        continue;
      if( !is_resource($bg) ) {
        $bg = imagecreatetruecolor($fullWidth, $fullHeight);
        $this->_prepareGDimage($bg);
      }
      $srcX = 0;
      $srcY = 0;
      $srcW = imageSX($frame);
      $srcH = imageSY($frame);
      $dstX = $this->_originalFramesMeta[$k]['left'];
      $dstY = $this->_originalFramesMeta[$k]['top'];
      imagecopy($bg, $frame, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH);
      $newImg = imagecreatetruecolor($cropWidth, $cropHeight);
      $this->_prepareGDimage($newImg);
      imagecopy($newImg, $bg, 0, 0, $cropX, $cropY, $cropWidth, $cropHeight);
      array_push($newFrames, $newImg);
    }
    $this->_frames = $newFrames;
    return $this;
  }

  protected function _crop($x, $y, $w, $h)
  {
    $dst = imagecreatetruecolor($w, $h);

    // Try to preserve transparency
    self::_allocateTransparency($this->_resource, $dst, $this->_format);

    // Crop
    if( !imagecopy($dst, $this->_resource, 0, 0, $x, $y, $w, $h) ) {
      imagedestroy($dst);
      throw new Engine_Image_Adapter_Exception('Unable to crop image');
    }

    // Now destroy old image and overwrite with new
    imagedestroy($this->_resource);
    $this->_resource = $dst;
    $this->_width = $w;
    $this->_height = $h;
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

    // Create new temporary image
    self::_isSafeToOpen($dstW, $dstH);


    if( $this->_isAnimatedGif() ) {
      $this->_resampleAnimated($srcX, $srcY, $srcW, $srcH, $dstW, $dstH);
    } else {
      $this->_resample($srcX, $srcY, $srcW, $srcH, $dstW, $dstH);
    }
    return $this;
  }

  public function rotate($angle)
  {
    $this->_checkOpenImage();

    // Check if is safe to open (note if angle is not divisible by 90, then
    // this may not be handled correctly
    self::_isSafeToOpen($this->_width, $this->_height);
    if( $this->_isAnimatedGif() ) {
      $this->_rotateAnimated($angle);
    } else {
      $this->_rotate($angle);
    }


    return $this;
  }

  public function flip($horizontal = true)
  {
    $this->_checkOpenImage();

    // Create new temporary image
    self::_isSafeToOpen($this->_width, $this->_height);
    if( $this->_isAnimatedGif() ) {
      $this->_flipAnimated($horizontal);
    } else {
      $this->_flip($horizontal);
    }


    return $this;
  }

  protected function _resize($width, $height)
  {
    $imgW = $this->_width;
    $imgH = $this->_height;
    $dst = imagecreatetruecolor($width, $height);

    // Try to preserve transparency
    self::_allocateTransparency($this->_resource, $dst, $this->_format);

    // Resize
    if( !imagecopyresampled($dst, $this->_resource, 0, 0, 0, 0, $width, $height, $imgW, $imgH) ) {
      imagedestroy($dst);
      throw new Engine_Image_Adapter_Exception('Unable to resize image');
    }

    // Now destroy old image and overwrite with new
    imagedestroy($this->_resource);
    $this->_resource = $dst;
    $this->_width = $width;
    $this->_height = $height;
  }

  protected function _resizeAnimated($width, $height)
  {
    $bg = null;
    $ratio = 1.0;
    $fullWidth = $this->_width;
    $fullHeight = $this->_height;
    $finalWidth = $width;
    $finalHeight = $height;
    $ratioW = $fullWidth / $finalWidth;
    $ratioH = $fullHeight / $finalHeight;
    $ratio = ($ratioH > $ratioW ? $ratioH : $ratioW);
    $originalFrames = $this->_frames;
    $frames = array();
    foreach( $originalFrames as $k => $v ) {
      $frame = @imagecreatefromstring($v);
      if( !is_resource($frame) )
        continue;
      $newImg = imagecreatetruecolor($finalWidth, $finalHeight);
      $this->_prepareGDimage($newImg);
      if( is_resource($bg) ) {
        imagecopy($newImg, $bg, 0, 0, 0, 0, $finalWidth, $finalHeight);
      }
      $srcX = 0;
      $srcY = 0;
      $srcW = imageSX($frame);
      $srcH = imageSY($frame);
      $dstX = floor($this->_originalFramesMeta[$k]['left'] / $ratio);
      $dstY = floor($this->_originalFramesMeta[$k]['top'] / $ratio);
      $dstW = ceil($this->_originalFramesMeta[$k]['width'] / $ratio);
      $dstH = ceil($this->_originalFramesMeta[$k]['height'] / $ratio);
      imagecopyresampled($newImg, $frame, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
      array_push($frames, $newImg);
      if( !is_resource($bg) ) {
        $bg = imagecreatetruecolor($finalWidth, $finalHeight);
        $this->_prepareGDimage($bg);
      }
      imagecopy($bg, $newImg, 0, 0, 0, 0, $finalWidth, $finalHeight);
    }
    $this->_frames = $frames;
    $this->_width = $width;
    $this->_height = $height;
    return;
  }

  protected function _resample($srcX, $srcY, $srcW, $srcH, $dstW, $dstH)
  {
    $dst = imagecreatetruecolor($dstW, $dstH);
    // Try to preserve transparency
    self::_allocateTransparency($this->_resource, $dst, $this->_format);

    // Resample
    $result = imagecopyresampled($dst, $this->_resource, 0, 0, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);

    if( !$result ) {
      imagedestroy($dst);
      throw new Engine_Image_Adapter_Exception('Unable to resample image');
    }

    // Now destroy old image and overwrite with new
    imagedestroy($this->_resource);
    $this->_resource = $dst;
    $this->_width = $dstW;
    $this->_height = $dstH;
  }

  protected function _resampleAnimated($srcX, $srcY, $srcW, $srcH, $dstW, $dstH)
  {
    // 4 = resize and crop from center with aspect ratio
    $bg = null;
    $bgX = $bgY = 0;
    $bgWidth = $this->_width;
    $bgHeight = $this->_height;
    $originalFrames = $this->_frames;
    $newFrames = array();
    foreach( $originalFrames as $k => $v ) {
      $frame = @imagecreatefromstring($v);
      if( !is_resource($frame) )
        continue;
      $newImg = imagecreatetruecolor($bgWidth, $bgHeight);
      $this->_prepareGDimage($newImg);
      if( is_resource($bg) ) {
        imagecopy($newImg, $bg, 0, 0, 0, 0, $bgWidth, $bgHeight);
      }
      imagecopyresampled($newImg, $frame, 0, 0, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
      if( !is_resource($bg) ) {
        $bg = imagecreatetruecolor($bgWidth, $bgHeight);
        $this->_prepareGDimage($bg);
      }
      imagecopy($bg, $newImg, 0, 0, 0, 0, $bgWidth, $bgHeight);
      $newImg = imagecreatetruecolor($dstW, $dstH);
      $this->_prepareGDimage($newImg);
      imagecopy($newImg, $bg, 0, 0, $bgX, $bgY, $dstW, $dstH);
      array_push($newFrames, $newImg);
      $originalFrames[$k] = null;
    }
    $this->_frames = $newFrames;
    $this->_width = $dstW;
    $this->_height = $dstH;
    return $this;
  }

  protected function _rotateAnimated($angle)
  {
    $originalFrames = $this->_frames;
    $newFrames = array();
    foreach( $originalFrames as $k => $v ) {
      $frame = @imagecreatefromstring($v);
      if( !is_resource($frame) )
        continue;
      $newImg = imagerotate($frame, $angle, 0);
      array_push($newFrames, $newImg);
    }
    $this->_frames = $newFrames;
    $this->_width = imagesx($newFrames[0]);
    $this->_height = imagesy($newFrames[0]);
  }

  protected function _rotate($angle)
  {
    // Rotate
    $result = imagerotate($this->_resource, $angle, 0);

    if( !$result ) {
      imagedestroy($result);
      throw new Engine_Image_Adapter_Exception('Unable to rotate image');
    }

    // Now destroy old image and overwrite with new
    imagedestroy($this->_resource);
    $this->_resource = $result;
    $this->_width = imagesx($this->_resource);
    $this->_height = imagesy($this->_resource);
    return $this;
  }

  protected function _flipAnimated($horizontal = true)
  {
    $originalFrames = $this->_frames;
    $newFrames = array();
    $bg = null;
    $bgX = $bgY = 0;
    $bgWidth = $this->_width;
    $bgHeight = $this->_height;
    foreach( $originalFrames as $k => $v ) {
      $frame = @imagecreatefromstring($v);
      if( !is_resource($frame) )
        continue;
      $newImg = imagecreatetruecolor($this->_width, $this->_height);
      $this->_prepareGDimage($newImg);
      if( is_resource($bg) ) {
        imagecopy($newImg, $bg, 0, 0, 0, 0, $bgWidth, $bgHeight);
      }

      if( $horizontal ) {
        $result = imagecopyresampled($newImg, $frame, 0, 0, ($this->_width - 1), 0, $this->_width, $this->_height, (0 - $this->_width), $this->_height);
      } else {
        $result = imagecopyresampled($newImg, $frame, 0, 0, 0, ($this->_height - 1), $this->_width, $this->_height, $this->_width, (0 - $this->_height));
      }
      if( !is_resource($bg) ) {
        $bg = imagecreatetruecolor($bgWidth, $bgHeight);
        $this->_prepareGDimage($bg);
      }
      imagecopy($bg, $newImg, 0, 0, 0, 0, $bgWidth, $bgHeight);
      $newImg = imagecreatetruecolor($this->_width, $this->_height);
      $this->_prepareGDimage($newImg);
      imagecopy($newImg, $bg, 0, 0, $bgX, $bgY, $this->_width, $this->_height);
      array_push($newFrames, $newImg);
      $originalFrames[$k] = null;
    }
    $this->_frames = $newFrames;
    $this->_width = imagesx($newFrames[0]);
    $this->_height = imagesy($newFrames[0]);
  }

  protected function _flip($horizontal = true)
  {

    $dst = imagecreatetruecolor($this->_width, $this->_height);

    // Try to preserve transparency
    self::_allocateTransparency($this->_resource, $dst, $this->_format);

    // Flip
    if( $horizontal ) {
      $result = imagecopyresampled($dst, $this->_resource, 0, 0, ($this->_width - 1), 0, $this->_width, $this->_height, (0 - $this->_width), $this->_height);
    } else {
      $result = imagecopyresampled($dst, $this->_resource, 0, 0, 0, ($this->_height - 1), $this->_width, $this->_height, $this->_width, (0 - $this->_height));
    }

    if( !$result ) {
      imagedestroy($result);
      throw new Engine_Image_Adapter_Exception('Unable to rotate image');
    }

    // Now destroy old image and overwrite with new
    imagedestroy($this->_resource);
    $this->_resource = $dst;
    $this->_width = imagesx($this->_resource);
    $this->_height = imagesy($this->_resource);
  }

  // Utility
  protected function _isAnimatedGif()
  {
    return 1 < preg_match_all('/\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)/s', $this->_getFileContent());
  }

  protected function _getFileContent()
  {
    if( $this->_fileContent == null ) {
      $this->_fileContent = file_get_contents($this->_file);
    }
    return $this->_fileContent;
  }

  protected function _prepareGDimage(&$gdimage)
  {
    if( !$this->_meta['trans'] )
      return;
    $transparentNew = imagecolorallocate($gdimage, $this->_meta['tr'], $this->_meta['tg'], $this->_meta['tb']);
    $transparentNewIndex = imagecolortransparent($gdimage, $transparentNew);
    imagefill($gdimage, 0, 0, $transparentNewIndex);
  }

  protected function _checkOpenImage($throw = true)
  {
    $isOpen = true;
    if( $this->_isAnimatedGif() ) {
      $isOpen = count(count($this->_originalFramesMeta) <= 0);
    } else {
      $isOpen = is_resource($this->_resource);
    }

    if( !$isOpen ) {
      if( $throw ) {
        throw new Engine_Image_Adapter_Exception('No open image to operate on.');
      } else {
        return false;
      }
    } else {
      return true;
    }
  }
  // Static

  /**
   * Check if it is safe to open an image (memory-wise)
   * 
   * @param integer $width Width in pixels
   * @param integer $height Height in pixels
   * @param integer $bpp Bytes per pixel
   */
  protected static function _isSafeToOpen($width, $height, $bpp = 4)
  {
    // "Fudge Factor"
    $fudge = 1.2;

    // Calculate used
    if( !function_exists('memory_get_usage') ) {
      $used = 15 * 1024 * 1024; // typical used
    } else {
      $used = memory_get_usage();
    }

    // Calculate limit
    $limit = false;
    if( function_exists('ini_get') ) {
      $limit = ini_get('memory_limit');
    }
    if( -1 == $limit ) {
      return true; // infinite mode
    } elseif( !$limit ) {
      $limit = 32 * 1024 * 1024; // recommended default
    } else {
      $limit = self::_convertBytes($limit);
    }

    // Calculate available and required
    $available = $limit - $used;
    $required = $width * $height * $bpp * $fudge;

    // Whoops, not enough memory
    if( $required > $available ) {
      throw new Engine_Image_Exception(sprintf('Insufficient memory to open ' .
        'image: %d required > %d available (%d limit, %d used)', $required, $available, $limit, $used));
    }
  }

  /**
   * Get supported format info
   * 
   * @return stdClass
   */
  protected static function getSupport()
  {
    if( null === self::$_support ) {
      $info = ( function_exists('gd_info') ? gd_info() : array() );
      $support = new stdClass();

      $support->freetype = !empty($info["FreeType Support"]);
      $support->t1lib = !empty($info["T1Lib Support"]);
      $support->gif = (!empty($info["GIF Read Support"]) && !empty($info["GIF Create Support"]) );
      $support->jpg = (!empty($info["JPG Support"]) || !empty($info["JPEG Support"]) );
      $support->jpeg = $support->jpg;
      $support->png = !empty($info["PNG Support"]);
      $support->wbmp = !empty($info["WBMP Support"]);
      $support->xbm = !empty($info["XBM Support"]);
      $support->bmp = true; // through b/c at bottom

      self::$_support = $support;
    }

    return self::$_support;
  }

  /**
   * Check if a specific image type is supported
   * 
   * @param string $type
   * @param boolean $throw
   * @return boolean
   * @throws Engine_Image_Adapter_Exception If $throw is true and not supported
   */
  protected static function _isSupported($type, $throw = true)
  {
    if( empty(self::getSupport()->$type) ) {
      if( $throw ) {
        throw new Engine_Image_Adapter_Exception(sprintf('Image type %s is not supported', $type));
      }
      return false;
    }
    return true;
  }

  /**
   * Convert short-hand bytes to integer
   * 
   * @param string $value
   * @return integer
   */
  protected static function _convertBytes($value)
  {
    if( is_numeric($value) ) {
      return $value;
    } else {
      $valueLength = strlen($value);
      $qty = substr($value, 0, $valueLength - 1);
      $unit = strtolower(substr($value, $valueLength - 1));
      switch( $unit ) {
        case 'k':
          $qty *= 1024;
          break;
        case 'm':
          $qty *= 1048576;
          break;
        case 'g':
          $qty *= 1073741824;
          break;
      }
      return $qty;
    }
  }

  protected static function _allocateTransparency(&$imgOne, &$imgTwo, $type)
  {
    // GIF
    if( $type == 'gif' ) {
      $transparentIndex = imagecolortransparent($imgOne);
      if( $transparentIndex >= 0 ) {
        $transparentColor = imagecolorsforindex($imgOne, $transparentIndex);
        $transparentIndexTwo = imagecolorallocate($imgTwo, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);
        imagefill($imgTwo, 0, 0, $transparentIndexTwo);
        imagecolortransparent($imgTwo, $transparentIndexTwo);
      }
    } elseif( $type == 'png' ) { // PNG
      imagealphablending($imgTwo, false);
      $transparentColor = imagecolorallocatealpha($imgTwo, 0, 0, 0, 127);
      imagefill($imgTwo, 0, 0, $transparentColor);
      imagesavealpha($imgTwo, true);
    }
  }
}

if( !function_exists('imagecreatefrombmp') ) {

  function imagecreatefrombmp($filename)
  {
    if( !function_exists('imagecreatefromgd') ) {
      return false;
    }

    // Create tmp file
    $src = $filename;
    $dest = $tmpName = tempnam("/tmp", "GD");

    if( !($srcFile = fopen($src, "rb")) ) {
      return false;
    }

    if( !($srcFile = fopen($dest, "wb")) ) {
      return false;
    }

    $header = unpack("vtype/Vsize/v2reserved/Voffset", fread($srcFile, 14));
    $info = unpack("Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant", fread($srcFile, 40));

    extract($info);
    extract($header);

    if( $type != 0x4D42 ) {  // signature "BM"
      @fclose($srcFile);
      @fclose($srcFile);
      @unlink($tmpName);
      return false;
    }

    $paletteSize = $offset - 54;
    $ncolor = $paletteSize / 4;
    $gdHeader = "";
    // true-color vs. palette
    $gdHeader .= ( $paletteSize == 0) ? "\xFF\xFE" : "\xFF\xFF";
    $gdHeader .= pack("n2", $width, $height);
    $gdHeader .= ( $paletteSize == 0) ? "\x01" : "\x00";
    if( $paletteSize ) {
      $gdHeader .= pack("n", $ncolor);
    }
    // no transparency
    $gdHeader .= "\xFF\xFF\xFF\xFF";

    fwrite($srcFile, $gdHeader);

    if( $paletteSize ) {
      $palette = fread($srcFile, $paletteSize);
      $gdPalette = "";
      $j = 0;
      while( $j < $paletteSize ) {
        $b = $palette{$j++};
        $g = $palette{$j++};
        $r = $palette{$j++};
        $a = $palette{$j++};
        $gdPalette .= "$r$g$b$a";
      }
      $gdPalette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);
      fwrite($srcFile, $gdPalette);
    }

    $scanLineSize = (($bits * $width) + 7) >> 3;
    $scanLineAlign = ($scanLineSize & 0x03) ? 4 - ($scanLineSize & 0x03) : 0;

    for( $i = 0, $l = $height - 1; $i < $height; $i++, $l-- ) {
      // BMP stores scan lines starting from bottom
      fseek($srcFile, $offset + (($scanLineSize + $scanLineAlign) * $l));
      $scanLine = fread($srcFile, $scanLineSize);
      if( $bits == 24 ) {
        $gdScanLine = "";
        $j = 0;
        while( $j < $scanLineSize ) {
          $b = $scanLine{$j++};
          $g = $scanLine{$j++};
          $r = $scanLine{$j++};
          $gdScanLine .= "\x00$r$g$b";
        }
      } elseif( $bits == 8 ) {
        $gdScanLine = $scanLine;
      } elseif( $bits == 4 ) {
        $gdScanLine = "";
        $j = 0;
        while( $j < $scanLineSize ) {
          $byte = ord($scanLine{$j++});
          $p = array();
          $p[] = chr($byte >> 4);
          $p[] = chr($byte & 0x0F);
          $gdScanLine .= join('', $p);
        }
        $gdScanLine = substr($gdScanLine, 0, $width);
      } elseif( $bits == 1 ) {
        $gdScanLine = "";
        $j = 0;
        while( $j < $scanLineSize ) {
          $byte = ord($scanLine{$j++});
          $p = array();
          $p[] = chr((int) (($byte & 0x80) != 0));
          $p[] = chr((int) (($byte & 0x40) != 0));
          $p[] = chr((int) (($byte & 0x20) != 0));
          $p[] = chr((int) (($byte & 0x10) != 0));
          $p[] = chr((int) (($byte & 0x08) != 0));
          $p[] = chr((int) (($byte & 0x04) != 0));
          $p[] = chr((int) (($byte & 0x02) != 0));
          $p[] = chr((int) (($byte & 0x01) != 0));
          $gdScanLine .= join('', $p);
        }
        $gdScanLine = substr($gdScanLine, 0, $width);
      }

      fwrite($srcFile, $gdScanLine);
    }

    fclose($srcFile);
    fclose($srcFile);

    // Create from GD
    $img = imagecreatefromgd($tmpName);
    @unlink($tmpName);
    return $img;
  }
}
