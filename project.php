<?php
include 'page.php';

$html = '
<h1>Das Projekt</h1>
<h3>Ziele, Absichten</h3>
<p>Dieses Projekt möchte die Leistungsdaten von Photovoltaikanlagen verfügbar machen. Dadurch soll es Anlagenbesitzern möglich sein ihre Anlage zu beobachten, um über den Ertrag und über etwaige Probleme frühzeitig informiert zu sein. Außerdem können sie ihre eigene Anlage mit anderen vergleichen. Bauherren können die Möglichkeiten in ihrer Region ausloten....</p>
<h3>Der Macher</h3>
<p>Hallo, mein Name ist Franz Gregor. Ich studiere Informatik an der Technischen Universität Dresden im nunmehr 5. Semester und bin nebenbei bei der i.S.X. Software GmbH & Co. KG, einem in Dresden ansässigem Softwarehaus, für die Arbeitszeiterfassung verantwortlich.</p>
<p>Wenn ich mich in meiner Freizeit Software zuwende, arbeite ich unter anderem an diesem Projekt.</p>
<h3>Die Entstehungsgeschichte</h3>
<h4>Der Anfang</h4>
<p>Die Projektgeschichte beginnt im Jahr 2007. Damals besuchte ich die 11. Klasse des Pestalozzi-Gymnasiums Dresden. Im Physikunterricht fragte mein damaliger Lehrer Herr Dr. Schmidt in die Klasse hinein, ob jemand Interesse hätte die Leistungsdaten der Photovoltaikanlage auf dem Dach der Schule Online zu stellen. Ich empfand diese Aufgabe als sehr reizvoll und meldete mich.</p>
<p>Zur damaligen Zeit wurde ich am Schülerrechenzentrum Dresden in Informatik unterrichtet. Für die Realisierung unserer Aufgaben dort nutzten wir Delphi. Daher entschied ich mich Delphi für die Client Applikation zu nutzen die auf einem normalem Computer lief, der über eine serielle Schnittstelle (RS232) mit dem Siemens Sitop Wechselrichter der Photovoltaikanlage des Gymnasiums verbunden war.</p>
<p>Der Webserver auf dem die Daten gespeichert und der Öffentlichkeit zugänglich gemacht wurden, stellt der Sächsische Bildungsserver in Person von Herrn Thuß.</p>
<p>Im Laufe der Entwicklung erkannte ich langsam die volle Komplexität der Aufgabe, zu jeder Zeit möglichst aktuelle Daten im Netz zu haben. Ohne einen Mehrnutzen für mich war sie nicht zu stemmen und so nutzte ich sie gleichzeitig als Jahresarbeit für das Schülerrechenzentrum.</p>
<p>Mit der Abgabe der Jahresarbeit war die erste Version lauffähig und lieferte erste Daten. Der Delphi Client las periodisch die Daten des Siemens Ausleseprogrammes und schickte diese an die Webapplikation,welche sie als CSV-Export zur Verfügung stellte.</p>
<h4>Neuer Client</h4>
<p>Diese erste Version lief aber nicht sonderlich gut. Durch Ausfälle sowohl bei meiner Client Applikation als auch beim Siemens Ausleseprogramm kam es regelmäßig zu Datenlücken.</p>
<p>Also programmierte ich eine neue Client Applikation. Dieses Mal in Python. Diese neue Version war viel mehr auf Fehlertoleranz ausgelegt als die alte Delphi Version. Somit und mit der Hilfe von regelmäßigen Neustarts des Siemens Ausleseprogramms war lange Zeit ein sehr stabiles Auslesen möglich.</p>
<h4>Der Logger</h4>
<p>Trotz des stabilen Betriebs der damaligen Lösung sah ich ein großes Problem. Die Clientsoftware lief auf einem ganz normalen ausgemusterten Computer und dieser musste Tag und Nacht an sein, da der Wechselrichter sich die Leistungsdaten nicht merkt. Das verbrauchte viel Strom. So viel, dass an manchem Wintertag die Erfassung mehr fraß als die Anlage produzierte. Für dieses Problem musste eine Lösung her. Auch aus einem anderen Grund, denn das altersschwache Netzteil des Computers gab eines Tages den Geist auf und so waren viele Daten verloren, da es dauerte bis ein neues da war.</p>
<p>Ich entschied mich einen kleinen Minicomputer genannt ALIX als Logger einzusetzen. Dieser verbraucht vernachlässigbar wenig Strom, enwickelt kaum Wärme, kommt daher ohne Lüfter aus und ist aufgrund dieser Tatsachen sehr viel langlebiger als ein normaler Computer.</p>
<p>Die gesamte Clientsoftware musste neu geschrieben werden, da es sich um ein völlig neues System handelte und dieses möglichst autonom funktionieren sollte. Viel von dem in meinem Studium erworbenen Wissen ist in die Entwicklung des Loggers gefloßen. Für die Konfiguration entstand eine Webapplikation, da der Logger über keinen Bildschirmanschluss verfügt. Der Zwischenspeicher, in dem die Leistungsdaten im Falle eines Problems mit der Internetverbindung gehalten werden, ist extrem fehlertolerant. Bei einem Stromausfall gehen keine Daten verloren, selbst wenn gerade auf den Speicher geschrieben wird. Alle Komponenten sind so flexibel wie möglich gehalten um nachträgliche Erweiterungen, z.B. neue Wechselrichtertypen, zu vereinfachen.</p>
<p>Des Weiteren konnte der Logger nicht wie bisher die Clientapplikation auf die Siemensauslesesoftware zurückgreifen und diese die Kommunikation mit der Anlage übernehmen lassen, da diese nur unter Windows läuft. Also musste das Übertragungsprotokoll der Sitop Wechselrichter analysiert und in den Logger intetegriert werden.</p>
<p>Mit dem Logger bin ich sehr zufrieden. Sofern niemand ein Kabel kappt läuft er und läuft und läuft. Die doch regelmässigen Internetausfälle im Pestalozzi-Gymnasium lassen ihn kalt. Die Daten kommen immer an, sobald das Internet wieder da ist.</p>
<h4>Neue Webseite</h4>
<p>Während die Applikation auf der Clientseite nun seit Projektstart häufiger ,leistet die Applikation auf der Serverseite fast unverändert ihren Dienst.</p>
<p>Das sieht man ihr leider auch an. Um dies zu ändern ensteht gerade diese neue Version mit ansprechenderen Grafiken und verbessertem Inhalt. Und unter der Haube hat sich natürlich auch einiges getan nach zweieinhalb Jahren Informatikstudium.</p>
';


// view setup
$view = new View('MainView.html');
$view->fill_subview_with_html("CONTENT", $html);
$view->render();



