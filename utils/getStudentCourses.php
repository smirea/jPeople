<?php

require_once "ldap.php";
require_once "../config.php";

function courseDN($id) {
  return 'CN=GS-CAMPUSNET-COURSE-' 
    . $id 
    . ',OU=Groups,OU=CampusNet,DC=jacobs,DC=jacobs-university,DC=de';
}

$topDN = 'ou=users,ou=campusnet,dc=jacobs,dc=jacobs-university,dc=de';
$hostname = 'jacobs.jacobs-university.de';

$ldap = new LdapConnection('JACOBS\\' . $argv[1], $argv[2], $hostname, $topDN);

$createTable = ' CREATE TABLE IF NOT EXISTS Studying ( '
  . ' id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, '
  . ' cid INT NOT NULL, '
  . ' sid INT NOT NULL, '
  . ' CONSTRAINT fk_courses FOREIGN KEY(cid) REFERENCES Courses(id) '
  . ' ON UPDATE CASCADE ON DELETE CASCADE, '
  . ' CONSTRAINT fk_students FOREIGN KEY(cid) REFERENCES RawData(id) '
  . ' ON UPDATE CASCADE ON DELETE CASCADE '
  . ')'
  ;

mysql_query($createTable) or die(mysql_error());

$mapping = array(); // the final mapping

$students = array();
$result = mysql_query(' SELECT email FROM RawData GROUP BY id');
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $students[] = $row['email'];
}

$courses = array();
$result = mysql_query(' SELECT number FROM Courses GROUP BY id');
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $courses[] = $row['number'];
  $mapping[] = array();
}

$errors = array();
$cnToMail = array(); // speed hack due to UTF8 problem

foreach ($courses as $id => $number) {
  $result = $ldap->query(courseDN($number));
  if (!isset($result[0]['member'])) {
    fwrite(STDERR, $number . '\n');
    continue;
  } else {
    $members = $result[0]['member'];
  }

  for ($i = 0; $i < $members['count']; $i++) {
    /* // could not get the UTF8 to work on displayname ... annoying
    $piece = explode('(', $members[$i]);
    $piece = explode('=', trim($piece[0]));
    $piece = explode('\\', trim($piece[1]));
    $displayname = implode('', $piece);
    $pos = array_search($displayname, $students);
    */

    // check cache
    if (isset($cnToMail[$members[$i]])) {
      $pos = $cnToMail[$members[$i]];
    } else {
      $result = $ldap->query($members[$i], null, array('mail'));
      $mail = $result[0]['mail'][0];
      $pos = array_search($mail, $students);
    }

    if (FALSE === $pos) {
      if (!isset($errors[$id])) {
        $errors[$id] = array(array($mail, $members[$i]));
      } else {
        $errors[$id][] = array($mail, $members[$i]);
      }
    } else {
      $mapping[$id][] = $pos;
    }
  }
}

var_export($errors);

$query =' INSERT INTO Studying (cid, sid) VALUES ';
foreach ($mapping as $cid =>  $students) {
  $cid += 1;
  foreach ($students as $sid) {
    $sid += 1;
    $query .= ' (' . $cid . ',' . $sid . '), ';
  }
}

$query = substr($query, 0, strlen($query) - 2);
mysql_query($query) or die(mysql_error());

?>
