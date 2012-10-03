<?php
  
  require_once 'config.php';

  $tips = array(
    'Basic queries' =>
    array(
      array( 'stefan mercator', 'all stefans in mercator college' ),
      array( 'romania undergrad', 'all Romanians that are undergraduates' ),
      //array( '26.08', 'all people born on the 26th of August (format: dd.yy)' ),
      array( 'mc-2', 'all mercatorians living in block C on the second floor' ),
    ),
    'Strict queries' =>
    array(
      array( 'fname:stefan', 'all people with their first-name containing `stefan`' ),
      array( 'major:eecs germany status:undergrad', 'everybody studying EECS from Germany who is also an undergraduate' ),
      array( 'major:eecs,bccb,iph', 'everyone from the majors: EECS, BCCB, IPH' )
    ),
    'Index of special symbols' =>
    array(
      array( 'germany eecs', '<b>space( )</b> - means <b>AND</b> (everyone who is german <b>and</b> studies eecs' ),
      array( 'lname:john', '<b>semicolon(:)</b> - designates a strict query (i.e. will only search for people whose last name is "john")'),
      array( 'country:japan,finland', '<b>comma(,)</b> in a <i>Strict query</i> - means <b>OR</b> (everyone from Germany <b>or</b> Finland)' ),
      array( '~alex romania', '<b>tilda(~)</b> <u>at the beginning of a word</u> - means <b>NOT</b> (all romanians who are not alex)' ),
      array( 'title:%president', '<b>percent sign(%)</b> <u>at the beginning of a word</u> - means <b>CONTAINS</b> (works only in <a href="#s-2">strict queries</a> and it means to look for attributes that contain the string raher than those which just start with the string). See the difference: <code>title:president</code>' ),
      //array( 'course:"advanced computer science"', '<b>double quotes(")</b> - take everything in between together (everyone who is taking that course this semester). Note, without the quotes it would have been: <i>everyone who takes the course advanced and is computer and science</i> :) )' )
    ),
    /*
    'Common tips' =>
    array(
      array( 'major:cs major:~eecs major:~ics', 'some majors\' acronym is contained in others (e.g. CS and EECS,ICS) . Exclude the ones that you don\'t want')
    ),
    */
    'List of all fields' =>
    array(
      array('fname:stefan', 'first name'),
      array('lname:mirea', 'last name'),
      //array('birthday:26.08', 'birth day'),
      array('country:romania', 'country. NOTE: wrap in double quotes for multiple-word countries (e.g. <code>country:"South Korea"</code>)'),
      array('majorlong:"Biochemistry and cell Biology"', 'long major name'),
      array('major:bccb', 'major abreviation'),
      array('status:master', '[undergrad, master, phd, phd-integrated, foundation-year]'),
      array('year:15', 'everyone who leaves jacobs in 2015 (graduating class, RAs, etc). If you want just students, you need to <code>year:15 status:undergrad</code>'),
      array('college:mercator', '[Mercator, Krupp, College-iii, Nordmetall'),
      array('room:mc-109', 'room number'),
      array('block:B', '[A, B, C, D, E]. Everyone who lives in `B` block (irrespective of college)'),
      array('floor:2', '[1, 2, 3, 4, 5]. Everyone who lives on the `2`nd floor (irrespective of college'),
      array('phone:5450', 'jacobs phone number (without prefixes)'),
      array('email:smirea@jacobs-university.de', 'email address'),
      array('description:"ug 15 cs"', 'Format: "status year major(s)"'),
      array('employeetype:faculty', '[admin, faculty, student, undefined]'),
      array('account:smirea', 'any account name'),
      array('office:"Research I, 100a"', 'office number'),
      array('deptinfo:physics', 'department information'),
      array('title:president', 'any type of status (e.g. "Research Associate", technician, etc)'),
      array('attributes:%president', 'different roles (e.g. President/Vice President)')
    )
  );

echo <<<HTML

<div class="title">Search Functionality Tips &amp; Tricks</div>
<div class="section" title="General Info">
The search works by comparing each word you insert with a set of attributes specified bellow. For example if you insert : <code>germany</code> it will find all Germans on campus, but if you isert only <code>ger</code> it will find all Germans plus all people named Germaine for example or any other person which has that token in its attributes. <br />
No algorithm can guess exactly what you really want, therefore if you really want to be sure of what you are searching either specify long exact words or use <a href="#s-2">Strict queries</a> (which only search in the field you select)
</div>

HTML;

$sections = 0;
foreach( $tips as $k => $v ){
  $c = 0;
  ++$sections;
  echo <<<HTML
    <table class="section codeTable" cellspacing="0" cellpadding="0" title="$k">
      <tr><td colspan="2" class="header" id="s-$sections">$k</td></tr>
HTML;
  foreach( $v as $val ){
    $class = ++$c % 2 == 0 ? "even" : "odd";
    echo <<<ROW
      <tr class="$class">
        <td><code>$val[0]</code></td>
        <td>$val[1]</td>
      </tr>
ROW;
  }
  echo "</table>";
}

?>
