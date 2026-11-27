<?php
// This file is part of Moodle - https://moodle.org/
// This file is part of a 108design source-available software product.
// Copyright (C) 2026 Andreas Giesen <andreas@108design.com>
// See LICENSE.md for the full terms.

/**
 * German language strings for Simply Upload.
 *
 * @package mod_upload
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license See LICENSE.md for the full terms.
 */
defined('MOODLE_INTERNAL') || die();

$string['uploadfiles_help'] = 'Wähle Dateien innerhalb der angezeigten Anzahl-, Größen- und Dateitypgrenzen. Klicke anschließend auf „Hochgeladene Dateien speichern“. Erst danach gehören die Dateien zu deiner Abgabe.';
$string['finalsubmitconfirm'] = 'Möchtest du endgültig abgeben? Danach kannst du die Dateien nicht mehr bearbeiten.';
$string['submissionlocked'] = 'Deine Abgabe ist endgültig und für die Bearbeitung gesperrt.';
$string['finalsubmitsuccess'] = 'Deine Abgabe ist jetzt endgültig.';
$string['cannotfinalsubmitnofiles'] = 'Speichere vor der endgültigen Abgabe mindestens eine Datei.';
$string['uploadcompleted'] = 'Dein Upload ist abgeschlossen.';
$string['uploadcompleted_canedit'] = 'Dein Upload ist gespeichert. Du kannst deine Dateien hier noch ändern.';
$string['finalsubmitneeded'] = 'Du hast Dateien gespeichert. Klicke auf „Endgültig abgeben“, um diese Aktivität abzuschließen.';
$string['finalsubmitneeded_nofiles'] = 'Lade mindestens eine Datei hoch und klicke danach auf „Endgültig abgeben“, um diese Aktivität abzuschließen.';
$string['adminhelp_upload_help'] = 'Speichere zuerst deine Dateien. Wenn eine endgültige Abgabe verlangt wird, sperrt „Endgültig abgeben“ danach die Bearbeitung.';
$string['adminhelp_upload_article'] = '<p>Wähle deine Dateien innerhalb der angezeigten Grenzen und klicke auf „Hochgeladene Dateien speichern“. Unter „Gespeicherte Dateien“ siehst du, was zu deiner Abgabe gehört. Ein Klick auf einen Dateinamen lädt die Datei herunter.</p><p>Wenn die Aktivität eine endgültige Abgabe verlangt, klickst du nach dem Speichern auf „Endgültig abgeben“. Danach sind die Dateien gesperrt. Ob du sie vorher erneut speichern oder ersetzen kannst, hängt von den Aktivitätseinstellungen ab.</p>';
$string['adminhelp_report_help'] = 'Hier siehst du die gespeicherten Dateien und den Abgabestatus. Das Leserecht erlaubt nicht automatisch das Löschen.';
$string['adminhelp_report_article'] = '<p>Der Reiter „Abgaben“ zeigt die Dateien der Teilnehmer/innen und ihren Abgabestatus. Über eine verlinkte Spaltenüberschrift änderst du die Sortierung. Ein Klick auf einen Dateinamen lädt die Datei herunter.</p><p>Das Recht zum Ansehen der Abgaben erteilt kein Löschrecht.</p>';
$string['adminhelp_deleting_article'] = '<p>Markiere Abgaben und wähle „Ausgewählte löschen“ oder verwende „Alle Abgaben löschen“ für diese Aktivität. Prüfe die Bestätigung vor dem Löschen.</p><p>Das Löschen entfernt die ausgewählten Abgabedatensätze und Dateien endgültig. Anschließend wird der uploadabhängige Abschluss für die betroffenen Personen neu berechnet. Andere Aktivitäten bleiben erhalten. Du benötigst das separate Recht „Simply-Upload-Abgaben löschen“.</p>';
$string['updateguard_hint'] = 'Verwende ein freigegebenes Paket für die installierte Ausgabe. Du kannst den Vorgang erneut prüfen.';
$string['updateguard_dependency'] = 'Eine benötigte Plugin-Version fehlt. Aktualisiere die Abhängigkeit gemeinsam mit diesem Plugin oder vorher.';
$string['updateguard_approval'] = 'Die Pakete oder der installierte Stand haben sich seit der Vorschau geändert. Prüfe den Vorgang erneut.';
$string['updateguard_busy'] = 'Eine andere Installation läuft. Versuche es nach deren Abschluss erneut.';
$string['updateguard_installed'] = 'Der installierte Updateschutz ist unvollständig. Stelle die Dateien der zuletzt installierten freigegebenen Version wieder her.';
$string['updateguard_operation'] = 'Diese Kombination von Installationsaktionen wird nicht unterstützt. Starte einen einzelnen Updatevorgang erneut.';
$string['updateinfo_choice'] = 'Kontrolliere vor dem Fortfahren die Paketquelle und Moodles Prüfergebnisse. Mit „{$a}“ unten kannst du abbrechen.';
