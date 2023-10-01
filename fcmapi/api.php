<?php
$ch = curl_init("https://fcm.googleapis.com/fcm/send");
$header = array("Content-Type:application/json","Authorization: key=AAAAR1BRYhY:APA91bEam8BTpMY_TlCuXcEIozoHAN4pEdFogD0X7qMk7D0HrNkfcjMX8Sb9BXwqF8jn1ZAU1Rzoupeq8fENvnx_Qt8kQivsvxVXzaF3merU5N1Fb0VsSKh3lEV65xcT0pJHWTwyLeLI");
$data = json_encode(array("to"=>"/topics/allDevices","notification"=>array("title"=>$_REQUEST['title'],"body"=>$_REQUEST["message"])));
curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($ch,CURLOPT_POST,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
curl_exec($ch);
?>