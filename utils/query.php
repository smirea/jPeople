<?php

function getSchools() {
  $query = "SELECT * FROM Schools s";
  $result = mysql_query($query) or die(mysql_error());
  $schools = array();

  while ($row = mysql_fetch_assoc($result)) {
    $schools[] = $row;
  }

  return $schools;
}

function getMajorsBySchool($schoolid) {
  $query = sprintf('SELECT m.id, m.name '
      . ' FROM Majors m '
      . ' JOIN Schools s ON s.id = "%d" '
      . ' WHERE m.school_id = "%d"',
      mysql_real_escape_string($schoolid),
      mysql_real_escape_string($schoolid));
  $result = mysql_query($query) or die(mysql_error());
  $majors = array();

  while ($row = mysql_fetch_assoc($result)) {
    $majors[] = $row;
  }

  return $majors;
}

function getCoursesByMajor($majorid) {
  $query = sprintf('SELECT c.id, c.name '
    . ' FROM Courses c '
    . ' JOIN Structure s ON s.mid = "%d"'
    . ' WHERE s.cid = c.id',
    mysql_real_escape_string($majorid));
  $result = mysql_query($query) or die(mysql_error());
  $courses = array();

  while ($row = mysql_fetch_assoc($result)) {
    $courses[] = $row;
  }

  return $courses;
}

function getProfessorsByMajor($majorid) {
  $query = sprintf('SELECT DISTINCT(p.id), p.name '
      . ' FROM Professors p, Courses c '
      . ' JOIN Structure s ON s.mid = "%d" '
      . ' JOIN Teaching t ON t.cid = s.cid '
      . ' WHERE t.pid = p.id ',
      mysql_real_escape_string($majorid));
  $result = mysql_query($query) or die(mysql_error());
  $professors = array();

  while ($row = mysql_fetch_assoc($result)) {
    $professors[] = $row;
  }

  return $professors;
}

function getCoursesByProfessor($professorid) {
  $query = sprintf('SELECT c.id, c.name '
      . ' FROM Courses c '
      . ' JOIN Teaching t ON t.pid = "%d"'
      . ' WHERE t.cid = c.id',
      mysql_real_escape_string($professorid));
  $result = mysql_query($query) or die(mysql_error());
  $courses = array();

  while ($row = mysql_fetch_assoc($result)) {
    $courses[] = $row;
  }

  return $courses;
}

function getProfessorsByCourse($courseid) {
  $query = sprintf('SELECT p.id, p.name '
      . ' FROM Professors p'
      . ' JOIN Teaching t ON t.cid = "%d"'
      . ' WHERE t.pid = p.id',
      mysql_real_escape_string($courseid));
  $result = mysql_query($query) or die(mysql_error());
  $professors = array();

  while ($row = mysql_fetch_assoc($result)) {
    $professors[] = $row;
  }

  return $professors;
}

function getCourseInformation($courseid) {
  $query = sprintf('SELECT c.id, c.number, c.name, c.description'
      . ' FROM Courses c '
      . ' WHERE c.id = "%d" ',
      mysql_real_escape_string($courseid));
  $result = mysql_query($query) or die(mysql_error());
  $course = array();

  while ($row = mysql_fetch_assoc($result)) {
    $course = $row;
  }

  if (!empty($course)) {
    $course["professors"] = getProfessorsByCourse($courseid);
    $course["students"]   = getStudentsByCourse($courseid);
  }

  return $course;
}

function getProfessorInformation($professorid) {
  $query = sprintf('SELECT p.id, p.name'
      . ' FROM Professors p '
      . ' WHERE p.id = "%d" ',
      mysql_real_escape_string($professorid));
  $result = mysql_query($query) or die(mysql_error());
  $professor = array();

  while ($row = mysql_fetch_assoc($result)) {
    $professor = $row;
  }

  if (!empty($professor)) {
    $professor["courses"] = getCoursesByProfessor($professorid);
  }

  return $professor;
}

function getMajorInformation($majorid) {
  $query = sprintf('SELECT m.id, m.name'
      . ' FROM Majors m '
      . ' WHERE m.id = "%d" ',
      mysql_real_escape_string($majorid));
  $result = mysql_query($query) or die(mysql_error());
  $major = array();

  while ($row = mysql_fetch_assoc($result)) {
    $major = $row;
  }

  if (!empty($major)) {
    $major["courses"] = getCoursesByMajor($majorid);
  }

  return $major;
}

function getSchoolInformation($schoolid) {
  $query = sprintf('SELECT s.id, s.name'
      . ' FROM Schools s '
      . ' WHERE s.id = "%d" ',
      mysql_real_escape_string($schoolid));
  $result = mysql_query($query) or die(mysql_error());
  $school = array();

  while ($row = mysql_fetch_assoc($result)) {
    $school = $row;
  }

  if (!empty($school)) {
    $school["majors"] = getMajorsBySchool($schoolid);
  }

  return $school;
}

function getCoursesByStudent($eid) {
  $query = sprintf('SELECT c.id, c.number, c.name '
      . ' FROM Courses c, Studying st, RawData raw '
      . ' WHERE st.sid = raw.id '
      . ' AND c.id = st.cid '
      . ' AND raw.eid = "%d" ',
      mysql_real_escape_string($eid));
  $result = mysql_query($query) or die(mysql_error());
  $courses = array();

  while ($row = mysql_fetch_assoc($result)) {
    $courses[] = $row;
  }

  return $courses;
}

function getStudentsByCourse( $courseid ){
  $query = sprintf('SELECT raw.id, raw.eid, raw.fname, raw.lname '
      . ' FROM Courses c, Studying st, RawData raw '
      . ' WHERE st.sid = raw.id '
      . ' AND c.id = st.cid '
      . ' AND c.id = "%d" ',
      mysql_real_escape_string($courseid));
  $result = mysql_query($query) or die(mysql_error());
  $courses = array();

  while ($row = mysql_fetch_assoc($result)) {
    $courses[] = $row;
  }

  return $courses;
}

?>
