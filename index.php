<?php
namespace KCRAFT\libs;

ini_set('display_errors',"1");
ini_set('display_startup_errors',"1");
ini_set('log_errors',"1");
ini_set('html_errors',"0");

require_once(dirname(__FILE__).'/system/core/MinecraftMonitoringPicture.class.php');

$mmp = new MinecraftMonitoringPicture();
switch(@$_GET['act'])
{
	//Получение картинки
	case 'get':
		if(!isset($_GET['params']))$_GET['params'] = '';

		$params = explode(',',$_GET['params']);

		$ar = array();
		foreach($params as $p)
			$ar[$p] = $p;
		$params = $ar;

		$mmp->drawInOutput($params);
		break;

	//Обновление кеша
	case 'update':
		if(!isset($_GET['params']))$_GET['params'] = '';

		$params = explode(',',$_GET['params']);

		$ar = array();
		foreach($params as $p)
			$ar[$p] = $p;
		$params = $ar;

		$ra = array();
		foreach($params as $p)
		{
			$r = $mmp->getMinecraftMonitoring()->info($p,false);
			$r = $mmp->drawInFile($p);
			$ra[] = $r?'<span style="color: green;">[OK]</span>':'<span style="color: red;">[FAILED]</span>';
		}
		echo implode("<br />\r\n",$ra);
		break;

	//Получение информации в формате JSON
	case 'info':
		if(!isset($_GET['params']))$_GET['params'] = '';

		$params = explode(',',$_GET['params']);

		$ar = array();
		foreach($params as $p)
			$ar[$p] = $p;
		$params = $ar;

		$ra = array();
		foreach($params as $p)
		{
			$r = $mmp->getMinecraftMonitoring()->info($p);
			$ra[$p] = $r;
		}
		echo json_encode($ra, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		break;

	//Обновление картинки и переадресация на кеш
	case 'picture':
		if(!isset($_GET['params']))$_GET['params'] = '';

		$params = explode(',',$_GET['params']);

		$ar = array();
		foreach($params as $p)
			$ar[$p] = $p;
		$params = $ar;

		sort($params);
		$img = implode('_',$params).'.png';
		if(!file_exists(dirname(__FILE__).'/system/cache/img/'.$img))
		$img = $mmp->drawInFile($params);
		header("Location: ".str_replace('index.php','',$_SERVER['PHP_SELF'])."system/cache/img/".$img);
		break;

	default:
		?>
			<!DOCTYPE html>
			<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
				<meta name="Description" content="Radial monitoring by KachalovCRAFT" />
				<meta name="robots" content="all,follow" />
				<meta name="author" content="Kachalov" />
				<meta name="copyright" content="KachalovCRAFT NET" />
				<link rel="shortcut icon" href="http://test.kcraft.su/client/style/img/main/favicon.ico">
				<title>Radial monitoring by KachalovCRAFT</title>
				<style type="text/css">
					body { color: #333333; background: #e7e7e8; font-size: 14px; font-family: Arial; }
					body a { color: #0088cc; text-decoration: none; }
					body a:hover { color: #005580; text-decoration: underline; }
					body div { margin: 15% auto; }
					body h1, body p { text-align: center; }
				</style>

				<!-- Yandex.Metrika counter -->
				<script type="text/javascript">(function (d, w, c) {    (w[c] = w[c] || []).push(function() {        try {            w.yaCounter22027237 = new Ya.Metrika({id:22027237,                    webvisor:true,                    clickmap:true,                    trackLinks:true,                    accurateTrackBounce:true});        } catch(e) { }    });    var n = d.getElementsByTagName("script")[0],        s = d.createElement("script"),        f = function () { n.parentNode.insertBefore(s, n); };    s.type = "text/javascript";    s.async = true;    s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";    if (w.opera == "[object Opera]") {        d.addEventListener("DOMContentLoaded", f, false);    } else { f(); }})(document, window, "yandex_metrika_callbacks");</script><noscript><div><img src="//mc.yandex.ru/watch/22027237" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
				<!-- /Yandex.Metrika counter -->

			</head>
		<body>
		<div>
		<?php if(isset($_GET['author'])){ ?>
			<h1>Author</h1>
			<p>Created by <a href='http://vk.com/ak1998'>Kachalov Alexey</a>(<a href='http://vk.com/kcraft'>KCRAFT</a>)</p>
		<?php }elseif(isset($_GET['version'])){ ?>
			<h1>Version</h1>
			<p><?php echo MinecraftMonitoring::VER; ?></p>
		<?php }else{ ?>
			<h1>Radial monitoring by KachalovCRAFT</h1>
			<p>Используйте <a target="_blank" href="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; ?>?act=picture&params=example">эту ссылку</a>
				для получения изображения из кеша(обновится, если cachelife подойдет к концу). Измените example на ID своего сервера.</p>
			<p>Чтобы обновить кеш, используйте <a target="_blank" href="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; ?>?act=update&params=example">эту ссылку</a>.</p>
			<p>Для получения картинки без кеширования оной используйте <a target="_blank" href="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; ?>?act=get&params=example">эту ссылку</a>.</p>
			<p>А если потребуется подробная информация о сервере, то используйте <a target="_blank" href="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; ?>?act=info&params=example">эту ссылку</a>
				для получения информации в формате JSON.</p>
			</div>
			</body>
			</html>
		<?php }
		break;
}