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
		$this->ver = 'v2.3';
		
		$config = new ini($this->sysf.'config.ini');
		
		if($config===false)
		{
			$this->font = array(
				'name'=>'minecraft.ttf',
				'width'=>'16',
				'color'=>array(243, 179, 27),
			);
			$this->precent_border = array(33, 66);
			$this->if_server_off = 'OFF';
			$this->timeout = 0.9;
			$this->cachelife = 45;
			$this->type = 'socket';
		}
		else
		{
			$c = $config->content;
			$this->font = array(
				'name'=>$c['font']['name'],
				'width'=>$c['font']['width'],
				'color'=>explode(',',$c['font']['color']),
			);
			$this->precent_border = explode(',',$c['main']['precent_border']);
			$this->if_server_off = $c['main']['if_server_off'];
			$this->timeout = $c['main']['timeout'];
			$this->cachelife = $c['main']['cachelife'];
			$this->type = $c['main']['type'];
			
			$this->db = array(
				'host'=>$c['sql']['host'],
				'port'=>$c['sql']['port'],
				'user'=>$c['sql']['user'],
				'pass'=>$c['sql']['pass'],
				'db'=>$c['sql']['db'],
				'table'=>$c['sql']['table'],
			);
		}
		
		if($this->type=='eo')
		{
			$this->sql();
		}
		else
		{
			$this->list = $this->get_list();
			$this->cache = $this->get_cache();
		}
	}
	
	public function sql()
	{
		$db = $this->db;
		$mysqli = new mysqli(/*"*/$db['host']/*:$db[port]"*/,$db['user'],$db['pass'],$db['db']);
		if($mysqli->connect_errno) trigger_error($mysqli->connect_errno, E_USER_ERROR);
		$mysqli->set_charset("utf8");
		$this->sql = $mysqli;
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