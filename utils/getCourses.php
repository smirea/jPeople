<?php

// include the db connection file...
require_once(dirname(__FILE__) . "/../config.php");

function curlGet($page) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $page);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  $result = curl_exec($ch);

  curl_close($ch);

  return $result;
}

function resolveHref($page, $href) {
  $baseurl = parse_url($page);

  return $baseurl["scheme"] . "://" . $baseurl["host"] . $href;
}

function getRegistrations($page) {
  $content = curlGet($page);

  $doc = new DOMDocument();
  @$doc->loadHTML($content);
  $result = array();

  $ul = $doc->getElementById("auditRegistration_list");
  // find all the li elements
  if (isset($ul)) {
    $lis = $ul->childNodes;
    for ($i = 0 ; $i < $lis->length ; ++$i) {
      $li = $lis->item($i);
      $children = $li->childNodes;
      // disregard null
      if(! $children) {
        continue;
      }

      // get all the right anchors
      for($j = 0 ; $j < $children->length ; ++$j) {
        if ($children->item($j)->getAttribute("class") == "auditRegNodeLink"
            && $children->item($j)->nodeName == "a") {
          $a = $children->item($j);

          // now with the href in place, recurse...
          $href = $a->getAttribute("href");
          $result[$a->nodeValue] = getRegistrations(resolveHref($page, $href));
          break;
        }
      }
      // break; // here for testing later :)
    }
  }

  if (empty($result)) {
    $result["courses"] = getCourses($page);
  }

  return $result;
}

function getCourses($page) {
  $content = curlGet($page);

  $doc = new DOMDocument();
  @$doc->loadHTML($content);

  $tables = $doc->getElementsByTagName("table");
  $result = array();

  for ($i = 0 ; $i < $tables->length ; ++$i) {
    $table = $tables->item($i);
    //item(0) is tbody
    $rows = $table->childNodes;
    for ($j = 0 ; $j < $rows->length ; ++$j) {
      $tr = $rows->item($j);
      if ($tr->nodeName == "tr"
          && $tr->getAttribute("class") == "tbdata") {
        $data = $tr->childNodes;

        for ($k = 0 ; $k < $data->length ; ++$k) {
          $td = $data->item($k);
          $children = $td->childNodes;
          if (! $children) {
            continue;
          }

          for ($l = 0 ; $l < $children->length ; ++$l) {
            if ($children->item($l)->nodeName == "a") {
              $a = $children->item($l);
              $courseName = $a->nodeValue;

              $href = $a->getAttribute("href");
              $coursePage = resolveHref($page, $href);
              $result[$courseName]["link"] = $coursePage;
              $result[$courseName]["information"] = getCourseInfo($coursePage);

              // hack to remove the unwanted node after processing
              $td->removeChild($a);

              $professors = explode(";",trim($td->textContent));
              array_walk($professors, function(&$arg, $key) { $arg = trim($arg); });
              $result[$courseName]["professors"] = $professors;

              break;
            }
          }
        }
      }
    }
  }

  return $result;
}

function getCourseInfo($page) {
  $result = array();
  $content = curlGet($page);
  $doc = new DOMDocument();
  @$doc->loadHTML($content);

  $tables = $doc->getElementsByTagName("table");

  for ($i = 0 ; $i < $tables->length ; ++$i) {
    $table = $tables->item($i);

    if ($table->getAttribute("courseid")) {
      $result['description'] = $doc->saveXML($table);
    }

    if ($i == 2) {
      $root = $tables->item(2);

      $appointments = array();
      $rows = $root->getElementsByTagName('tr');
      if ($rows->length < 4) { // hack due to more darn campusnet shit :|
        $result['appointments'] = array();
        continue;
      }

      for ($i = 1; $i < $rows->length; $i++) {
        $row = $rows->item($i)->childNodes;
        $appointments[] = array(
          'date' => $row->item(2*1)->nodeValue,
          'room' => $row->item(2*4)->childNodes->item(1)->nodeValue,
          'start' => $row->item(2*2)->nodeValue,
          'end' => $row->item(2*3)->nodeValue,
        );
      }
      $result['appointments'] = $appointments;
    }
  }

  return $result; 
}

