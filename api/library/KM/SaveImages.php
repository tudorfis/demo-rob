<?php

class KM_SaveImages
{
   protected static $_allowedExts = array('gif','jpeg', 'jpg', 'png');
   protected static $_allowedTypes = array('image/gif', 'image/jpeg', 'image/jpg', 'image/pjpeg', 'image/x-png', 'image/png');
   protected static $_folderPath = "files/images";
   protected static $_thumb_width = 200;
   protected static $_thumb_height = 150;
   
   /**
   * Rotates the image on mobile versions
   * 
   * @param mixed $image
   * @param mixed $encodedString
   */
   protected static function _exifRotate($image, $encodedString) 
   {
       $exif = @exif_read_data($encodedString);
       if(isset($exif['Orientation'])) {
           switch($exif['Orientation']) {
               case 8:
                   $image = imagerotate($image,90,0);
                   break;
               case 3:
                   $image = imagerotate($image,180,0);
                   break;
               case 6:
                   $image = imagerotate($image,-90,0);
                   break;
           }
       }

       return $image;

   }
   
   /**
   * Save encoded base64 image
   * 
   * @param mixed $image_src
   * @param mixed $image_type
   * @param mixed $file_type
   */
   public static function saveImageBase64($image_src, $image_type, $thumb_width = '', $thumb_height = '')
   {
       $response = array();

       $encodedString = utf8_decode($image_src);
       if ($image_type == 'image/jpeg') {
           $file_ext = 'jpg';
           $image = imagecreatefromjpeg($encodedString);
       } else if ($image_type == 'image/png') {
           $file_ext = 'png';
           $image = imagecreatefrompng($encodedString);
       } else if ($image_type == 'image/gif') {
           $file_ext = 'gif';
           $image = imagecreatefromgif($encodedString);
       } else {
           die;
       }
       
       // Make thumbail 
       $thumb_width = (!empty($thumb_width)) ? $thumb_width : self::$_thumb_width;
       $thumb_height = (!empty($thumb_height)) ? $thumb_height : self::$_thumb_height;

       // EXIF
       $image = self::_exifRotate($image, $encodedString);
       
       $width = imagesx($image);
       $height = imagesy($image);
       
       $original_aspect = $width / $height;
       $thumb_aspect = $thumb_width / $thumb_height;

       if ( $original_aspect >= $thumb_aspect ) {
           // If image is wider than thumbnail (in aspect ratio sense)
           $new_height = $thumb_height;
           $new_width = $width / ($height / $thumb_height);
       } else {
           // If the thumbnail is wider than the image
           $new_width = $thumb_width;
           $new_height = $height / ($width / $thumb_width);
       }


       $thumb = imagecreatetruecolor( $thumb_width, $thumb_height );
        
       imagealphablending($thumb, false);
       imagesavealpha($thumb, true);
       
       // Resize and crop
       imagecopyresampled($thumb,
                           $image,
                           0, // - ($new_width - $thumb_width) / 2, // Center the image horizontally
                           0, //- ($new_height - $thumb_height) / 2, // Center the image vertically
                           0, 0,
                           $new_width, $new_height,
                           $width, $height);
        
        // Save path
        $uniqid =  uniqid();
        $file_path = self::$_folderPath."/". $uniqid .".".$file_ext;
        $file_path_thumb = self::$_folderPath."/". $uniqid ."_thumb.".$file_ext;;
        $file_path_absolute = APPLICATION_PATH . "/../public/";

        
        // Create image   
        if ($image_type == 'image/jpeg') {
           $image_result = imagejpeg($image, $file_path_absolute.$file_path, 80);
           $thumb_result = imagejpeg($thumb, $file_path_absolute.$file_path_thumb, 80);
        } else if ($image_type == 'image/png') {
        
            imagealphablending($image, false);
            imagesavealpha($image, true);
            
            $image_result = imagepng($image, $file_path_absolute.$file_path, 8);
            $thumb_result = imagepng($thumb, $file_path_absolute.$file_path_thumb, 8);
            
        } else if ($image_type == 'image/gif') {
           $image_result = imagegif($image, $file_path_absolute.$file_path, 80);
           $thumb_result = imagegif($thumb, $file_path_absolute.$file_path_thumb, 80);
        }
        
        if ($image_result && $thumb_result) 
        {

             $tbl = KM_Abstract::f('files');
             $image = $tbl->createRow();

             $image->path = $file_path;
             $image->path_thumb = $file_path_thumb;
             $image->width = $width;
             $image->width_thumb = $new_width;
             $image->height = $height;
             $image->height_thumb = $new_height;
             $image->mime_type = $image_type;
             $image->status_id = 1;

             $image->save();
             return $image->toArray();
        };
        
        return null;
   }
   
