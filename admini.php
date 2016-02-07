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
        <title>Category bot</title>
  <link href="//tools-static.wmflabs.org/cdnjs/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      padding-top: 60px;
    }
  </style>
</head>
<body>
  <div class="navbar navbar-default navbar-fixed-top">
   <div class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand" href="#">Admin inactivity section tool</a>
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
SELECT log_user_text,
log_user,
count(log_id) AS admin_actions
FROM logging
WHERE log_type IN ("block","delete","import","protect","rights", "merge", "massmessage", "abusefilter")
AND log_type != "5"
AND log_timestamp > DATE_FORMAT(DATE_ADD(NOW(), INTERVAL -6 MONTH), "%Y%m%d%H%i%s")
GROUP BY log_user_text
 ) AS sysc
ON log_user = ug_user
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
   <?php while ($row = $r->fetch_row()): ?>
   <tr>  <td><a href="//commons.wikimedia.org/wiki/User:<?= htmlspecialchars( $row[0] ) ?>"><?= htmlspecialchars( $row[0] ) ?></td> <td> <a href="//commons.wikimedia.org/wiki/Special:Log/<?= htmlspecialchars( $row[0] ) ?>"><?= htmlspecialchars( $row[1] ) ?></td></tr>
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
while ($row = $r->fetch_row()): ?>
|-
|{{user6|<?= htmlspecialchars( $row[0] ) ?>}} || <?= htmlspecialchars( $row[1] ) ?> ||
<?php endwhile; ?>
|}
</pre>
<hr>
<a href="https://github.com/Toollabs/joinedventure/blob/master/admini.php">Source code</a>
</body>
</html>
