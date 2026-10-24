<?php
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

$string['pluginname'] = 'Simply Upload';
$string['modulename'] = 'Simply Upload';
$string['modulenameplural'] = 'Simply Upload';
$string['modulename_help'] = 'Mit Simply Upload können Teilnehmende eine oder mehrere Dateien abgeben. Je nach Einstellung zählt das Speichern der Dateien oder die endgültige Abgabe als Abschluss.';
$string['pluginadministration'] = 'Simply-Upload-Administration';
$string['upload:addinstance'] = 'Neue Simply-Upload-Aktivität hinzufügen';
$string['upload:view'] = 'Simply-Upload-Aktivität ansehen';
$string['upload:submit'] = 'Dateien in Simply Upload abgeben';
$string['upload:viewsubmissions'] = 'Simply-Upload-Abgaben ansehen';
$string['upload:deletesubmissions'] = 'Simply-Upload-Abgaben löschen';
$string['maxfiles'] = 'Maximale Anzahl der Dateien';
$string['maxbytes'] = 'Maximale Dateigröße';
$string['allowedtypes'] = 'Erlaubte Dateitypen';
$string['allowedtypes_help'] = 'Kommagetrennte Liste, zum Beispiel .pdf,.docx,.png. Ein leeres Feld erlaubt alle Dateitypen.';
$string['allowresubmission'] = 'Erneutes Speichern erlauben';
$string['allowresubmission_help'] = 'Wenn aktiviert, können Teilnehmende ihre Dateien bis zur endgültigen Abgabe ersetzen. Die endgültige Abgabe sperrt die Bearbeitung immer.';
$string['appenduserid'] = 'Nutzer-ID an Dateinamen anhängen';
$string['appenduserid_help'] = 'Wenn aktiviert, wird die Nutzer-ID an gespeicherte Dateinamen angehängt, zum Beispiel bericht_1234.pdf.';
$string['appendusername'] = 'Anmeldenamen an Dateinamen anhängen';
$string['appendusername_help'] = 'Wenn aktiviert, wird der Moodle-Anmeldename an gespeicherte Dateinamen angehängt, zum Beispiel bericht_jane.doe.pdf.';
$string['completionmode'] = 'Abschlussbedingung';
$string['completionmode_hasfile'] = 'Abgeschlossen, sobald mindestens eine Datei gespeichert ist';
$string['completionmode_finalsubmit'] = 'Erst nach der endgültigen Abgabe abgeschlossen';
$string['completionuploaddone'] = 'Teilnehmende müssen die Simply-Upload-Abschlussbedingung erfüllen';
$string['completionuploaddone_desc'] = 'Die Aktivität ist abgeschlossen, wenn die gewählte Simply-Upload-Abschlussbedingung erfüllt ist.';
$string['completionuploaddone_help'] = 'Je nach Abschlussbedingung muss mindestens eine Datei gespeichert oder die endgültige Abgabe bestätigt sein. Das Löschen einer Abgabe berechnet diesen Abschluss neu.';
$string['completiondetail:uploaddone'] = 'Die Simply-Upload-Abschlussbedingung muss erfüllt sein';
$string['uploadfiles'] = 'Dateien hochladen';
$string['uploadfiles_help'] = 'Wähle Dateien innerhalb der angezeigten Anzahl-, Größen- und Dateitypgrenzen. Klicke anschließend auf „Hochgeladene Dateien speichern“. Erst danach gehören die Dateien zu Deiner Abgabe.';
$string['uploadtab'] = 'Upload';
$string['submitfiles'] = 'Hochgeladene Dateien speichern';
$string['finalsubmit'] = 'Endgültig abgeben';
$string['finalsubmitconfirm'] = 'Möchtest Du endgültig abgeben? Danach kannst Du die Dateien nicht mehr bearbeiten.';
$string['finalsubmitdisabled'] = 'Die endgültige Abgabe ist verfügbar, sobald mindestens eine Datei gespeichert ist.';
$string['submissionlocked'] = 'Deine Abgabe ist endgültig und für die Bearbeitung gesperrt.';
$string['nofiles'] = 'Noch keine Dateien hochgeladen.';
$string['currentfiles'] = 'Gespeicherte Dateien';
$string['filesaved'] = 'Hochgeladene Dateien gespeichert.';
$string['finalsubmitsuccess'] = 'Deine Abgabe ist jetzt endgültig.';
$string['cannotfinalsubmitnofiles'] = 'Speichere vor der endgültigen Abgabe mindestens eine Datei.';
$string['uploadcompleted'] = 'Dein Upload ist abgeschlossen.';
$string['uploadcompleted_canedit'] = 'Dein Upload ist gespeichert. Du kannst Deine Dateien hier noch ändern.';
$string['finalsubmitneeded'] = 'Du hast Dateien gespeichert. Klicke auf „Endgültig abgeben“, um diese Aktivität abzuschließen.';
$string['finalsubmitneeded_nofiles'] = 'Lade mindestens eine Datei hoch und klicke danach auf „Endgültig abgeben“, um diese Aktivität abzuschließen.';
$string['returntocourse'] = 'Zurück zum Kurs';
$string['submissions'] = 'Abgaben';
$string['viewsubmissions'] = 'Abgaben ansehen';
$string['user'] = 'Nutzer/in';
$string['status'] = 'Status';
$string['filename'] = 'Datei';
$string['timeuploaded'] = 'Hochgeladen';
$string['submitted'] = 'Endgültig abgegeben';
$string['notsubmitted'] = 'Noch nicht endgültig abgegeben';
$string['status_finalsubmitted'] = 'Endgültig abgegeben';
$string['status_draftwithfiles'] = 'Dateien gespeichert (endgültige Abgabe steht aus)';
$string['status_draftnofiles'] = 'Entwurf (keine Dateien gespeichert)';
$string['status_uploaded'] = 'Dateien gespeichert';
$string['status_noupload'] = 'Noch kein Upload';
$string['selectall'] = 'Alle auswählen';
$string['deleteselected'] = 'Ausgewählte löschen';
$string['deleteallsubmissions'] = 'Alle Abgaben löschen';
$string['noselectedsubmissions'] = 'Keine Abgaben ausgewählt.';
$string['submissionsdeleted'] = '{$a} Abgabe(n) gelöscht.';
$string['nosubmissions'] = 'Keine Abgaben gefunden.';
$string['nonewmodules'] = 'In diesem Kurs wurden keine Simply-Upload-Aktivitäten gefunden.';
$string['pluginnotconfigured'] = 'Simply Upload ist nicht korrekt konfiguriert.';
$string['modulename_link'] = 'mod/upload/view';
$string['eventsubmissionupdated'] = 'Simply-Upload-Abgabe aktualisiert';
$string['eventsubmissionfinalised'] = 'Simply-Upload-Abgabe endgültig bestätigt';
$string['eventsubmissiondeleted'] = 'Simply-Upload-Abgabe gelöscht';
$string['page-mod-upload-x'] = 'Beliebige Simply-Upload-Modulseite';
$string['uploadfieldset'] = 'Simply-Upload-Einstellungen';
$string['privacy:metadata:submission'] = 'Abgaben der Teilnehmenden mit Status und Zeitpunkten der Abgabe.';
$string['privacy:metadata:submission:uploadid'] = 'Die Aktivität, zu der die Abgabe gehört.';
$string['privacy:metadata:submission:userid'] = 'Die Person, der die Abgabe gehört.';
$string['privacy:metadata:submission:status'] = 'Der Status der Abgabe.';
$string['privacy:metadata:submission:finalsubmitted'] = 'Ob die Person die endgültige Abgabe bestätigt hat.';
$string['privacy:metadata:submission:finalsubmittedat'] = 'Wann die endgültige Abgabe bestätigt wurde.';
$string['privacy:metadata:submission:timecreated'] = 'Wann der Abgabedatensatz erstellt wurde.';
$string['privacy:metadata:submission:timemodified'] = 'Wann die Abgabe zuletzt geändert wurde.';
$string['privacy:metadata:files'] = 'Abgabedateien, die personenbezogene Daten enthalten können. Dateinamen können je nach Einstellung die Nutzer-ID oder den Anmeldenamen enthalten.';
$string['privacy:path'] = 'Abgabe';
$string['confirmdeleteall'] = 'Alle Abgaben und ihre Dateien in dieser Aktivität löschen? Dies kann nicht rückgängig gemacht werden. Der uploadabhängige Abschluss wird neu berechnet.';
$string['confirmdeleteselected'] = 'Die ausgewählten Abgaben und ihre Dateien in dieser Aktivität löschen? Dies kann nicht rückgängig gemacht werden. Der uploadabhängige Abschluss wird neu berechnet.';
$string['selectsubmission'] = 'Abgabe von {$a} auswählen';
$string['deletionhelp'] = 'Beim Löschen einer Abgabe werden auch ihre Dateien entfernt und der uploadabhängige Abschluss wird neu berechnet. Vor dem Löschen folgt eine Bestätigung.';
$string['adminhelp'] = 'Simply-Upload-Hilfe';
$string['adminhelptopics'] = 'Hilfethemen';
$string['adminhelpmore'] = 'Mehr dazu im Hilfepanel';
$string['adminhelp_upload'] = 'Dateien speichern und endgültig abgeben';
$string['adminhelp_upload_help'] = 'Speichere zuerst Deine Dateien. Wenn eine endgültige Abgabe verlangt wird, sperrt „Endgültig abgeben“ danach die Bearbeitung.';
$string['adminhelp_upload_article'] = '<p>Wähle Deine Dateien innerhalb der angezeigten Grenzen und klicke auf „Hochgeladene Dateien speichern“. Unter „Gespeicherte Dateien“ siehst Du, was zu Deiner Abgabe gehört. Ein Klick auf einen Dateinamen lädt die Datei herunter.</p><p>Wenn die Aktivität eine endgültige Abgabe verlangt, klicke nach dem Speichern auf „Endgültig abgeben“. Danach sind die Dateien gesperrt. Ob Du sie vorher erneut speichern oder ersetzen kannst, hängt von den Aktivitätseinstellungen ab.</p>';
$string['adminhelp_report'] = 'Abgaben ansehen';
$string['adminhelp_report_help'] = 'Hier siehst Du die gespeicherten Dateien und den Abgabestatus. Das Leserecht erlaubt nicht automatisch das Löschen.';
$string['adminhelp_report_article'] = '<p>Der Reiter „Abgaben“ zeigt die Dateien der Teilnehmenden und ihren Abgabestatus. Über eine verlinkte Spaltenüberschrift änderst Du die Sortierung. Ein Klick auf einen Dateinamen lädt die Datei herunter.</p><p>Das Recht zum Ansehen der Abgaben erteilt kein Löschrecht.</p>';
$string['adminhelp_deleting'] = 'Abgaben löschen';
$string['adminhelp_deleting_help'] = 'Löschen entfernt Abgabedatensätze und Dateien aus dieser Aktivität. Die nächste Seite verlangt eine Bestätigung.';
$string['adminhelp_deleting_article'] = '<p>Markiere Abgaben und wähle „Ausgewählte löschen“ oder verwende „Alle Abgaben löschen“ für diese Aktivität. Prüfe die Bestätigung vor dem Löschen.</p><p>Das Löschen entfernt die ausgewählten Abgabedatensätze und Dateien endgültig. Anschließend wird der uploadabhängige Abschluss für die betroffenen Personen neu berechnet. Andere Aktivitäten bleiben erhalten. Du benötigst das separate Recht „Simply-Upload-Abgaben löschen“.</p>';
$string['deleting'] = 'Abgaben löschen';
$string['deleting_help'] = 'Löschen entfernt Abgabedatensätze und Dateien aus dieser Aktivität. Die nächste Seite verlangt eine Bestätigung.';