   /**
   * Gets images from pages and zones
   * on admin, needs improvement
   *  
   * @param mixed $files
   */
   public static function getImages($files, $file_type = Table_Abstract::FILE_TYPE_STATIC, $with_gallery = true) 
   {
        if (isset($_FILES[$files]) && !empty($_FILES[$files]["name"][0])) 
        {
            $files = $_FILES[$files];
            $albumImagesString = '';
            $profile_image_id = ''; 
            
            foreach ($files['name'] as $key => $fileName)
            {
                $tempImg = explode(".", $fileName);
                $extImg = strtolower(end($tempImg));
                $typeImg = strtolower($files["type"][$key]);
                
                if ( in_array($typeImg, self::$_allowedTypes) &&
                    in_array($extImg, self::$_allowedExts)) {

                    $imgPath = self::$_folderPath."/".uniqid().".".$extImg;
                    if (move_uploaded_file($files["tmp_name"][$key], $_SERVER['DOCUMENT_ROOT'].'/'.$imgPath)) {

                        if ($extImg == 'jpeg' || $extImg == 'jpg') {
                            $image = imagecreatefromjpeg($_SERVER['DOCUMENT_ROOT'].$imgPath);
                        } else if ($extImg == 'png') {
                            $image = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'].$imgPath);
                        } else if ($extImg == 'gif') {
                            $image = imagecreatefromgif($_SERVER['DOCUMENT_ROOT'].$imgPath);
                        } else {
                            die;
                        }

                        $width = imagesx($image);
                        $height = imagesy($image);

                        $filesTable = new Table_Files();
                        $image = $filesTable->createRow()
                                        ->setPath($imgPath)
                                        ->setFileType($file_type)
                                        ->setStatus(Table_Abstract::STATUS_ACTIVE)
                                        ->setWidth($width)
                                        ->setHeight($height);
                        if ($image_id = $image->save()) {
                            $albumImagesString .= $image_id.',';  
                        }

                        /** REMOVE **/
                        if (empty($profile_image_id)) {
                            $profile_image_id = $image_id;
                        }
                    }
                }    
            }
            
            // Set gallery if more than one image
            if (count($files['name']) == 1 || !$with_gallery) {
                $gallery_id = null;   
            } else {
                $galleryTable = new Table_Gallery();
                $gallery = $galleryTable->createRow();
                $gallery->setImages(substr($albumImagesString, 0, -1));
                $gallery->setStatus(Table_Abstract::STATUS_ACTIVE);
                $gallery_id = $gallery->save();    
            }

            return  array(
                        'image_id' => $profile_image_id,
                        'gallery_id' => $gallery_id);
        
        }
        return array();
   }
   
    /**
    * Save image from URL
    * 
    * @param $url
    * @return image path
    */
    public static function saveImageFromUrl($url)
    {
        if ( preg_match('/https?\:/', $url) 
            || preg_match('/www\./', $url)) {
                
            $tempImg = explode(".", $url);
            $extImg = strtolower(end($tempImg));

            if (in_array($extImg, self::$_allowedExts)) 
            {
                $imgPath = self::$_folderPath."/".uniqid().".".$extImg;
                $imgContent = file_get_contents($url);
                if (file_put_contents($_SERVER['DOCUMENT_ROOT'].'/'.$imgPath , $imgContent)) {
                    return $imgPath;
                }
            }         
        }

        return null;
    }

}