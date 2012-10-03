<?php
  require_once 'config.php';
?>
<section>
  <h1>
    Hello World!
    <a class="jPeople-popup-close" href="javascript:void(0)"
        onclick="jQuery('.jPeople-infoOverlay').fadeOut()">close (x)</a>
  </h1>
  <p>
    This is the new (better) version of jPeople.<br />
    Few things you should know about it:
  </p>
  <ul style="margin-top:0">
    <li>it is only accessible within the Jacobs VPN</li>
    <li>a separate version which will allow you to connect from outside campus
        by logging in with your campusnet is under development
    </li>
    <li>it was developed by <a href="mailto:steven.mirea@gmail.com">Stefan Mirea
        </a> with a lot of input from a growing number of people
    </li>
    <li>if you want to leave your impressions and/or report a bug, please use
        the form bellow
    </li>
  </ul>
  <p>
    Happy searching!
  </p>

  <hr />

  <form action="feedback.php" id="feedback-form" method="get">
    <fieldset>
      <legend>Contact form</legend>
      <table>
        <tr>
          <td>Who are you?</td>
          <td><input type="text" name="name" placeholder="John Doe" /></td>
        </tr>
        <tr>
          <td>Can I contact you?</td>
          <td><input type="text" name="email" placeholder="somebody@jacobs-university.de" /></td>
        </tr>
        <tr>
          <td>What do you want?</td>
          <td>
            <label for="input-name-1">
              <input type="radio" name="type" value="feedback" id="input-name-1" checked="checked"/>
              to give feedback
            </label>
            <label for="input-name-2">
              <input type="radio" name="type" value="bug" id="input-name-2" />
              to report a bug
            </label>
            <br />
            <label for="input-name-3">
              <input type="radio" name="type" value="image" id="input-name-3" />
              to report a broken image
            </label>
            <label for="input-name-4">
              <input type="radio" name="type" value="feature" id="input-name-4" />
              to request a feature
            </label>
          </td>
        </tr>
        <tr>
          <td>Tell me more</td>
          <td><textarea name="message" placeholder="Description..."></textarea></td>
        </tr>
        <tr><td colspan="2" style="text-align:right;"><input type="submit" value="submit" /></td></tr>
      </table>
    </fieldset>
  </form>
</section>