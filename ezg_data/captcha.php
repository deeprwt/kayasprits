<?php
$version="ezgenerator captcha - 1.7";
/*
	captcha.php
	http://www.ezgenerator.com
	Copyright (c) 2004-2015 Image-line
*/

class CaptchaClass
{
	protected $c_captcha_img_type;
	protected $f_captcha_size;
	protected $f_proj_id;
	protected $f_numeric;

	public function __construct($imageType,$size,$pid)
	{
		$this->c_captcha_img_type=strtolower($imageType);
		$this->f_captcha_size=$size;
		$this->f_proj_id=$pid;
		$this->f_numeric=strpos($this->f_captcha_size,'number')!==false;
	}

	protected function f_draw_captcha($captcha)
	{
		$sa=array(16,25,'csmall.gif',1,2,17,
			 25,30,'cmedium.gif',3,2,17,
			 45,50,'clarge.gif',4,6,21,
			 16,25,'cnumbersblue.gif',1,3,26,
			 16,25,'cnumbersgray.gif',1,3,26
			 );

		$ss=array_search('c'.$this->f_captcha_size.'.gif',$sa)-2;
		$h=$sa[$ss]+2;
		$w=(($sa[$ss+1]*4)+2)+$sa[$ss+5];
		$im=imagecreate($w,$h);

		$src=imagecreatefromgif($sa[$ss+2]);
		$clr1=imagecolorallocate($im,255,255,255);

		imagerectangle($im,0,0,$w-1,$h-1,$clr1);
		for($i=0;$i<strlen($captcha);$i++)
	 	{
			$char=substr($captcha,$i,1);
			$or=ord($char)-($this->f_numeric?48:65);
			$yas2=$sa[$ss+3]==0?0:rand(-1,$sa[$ss+3]);
			imagecopy($im,$src,($i*$sa[$ss+1])+1,1,0,($or*$sa[$ss])+$yas2,$sa[$ss+1],$sa[$ss]);
		}
		$or=$this->f_numeric?10:26;
		$orl=$ss==10?-1:1;
		$orr=$sa[$ss+5];

		imagecopy($im,$src,(4*$sa[$ss+1])+$orl,1,0,($or*$sa[$ss]),$orr,$sa[$ss]);

		$img_type=(function_exists("image".$this->c_captcha_img_type))?
					$this->c_captcha_img_type:
					((function_exists("imagegif"))?
							  'gif':
							  (function_exists("imagejpeg"))?
									'jpeg':
									((function_exists("imagepng")))?
											 'png':'');
		if($img_type!='')
		{
			header("Content-type: image/$img_type");
			header("Cache-Control: no-cache");
			header("Pragma: no-cache");
			if($img_type=='gif')
				imagegif($im);
			elseif($img_type=='jpeg')
				imagejpeg($im);
			elseif($img_type=='png')
				imagepng($im);
		}
		imagedestroy($im);
	}

	protected function f_generate_captcha_code2()
	{
		$str="";
		if($this->f_numeric)
			 for($i=0;$i<4;$i++) $str.=chr(rand(48,57));
		else
			 for($i=0;$i<4;$i++) $str.=chr(rand(97,122));
		$str=strtoupper($str);

		$ssp='';
		if(($ssp!='')&&(strpos($ssp,'%SESSIONS_')===false))
			session_save_path($ssp);
		session_name('PHPSESSID'.$this->f_proj_id);
		session_start();
		$_SESSION['CAPTCHA_CODE']=md5($str);
		session_write_close();
		return $str;
	}

	public function process()
	{
		$code=$this->f_generate_captcha_code2();
		if(isset($_GET['asStr']))
			print $code;
		else
			$this->f_draw_captcha($code);
	}

}

$captcha=new CaptchaClass('gif','numbersgray','425655185872917');
$captcha->process();


?>
