<?php

  $tips = array(
    'Basic queries' =>
    array(
      array( 'stefan mercator', 'all stefans in mercator college' ),
      array( 'romania undergrad', 'all Romanians that are undergraduates' ),
      //array( '26.08', 'all people born on the 26th of August (format: dd.yy)' ),
      array( 'mc-2', 'all mercatorians living in block C floor 2' ),
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
      array( 'room:mc-237', '<b>semicolon(:)</b> - designates a strict query'),
      array( 'country:germany,finland', '<b>comma(,)</b> in a <i>Strict query</i> - means <b>OR</b> (everyone from Germany <b>or</b> Finland)' ),
      array( '~alex romania', '<b>tilda(~)</b> <u>at the beginning of a word</u> - means <b>NOT</b> (all romanians who are not alex' ),
      //array( 'course:"advanced computer science"', '<b>double quotes(")</b> - take everything in between together (everyone who is taking that course this semester). Note, without the quotes it would have been: <i>everyone who takes the course advanced and is computer and science</i> :) )' )
    ),
    'Common tips' =>
    array(
      array( 'major:cs major:~eecs major:~ics', 'some majors\' acronym is contained in other. Exclude the ones that you don\'t want')
    )
  );

echo <<<HTML

<div class="title">Search Functionality Tips &amp; Tricks</div>
<div class="section" title="General Info">
The search works by comparing each word you insert with a set of attributes specified bellow. For example if you insert : <code>germany</code> it will find all Germans on campus, but if you isert only <code>ger</code> it will find all Germans plus all people named Germaine for example or any other person which has that token in its attributes. <br />
No algorithm can guess exactly what you really want, therefore if you really want to be sure of what you are searching either specify long exact words or use <a href="#">Strict queries</a> (which only search in the field you select)
</div>

HTML;

$c = 0;
foreach( $tips as $k => $v ){
  $c = 0;
  echo <<<HTML
    <table class="section codeTable" cellspacing="0" cellpadding="0" title="$k">
      <tr><td colspan="2" class="header">$k</td></tr>
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