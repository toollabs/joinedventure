<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<title>CatMove RC</title>
<link href="css/bootstrap.css" rel="stylesheet">
    <style>
      body {
        padding-top: 60px;
      }
    </style>
</head>
<body>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">

          <a class="brand" href="#">CatMove RC</a>
          <div class="nav-collapse collapse">
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

  <div class="container">
<a href="//commons.wikimedia.org/wiki/File:Pictogram_voting_move.svg"><img class="decoded" alt="https://tools.wmflabs.org/joinedventure/move.png" src="https://tools.wmflabs.org/joinedventure/move.png" align="right" width="100" height="102"></a>
<p>Last 200 category moves/renamings on Wikiemdia Conmons.</p>
<?php
$tools_pw = posix_getpwuid (posix_getuid ());
$tools_mycnf = parse_ini_file($tools_pw['dir'] . "/replica.my.cnf");
$db = new mysqli('commonswiki.labsdb', $tools_mycnf['user'], $tools_mycnf['password'], 'commonswiki_p');
if ($db->connect_errno)
	die("Error when connecting to database: (" . $db->connect_errno . ") " . $db->connect_error);
$r = $db->query('SELECT
 rc_timestamp,
 actor_user AS rc_user_text,
 rc_title,
 rc_params,
 rc_namespace,
 rc_log_type
FROM recentchanges
INNER JOIN actor
 ON rc_actor = actor_id
WHERE rc_namespace = "14"
AND rc_log_type = "move" 
ORDER BY rc_timestamp DESC
LIMIT 200;');
unset($tools_mycnf, $tools_pw);
?>

<ul>
   <?php while ($row = $r->fetch_row()): 
   $u = unserialize( $row[3] );
   $to = $u["4::target"];
   ?>
  <li><a href="//commons.wikimedia.org/wiki/User:<?= htmlspecialchars( $row[1] ) ?>"><?= htmlspecialchars( $row[1] ) ?></a> moved <b>Category:<?= str_replace("_", " ", htmlspecialchars( $row[2] )); ?></b> (<a href="https://commons.wikimedia.org/w/index.php?title=Special%3ALog&type=&user=&page=<?= urlencode( "Category:". $row[2] ) ?>"><font color="#999999">log</font></a>) moved to: <i><a href="//commons.wikimedia.org/wiki/<?= htmlspecialchars( $to ) ?>"><?= htmlspecialchars( $to ) ?></a></i></li>
   <?php endwhile; ?>
</ul>
<hr>
<small>Tool by <a href="//commons.wikimdia.org/wiki/User:Rillke">Rillke</a> &amp; <a href="//commons.wikimdia.org/wiki/User:Steinsplitter">Steinsplitter</a></small>
   </div>
</body>
</html>