function decypherCourseName($coursename) {
  $details = explode(" ", $coursename, 2);
  return array(
      "id" => $details[0],
      "name" => trim($details[1]),
  );
}

function populateDatabase($page) {
  $information = getRegistrations($page);
  file_put_contents("serial.txt", serialize($information));
  //$information = unserialize(file_get_contents("serial.txt"));

  $idschools = array(
      "SHSS" => 1,
      "SES" => 2,
      "USC" => 3,
      "FOUNDATION" => 4,
      "BIGSSS" => 5,
      "LANGUAGE" => 6,
  ); // map from all our schools to their ids -- for lower
  // kind of a problematic hack to check for schools and stuff...

  $majors = array(); // majorname => school
  $professors = array(); // professorname
  $courses = array(); // coursename => array(desc, array(major), array(professor))
  foreach ($information as $school => $details) {
    $majorlist = array();
    if (FALSE !== strpos($school, "Humanities")) {
      $school = "SHSS";
      foreach ($details as $when => $what) {
        $majorlist = array_merge($majorlist, $what);
      }
    } else if (FALSE !== strpos($school, "Engineering")) {
      $school = "SES";
      foreach ($details as $when => $what) {
        $majorlist = array_merge($majorlist, $what);
      }
    } else if (FALSE !== strpos($school, "University Studies Courses")) {
      $majorlist = array($school => $details);
      $school = "USC";
    } else if (FALSE !== strpos($school, "Foundation")) {
      $majorlist = array( $school => $details);
      $school = "FOUNDATION";
    } else if (FALSE !== strpos($school, "BIGSSS")) {
      $majorlist = array($school => $details);
      $school = "BIGSSS";
    } else if (FALSE !== strpos($school, "Language")) {
      $school = "LANGUAGE";
      $majorlist = $details;
    } else {
      error_log("Unsuported yet...");
    }

    foreach ($majorlist as $major => $courselist) {
      // set majors => school
      if (! isset($majors[$major])) {
        $majors[$major] = $school;
      }

      foreach ($courselist["courses"] as $coursename => $info) {
        // break down course name
        $course = decypherCourseName($coursename);

        // get course information ; we already have id and name
        $course["description"] = $info['information']["description"];
        $course["link"] = $info["link"];
        $course["appointments"] = $info['information']['appointments'];
        $course["professors"] = $info["professors"];

        // take care of each professor
        foreach ($course["professors"] as $profname) {
          if (! in_array($profname, $professors)) {
            $professors[] = $profname;
          }
        }

        // take care of courses now
        if (! isset($courses[$course["name"]])) {
          $courses[$course["name"]] = array(
              "id" => $course["id"],
              "majors" => array($major),
              "link" => $course["link"],
              "description" => $course["description"],
              "appointments" => $course["appointments"],
              "professors" => $course["professors"],
              );
        } else {
          // handle the majors
          if (! in_array($major, $courses[$course["name"]]["majors"])) {
            $courses[$course["name"]]["majors"][] = $major;
          }
          // handle the professors
          $courses[$course["name"]]["professors"] = array_unique(array_merge(
              $courses[$course["name"]]["professors"],
              $course["professors"]
              ));
        }
      }
    }
  }

  sort($professors);
  asort($majors);
  asort($courses);

  $idprofessors = array(); // map from profname to db id
  $idmajors = array(); // map from majorname to db id
  $idcourses = array(); // set of all course ids (stupid campusnet...)

  // INSERT SCHOOLS
  $query = "INSERT INTO Schools (id, name) VALUES ";
  foreach ($idschools as $school => $id) {
    $query .= sprintf('("%d","%s"), ', $id, trim($school) );
  }
  $query = substr($query, 0, strlen($query) - 2);
  mysql_query($query) or var_export(mysql_error());


  // INSERT PROFESSORS
  $id = 1;
  $query = "INSERT INTO Professors (id, name) VALUES ";
  foreach ($professors as $prof) {
    $idprofessors[$prof] = $id;
    $query .= sprintf('("%d","%s"), ',
        $id++,
        trim(mysql_real_escape_string($prof)) );
  }
  $query = substr($query, 0, strlen($query) - 2);
  mysql_query($query) or var_export(mysql_error());

  // INSERT MAJORS
  $id = 1;
  $query = "INSERT INTO Majors (id, name, school_id) VALUES ";
  foreach ($majors as $major => $school) {
    $idmajors[$major] = $id;
    $query .= sprintf('("%d","%s", "%s"), ',
        $id++,
        trim(mysql_real_escape_string($major)),
        $idschools[$school]);
  }
  $query = substr($query, 0, strlen($query) - 2);
  mysql_query($query) or var_export(mysql_error());

  // INSERT COURSE & RELATED
  $id = 0;
  $queryCourses = "INSERT INTO Courses (id, number, name, link, description) VALUES ";
  $queryAppointments = "INSERT INTO Appointments (cid, date, start, end, room) VALUES ";
  $queryTeaching = "INSERT INTO Teaching (cid, pid) VALUES ";
  $queryStructure = "INSERT INTO Structure (cid, mid) VALUES ";
  foreach ($courses as $course => $details) {
    // I FUCKING HATE ADMIN COURSE MANAGEMENT!!!
    // they have same course number for different courses...
    // if (! in_array($details["id"], $idcourses)) {
    //   $idcourses[] = $details["id"];
    // } else {
    //   continue;
    // }
    $id++;
    $idcourses[$details["id"]] = $id;

    // INSERT COURSES
    $queryCourses .= sprintf('("%d","%s","%s","%s","%s"), ',
        $id,
        trim( mysql_real_escape_string($details["id"]) ),
        trim( mysql_real_escape_string($course) ),
        trim( mysql_real_escape_string($details["link"]) ),
        trim( mysql_real_escape_string($details["description"]) )
    );

    // INSERT COURSE <-> APPOINTMENT
    foreach ($details['appointments'] as $app) {
      $queryAppointments .= sprintf('("%d","%s","%s","%s","%s"), ',
          $id,
          trim( mysql_real_escape_string($app['date']) ),
          trim( mysql_real_escape_string($app['start']) ),
          trim( mysql_real_escape_string($app['end']) ),
          trim( mysql_real_escape_string($app['room'])) );
    }
    // INSERT COURSE <-> PROFESSOR
    foreach ($details["professors"] as $prof) {
      $queryTeaching .= sprintf('("%d","%d"), ',
          trim( mysql_real_escape_string($idcourses[$details["id"]]) ),
          trim( mysql_real_escape_string($idprofessors[$prof]) ) 
      );
    }

    // INSERT COURSE <-> MAJOR
    foreach ($details["majors"] as $major) {
      $queryStructure .= sprintf('("%d","%d"), ',
          trim( mysql_real_escape_string($idcourses[$details["id"]]) ),
          trim( mysql_real_escape_string($idmajors[$major]) )
      );
    }
  }
  $queryCourses = substr($queryCourses, 0, strlen($queryCourses) - 2);
  $queryAppointments = substr($queryAppointments, 0, strlen($queryAppointments) - 2);
  $queryTeaching = substr($queryTeaching, 0, strlen($queryTeaching) - 2);
  $queryStructure = substr($queryStructure, 0, strlen($queryStructure) - 2);
  mysql_query($queryCourses) or var_export(mysql_error());
  mysql_query($queryAppointments) or var_export(mysql_error());
  mysql_query($queryTeaching) or var_export(mysql_error());
  mysql_query($queryStructure) or var_export(mysql_error());
}

$pages = array(
  "https://campusnet.jacobs-university.de/scripts/mgrqispi.dll?APPNAME=CampusNet&PRGNAME=ACTION&ARGUMENTS=-A7u0Og-I6rB.SlXNbR8pOTb5UBShIxXTbTjd4gZmN6vGv66yeRfFuU8TPRd4ohIhc0KvI3ssCftcsp1MQqwHPV2sjFxfr-.uCYxqWK-6e1hiDo3nDYOj1tIZtpTN=",
);

populateDatabase( $pages[0] );

?>
