<?php

require_once('page.php');

function codeFor($str){
    $code = md5($str);
    return substr($code, 4, 4);
}

$html = "<h1>Impressum</h1>";

$html .= "<p>Um die automatische Auslesung meiner Daten zu verhindern sind eben diese durch ein Captcha geschützt.</p>";
$html .= "<p>Rechnen Sie einfach das Ergebnis der kleinen Matheaufgabe aus und Sie erhalten die Daten.</p>";

$show = isset($_POST['entered']) && isset($_POST['code']) ? codeFor($_POST['entered']) == $_POST['code'] : false;

if ($show){
    $html .= "<p>Franz Gregor</br>Weinbergstraße 69</br>01129 Dresden</p><p>_Franz_._Gregor_@mailbox.tu-dresden.de (ohne die Unterstriche)</p>";
} else {
    $a = rand(0, 10);
    $b = rand(0, 10);
    $html .=   '<form method="POST" class="yform">
                <input type="hidden" name="code" value="'.codeFor($a+$b).'" />
                    <fieldset>
                        <legend>Captcha</legend>
                        <div class="type-text">
                            <label for="entered">'.$a.' + '.$b.' =</label>
                            <input type="text" id="entered" name="entered" />
                        </div>
                    </fieldset>
                    <div class="type-button">
                        <input type="submit" value="Impressum anzeigen" />
                    </div>
                </form>';
}

$html .= '<p>Das hier kein Disclaimer zu finden ist hat einen einfachen Grund:</p>
          <p><a href="http://www.law-podcasting.de/der-disclaimer-10-jahre-unausrottbarer-schwachsinn">http://www.law-podcasting.de/der-disclaimer-10-jahre-unausrottbarer-schwachsinn</a></p>';



$view = new View();
$view->fill_subview_with_html("CONTENT", $html);
$view->render();
