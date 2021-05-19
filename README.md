# Automatische Listen 1.0
Ermöglicht das Generieren automatischer Listen aus dem Admin CP heraus. 
Die Automatischen Listen können anhand von Profilfeldern & Usergruppen erstellt werden.

# Funktionen
<ul>
  <li> Erstellen von automatischen Listen
  <li> Vergabe von Listen-Namen
  <li> Konfiguration der Listen-URL für jede Liste
  <li> Optional: Angabe eines Beschreibungstexts, der über der Liste angezeigt wird
  <li> Optional: Angabe von Extra-Informationen, die in den Listen angezeigt werden sollen
  <li> Optional: Angabe eines Suchwortes, auf das die Liste filtern soll
  <li> Auflistungen nach Usergruppen oder Profilfeldern
  <li> Ausklammern einzelner Usergruppen aus den Listen
  <li> Einfache Erweiterung der lists.php & Listen-Navigation um eigene, spezielle Listen
</ul>

# Demo

<center>
  
  <img src="https://snipboard.io/aFzL18.jpg" />
  
<img src="https://snipboard.io/lvIGgj.jpg" />  
  
  <img src="https://snipboard.io/RgMUqm.jpg" />
  
  <img src="https://snipboard.io/ZFkuEl.jpg" />
   
   <img src="https://snipboard.io/JwR9Fr.jpg" />
  
</center>

# Anleitungen 
Hi!
Anbei ein paar Kurzanleitungen zu dem Plugin. Generell gilt: tobt euch aus, probiert die Funktionen aus! (So gut wie) jede Liste ist <em>irgendwie</em> möglich. Die Handhabung setzt vielleicht etwas Übung voraus, aber wenn ihr einfach mal rumspielt und Listen erstellt, habt ihr schon bald den Dreh raus! ♫ Anbei also ein paar Beispiele um zu verstehen, wie das Ganze so funktionieren kann.

<a href="#hogwarts">Hogwarts-Häuser</a>
<a href="#patronus">Patronus</a>
<a href="#weristwer">Wer ist wer?</a>
<a href="#extern">Externe Listen an Navigation anfügen</a>
<a href="#eigene">Eigene/Spezielle Listen an die lists.php anfügen</a>

<h1 id="hogwarts">Beispiel: Hogwarts-Häuser</h1>

In diesem Beispiel möchte ich euch zeigen, wie ihr mit dem Automatische Listen-Plugin eine Übersicht der Hogwarts-Häuser erstellt.

<center><a href="https://snipboard.io/NkEbzs.jpg">[img]https://snipboard.io/NkEbzs.jpg[/img]</a>
Bild ist anklickbar!</center>

Diese Art von Liste ermöglicht es euch, die Liste direkt mit einem Info-Text zum jeweiligen Hogwartshaus zu versehen, zeitgleich aber auch eine Darstellung aller Charaktere in diesem Hogwartshaus zu bieten. Diese Liste kann demnach nicht nur als Übersicht gelten, sondern auch als Informationsquelle für eure User. 

<h2>Benötigte Profilfelder</h2>
[list]
[*]Hogwarts-Haus (Auswahlbox)
[*]Schulklasse (Auswahlbox)
[/list]

Für das Feld Hogwarts-Haus hinterlegt ihr (logisch) die vier möglichen Häuser.
Im Feld Schulklasse hinterlegt ihr als mögliche Auswahl die sieben Schulklassen.

<center><img src="https://snipboard.io/PBv73N.jpg" /></center>

Anschließend erstellt ihr vier Listen nach folgendem Muster:

<center><a href="https://snipboard.io/x2P6jK.jpg"><img src="https://snipboard.io/x2P6jK.jpg"></a>
Bild ist anklickbar!</center>

<b>Tipp:</b> Anstatt ein Profilfeld zu haben, in dem das Hogwartshaus hinterlegt ist, könnt ihr auch nach Usergruppe filtern, falls diese Usergruppen nach den unterschiedlichen Häusern benannt sind.

Ihr habt nun eine Liste erstellt, die alle User ausgibt, die im Profilfeld "Hogwarts-Haus" das Wort "Hufflepuff" drin stehen haben! Zusätzlich habt ihr angegeben, dass die Schulklasse als Extra-Information in der Liste ausgegeben wird. Und damit das alles Sinn ergibt und schon chronologisch dargestellt wird, sortiert ihr die Userliste zusätzlich nach Schulklasse, damit ihr Klasse 1 - 7 der Reihe nach angezeigt bekommt. 

<h2>Weitere Listen-Ideen</h2>
Ihr benötigt keine Hogwartshäuser? Kein Ding! Mit dieser Art von Liste kann man noch weitere Dinge bauen. Anbei kleine Ideen! 

<ul>
<li>[Fantasy] Rassenübersicht / Register
<li>Joblisten
<li>Wohnortliste
<li>Schul-/Universitätslisten
</ul>

<h1 id="patronus">Beispiel: Patronus-Liste</h1>

