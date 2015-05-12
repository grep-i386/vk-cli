<?php
/*
VKCLI by grep<DOT>i386#yandex<DOT>com
Under GNU/GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
*/
$help_mess = '
VKCLI v.1.0 beta
(утилита работает через vkapi, и для её работы нужен access-token, получить который можно,перейдя по адресу:
"https://oauth.vk.com/authorize?client_id=3663949&scope=friends,status,messages,offline&redirect_uri=http://oauth.vk.com/blank.html&display=popup&response_type=token"
На этой странице требуется подтвердить доступ, и забрать из адресной строки параметр access_token (утилита не передаёт его никуда кроме vk.com)
Использование:
vk <комманда>
Список комманд:
savekey <key> 			- сохранить ключ
dropkey       			- удалить ключ
lsfriends     			- список друзей
lsdialogs     			- список диалогов
lsnewmsg                - показать новые сообщения
lsdlg <id>	 			- последние 5 сообщений из диалога
lsdialog <id> <len>		- несколько сообщений из диалога
sendmsg <id> <msg>      - послать сообщение в диалог/юзеру
mkonline				- установить статус online на 15 минут (можно добавить в крон :)
setstatus <status>      - установить статус

';
//#######################################################отсутствие команды
if ($_SERVER['argv'][1] == NULL)
{
echo $help_mess;
die();
}
//#######################################################Добавить токен
if ($_SERVER['argv'][1] == "savekey")
{
if ($_SERVER['argv'][2] == NULL )
{
echo "ERROR: Access-token должен быть вторым параметром!\n";
echo $help_mess;
die();
}
file_put_contents($_SERVER['HOME']."/.vkcli.token.txt",$_SERVER['argv'][2]);
die("Сделано!\n");
}
//№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№№Удалить токен
if ($_SERVER['argv'][1] == "dropkey")
{
file_put_contents($_SERVER['HOME']."/.vkcli.token.txt","");
die("Токен удалён\n");
}
//####################################################mkonline
if ($_SERVER['argv'][1] == "mkonline")
{
$token = file_get_contents($_SERVER['HOME']."/.vkcli.token.txt");
$resp = file_get_contents("https://api.vk.com/method/account.setOnline?access_token=".urlencode($token));
if ($resp == NULL){die("ERR: Не удалось соедениться с vk.com. Проверьте интернет!\n");}
$resp = json_decode($resp,true);
if (isset($resp['error']))
{
fetch_error("https://api.vk.com/method/account.setOnline?access_token=".urlencode($token),$resp);
}else{
echo "Онлайн на 15 минут!\n";
}
die();
}
//#######################################################SETSTATUS
if ($_SERVER['argv'][1] == "setstatus")
{
$token = file_get_contents($_SERVER['HOME']."/.vkcli.token.txt");
if (!isset($_SERVER['argv'][2]))
{
echo "WARN: будет установлен пустой статус\n";
$status = "";
}else{
$status = get_all_cmdline($_SERVER['argv'],2);

}

$token = file_get_contents($_SERVER['HOME']."/.vkcli.token.txt");
$resp = file_get_contents("https://api.vk.com/method/status.set?access_token=".urlencode($token)."&text=".urlencode($status));
if ($resp == NULL){die("ERR: Не удалось соедениться с vk.com. Проверьте интернет!\n");}
$resp = json_decode($resp,true);
if (isset($resp['error']))
{
fetch_error("https://api.vk.com/method/status.set?access_token=".urlencode($token)."&text=".urlencode($status),$resp);
}else{
echo "Статус установлен!\n";
}

die();
}

//####################################################### lsfriends
if ($_SERVER['argv'][1] == "lsfriends")
{
$token = file_get_contents($_SERVER['HOME']."/.vkcli.token.txt");
$request = "https://api.vk.com/method/friends.get?access_token=".urlencode($token)."&order=name&fields=city,domain";
$resp = file_get_contents($request);
if ($resp == NULL){die("ERR: Не удалось соедениться с vk.com. Проверьте интернет!\n");}
$resp = json_decode($resp,true);
if (isset($resp['error']))
{
fetch_error($request,$resp);
}else{
//echo "########################################################\n";
//echo "# Фамилия\t#  Имя\t#    ID\t#     В Сети?\t#\n";
//echo "########################################################\n";
//echo "#                                                      #\n";
foreach($resp["response"] as $friend)
{
echo "# {$friend['last_name']} {$friend['first_name']}   [ #{$friend['user_id']}  ]   ";
if ($friend['online'] == "1")
{
echo "online\n";
}else{
echo "offline\n";
}


}
//echo "########################################################\n";


}
die();
}
//############################################################# lsdialogs
if ($_SERVER['argv'][1] == "lsdialogs")
{
$token = file_get_contents($_SERVER['HOME']."/.vkcli.token.txt");
$request = "https://api.vk.com/method/messages.getDialogs?access_token=".urlencode($token)."&count=200&preview_length=70";
$resp = file_get_contents($request);
if ($resp == NULL){die("ERR: Не удалось соедениться с vk.com. Проверьте интернет!\n");}
$resp = json_decode($resp,true);
if (isset($resp['error']))
{
fetch_error($request,$resp);
}else{
$skp = true;
foreach ($resp["response"] as $msg)
{
if ($skp)
{
$skp = false;
}else{

echo id2fio($msg['uid'])." -> ".$msg['body']."\n";



}

}
}
die();
}

//####################################################### lsnewmsg
if ($_SERVER['argv'][1] == "lsnewmsg")
{
$token = file_get_contents($_SERVER['HOME']."/.vkcli.token.txt");
$request = "https://api.vk.com/method/messages.get?access_token=".urlencode($token)."&count=10";
$resp = file_get_contents($request);
if ($resp == NULL){die("ERR: Не удалось соедениться с vk.com. Проверьте интернет!\n");}
$resp = json_decode($resp,true);
if (isset($resp['error']))
{
fetch_error($request,$resp);
}else{

$skp = true;
foreach ($resp["response"] as $msg)
{
if ($skp)
{
$skp = false;
}else{

if ($msg['read_state'] == "0"){
echo id2fio($msg['uid'])." -> ".$msg['body']."\n";
}

}

}
}

die();
}
//############################################################ lsdlg
if ($_SERVER['argv'][1] == "lsdlg")
{
if ($_SERVER['argv'][2] == NULL )
{
echo "ERROR: ID пользователя не должен быть пустым!\n";
echo $help_mess;
die();
}
$token = file_get_contents($_SERVER['HOME']."/.vkcli.token.txt");
$request = "https://api.vk.com/method/messages.getHistory?access_token=".urlencode($token)."&count=5&user_id=".urlencode($_SERVER['argv'][2]);
$resp = file_get_contents($request);
if ($resp == NULL){die("ERR: Не удалось соедениться с vk.com. Проверьте интернет!\n");}
$resp = json_decode($resp,true);
if (isset($resp['error']))
{
fetch_error($request,$resp);
}else{

if ($resp["response"][0] == "0") { die("Нет сообщений от/к пользователю \n"); }

$skp = true;
foreach ($resp["response"] as $msg)
{
if ($skp)
{
$skp = false;
}else{

//if ($msg['read_state'] == "0"){ //вывести только непрочитанные
echo id2fio($msg['uid'])." -> ".$msg['body']."\n";
//}

}

}


}
die();
}
//############################################################ lsdialog
if ($_SERVER['argv'][1] == "lsdialog")
{
if ($_SERVER['argv'][2] == NULL )
{
echo "ERROR: ID пользователя не должен быть пустым!\n";
echo $help_mess;
die();
}
if ($_SERVER['argv'][3] == NULL )
{
echo "ERROR: количество сообщений не должно быть пустым!\n";
echo $help_mess;
die();
}

$token = file_get_contents($_SERVER['HOME']."/.vkcli.token.txt");
$request = "https://api.vk.com/method/messages.getHistory?access_token=".urlencode($token)."&count=".urlencode($_SERVER['argv'][3])."&user_id=".urlencode($_SERVER['argv'][2]);
$resp = file_get_contents($request);
if ($resp == NULL){die("ERR: Не удалось соедениться с vk.com. Проверьте интернет!\n");}
$resp = json_decode($resp,true);
if (isset($resp['error']))
{
fetch_error($request,$resp);
}else{

if ($resp["response"][0] == "0") { die("Нет сообщений от/к пользователю \n"); }

$skp = true;
foreach ($resp["response"] as $msg)
{
if ($skp)
{
$skp = false;
}else{

//if ($msg['read_state'] == "0"){
echo id2fio($msg['uid'])." -> ".$msg['body']."\n";
//}

}

}


}
die();
}
//############################################  SENDMSG
if ($_SERVER['argv'][1] == "sendmsg")
{
if ($_SERVER['argv'][2] == NULL )
{
echo "ERROR: ID пользователя не должен быть пустым!\n";
echo $help_mess;
die();
}
if ($_SERVER['argv'][3] == NULL )
{
echo "ERROR: текст сообщения не должен быть пустым!\n";
echo $help_mess;
die();
}

$token = file_get_contents($_SERVER['HOME']."/.vkcli.token.txt");
$request = "https://api.vk.com/method/messages.send?access_token=".urlencode($token)."&user_id=".urlencode($_SERVER['argv'][2])."&message=".urlencode(get_all_cmdline($_SERVER['argv'],3));
$resp = file_get_contents($request);
if ($resp == NULL){die("ERR: Не удалось соедениться с vk.com. Проверьте интернет!\n");}
$resp = json_decode($resp,true);
if (isset($resp['error']))
{
fetch_error($request,$resp);
}else{
echo "Отправлено!\n";
}
die();
}





echo "Комманда не распознана\n";
echo $help_mess;
die();













//####################################################### Функция для вывода и обработки ошибок (например, капчи)
function fetch_error($request,$error)
{
if ($error['error']['error_code'] == "5")
{
echo "Неверный или устаревший токен!\nЗапустите программу без параметров для просмотра справки\n";
die();
}else{

echo "REQUEST: $request\n";
echo "RESPONSE:\n";
print_r($error);
echo "\n";
}
die();
}
//костыль для считывания всех элементов коммандной строки со смещением на несколько аргументов
function get_all_cmdline($cmdline,$offset)
{
$result = "";
$counter = 0;
foreach($cmdline as $ky=>$cmd)
{
$counter++;
if ($counter > $offset)
{
if ($counter != ($offset+1))
{
$result .= " ";
}
$result .= $cmd;
}

}
return $result;
}

//переобразование id пользователя в его фамилию и имя
function id2fio($uid)
{
$request = "https://api.vk.com/method/users.get?user_ids=$uid&fields=city,vierifies&name_case=Nom";
$resp = file_get_contents($request);
if ($resp == NULL){die("ERR: Не удалось соедениться с vk.com. Проверьте интернет!\n");}
$resp = json_decode($resp,true);
if (isset($resp['error']))
{
fetch_error($request,$resp);
}else{
return $resp['response'][0]['first_name']." ".$resp['response'][0]['last_name'];
}
}


?>
