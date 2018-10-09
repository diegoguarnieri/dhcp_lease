<?php

include_once('class/Api.php');

$current_time = new DateTime();
$leases = Api::getDhcpLeaseMikrotik($ip, $user, $password);

$sql2 = "select * from dhcp_lease where status = 'A' and id_company = $id_company"; //alive
$res2 = $con->execSQL($sql2);

while($r2 = $con->getFetch($res2)) {
      $id = $r2['id'];

      $found = false;
      foreach($leases as $i => $host) {
         if($host['.id'] == $r2['id_router'] && $host['mac-address'] == $r2['mac_address']) {
            $find = "/^([0-9]+)m([0-9]*)(s)*/";
            preg_match($find, $host['expires-after'], $matches);

            $sec = (isset($matches[2]) ? $matches[2] : 0);
            $expire = ($matches[1] * 60) + $sec;

            $stop = clone $current_time;
            $stop->add(new DateInterval('PT' . $expire . 'S'));

            $stop_s = $stop->format('Y-m-d H:i:s');

            $sql5 = "update dhcp_lease set stop = '$stop_s' where id = $id";
            $res5 = $con->execSQL($sql5);

            unset($leases[$i]);
            $found = true;
            continue;
         }
      }

      if(!$found) {
         $sql3 = "update dhcp_lease set status = 'D' where id = $id";
         $res3 = $con->execSQL($sql3);
      }
   }

   var_dump($leases);

   foreach($leases as $i => $host) {
      if(isset($host['host-name'])) {
         $hostname = $host['host-name'];
      } else {
         $hostname = '';
      }

      $find = "/^([0-9]+)m([0-9]*)(s)*/";
      preg_match($find, $host['expires-after'], $matches);

      $sec = (isset($matches[2]) ? $matches[2] : 0);
      $expire = ($matches[1] * 60) + $sec;
      $diff = 600 - $expire;

      $start = clone $current_time;
      $start->sub(new DateInterval('PT' . $diff . 'S'));

      $stop = clone $start;
      $stop->add(new DateInterval('PT600S'));

      $start_s = $start->format('Y-m-d H:i:s');
      $stop_s = $stop->format('Y-m-d H:i:s');

      $sql4 = "insert into dhcp_lease
               values(null, $id_company, '{$host['.id']}', '{$host['address']}', '{$host['mac-address']}', '$hostname', '{$host['server']}', '$start_s', '$stop_s', 'A')";
      $res4 = $con->executaSQL($sql4);
}


?>
