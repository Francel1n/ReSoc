<?php
session_start();
?>
<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Mur</title>
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
        <header>
            <img src="resoc.jpg" alt="Logo de notre réseau social"/>
            <nav id="menu">
                <a href="news.php">Actualités</a>
                <a href="wall.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Mur</a>
                <a href="feed.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Flux</a>
                <a href="tags.php?tag_id=<?php echo $_SESSION['connected_id'] ?>">Mots-clés</a>
            </nav>
            <nav id="user">
                <a href="#">Profil</a>
                <ul>
                    <li><a href="settings.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Paramètres</a></li>
                    <li><a href="followers.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Mes suiveurs</a></li>
                    <li><a href="subscriptions.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Mes abonnements</a></li>
                </ul>

            </nav>
        </header>
        <div id="wrapper">
            <?php
            /**
             * Etape 1: Le mur concerne un utilisateur en particulier
             * La première étape est donc de trouver quel est l'id de l'utilisateur
             * Celui ci est indiqué en parametre GET de la page sous la forme user_id=...
             * Documentation : https://www.php.net/manual/fr/reserved.variables.get.php
             * ... mais en résumé c'est une manière de passer des informations à la page en ajoutant des choses dans l'url
             */
            $userId = $_GET['user_id'];
            ?>
            <?php
            /**
             * Etape 2: se connecter à la base de donnée
             */
            $mysqli = new mysqli("localhost", "root", "", "socialnetwork");
            ?>

            <aside>
                <?php
                /**
                 * Etape 3: récupérer le nom de l'utilisateur
                 */
                $laQuestionEnSql = "SELECT * FROM `users` WHERE id=" . intval($userId);
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $user = $lesInformations->fetch_assoc();
                //@todo: afficher le résultat de la ligne ci dessous, remplacer XXX par l'alias et effacer la ligne ci-dessous
                //echo "<pre>" . print_r($user, 1) . "</pre>";
                ?>
                <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez tous les message de l'utilisatrice : <?php echo $user['alias'] ?>
                        (n° <?php echo $_SESSION['connected_id'] ?>)
                    </p>
                </section>
            </aside>
            <main>
              <article>
                  <h2>Poster un message</h2>
                  <?php
                  $mysqli = new mysqli("localhost", "root", "", "socialnetwork");
                  $Auteur = $_SESSION['connected_id'];
                //   $laQuestionEnSql = "SELECT * FROM 'users'";
                //   $lesInformations = $mysqli->query($laQuestionEnSql);
                //   while ($user = $lesInformations->fetch_assoc())
                //   {
                //       $listAuteurs[$user['id']] = $user['alias'];
                //   }
                  
                  
                  $enCoursDeTraitement = isset($_POST['auteur']);
                  if ($enCoursDeTraitement)
                  {
                      $authorId = $_POST['auteur'];
                      $postContent = $_POST['message'];
                      $authorId = intval($mysqli->real_escape_string($authorId));
                      $postContent = $mysqli->real_escape_string($postContent);
                    //   $alias = "SELECT `alias` FROM `users` WHERE `id` =$_SESSION['connected_id']";
                      $lInstructionSql = "INSERT INTO `posts` "
                              . "(`id`, `user_id`, `content`, `created`, `parent_id`) "
                              . "VALUES (NULL, "
                              . "" . $authorId . ", "
                              . "'" . $postContent . "', "
                              . "NOW(), "
                              . "NULL);"
                              . "";
                      $ok = $mysqli->query($lInstructionSql);
                      if ( ! $ok)
                      {
                          echo "Impossible d'ajouter le message: " . $mysqli->error;
                      }
                  }
                  ?>
                  
                  <form action="wall.php?user_id=<?php echo $_SESSION['connected_id'] ?>" method="post">
                      <input type='hidden' name='user_id' value='<?php echo $_SESSION['connected_id'] ?>'>
                      <dl>
                          <dt><label for='auteur'>Auteur</label></dt>
                          <dd><select name='user_id'>
                                  <?php
                                      echo "<option value='$Auteur'>$alias</option>";
                                  ?>
                              </select></dd>
                          <dt><label for='message'>Message</label></dt>
                          <dd><textarea name='message'></textarea></dd>
                      </dl>
                      <input type='submit'>
                  </form>
              </article>
                <?php
                $laQuestionEnSql = "SELECT `posts`.`content`,"
                        . "`posts`.`created`,"
                        . "`users`.`alias` as author_name,  "
                        . "count(`likes`.`id`) as like_number,  "
                        . "GROUP_CONCAT(distinct`tags`.`label`) AS taglist "
                        . "FROM `posts`"
                        . "JOIN `users` ON  `users`.`id`=`posts`.`user_id`"
                        . "LEFT JOIN `posts_tags` ON `posts`.`id` = `posts_tags`.`post_id`  "
                        . "LEFT JOIN `tags`       ON `posts_tags`.`tag_id`  = `tags`.`id` "
                        . "LEFT JOIN `likes`      ON `likes`.`post_id`  = `posts`.`id` "
                        . "WHERE `posts`.`user_id`='" . intval($userId) . "' "
                        . "GROUP BY `posts`.`id`"
                        . "ORDER BY `posts`.`created` DESC  "
                ;
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }
                while ($post = $lesInformations->fetch_assoc())
                {
                    //echo "<pre>" . print_r($post, 1) . "</pre>";
                    ?>
                    <article>
                        <h3>
                            <time><?php echo $post['created'] ?></time>
                        </h3>
                        <address><?php echo $post['author_name'] ?></address>
                        <div>
                            <p><?php echo $post['content'] ?></p>
                        </div>
                        <footer>
                            <small>♥ <?php echo $post['like_number'] ?></small>
                            <a href=""><?php echo $post['taglist'] ?></a>,
                        </footer>

                    </article>
                <?php } ?>
            </main>
        </div>
    </body>
</html>
