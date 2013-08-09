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
		if(sizeof($args)==0)
		{
			foreach($this->conf->list as $arg => $serv)
			{
				$this->get($serv['name'],$serv['host'],$serv['port'],$arg);
			}
		}
		else 
		{
			if(sizeof($args)==1 and is_array($args[0]))
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
			$serv = new ms($host, $port, $this->conf->timeout);
			
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
				'motd'=>$serv->MOTD
			));
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
	 * 
	 * FIXME: Рваные края статусбара
	 */
	private function pict($name, $current, $max, $write = false)
	{
		$imgbgfolder = $this->conf->sysf;
		$font = $imgbgfolder.$this->conf->font['name'];
		$fw = $this->conf->font['width'];
		$textcolor = $this->conf->font['color'];
		$prec_border = $this->conf->precent_border;
		$if_offline = $this->conf->if_server_off;
		
		$players_max = $max;
		$players = $current;
		
		if($players_max==0)
		{
			$players_max = 1;
			$players = $if_offline;
		}
		
		$prec = floor($players/$players_max*100);
		
		if($players>=$players_max)
			$prec = 100;
		
		$angle = floor($prec*360/100);
		$width = 132; $height = 132;/*img size*/
		$im = imagecreatetruecolor($width, $height+30);
		$imbg1 = imagecreatefrompng($imgbgfolder.'bg.png');
		$imbg2 = imagecreatefrompng($imgbgfolder.'bg2.png');
		
		if($prec_border[0]>=$prec)
		{
		$imgcol = 'greenbar.png';
		$barbg = 'greenbg.png';
		}
		elseif($prec_border[1]>=$prec)
		{
		$imgcol = 'yellowbar.png';
		$barbg = 'yellowbg.png';
		}
		else
		{
		$imgcol = 'redbar.png';
		$barbg = 'redbg.png';
		}
		
		$imbg3 = imagecreatefrompng($imgbgfolder.$imgcol);
		$imbg4 = imagecreatefrompng($imgbgfolder.$barbg);
		
		imagefill($im, 0, 0, imagecolorallocatealpha($im,0, 0, 0, 127));
		
		imagesavealpha($im, true);
		
		$fontsize = 16;
		$fh = 6;
		
		imagecopy ($im, $imbg1, 0, 0, 0, 0, $width, $height);
		imagecopy ($im, $imbg3, 0, 0, 0, 0, $width, $height);
		
		if($prec!=100)imagefilledarc($im, $height/2, $width/2, $height-17, $width-17, -90+$angle, -90, imagecolorallocate($im, 1, 1, 1), IMG_ARC_PIE);
		
		imagecopy ($im, $imbg2, 0, 0, 0, 0, $width, $height);
		imagecopy ($im, $imbg4, 0, 0, 0, 0, $width, $height);
		
		$tw1 = ($width - strlen($players)*$fw)/2;
		$curcolor1 = imagecolorallocate($im, $textcolor[0], $textcolor[1], $textcolor[2]);
		imagettftext($im, $fontsize, 0, $tw1, $width/2+$fh, $curcolor1, $font, $players);
		
		$tw2 = ($width - strlen($name)*($fw-2))/2;
		$curcolor2 = imagecolorallocate($im, $textcolor[0], $textcolor[1], $textcolor[2]);
		imagettftext($im, $fontsize, 0, $tw2, $width/2+$fh+80, $curcolor2, $font, $name);
		
		if($write==false)
		{
			//header('Content-Type: image/png');
			imagepng($im);
		}
		else
		{
			imagepng($im, $imgbgfolder.'../cache/'.$write.'.png');
		}
		
		imagedestroy($im);
	}
}
?>
