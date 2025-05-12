<?php

/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details:
* http://www.gnu.org/licenses/gpl.html
*
*/

class SimpleImage {

   var $image;
   var $image_type;
   var $image_w;
   var $image_h;

   function load($filename) {
   

    $image_info = getimagesize($filename);
	  $this->image_w = $image_info[0];
	  $this->image_h = $image_info[1];
    $this->image_type = $image_info[2];
    
		if(ini_get('memory_limit')<100)
			ini_set('memory_limit','100M');

    
     if( $this->image_type == IMAGETYPE_JPEG ) {
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
         $this->image = imagecreatefrompng($filename);
      }
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {

      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {

         imagegif($this->image,$filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {

         imagepng($this->image,$filename);
      }
      if( $permissions != null) {

         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {

      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {

         imagegif($this->image);
      } elseif( $image_type == IMAGETYPE_PNG ) {

         imagepng($this->image);
      }
   }
   function getWidth() {

      return imagesx($this->image);
   }
   function getHeight() {

      return imagesy($this->image);
   }
   function resizeToHeight($height,$upscaleAllowed=true) {

	  if(!$upscaleAllowed && $height >= $this->image_h ) return; //don't upscale
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }

   function resizeToWidth($width,$upscaleAllowed=true) {
	  if(!$upscaleAllowed && $width >= $this->image_w) return; //don't upscale
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }

   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100;
      $this->resize($width,$height);
   }

   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
	  	$this->setTransparency($new_image); //Joe: this is commented, as turned out that this code breaks the background
	  	//miro not commented anymore as turned out media lib saves all images as JPG (with .png extension)
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
   }

   function setTransparency(&$image_p)
   {
	   if($this->image_type == IMAGETYPE_GIF)
	   {
	   	$trnprt_indx=imagecolortransparent($this->image);
	   if($trnprt_indx>=0)
			{
				$trnprt_color=imagecolorsforindex($this->image,$trnprt_indx);
				$trnprt_indx=imagecolorallocate($image_p,$trnprt_color['red'],$trnprt_color['green'],$trnprt_color['blue']);
				imagefill($image_p,0,0,$trnprt_indx);
				imagecolortransparent($image_p,$trnprt_indx);
			}
		}
		elseif($this->image_type==IMAGETYPE_PNG)
		{
			imagealphablending($image_p,false);
			$color=imagecolorallocatealpha($image_p,0,0,0,127);
			imagefill($image_p,0,0,$color);
			imagesavealpha($image_p,true);
		}
		return;
   }

}
?>

