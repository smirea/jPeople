
<script src="js/jquery.js"></script>
<script>
  $(function(){
    $('#info tr:odd').css('background', 'lightblue' );
    $('.face')
      .find('.fName, .lName, .major, .major, .majorLong, .description, .email, .phone, .room, .birthday, .country, .college')
      .css({
        position  : 'relative'
      })
      .bind( 'mouseenter.showInfo', function(e){
        var span = $(document.createElement('span'));
        span
          .html( $(this).attr('class') )
          .css({
          'position'    : 'absolute',
          'z-index'     : 69000,
          'top'         : -$(this).outerHeight() - 5,
          'right'       : 0,
          'border'      : '1px solid darkblue',
          'background'  : 'yellow',
          'padding'     : 1,
          'font-size'   : '8pt',
          'font-weight' : 'normal'
        });
        $(this).append( span ).data( 'info', span );
      })
      .bind( 'mouseleave.hideInfo', function(e){
        $(this).data('info').remove();
      });

  });
</script>
<style>
  .face, .face table{
    font-family : verdana, arial, sans;
    font-size   : 9pt;
  }
  .face{
    border      : 1px solid #666;
    width       : 300px;
  }
  .face .header{
    border-bottom : 1px solid #bbb;
    margin  : 5px;
  }
  .face .header .photo{
    float       : left;
    position    : relative;
    z-index     : 100;
    border      : 1px solid #666;
    background  : black;
    width       : 112px;
    height      : 112px;
    margin      : 0 5px 5px 0;
    text-align  : center;
    overflow    : hidden;
  }
  .face .header .photo img{
    max-width : 112px;
    max-height: 112px;
  }
  .face .header .name{
    font-size   : 11pt;
    font-weight : bold;
  }
  .face .header .majorLong{
    font-size : 10pt;
  }

  .face .body{
    margin  : 5px 5px 5px 10px;
  }

  .face .country{
    z-index     :-1;
    background  : lightblue;
    position    : relative;
    border-top  : 1px solid #666;
    margin-top  : 30px;
    padding     : 2px 0;
    text-indent : 10px;
  }
  .face .country img{
    position  : absolute;
    right     : 3px;
    bottom    : -2px;
  }

  .clearBoth{
    visibility  : hidden;
    clear       : both;
  }
</style>
<style>
  .jPeople-ID {
	  display		: inline-block;
	  border		: 1px solid #696969;
	  width			: 310px;
	  height		: 140px;
	  margin		: 2px;
	  font-family	: trebuchet ms;
	  font-size	: 10pt;
	  text-align	: left;
  }
  .jPeople-ID img{
	  text-align	: center;
  }
  .jPeople-ID-TD{
	  width			: 105px;
	  height		: 140px;
	  text-align	: center;
  }
  .jPeople-ID a{
	  color					: #003537;
	  font-size			: 8pt;
  }
  .jPeople-ID a:hover{
	  color					: darkgreen;
	  text-decoration	: underline;
  }
  .jPeople-ID-infoTD{
	  vertical-align	: top;
  }
  .jPeople-ID-info {
	  float					: right;
	  width					: 100%;
	  margin 				: 0 0 0 5px;
	  font-family			: trebuchet ms;
	  font-size			: 10pt;
	  list-style-type	: none;
  }
  .jPeople-ID-infoCell{
	  font-weight		: bold;
	  text-align		: right;
	  padding-right	: 3px;
  }
  .jPeople-ID-marginTD{
	  border-bottom	: 2px inset #696969;
	  margin 			: 5px;
  }
  .jPeople-ID-info h3{ margin : 0; }
  .jPeople-ID-info h4 {
	  margin		: 0;
	  font-weight	: normal;
	  font-style	: italic;
	  font-size	: 7pt;
  }
