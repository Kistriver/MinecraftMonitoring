<?php
/**
 * @copyright Alexey Kachalov <alex-kachalov@mail.ru>
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://178.140.61.70/
 * @license GNU Public Licence - Version 3
 */
class ini
{
	public $file;
	public $content;
	
	public function __construct($file = null)
	{
		if(empty($file))return;
		return $this->open($file);
	}
	
	public function open($file)
	{
		if(!file_exists($file) OR !is_readable($file))return false;
		
		$parse = parse_ini_file($file,true);
		if($parse===false)return false;
		
		$this->file = $file;
		$this->content = $parse;
		return true;
	}
	
	public function close()
	{
		$s = $this->write();
		if(!$s)return false;
		$this->file = null;
		$this->content = null;
		return true;
	}
	
	public function write()
	{
		$result = '';
		foreach ($this->content as $name=>$section)
		{
			$result .= "[$name]\r\n";
			
			foreach ($section as $key=>$value)
			{
				$result .= $key.'='.$value."\r\n";
			}
			$result .= "\r\n";
		}
		
		file_put_contents($this->file, $result);
		return true;
	}
}
?>