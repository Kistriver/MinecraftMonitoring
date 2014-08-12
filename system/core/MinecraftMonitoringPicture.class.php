<?php
/**
 * @copyright Alexey Kachalov <kachalov92@yandex.ru>
 * @author Alexey Kachalov <kachalov92@yandex.ru>
 * @access public
 * @see http://kistriver.com/
 * @license GNU Public Licence - Version 3
 */

namespace Kistriver\libs;
class MinecraftMonitoringPicture
{
	protected $MinecraftMonitoring;

	public function __construct()
	{
		require_once(dirname(__FILE__).'/MinecraftMonitoring.class.php');
		$this->MinecraftMonitoring = new MinecraftMonitoring();
	}

	public function getMinecraftMonitoring(){return $this->MinecraftMonitoring;}

	public function drawInOutput($id)
	{
		$mm = $this->MinecraftMonitoring;

		if(!is_array($id))$id = array($id);

		$cur = 0;
		$max = 0;

		foreach($id as $i)
		{
			$info = $mm->info($i);
			if(sizeof($info)==0 || $info===false)continue;
			if($info['online']==false)continue;
			$cur += $info['cur'];
			$max += $info['max'];
		}

		$r = $this->draw($cur,$max);
		header('Content-Type: image/png');
		imagepng($r);
	}

	public function drawInFile($id)
	{
		$mm = $this->MinecraftMonitoring;

		if(!is_array($id))$id = array($id);

		$cur = 0;
		$max = 0;

		foreach($id as $i)
		{
			$info = $mm->info($i);
			if(sizeof($info)==0 || $info===false)return false;
			if($info['online']==false)continue;
			$cur += $info['cur'];
			$max += $info['max'];
		}

		$r = $this->draw($cur,$max);
		sort($id);
		imagepng($r,dirname(__FILE__).'/../cache/img/'.implode('_',$id).'.png');
		return implode('_',$id).'.png';
	}

	protected function draw($current,$max)
	{
		$mm = $this->MinecraftMonitoring;
		$confs = $mm->getConfs();

		$imgbgfolder = dirname(__FILE__).'/theme/';
		$font = $imgbgfolder.$confs['font']['name'];
		$textcolor = $confs['font']['color'];
		$prec_border = $confs['precent_border'];
		$if_offline = $confs['if_server_off'];
		$fontsize = 26;
		$arc_cut = 34;
		$zoom = $confs['size']/125;

		if($max==null)
		{
			$max = 1;
			$current = 0;
			$current = $if_offline;
		}

		$prec = floor( $current/$max*100);

		if($current>=$max)
			$prec = 100;

		$angle = floor($prec*360/100);
		$width = $height = 250;


		$main = imagecreatetruecolor($width, $height);
		imagefill($main, 0, 0, imagecolorallocatealpha($main,0, 0, 0, 127));
		imagesavealpha($main, true);

		$main2 = imagecreatetruecolor($width/2*$zoom, $height/2*$zoom);
		imagefill($main2, 0, 0, imagecolorallocatealpha($main2,0, 0, 0, 127));
		imagesavealpha($main2, true);

		$back = imagecreatefrompng($imgbgfolder.'bg.png');
		$front = imagecreatefrompng($imgbgfolder.'bg2.png');

		if($prec_border[0]>=$prec)
		{
			$bar_color = 'green';
		}
		elseif($prec_border[1]>=$prec)
		{
			$bar_color = 'yellow';
		}
		else
		{
			$bar_color = 'red';
		}

		$bar = imagecreatefrompng($imgbgfolder.$bar_color .'bar.png');
		$bar_bg = imagecreatefrompng($imgbgfolder.$bar_color .'bg.png');

		$center_w = $width/2;
		$center_h = $height/2;
		$box = imagettfbbox($fontsize, 0, $font, $current);
		$position_w = $center_w-($box[2]-$box[0])/2;
		$position_h = $center_h-($box[7]-$box[1])/2;

		imagecopy($main, $back, 0, 0, 0, 0, $width, $height);
		imagecopy($main, $bar, 0, 0, 0, 0, $width, $height);

		if($prec!=100)imagefilledarc($main, $height/2, $width/2, $height-$arc_cut, $width-$arc_cut, -90+$angle, -90, imagecolorallocate($main, 1, 1, 1), IMG_ARC_PIE);

		imagecopy($main, $front, 0, 0, 0, 0, $width, $height);
		imagecopy($main, $bar_bg, 0, 0, 0, 0, $width, $height);

		$bar_text = imagecolorallocate($main, $textcolor[0], $textcolor[1], $textcolor[2]);
		imagettftext($main, $fontsize, 0, $position_w, $position_h, $bar_text, $font, $current);

		/*$tw2 = ($width - strlen($name)*($fw-2))/2;
		$curcolor2 = imagecolorallocate($im, $textcolor[0], $textcolor[1], $textcolor[2]);
		imagettftext($im, $fontsize, 0, $tw2, $width/2+$fh+80, $curcolor2, $font, $name);*/

		imagecopyresampled($main2,$main,0,0,0,0,$width/2*$zoom,$height/2*$zoom,$width,$height);

		imagedestroy($main);
		imagedestroy($back);
		imagedestroy($front);
		imagedestroy($bar);
		imagedestroy($bar_bg);

		return $main2;
	}
}