</style>
<?php

  require_once 'config.php';
  require_once 'class.Search.php';

  $r = mysql_fetch_assoc( mysql_query( "SELECT * FROM ".TABLE_SEARCH." WHERE lname='mirea'" ) );

  echo '<table id="info" style="border:1px solid #ccc;background white; z-index:10000; position:absolute; top:3px; right:0px; width:300px;padding:5px" cellspacing="0" cellpadding="0">';
  foreach( $r as $k => $v ){
    echo "<tr><td><b>$k</b></td><td>$v</td></tr>\n";
  }
  echo '</table>';

  $photo  = imageURL( $r['eid'] );
  $flag   = flagURL( $r['country'] );
  $room = $r['room'];
  if( preg_match( '/^[A-Z]{2}-[0-9]{3}$/', $room ) ){
    $room = '<span class="college">'.substr( $room, 0, 1 ).'</span>'.
            '<span class="block">'.substr( $room, 1, 1 ).'</span>'.
            '-'.
            '<span class="floor">'.substr( $room, 3, 1 ).'</span>'.
            substr( $room, 4 );
  }
  echo <<<FACE
    <div class="face">

      <div class="header">
        <table class="photo" cellspacing="0" cellpadding="0">
          <tr><td><img src="$photo" alt="My photo" /></td></tr>
        </table>

        <div class="name">
          <span class="fName" title="tag: fName">$r[fname]</span>,
          <span class="lName" title="tag: lName">$r[lname]</span>
        </div>

        <div>
          <span class="majorLong">$r[majorlong]</span> <br />
          <span class="description">$r[description]</span>
        </div>
        <div class="clearBoth"></div>
      </div>

      <table class="body" cellspacing="0">
        <tr>
          <td> College </td>
          <td><span class="college">$r[college]</span></td>
        </tr>
        <tr>
          <td> Email </td>
          <td><span class="email"><a href="mailto:$r[email]">$r[email]</a></span></td>
        </tr>
        <tr>
          <td> Phone </td>
          <td><span class="phone"> +49 421 (200) <b>$r[phone]</b></span></td>
        </tr>
        <tr>
          <td> Room </td>
          <td><span class="room">$room</span></td>
        </tr>
        <tr>
          <td> Birthday </td>
          <td><span class="birthday">$r[birthday]</span></td>
        </tr>
      </table>

      <div class="country">
        $r[country] <img src="$flag" alt="country" />
      </div>
    </div>

    <hr style="width:500px; margin: 10px auto 10px 0" />

    <table class="jPeople-popup-table" cellpadding="0">
      <tr>
        <td class="jPeople-id">

        </td>
      </tr>
    </table>
FACE;

?>
<link rel="stylesheet" type="text/css" href="css/tCheckbox.css" />
<script src="js/tCheckbox.js"></script>
<style>

  .college-icon{
    display     : inline-block;
    border      : 1px solid #666;
    background  : #666;
    padding     : 1px 3px;
    font-family : verdana, arial, courier;
    font-size   : 8pt;
    font-weight : bold;
    text-align  : center;
    color       : #fff;
  }
  .krupp{
    background : red;
  }
  .mercator{
    background : blue;
  }
  .college-iii{
    background : green;
  }
  .nordmetall{
    background  : purple;
  }

</style>
<script>
  $(function(){

    $('input[type="checkbox"]').tCheckbox({
      
    });

  });
</script>

<input type="checkbox" name="check1" value="val1" checked="checked" />
<input type="checkbox" name="check2" value="val2" />
<span class="college-icon krupp">K</span>
<span class="college-icon mercator">M</span>
<span class="college-icon college-iii">C3</span>
<span class="college-icon nordmetall">N</span>

<hr /></hr />

<style>
  .btn{
    display         : inline-block;
    border          : 1px solid #3b79df;
    border-radius   : 10px;
    background-color: #4986e8;
    box-shadow      : 2px 2px 5px #000;
    margin          : 1px 3px;
    padding         : 3px 10px;
    font-family     : verdana, arial;
    font-size       : 9pt;
    font-weight     : bold;
    text-align      : center;
    text-decoration : none;
    text-shadow     : 0 1px 1px #0d3474;
    color           : #fff;
    cursor          : pointer;
  }
  .btn:hover{
    background-color  : #1E90FF;
    border-color      : darkblue;
    color             : white;
  }
  .btn:active{
    position          : relative;
    top               : 1px;
    left              : 1px;
  }
  .btn-email{
    background-image    : url('images/email.png');
    background-repeat   : no-repeat;
    background-position : 9px 50%;
    padding-left        : 30px;
  }
</style>

<a href="mailto:a@a;b@b.b;Stefan<s.mirea@jacobs.com>" class="btn btn-email">Email all</a>