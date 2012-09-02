<?php

$campusnetLogin = "https://campusnet.jacobs-university.de/scripts/mgrqispi.dll";

function fullHeaders($referer) {
  $headers = array(
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.3',
    'Accept-Encoding: gzip,deflate,sdch',
    'Accept-Language: en-US,en;q=0.8',
    'Cache-Control: max-age=0',
    'Connection: keep-alive',
    'Content-Type: application/x-www-form-urlencoded',
    'Host: campusnet.jacobs-university.de',
    'Referer: ' . $referer,
    'Origin: https://campusnet.jacobs-university.de',
    'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.1 (KHTML, like Gecko) Ubuntu/10.04 Chromium/14.0.835.202 Chrome/14.0.835.202 Safari/535.1',
  );

  return $headers;
}

function buildLink($base, $next) {
  $baseurl = parse_url($base);
  $result = $baseurl['scheme'] . '://' . $baseurl['host'] . $next;

  return $result;
}

function parseResult($result) {
  $url = null;
  if (FALSE !== strpos($result, "HTTP/1.1 200 OK")) {
    $rows = explode("\n", $result);
    foreach ($rows as $row) {
      $i = strpos($row, "URL");
      if (FALSE !== $i) {
        $url = substr($row, $i + 4);
        break;
      }
    }
    $url = trim($url);

    if ($url[strlen($url) - 1] == ',') {
      $url = substr($url, 0, strlen($url) - 1);
    }

    return $url;
  } else {
    return null;
  }

  
}

function curlGet($page, $referer) {
  $headers = fullHeaders($referer);

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, $page);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_HEADER, 1);

  $result = curl_exec($ch);
  curl_close($ch);

  return $result;
}

function curlLoginPost($page, $referer, $args) {
  $headers = fullHeaders($referer);

  $fields = array(
    'usrname' => $args['username'],
    'pass' => $args['password'],
    'APPNAME' => 'CampusNet',
    'PRGNAME' => 'LOGINCHECK',
    'ARGUMENTS' => urlencode('clino,usrname,pass,menuno,persno,browser,platform'),
    'clino' => '000000000000001',
    'menuno' => '000084',
    'persno' => '00000000',
    'browser' => '',
    'platform' => '',
  );

  $fields_string = '';
  foreach ($fields as $k => $v) {
    $fields_string .= $k . '=' . $v . '&';
  }
  rtrim($fields_string, '&');

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, $page);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);

  curl_close($ch);

  return $result;
}

/**
 * Logs in to campusnet.
 * @param $username the username
 * @param $password the password
 */
function loginToCampusNet($username, $password) {
  global $campusnetLogin;
  $loginReferer = 'https://campusnet.jacobs-university.de/scripts/mgrqispi.dll?APPNAME=CampusNet&PRGNAME=ACTION&ARGUMENTS=-AH9.b5I.TrNDJ4TPD-1D8o4W9Dre9NnYpgktbwHUOEhxBbdGHojwHQGU52k6w39VDryBxwsL.cgqyOTahLUBgl2DsFAtze8d7sfSj371m4iUzGAckQzY-vQHS7vy=';
  $credentials = array(
        "username" => $username,
        "password" => $password,
      );
      
  $result = curlLoginPost($campusnetLogin, $loginReferer, $credentials);
  $nextpage = parseResult($result);
  if (!is_null($nextpage)) {
    $login = buildLink($campusnetLogin, $nextpage);
  } else {
    return null;
  }
  
  $result = curlGet($login, $campusnetLogin);
  $nextpage = parseResult($result);
  if (!is_null($nextpage)) {
    $frontpage = buildLink($login, $nextpage);
  } else {
    return null;
  }
  
  $homepage = curlGet($frontpage, $login);
  return $homepage;
}

?>
