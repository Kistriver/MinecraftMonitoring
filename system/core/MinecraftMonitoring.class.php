<?php
/**
 * @copyright Alexey Kachalov <kachalov92@yandex.ru>
 * @author Alexey Kachalov <kachalov92@yandex.ru>
 * @access public
 * @see http://kistriver.com/
 * @license GNU Public Licence - Version 3
 */

namespace Kistriver\libs;
class MinecraftMonitoring
{
	private $confs, $list, $cache;
	const VER = '3.0';

	public function __construct()
	{
		$confs = @file_get_contents(dirname(__FILE__).'/../confs/core.json');
		if($confs===false)throw new \Exception('Could not load core.json');
		$confs = @json_decode($confs,true);
		if(json_last_error()!==JSON_ERROR_NONE)
			throw new \Exception('Could not parse core.json: json error #'.json_last_error());
		$this->confs = $confs;

		$confs = @file_get_contents(dirname(__FILE__).'/../confs/list.json');
		if($confs===false)throw new \Exception('Could not load list.json');
		$confs = @json_decode($confs,true);
		if(json_last_error()!==JSON_ERROR_NONE)
			throw new \Exception('Could not parse list.json: json error #'.json_last_error());
		$this->list = $confs;

		$confs = @file_get_contents(dirname(__FILE__).'/../cache/cache.json');
		if($confs===false)throw new \Exception('Could not load cache.json');
		$confs = @json_decode($confs,true);
		if(json_last_error()!==JSON_ERROR_NONE)
			throw new \Exception('Could not parse cache.json: json error #'.json_last_error());
		$this->cache = $confs;
	}

	public function getConfs(){return $this->confs;}
	public function getList(){return $this->list;}
	public function getCache(){return $this->cache;}

	public function info($id, $cache = true)
	{
		static $db = array();

		if(!isset($this->list[$id]))return false;
		$server = $this->list[$id];
		if(!isset($server['type']))return false;

		if(isset($this->cache[$id]))
		if($cache!==false && $this->cache[$id]['timestamp']>=
			time()-(isset($this->cache[$id]['cachelife'])?
				$this->cache[$id]['cachelife']:
				$this->confs[$server['type']]['cachelife'])
		)
		{
			$return  = array();

			$return['online'] = $this->cache[$id]['online'];
			$return['host'] = $this->cache[$id]['host'];
			$return['port'] = $this->cache[$id]['port'];
			$return['name'] = $this->cache[$id]['name'];
			if($return['online']==true)
			{
				$return['cur'] = $this->cache[$id]['cur'];
				$return['max'] = $this->cache[$id]['max'];
			}

			return $return;
		}

		switch($server['type'])
		{
			case 'sockets':
				require_once(dirname(__FILE__).'/libs/minecraftstatus.class.php');
				$info = new \MinecraftServerStatus(
					$server['host'],
					isset($server['port'])?$server['port']:$this->confs['sockets']['port'],
					isset($server['timeout'])?$server['timeout']:$this->confs['sockets']['timeout']
				);

				$return = array();
				if($info->Online)
				{
					$return = array
					(
						'online'=>true,
						'max'=>$info->MaxPlayers,
						'cur'=>$info->CurPlayers,
					);
				}

				$this->setCache($id,$return);
				return $this->info($id);
				break;

			case 'db':
				$mysqli = isset($db[$id])?$db[$id]:@new \mysqli(
					isset($server['db']['host'])?$server['db']['host']:$this->confs['db']['host'],
					isset($server['db']['user'])?$server['db']['user']:$this->confs['db']['user'],
					isset($server['db']['pass'])?$server['db']['pass']:$this->confs['db']['pass'],
					isset($server['db']['db'])?$server['db']['db']:$this->confs['db']['db']
				);
				if($mysqli->connect_errno)
					throw new \Exception('Could not get info: mysqli error #'.$mysqli->connect_errno);
				$mysqli->set_charset("utf8");

				$table = $mysqli->real_escape_string(
					isset($server['db']['table'])?$server['db']['table']:$this->confs['db']['table']);
				$query = "SELECT online,max_online FROM {$table} WHERE server=?";
				$pr = $mysqli->prepare($query);
				if(!$pr)throw new \Exception('Could not get info: mysqli error #'.$mysqli->errno);

				$server_name = isset($server['db']['server'])?$server['db']['server']:$this->confs['db']['server'];
				$pr->bind_param("s", $server_name);
				$pr->execute();
				$pr->bind_result($online, $max_online);
				$pr->fetch();

				$return = array();
				if($max_online!=0 && $online!=-1)
				{
					$return = array
					(
						'online'=>true,
						'max'=>$max_online,
						'cur'=>$online,
					);
				}

				$this->setCache($id,$return);
				return $this->info($id);
				break;

			case 'minequery':
				require_once(dirname(__FILE__).'/libs/minequery.class.php');
				$info = \Minequery::query(
					$server['host'],
					isset($server['mq_port'])?$server['mq_port']:$this->confs['minequery']['port'],
					isset($server['timeout'])?$server['timeout']:$this->confs['minequery']['timeout']
				);

				$return = array();
				if($info!==false)
				{
					$return = array
					(
						'online'=>true,
						'max'=>$info->PlayerCount,
						'cur'=>$info->CurPlayers,
					);
				}

				$this->setCache($id,$return);
				return $this->info($id);
				break;

			default:
				throw new \Exception('Undefined type of monitoring: '.$this->list[$id]['type']);
				break;
		}
	}

	protected function setCache($id,$p=array())
	{
		if(!isset($this->list[$id]))throw new \Exception('Could not update cache: ID error');
		$server = $this->list[$id];

		$cache = array(
			'online'=>false,
			'host'=>$server['host'],
			'port'=>$server['port'],
			'name'=>$server['name'],
			'max'=>null,
			'cur'=>null,
			'timestamp'=>time(),
			'cachelife'=>isset($this->cache[$id]['cachelife'])?
				$this->cache[$id]['cachelife']:
				$this->confs[$server['type']]['cachelife'],
		);

		foreach($p as $k=>$v)
			$cache[$k] = $v;

		$this->cache[$id] = $cache;
		if(!@file_put_contents(dirname(__FILE__).'/../cache/cache.json',json_encode($this->cache)))
		throw new \Exception('Could not update cache: write error');
	}
}
