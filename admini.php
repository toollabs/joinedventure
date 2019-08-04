<?php
/**
 * @author Steinsplitter / https://commons.wikimedia.org/wiki/User:Steinsplitter
 * @copyright 2016 Tool authors
 * @license http://unlicense.org/ Unlicense
 */
 ?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <title>Admin inactivity section tool (commons)</title>
  <link href="//tools-static.wmflabs.org/cdnjs/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
  <script src="//tools-static.wmflabs.org/cdnjs/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
  <style>
    body {
      padding-top: 60px;
    }
  </style>
  <script>
      $(document).on("click", "#sendreq1", function() {
      $('#spinner').show();
      });
  </script>
</head>
<body>
  <div class="navbar navbar-default navbar-fixed-top">
   <div class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand" href="./admini.php?return=true">Admin inactivity section tool</a>
      </div>

        <ul class="nav navbar-nav navbar-right">
          <li><a href="https://commons.wikimedia.org/wiki/Commons:Administrators/Inactivity_section"><span class="glyphicon glyphicon-briefcase"></span> coordination page</a></li>
        </ul>

    </div>
  </div>

  <div class="container">
<b>Administrators on Wikimedia Commons with less than five (5) adminactions in the last six months are listed below.</b>
<br><br>
<?php
if ($_POST['start'] != 'yes') {
 echo "It may take a few minutes to generate the report. Do you want to poceed?";
 echo "<form action='admini.php' method='post'>
  <input type='hidden' id='start' name='start' value='yes'>
  <button type='submit' id='sendreq1' class='btn btn-success sendreq1' value='Yes'>Yes</button>
</form> ";
 echo "<br><br><span id='spinner' style='display:none;'><img src='https://upload.wikimedia.org/wikipedia/commons/7/78/24px-spinner-0645ad.gif'/ > <b>Fetching data...</b> This may take a while, depending on database's speed.</span>";
 die();
}

$tools_pw = posix_getpwuid (posix_getuid ());
$tools_mycnf = parse_ini_file($tools_pw['dir'] . "/replica.my.cnf");
$db = new mysqli('commonswiki.labsdb', $tools_mycnf['user'], $tools_mycnf['password'], 'commonswiki_p');
if ($db->connect_errno)
        die("Error when connecting to database: (" . $db->connect_errno . ") " . $db->connect_error);
$r = $db->query('
SELECT
 user_name AS sysop,
 admin_actions
FROM user_groups
LEFT JOIN
  (
SELECT actor_name,
actor_user,
count(log_id) AS admin_actions
FROM logging
INNER JOIN actor
 ON log_actor = actor_id
WHERE log_type IN ("block","delete","import","protect","rights", "merge", "massmessage", "abusefilter")
AND log_type != "5"
AND log_timestamp > DATE_FORMAT(DATE_ADD(NOW(), INTERVAL -6 MONTH), "%Y%m%d%H%i%s")
GROUP BY actor_name
 ) AS sysc
ON actor_user = ug_user
INNER JOIN user
ON user_id = ug_user
WHERE ug_group = "sysop"
AND ( admin_actions < 5 OR admin_actions IS NULL)
ORDER BY admin_actions DESC;
');
unset($tools_mycnf, $tools_pw);
?>

<table class="table table-bordered">
<thead> <tr> <th>Username</th> <th>Admin actions</th> </tr> </thead>
   <?php while ($row = $r->fetch_row()):
    if($row[1] === NULL) {
     $aac = "0";
    } else {
     $aac = $row[1];
    }
   ?>
   <tr>  <td><a href="//commons.wikimedia.org/wiki/User:<?= htmlspecialchars( $row[0] ) ?>"><?= htmlspecialchars( $row[0] ) ?></td> <td> <a href="//commons.wikimedia.org/wiki/Special:Log/<?= htmlspecialchars( $row[0] ) ?>"><?= htmlspecialchars( $aac ) ?></td></tr>
   <?php endwhile; ?>
</table>
<br>
<b>In wikitext format</b>
<pre>
{| class="wikitable sortable" style="text-align: center"
!Username || Admin actions || Current status
<?php
mysqli_data_seek($r, 0);
?><?php
while ($row = $r->fetch_row()):
if($row[1] === NULL) {
 $aac = "0";
} else {
 $aac = $row[1];
}
?>
|-
|{{user6|<?= htmlspecialchars( $row[0] ) ?>}} || <?= htmlspecialchars( $aac ) ?> ||
<?php endwhile; ?>
|}
</pre>
<hr>
<a href="https://github.com/Toollabs/joinedventure/blob/master/admini.php">Source code</a>
</body>
</html>
