<?php
/**
 * @copyright Alexey Kachalov <alex-kachalov@mail.ru>
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://178.140.61.70/
 * @license GNU Public Licence - Version 3
 */
class conf
{
	public function __construct($core)
	{
		$this->core = $core;
		
		$this->sysf = dirname(__FILE__).'/sys/';
		$this->ver = 'v2.2';
		$this->font = array(
			'name'=>'minecraft.ttf',
			'width'=>'16',
			'color'=>array(243, 179, 27),
		);
		$this->precent_border = array(33, 66);
		$this->if_server_off = 'OFF';
		$this->timeout = 0.9;
		$this->cachelife = 45;
		
		$this->list = $this->get_list();
		$this->cache = $this->get_cache();
	}
	
	/**
	 * Получение кеша
	 * 
	 * @access private
	 */
	private function get_cache()
	{
		$this->author_protect();
		$cache = new ini($this->sysf.'cache.ini');
		if(empty($cache->file))return false;
		
		$arr = array();
		if(sizeof($cache->content)==0)return $arr;
		foreach($cache->content as $id=>$info)
		{
			$arr[$id] = array('timestamp'=>$info['timestamp'],'min'=>$info['current'],'max'=>$info['slots'],'motd'=>$info['motd']);
		}
		return $arr;
	}
	
	/**
	 * Запись/обновление кеша
	 * 
	 * @access private
	 * @param string $name уникальное имя сервера
	 * @param array $params 
	 */
	public function set_cache($name,$params)
	{
		$cache = new ini($this->sysf.'cache.ini');
		if(empty($cache->file))return false;
		
		$cache->content[$name] = $params;
		$cache->write();
	}
	
	/**
	 * Получение списка серверов
	 * 
	 * @access private
	 * @param string [optional] уникальное имя сервера/серверов
	 */
	private function get_list()
	{
		$cache = new ini($this->sysf.'list.ini');
		if(empty($cache->file))return false;
		
		$arr = array();
		if(sizeof($cache->content)==0)return $arr;
		foreach($cache->content as $id=>$info)
		{
			$arr[$id] = array('name'=>$info['name'],'host'=>$info['ip'],'port'=>$info['port']);
		}
		return $arr;
	}
	
	private function author_protect()
	{
		if(!method_exists($this->core,'author'))
		die;
	}
}
?>