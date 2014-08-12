<?php
namespace Kistriver\libs;

ini_set('display_errors',"1");
ini_set('display_startup_errors',"1");
ini_set('log_errors',"1");
ini_set('html_errors',"0");

require_once(dirname(__FILE__).'/system/core/MinecraftMonitoringPicture.class.php');

try
{
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
				//Uncomment second parameter(false) to force updating
				$r = $mmp->getMinecraftMonitoring()->info($p/*,false*/);
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
			if(!defined('JSON_PRETTY_PRINT'))define('JSON_PRETTY_PRINT',0);
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
				<meta name="Description" content="Radial monitoring by Kistriver" />
				<meta name="robots" content="all,follow" />
				<meta name="author" content="Kachalov" />
				<meta name="copyright" content="Kistriver" />
				<title>Radial monitoring by Kistriver</title>
				<style type="text/css">
					body { color: #333333; background: #e7e7e8; font-size: 14px; font-family: Arial;
						background-image: url('http://test.kcraft.su/client/style/img/main/bg.jpg') }
					body a { color: #0088cc; text-decoration: none; }
					body a:hover { color: #005580; text-decoration: underline; }
					body div.main { margin: 7% auto 5%; max-width: 500px;}
					body h1 { text-align: center; }
					body h3 { margin: 40px auto auto 0; }

					.code div.ch {border: 1px #ccc dashed; border-bottom: none; font-weight: bold; background:
						#f3f3f4; padding: 9px 6px;}
					.code .cb {margin: 0;border: 1px #ccc dashed; overflow: auto; background: #f3f3f4; padding: 12px
					6px;}
					.code {}
					iframe{border: 1px #999 dashed; width:100%;}
				</style>

				<!-- Yandex.Metrika counter -->
				<script type="text/javascript">(function (d, w, c) {    (w[c] = w[c] || []).push(function() {        try {            w.yaCounter22027237 = new Ya.Metrika({id:22027237,                    webvisor:true,                    clickmap:true,                    trackLinks:true,                    accurateTrackBounce:true});        } catch(e) { }    });    var n = d.getElementsByTagName("script")[0],        s = d.createElement("script"),        f = function () { n.parentNode.insertBefore(s, n); };    s.type = "text/javascript";    s.async = true;    s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";    if (w.opera == "[object Opera]") {        d.addEventListener("DOMContentLoaded", f, false);    } else { f(); }})(document, window, "yandex_metrika_callbacks");</script><noscript><div><img src="//mc.yandex.ru/watch/22027237" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
				<!-- /Yandex.Metrika counter -->

			</head>
		<body>
		<div class="main">
		<?php if(isset($_GET['author'])){ ?>
			<h1>Author</h1>
			<p style="text-align: center;">Created by <a href='http://vk.com/ak1998'>Kachalov Alexey</a>(<a href='http://vk.com/kistriver'>Kistriver</a>)</p>
		<?php }elseif(isset($_GET['version'])){ ?>
			<h1>Version</h1>
			<p style="text-align: center;"><?php echo MinecraftMonitoring::VER; ?></p>
		<?php }else{ ?>
			<h1>Radial monitoring by Kistriver</h1>

<h3>1. Picure</h3>
			<p>Получаем картинку из кеша. Если ее там нет, то создаем.</p>
<p style="text-align: center;">
	<img src="?act=picture&params=example" />
	<img src="?act=picture&params=example2" />
	<img src="?act=picture&params=example3" />
	<img src="?act=picture&params=example,example2,example3" />
</p>
<div class="code">
	<div class="ch">
		CODE:
	</div>
	<pre class="cb">&lt;img src="<a target="_blank" href="?act=picture&params=example">?act=picture&amp;params=example</a>" /&gt;
&lt;img src="<a target="_blank" href="?act=picture&params=example2">?act=picture&amp;params=example2</a>" /&gt;
&lt;img src="<a target="_blank" href="?act=picture&params=example3">?act=picture&amp;params=example3</a>" /&gt;
&lt;img src="<a target="_blank" href="?act=picture&params=example,example2,example3">?act=picture&amp;params=example,example2,example3</a>" /&gt;</pre>
</div>

			<h3>2. Get</h3>
			<p>Создаем картинку "на лету". Может работать медленно.</p>
			<p style="text-align: center;">
				<img src="?act=get&params=example" />
				<img src="?act=get&params=example2" />
				<img src="?act=get&params=example3" />
				<img src="?act=get&params=example,example2,example3" />
			</p>
			<div class="code">
				<div class="ch">
					CODE:
				</div>
	<pre class="cb">&lt;img src="<a target="_blank" href="?act=get&params=example">?act=get&amp;params=example</a>" /&gt;
&lt;img src="<a target="_blank" href="?act=get&params=example2">?act=get&amp;params=example2</a>" /&gt;
&lt;img src="<a target="_blank" href="?act=get&params=example3">?act=get&amp;params=example3</a>" /&gt;
&lt;img src="<a target="_blank" href="?act=get&params=params=example,example2,example3">?act=get&amp;params=example,example2,example3</a>" /&gt;</pre>
			</div>

			<h3>3. Update</h3>
			<p>Обновляем кеш.</p>
			<p style="text-align: center;">
				<iframe src="?act=update&params=example">IFRAME</iframe>
				<iframe src="?act=update&params=example2">IFRAME</iframe>
				<iframe src="?act=update&params=example3">IFRAME</iframe>
				<iframe src="?act=update&params=example,example2,example3">IFRAME</iframe>
			</p>
			<div class="code">
				<div class="ch">
					CODE:
				</div>
	<pre class="cb">&lt;iframe src="<a target="_blank" href="?act=update&params=example">?act=update&amp;params=example</a>" style="display: none;"&gt;&lt;/iframe&gt;
&lt;iframe src="<a target="_blank" href="?act=update&params=example2">?act=update&amp;params=example2</a>" style="display: none;"&gt;&lt;/iframe&gt;
&lt;iframe src="<a target="_blank" href="?act=update&params=example3">?act=update&amp;params=example3</a>" style="display: none;"&gt;&lt;/iframe&gt;
&lt;iframe src="<a target="_blank" href="?act=update&params=example,example2,example3">?act=update&amp;params=example,example2,example3</a>" style="display: none;"&gt;&lt;/iframe&gt;
</pre>
			</div>

			<h3>4. Info</h3>
			<p>Получение информации.</p>
			<p style="text-align: center;">
				<iframe src="?act=info&params=example">IFRAME</iframe>
				<iframe src="?act=info&params=example2">IFRAME</iframe>
				<iframe src="?act=info&params=example3">IFRAME</iframe>
				<iframe src="?act=info&params=example,example2,example3">IFRAME</iframe>
			</p>
			<div class="code">
				<div class="ch">
					Примечание:
				</div>
	<div class="cb">Можно использовать в JS скриптах для создания мониторинга на CSS и JS. Например: <a
			target="_blank" href="?act=info&params=example">?act=info&amp;params=example</a>.</div>
			</div>

			<!--<p>Используйте <a target="_blank" href="http://<?php echo $_SERVER['HTTP_HOST']
			.$_SERVER['SCRIPT_NAME'];	?>?act=picture&params=example">эту ссылку</a>
				для получения изображения из кеша(обновится, если cachelife подойдет к концу). Измените example на ID своего сервера.</p>
			<p>Чтобы обновить кеш, используйте <a target="_blank" href="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; ?>?act=update&params=example">эту ссылку</a>.</p>
			<p>Для получения картинки без кеширования оной используйте <a target="_blank" href="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; ?>?act=get&params=example">эту ссылку</a>.</p>
			<p>А если потребуется подробная информация о сервере, то используйте <a target="_blank" href="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; ?>?act=info&params=example">эту ссылку</a>
				для получения информации в формате JSON.</p>-->

			<p style="text-align: center;">Powered by <a href="http://kcraft.su/">KachalovCRAFT</a></p>
			</div>
			</body>
			</html>
			<?php }
			break;
	}
}
catch(\Exception $e)
{
	echo "<b>Exception was thrown:</b> ";
	echo $e->getMessage();
	echo "<br />\r\n";
	echo "Stack trace: ";
	echo "<br />\r\n";
	foreach($e->getTrace() as $k=>$tr)
	{
		$tr['file'] = str_replace(dirname(__FILE__),'<i>[MonitoringRoot]</i>',$tr['file']);
		echo "<b>#{$k}</b> {$tr['file']}:{$tr['line']}->{$tr['function']}(";
		echo implode(',',$tr['args']);
		echo ")";
	}
}
