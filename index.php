<?php

	$hostname_bd="localhost";
	$login_bd="login";
	$password_bd="password";
	$name_bd="db_name";

@mysql_connect ($hostname_bd, $login_bd, $password_bd) or die ("Noconnection with DB!");
@mysql_select_db ($name_bd) or die ("No select DB!");
mysql_query("SET NAMES cp1251");



// Проверяем текущую сессию	
if($_COOKIE['sid'] && !isset($_GET['exit'])){
	$get_sid = mysql_real_escape_string($_COOKIE['sid']);
	$res_auth = mysql_fetch_assoc(mysql_query("SELECT id, login FROM lan_users WHERE sid = '$get_sid'"));
	if(trim($res_auth['id']) !=""){
		$result = "<h1>Привет, ".$res_auth['login']."! <a href=\"?exit\">Выход</a></h1>";
		$wall = 1;
	} else {setcookie ("sid", $sid, time()-(3600*24));}
}

// Инициализирован выход
if(isset($_GET['exit'])) {
	setcookie ("sid", $sid, time()-(3600*24));
	$wall = 0;
}

// Инициализирована регистрация
if(trim($_GET['reg_login']) != "" && trim($_GET['reg_pwd']) != "") {
	$reg_login = mysql_real_escape_string($_GET['reg_login']);
	$reg_pwd = md5(md5($_GET['reg_pwd']).$_GET['reg_pwd']);
	$reg_query = "INSERT INTO `lan_users` (`id`, `login`, `pwd`) VALUES (NULL, '$reg_login', '$reg_pwd');";
	mysql_query($reg_query) or die("Ошибка запроса 'reg_query'");
	$result = "<h1>Регистрация прошла успешно!</h1>";
} 

// Инициалищирована авторизация
if(trim($_GET['login']) != "" && trim($_GET['pwd']) != "") {
	$login = mysql_real_escape_string($_GET['login']);
	$pwd = md5(md5($_GET['pwd']).$_GET['pwd']);
	$auth_query = "SELECT id, login FROM lan_users WHERE login = '$login' AND pwd = '$pwd';";
	$res_auth = mysql_fetch_assoc(mysql_query($auth_query));
	
	// Если пользователь существует, сохраняем его сессию на 24 часа
	if(trim($res_auth['id']) != ""){
		$sid = md5(time());
		setcookie ("sid", $sid, time()+(3600*24));
		$sid_query = "UPDATE lan_users SET sid = '$sid' WHERE id = $res_auth[id]";
		mysql_query($sid_query) or die("Ошибка в запросе 'sid_query'");
		$result = "<h1>Привет, ".$res_auth['login']."! <a href=\"?exit\">Выход</a></h1>";
		$wall = 1;
	} else {$result = "<h1>Неверный логин или пароль</h1>";}

}

?>

<html>
	<head>
		<title>Главная страница</title>
		<style>
			.login {margin: 10px; padding: 10px; border: 1px #000 solid; width: 300px;}
			.login input {width: 100%;}
			.new_record {width: 300px;}
			.new_record textarea {width: 100%; height: 100px; margin-bottom: 10px; padding: 5px;}
			.new_record input {width: 100%;}
			.hcontent {display: none;}
			.hcontent textarea, input {width: 100%; margin-bottom: 10px;}
			.content  {border: 1px #eee solid; padding: 10px; margin: 5px;}
			.records {width: 300px; margin-top: 50px;}
			.time {font-size: 12px; padding-right: 10px;}
			.controls {text-align: right; margin-bottom: 30px;}
		</style>
	</head>
	<body>
		<?= $result; ?>
		<?php
			if($wall != 1){ // Стена закрыта
		?>
		<div class="auth login">
			<p>Авторизация</p>
			<form>
				<p><input type="text" name="login" value="<?= $_GET['login']?>" placeholder="Логин"></p>
				<p><input type="password" name="pwd" value="<?= $_GET['pwd']?>" placeholder="Пароль"></p>
				<p><input type="submit" value="Войти"></p>
			</form>
		</div>
		<p>или...</p>
		<div class="reg login">
			<p>Регистрация</p>
			<form>
				<p><input type="text" name="reg_login" placeholder="Логин"></p>
				<p><input type="password" name="reg_pwd" placeholder="Пароль"></p>
				<p><input type="submit" value="Зарегистрироваться"></p>
			</form>
		</div>
		<?php
			} else { // Пользователь авторизован - стена открыта
		?>
		
		
		
		<div class="new_record">
			
			<form>
				<textarea name="record"></textarea>
				<input type="submit" value="Добавить запись">
			<form/>
			<?php
				if(trim($_GET['record']) != ""){
					$text = mysql_real_escape_string($_GET['record']);
					$time = time();
					mysql_query("INSERT INTO lan_content (`id`, `text`) VALUES (NULL, '$text');") or die("Ошика в запросе 'new_record'");
				}
			?>
			
		
		</div>
		<div class="records">
			<h2>Существующие записи</h2>
			<?php
			
			
			
				if(trim($_GET['up_record']) != ""){
				
					$text = mysql_real_escape_string($_GET['up_text']);
					$id = mysql_real_escape_string($_GET['up_record']);
					
					mysql_query("UPDATE lan_content SET text = '$text' WHERE id = $id;") or die("Ошибка в запросе 'up_record'");
				
				}
			
				$get_records = mysql_query("SELECT * FROM lan_content;");
				$i = 0;
				while($record = mysql_fetch_assoc($get_records)){
					$i++;
					?>
						
						<div class="content" id="content_<?=$record['id'];?>">
							<?= $record['text'];?>
						</div>
						<div class="hcontent" id="hcontent_<?=$record['id'];?>">
							<form>
								<textarea name="up_text"><?= $record['text'];?></textarea>
								<input name="up_record" value="<?=$record['id'];?>" type="hidden">
								<input type="submit">
							</form>
						</div>
						<div class="controls">
							<span class="time"><?= $record['date'];?></span>
							<a href="#" onClick="getElementById('content_<?=$record[id];?>').style.display='none';getElementById('hcontent_<?=$record[id];?>').style.display='block'; return false;">изменить</a>
						</div>
						
					<?
				}
				if($i == 0) {echo "Записей пока нет. Добавь первую.";}
			
			?>
		</div>

		<?php
			}
		?>
	</body>
</html>