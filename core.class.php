<?php
/**
 * @copyright Alexey Kachalov <alex-kachalov@mail.ru>
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://178.140.61.70/
 * @license GNU Public Licence - Version 3
 */
class core
{
	public function __construct()
	{
		require_once(dirname(__FILE__).'/ini.class.php');
		
		require_once(dirname(__FILE__).'/conf.class.php');
		$this->conf = new conf($this);
		
		require_once(dirname(__FILE__).'/minecraftstatus.class.php');
		$this->author();
	}
	
	/**
	 * Обновление статуса серверов
	 * 
	 * @param [optional]
	 * @param ...
	 * @access public
	 */
	public function update()
	{
		$args = func_get_args();
		//$args = $args[0];
		if(sizeof($args)==0)
		{
			foreach($this->conf->list as $arg => $serv)
			{
				$this->get($serv['name'],$serv['host'],$serv['port'],$arg);
			}
		}
		else 
		{
			/*if(sizeof($args)==1 and is_array($args[0]))
			{
				foreach($args[0] as $arg)
				{
					if(isset($this->conf->list[$arg]))
					{
						$serv = $this->conf->list[$arg];
						$this->get($serv['name'],$serv['host'],$serv['port'],$arg);
					}
				}
			}
			else
			{*/
				if($this->conf->type=='socket')
				{
					foreach($args as $arg)
					{
						if(isset($this->conf->list[$arg]))
						{
							$serv = $this->conf->list[$arg];
							$this->get($serv['name'],$serv['host'],$serv['port'],$arg);
						}
					}
				}
				else
				{
					foreach($args as $arg)
					{
						$this->get($arg,null,null,$arg);
					}
				}
			/*}*/
		}
	}
	
	/**
	 * Получение статуса сервера и обновление картинки
	 * 
	 * @access private
	 * @param string $name название сервера(на картинке)
	 * @param string $host ip сервера
	 * @param string $port порт, который слушает сервер
	 * @param $write[optional] Вывод(false) или запись(имя файла) картинки(по умолчанию false, то есть вывод)
	 */
	private function get($name, $host, $port, $write = false)
	{
		if(@$this->conf->cache[$write]['timestamp']<(time()-$this->conf->cachelife))
		{
			if($this->conf->type=='eo')
			{
				$mysqli = $this->conf->sql;
				$ans = $mysqli->query("SELECT * FROM ".$mysqli->real_escape_string($this->conf->db['table'])." WHERE server='".$mysqli->real_escape_string($write)."'");
				print_r(mysqli_error($mysqli));
				
				$ans = $ans->fetch_array(MYSQLI_BOTH);
				if($ans['max_online']!=0)
				{
					$this->pict($name, $ans['online'], $ans['max_online'], $write);
				}
				else
				{
					$this->pict($name, 0, 0, $write);
				}
			}
			if($this->conf->type=='socket')
			{
				$serv = new MinecraftServerStatus($host, $port, $this->conf->timeout);
				
				if($serv->Online)
				{
					$this->pict($name, $serv->CurPlayers, $serv->MaxPlayers, $write);
				}
				else
				{
					$this->pict($name, 0, 0, $write);
				}
				
				$this->conf->set_cache($write,array(
					'timestamp'=>time(),
					'current'=>$serv->CurPlayers,
					'slots'=>$serv->MaxPlayers,
					//'motd'=>$serv->MOTD
				));
			}
		}
	}
	
	public function author()
	{
		if(isset($_GET['getauthor']))
		die("Created by <a href='http://vk.com/ak1998'>Kachalov Alexey</a>(<a href='http://vk.com/kcraft'>KachalovCRAFT NET</a>)");
	}
	
	/**
	 * Рисование картинки
	 * 
	 * @access private
	 * @param string $name название сервера(на картинке)
	 * @param int $current количество игроков в данный момент на сервере
	 * @param int $max количество слотов
	 * @param $write[optional] Вывод(false) или запись(имя файла) картинки(по умолчанию false, то есть вывод)
	 */
	private function pict($name, $current, $max, $write = false)
	{
		$imgbgfolder = $this->conf->sysf;
		$font = $imgbgfolder.$this->conf->font['name'];
		$textcolor = $this->conf->font['color'];
		$prec_border = $this->conf->precent_border;
		$if_offline = $this->conf->if_server_off;
		$fontsize = 26;
		$arc_cut = 34;
		$zoom = $this->conf->size/125;

		if($max==0)
		{
			$max = 1;
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

		if($write==false)
		{
			//header('Content-Type: image/png');
			imagepng($main2);
		}
		else
		{
			imagepng($main2, $imgbgfolder.'../cache/'.$write.'.png');
		}

		imagedestroy($main);
		imagedestroy($main2);
		imagedestroy($back);
		imagedestroy($front);
		imagedestroy($bar);
		imagedestroy($bar_bg);
	}
}
?>
