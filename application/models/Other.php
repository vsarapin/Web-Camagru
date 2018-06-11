<?php

namespace application\models;

use application\controllers\ErrorController;
use application\core\base\Model;

class Other extends Model {

    public function makeImage($imageBaseCode){
        $path = ROOT . "/public/png";
        $outputFile = md5(uniqid(rand(),1)) . ".png";
        $ifp = fopen($path . "/" . $outputFile, 'wb');
        $data = explode( ',', $imageBaseCode );
        fwrite($ifp, base64_decode($data[1]));
        fclose($ifp);
        $tmpArray = ["/png/" . $outputFile];
        $this->insertOne($tmpArray);
        $imgSmall = 'matrixheroes.png'; //Тут надо доделать, указыватьб фото не ручками
        $img1 = imagecreatefrompng($path . DIRECTORY_SEPARATOR . $outputFile);
        $img2 = imagecreatefrompng($path . DIRECTORY_SEPARATOR . $imgSmall);
        if($img1 && $img2) {
            $x2 = imagesx($img2);
            $y2 = imagesy($img2);
            imagecopyresampled($img1, $img2, -70, -5, 0, 0, $x2, $y2, $x2, $y2);
            imagepng($img1, $path . "/" . $outputFile, 9);
            header('Location: /camagru/');
        }else {
            ErrorController::errorPage();
        }
    }

    public function showAllPhoto()
    {
        $photo = '';
        $this->table = 'images';
        $tmpArray = $this->findAll();
        $tmpArray = array_reverse($tmpArray);
        foreach ($tmpArray as $key) {
            if (file_exists(WWW . "/" . $key['src'])) {
                $likes = $key['likes'];
                $src = $key['src'];
                $pSrc = $src . "1";
                $aSrc = $src . "2";
                $dSrc = $src . "3";
                $photo = $photo
                    . "<div class=\"container_tmp\">"
                    . "<div class=\"row center-align\">"
                    . "<div class=\"col s12 grey darken-4\">"
                    . "<div style='padding-bottom: 50px'>"
                    . "<img src=" ."\"". $key['src'] . "\">"
                    . "<figcaption>" . "<a id='$aSrc' class='content_img' onclick='addComment(this.id)'>Comments</a>" . "<p id='$pSrc' align=right class='p_like_img'>$likes</p>"
                    . "<img id=\"$src\" src=" ."\"". "/png/like.png" . "\" class='like_img' onclick=\"addLike(this.id)\">"
                    . "</figcaption>"
                    . "<div id='$dSrc'>"
                    . "</div></div></div></div></div>";
            }
        }
        return $photo;
    }

    public function addLikes() {
        $this->table = "likes";
        $findUser = $this->findAll();
        if (count($findUser)) {
            $trigger = 0;
            foreach ($findUser as $key => $value) {
                if ($value['user'] == $_SESSION['login'] && $value['photo'] == $_POST['path'] && $value['likes'] == 1) {
                    $this->addOrRemoveOneLike("likes", "likes", "0", "user", "\"" .$_SESSION['login'] . "\"", "photo", "\"" . $_POST['path'] . "\"");
                    $this->table = "images";
                    $foundRow = $this->findOne($_POST['path'], "src");
                    $numOfLikes = $foundRow[0]['likes'] - 1;
                    $this->updateOne("images", "likes", $numOfLikes, "src", "\"" . $_POST['path'] . "\"");
                    $trigger = 1;
                    echo $numOfLikes;
                    exit;
                } else if ($value['user'] == $_SESSION['login'] && $value['photo'] == $_POST['path'] && $value['likes'] == 0) {
                    $this->addOrRemoveOneLike("likes", "likes", "1", "user", "\"" .$_SESSION['login'] . "\"", "photo", "\"" . $_POST['path'] . "\"");
                    $this->table = "images";
                    $foundRow = $this->findOne($_POST['path'], "src");
                    $numOfLikes = $foundRow[0]['likes'] + 1;
                    $this->updateOne("images", "likes", $numOfLikes, "src", "\"" . $_POST['path'] . "\"");
                    $trigger = 1;
                    echo $numOfLikes;
                    exit;
                }
            }
            if (!$trigger) {
                $this->table = "likes";
                $tmpArray = [$_SESSION['login'], $_POST['path'], "1"];
                $this->insertLikes($tmpArray);
                $this->table = "images";
                $foundRow = $this->findOne($_POST['path'], "src");
                $numOfLikes = $foundRow[0]['likes'] + 1;
                $this->updateOne("images", "likes", $numOfLikes, "src", "\"" . $_POST['path'] . "\"");
                echo $numOfLikes;
                exit;
            }
        }else {
            $this->table = "likes";
            $tmpArray = [$_SESSION['login'], $_POST['path'], "1"];
            $this->insertLikes($tmpArray);
            $this->table = "images";
            $foundRow = $this->findOne($_POST['path'], "src");
            $numOfLikes = $foundRow[0]['likes'] + 1;
            $this->updateOne("images", "likes", $numOfLikes, "src", "\"" . $_POST['path'] . "\"");
            echo $numOfLikes;
            exit;
        }
    }

    public function addComments() {
        $comment = htmlspecialchars(stripslashes("<b>" . "<p align='left'>" . $_SESSION['login'] . ":" . "<p>" . "</b>" . "<p align='left'>" . $_POST['comment'] . "<p>"));
        $commentsArray = [$_SESSION['login'], htmlspecialchars(stripslashes($_POST['photo'])), $comment];
        $this->insertComments($commentsArray);
        echo $_SESSION['login'];
        exit;
    }

    public function loadComments() {
//        header('Access-Control-Allow-Origin: http://localhost:8080');
        $tmpComments = '';
        $allComments = $this->findAllComments("\"" . $_POST['commentId'] . "\"");
        $allComments = array_reverse($allComments);
        if (!count($allComments)) {
            echo "";
            exit;
        }else {
            foreach ($allComments as $key => $value) {
                    $tmpComments = $tmpComments . htmlspecialchars_decode($value['comment']);
//                    if ($value['user'] == $_SESSION['login']) {
//                        $tmpLinks = htmlspecialchars("<span style='text-align: left'>удалить</span>");
//                        $tmpComments = $tmpComments . htmlspecialchars_decode($tmpLinks);
//                    }
            }
            echo $tmpComments;
            exit;
        }
    }
}