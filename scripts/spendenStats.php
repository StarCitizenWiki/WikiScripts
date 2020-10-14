<?php

if (isset($_GET['alphaslots'])) {
    if ($_GET['alphaslots'] == 1) {
        $alpha = 1;
    }
} else {
    $alpha = 0;
}
if (isset($_GET['balken'])) {
    if ($_GET['balken'] == 1) {
        $balken = 1;
    }
} else {
    $balken = 0;
}

if (isset($_GET['spenden'])) {
    if ($_GET['spenden'] == 1) {
        $spenden = 1;
    }
} else {
    $spenden = 0;
}

if (isset($_GET['lang'])) {
    if ($_GET['lang'] == 'en') {
        $lang = 'en';
    }
} else {
    $lang = 'de';
}

if ($spenden == 0 && $alpha == 0 && $balken == 0) {
    $alpha = 0;
    $balken = 1;
    $spenden = 1;
}

$Url = 'https://robertsspaceindustries.com/api/stats/getCrowdfundStats';

$datatopost = array(
    'chart' => 'hour',
    'alpha_slots' => true,
    'funds' => true,
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $Url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $datatopost);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$output = curl_exec($ch);
curl_close($ch);

$test = json_decode($output, true);

if ($test['success'] == 1) {
    $ziel_num = $test['data']['funds'];

    $ziel_max = substr($ziel_num, 0, -2);
    $ziel_max = $ziel_max / 1000000;
    $ziel_max = ceil($ziel_max);
    $ziel_max = $ziel_max * 1000000;
    $ziel_max_num = number_format($ziel_max, 0, '', ',');

    $remove = $ziel_max - 1000000;

    $percent = round(((substr($ziel_num, 0, -2) - $remove) / ($ziel_max - $remove)) * 100);
    if (empty($test['data']['next_goal']['goal'])) {
        $test['data']['next_goal']['goal'] = 'REDACTED';
        $test['data']['next_goal']['percentage'] = 0;
    }
    $ziel = number_format(substr($ziel_num, 0, -2), 0, '', ',');
    if ($balken == 1 && $spenden == 0) {
        $my_img = imagecreate(303, 23);
    } else {
        $my_img = imagecreate(325, 75);
    }
    $background = imagecolorallocatealpha($my_img, 0, 0, 0, 254);
    #Test Farbe
    $text_colour = imagecolorallocate($my_img, 106, 187, 207);
    #Farbe der Balken, welche noch nicht gefüllt sind
    $text_colour2 = imagecolorallocate($my_img, 69, 117, 129);

    if ($spenden == 1) {
        if ($lang == 'de') {
            #Erste Text-Zeile schreiben

            $text = 'Aktuelle Spenden: $'.$ziel.' von '.$ziel_max_num.' ('.$percent.'%)';

            imagestring($my_img, 2, 0, 0, $text, $text_colour);
        } else {
            $text = 'Pledges: $'.$ziel.' out of '.$ziel_max_num.' ('.$percent.'%)';

            imagestring($my_img, 2, 0, 0, $text, $text_colour);
        }
    }

    if (isset($alpha) && $alpha == 1) {
        if ($alpha == 1 && $balken == 1 && $spenden == 1) {
            $x_alpha = 45;
        } elseif ($alpha == 1 && $balken == 1 && $spenden == 1) {
            $x_alpha = 45;
        } elseif ($alpha == 1 && $balken == 1 && $spenden == 1) {
            $x_alpha = 45;
        } elseif ($alpha == 1 && $balken == 0 && $spenden == 1) {
            $x_alpha = 15;
        } elseif ($alpha == 1 && $balken == 1 && $spenden == 0) {
            $x_alpha = 30;
        } elseif ($alpha == 1 && $spenden == 0) {
            $x_alpha = 0;
        } else {
            $x_alpha = 45;
        }
        if ($lang == 'de') {
            if ($test['data']['alpha_slots_left'] < 100) {
                #Zweite Text-Zeile schreiben
              imagestring($my_img, 2, 0, $x_alpha, 'Keine Alphaslots mehr vorhanden!', $text_colour);
            } else {
                #Zweite Text-Zeile schreiben
              imagestring($my_img, 2, 0, $x_alpha, 'Es sind noch '.$test['data']['alpha_slots_left'].' Alphaslots frei.', $text_colour);
            }
        } else {
            #Zweite Text-Zeile schreiben
            imagestring($my_img, 2, 0, $x_alpha, $test['data']['alpha_slots_left'].' Alphaslots left.', $text_colour);
        }
    }
    if ($balken == 1) {
        if ($balken == 1 && $spenden == 0) {
            $hoehe_unten = 25;
            $hoehe_oben = 0;
        } else {
            $hoehe_unten = 40;
            $hoehe_oben = 15;
        }

        $remove = $ziel_max - 1000000;
        $ziel_funds = (((substr($test['data']['funds'], 0, -2) - $remove) / 1000000) * 100);
        #Prozentzahl mal 3 nehmen (Wird für die Länge des Balkens benötigt
        $ziel_funds = $ziel_funds * 3;

        #Schleife die den Spendenbalken erstellt
        for ($i = 0;$i <= 300;$i = $i + 5) {
            #Hier werden die gefüllten Balken dargestellt
            if ($ziel_funds >= $i) {
                #Die Balken bestehen aus 3 Vertikalen Linien (3px dick) deshalb 3 Linien nebeneinander
                imageline($my_img, $i, $hoehe_oben, $i, $hoehe_unten, $text_colour);
                imageline($my_img, $i + 1, $hoehe_oben, $i + 1, $hoehe_unten, $text_colour);
                imageline($my_img, $i + 2, $hoehe_oben, $i + 2, $hoehe_unten, $text_colour);
            }
            #Ist der Balken nicht gefüllt wird dieser mit einer anderen farbe dargestellt
            else {
                imageline($my_img, $i, $hoehe_oben, $i, $hoehe_unten, $text_colour2);
                imageline($my_img, $i + 1, $hoehe_oben, $i + 1, $hoehe_unten, $text_colour2);
                imageline($my_img, $i + 2, $hoehe_oben, $i + 2, $hoehe_unten, $text_colour2);
            }
        }
    }

    header('Content-type: image/png');
    imagepng($my_img);
    #Textfarbe
    imagecolordeallocate($my_img, $text_colour);
    #Hintergrundfarbe
    imagecolordeallocate($my_img, $background);
    imagedestroy($my_img);
} else {
    $my_img = imagecreate(350, 70);
    $background = imagecolorallocate($my_img, 0, 10, 20);
    $text_colour = imagecolorallocate($my_img, 106, 187, 207);
    #Textzeile schreiben
    imagestring($my_img, 5, 40, 28, 'Ein Fehler ist aufgetreten.', $text_colour);
    #imagestring( $my_img, 5, 40, 28, '21 Millionen Dollar Ziel erreicht!',$text_colour);
    #siehe oben
    header('Content-type: image/png');
    imagepng($my_img);
    imagecolordeallocate($text_color);
    imagecolordeallocate($background);
    imagedestroy($my_img);
}
