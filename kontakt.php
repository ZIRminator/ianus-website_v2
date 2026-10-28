<?php
/* =========================================================
   ianus IT – Versand des Kontaktformulars
   Läuft auf jedem Webspace mit PHP. Hier nur die beiden
   Adressen anpassen, falls nötig.
   ========================================================= */

$empfaenger = 'info@ianus-it.de';      // hier kommen die Anfragen an
$absender   = 'website@ianus-it.de';   // muss eine Adresse eurer eigenen Domain sein

function fehler() {
    header('Location: kontakt.html#fehler');
    exit;
}

function feld($name) {
    return trim((string)($_POST[$name] ?? ''));
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: kontakt.html');
    exit;
}

// Bot-Schutz 1: Das unsichtbare Fangfeld muss leer sein.
// Bots bekommen trotzdem die Danke-Seite, damit sie nichts merken.
if (feld('website') !== '') {
    header('Location: danke.html');
    exit;
}

// Bot-Schutz 2: Das Skript auf der Seite setzt beim Laden eine Zeitmarke.
// Fehlt sie oder wurde schneller als in 3 Sekunden abgeschickt, ist es kein Mensch.
if (!preg_match('/^ianus-(\d{10,13})$/', feld('form_token'), $treffer)) {
    fehler();
}
$start = (int)$treffer[1];
if (strlen($treffer[1]) > 10) {
    $start = intdiv($start, 1000);
}
if (time() - $start < 3) {
    fehler();
}

$name      = feld('name');
$firma     = feld('firma');
$email     = feld('email');
$telefon   = feld('telefon');
$nachricht = feld('nachricht');

// Pflichtfelder und gültige E-Mail-Adresse
if ($name === '' || $nachricht === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fehler();
}

// Keine Zeilenumbrüche in den einzeiligen Feldern (schützt die E-Mail-Kopfzeilen)
foreach ([$name, $firma, $email, $telefon] as $wert) {
    if (preg_match('/[\r\n]/', $wert)) {
        fehler();
    }
}

// Längen begrenzen und typische Link-Spam-Nachrichten abweisen
if (mb_strlen($name) > 200 || mb_strlen($firma) > 200 || mb_strlen($telefon) > 60 || mb_strlen($nachricht) > 5000) {
    fehler();
}
if (preg_match_all('#https?://#i', $nachricht) > 3) {
    fehler();
}

$betreff = '=?UTF-8?B?' . base64_encode('Anfrage über die Website: ' . $name) . '?=';
$text = "Name: $name\nFirma: $firma\nE-Mail: $email\nTelefon: $telefon\n\nNachricht:\n$nachricht\n";
$kopfzeilen = "From: ianus IT Website <$absender>\r\n"
            . "Reply-To: $email\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: text/plain; charset=UTF-8";

// Das "-f" setzt den Absender für den Versand. Falls der Hoster das nicht erlaubt,
// den letzten Parameter einfach weglassen.
if (mail($empfaenger, $betreff, $text, $kopfzeilen, '-f' . $absender)) {
    header('Location: danke.html');
    exit;
}

fehler();
