<?php
require APP . 'Vendor/autoload.php';
use Imagine\Image\ImageInterface;
use Imagine\Image\Box;
use Imagine\Image\Point;

class ImageComponent extends Component {
	function makePath($path) {
		if(!is_dir($path)) {
            $newPath = '';
            $dirs = explode('/', $path);
            foreach($dirs as $dir) {
                $newPath .= $dir.'/';
                if(!is_dir($newPath)) {
                    mkdir($newPath);
                }
            }
		}
	}

	function __add($image, $path, $prefix = '', $maxWidth = 500, $maxHeight = 500) {
		$return = array();
        if(!$image['error']) {
            $imageName = $prefix.$image['name'];
            $this->resize($image['tmp_name'], $path, $imageName, $maxWidth, $maxHeight);
            $return['name'] = '/'.$path.'/'.$imageName;
        }else {            
            switch($image['error']) {
                case 1:
                case 2:
                    $error = __('Image is too big');
                    break;
                case 3:
                    $error = __('An error occur while uploading');
                    break;
                case 4:
                    $return['name'] = null;
                    break;
            }
            if(!empty($error)) {
                $this->Session->setFlash($error, 'flash_error');  
                $return['error'] = $error;
            }
        }

        return $return;
	}

	function resize($source, $path, $fileName, $maxWidth = 500, $maxHeight = 500) {
		$imagine = new Imagine\Gd\Imagine($source);
		$image = $imagine->open($source);
		$imageSize  = $image->getSize();
		if($maxWidth || $maxHeight) {
			if(!$maxHeight && $imageSize->getWidth() > $maxWidth) {
				$image->resize($imageSize->widen($maxWidth));
			}elseif(!$maxWidth && $imageSize->getHeight() > $maxHeight) {
				$image->resize($imageSize->heighten($maxHeight));
			}elseif($maxWidth && $maxHeight) {
				$image->resize(new Box($maxWidth, $maxHeight));
			}
		}
		$this->makePath($path);
		$image->save($path.'/'.$fileName);
	}
}