Magisch hat es angefangen, magisch soll es weitergehen! In dieser Anleitung zeig' ich euch, wie man eine Patronus-Liste bauen kann!

<center><a href="https://snipboard.io/UVQG4C.jpg"><img src="https://snipboard.io/UVQG4C.jpg"></a>
Bild ist anklickbar!</center>

<h2>Benötigte Profilfelder</h2>
<ul>
<li>Patronus (Text)
</ul>

Das "Besondere" an dieser List ist, dass es keinen Sinn ergibt, stumpf alle Tiere untereinander anzuzeigen und darunter dann eine Liste aller User mit diesem Patronus-Tier ausgeben zu lassen. Hier müssen wir also nicht nach dem Profilfeld "Patronus" filtern, sondern erstellen eine Liste mit Usernamen, in der wir als Extra-Information das Profilfeld Patronus anzeigen. Abschließend sortieren wir die Liste dann nach dem Feld Patronus und erhalten - zack - eine Patronusliste!

<center><a href="https://snipboard.io/rYLEip.jpg"><img src="https://snipboard.io/rYLEip.jpg"></a>
Bild ist anklickbar!</center>

<h2>Weitere Listen-Ideen</h2>
Nicht so der magische Typ? Kein Problem, die Anleitung lässt sich auch auf andere Ideen übertragen!

<ul>
<li>Geschlechtsneutrale Avatarliste
<li>Gestaltwandlerformen
</ul>

<h1 id="weristwer">Beispiel: Wer ist Wer-Liste</h1>
Und anhand dieses Beispiels zeige ich euch, wie ihr eine Liste generieren könnt, die Charaktere anhand eines einzelnen Profilfelds "gruppiert" - in diesem Beispiel zeige ich eine kleine "Wer ist Wer"-Liste, die auf das Spieler-Profilfeld referenziert. 

<center><a href="https://snipboard.io/Hjphqy.jpg"><img src="https://snipboard.io/Hjphqy.jpg" /></a>
Bild anklickbar!</center>

<h2>Benötigte Profilfelder</h2>
<ul>
<li>Spielernamen (Text)
</ul>

Darüber hinaus darf jeder Spielernamen nur <b>einmal</b> vergeben sein.

Was wir nun machen ist ganz einfach: wir filtern auf das Spielernamen-Feld. Damit werden alle Charaktere mit demselben Wert in einen Block zusammengefasst. 

<center><a href="https://snipboard.io/updDOM.jpg"><img src="https://snipboard.io/updDOM.jpg" /></a>
Bild anklickbar!</center>

Na - das war einfach, oder? Übrigens könnt ihr so nicht nur auf ein Profilfeld filtern, sondern auch eine Übersicht von Usergruppen und dazugehörigen Charakteren erstellen. Klickt euch einfach mal durch die Optionen! 

<h2>Weitere Listen-Ideen</h2>
Nicht so der magische Typ? Kein Problem, die Anleitung lässt sich auch auf andere Ideen übertragen!

[list]
[*]Fantasy-Rassenliste & dazugehörige Charaktere
[*]Quidditchmannschaften (Haus als Eigenschaft, Position als Extra-Information) 
[/list]

<h1 id="extern">Externe Listen an Navigation anhängen</h1>
Man hat gern alle Listen an einem Platz ... auch das ist möglich - na ja, zumindest kann man so tun. Ihr habt ein Avatarlistenplugin installiert? Oder eine automatische Wer ist Wer-Liste gibt es schon und ihr wollt sie nicht über mein Plugin generieren? 

Die Navigation der Listen wird zwar automatisch gebaut, aber ihr könnt sie auch händisch erweitern. Öffnet das Template <b>lists_menu</b> und dort könnt ihr spielend leichter und <b>{$menu_bit}</b> weitere Links händisch hinzufügen! 

<blockquote><div class="lists_menu-item"><a href="Link zur Liste">Name der Liste</a></div></blockquote>

So könnt ihr auf weitere Extraseiten oder Plugins verlinken! 

<h1 id="eigene">Eigene Listen-Codes hinzufügen</h1>
<b>Für Fortgeschrittene!!</b>
Manchmal sind Listen sehr speziell - einige können nicht automatisch generiert werden! Ihr habt also eigene Listen geschrieben, z.B. mit dem Tutorial von Dani - oder ihr habt ein anderes tolles Tutorial gefunden? Eine Namensliste, eine Geburtstagsliste? Keine Sorge, ihr könnt diesen Code mit der neuen lists.php-Datei verbinden. Anstatt die Listen also in eine neue listen.php zu packen, könnt ihr den Code einfach in die vorhandene lists.php schmeißen, die mit dem Automatische Listen-Plugin kommt.

Sucht einfach nach
<blockquote>// get field type of profilefield</blockquote>
und fügt darüber den Code eurer Extra-Liste ein. Sie lässt sich dann über euerforum.de/lists.php?action=eureaction aufrufen. 
