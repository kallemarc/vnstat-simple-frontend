# vnStat Netzwerkstatistik-Viewer

Dieses Projekt stellt eine Weboberfläche bereit, die Netzwerkstatistiken von einem Server anzeigt, auf dem `vnStat` installiert ist. Es wird eine Übersicht über die Datenmengen im Stunden-, Tages- und Monatsbereich zur Verfügung gestellt.

## Voraussetzungen

- **vnStat** muss auf dem Server installiert sein und über den Pfad `/usr/bin/vnstat` erreichbar sein.
- PHP muss auf dem Server installiert sein.
- Der Webserver muss so konfiguriert sein, dass das Skript über einen Browser zugänglich ist.

## Installation

1. Stelle sicher, dass `vnStat` installiert und funktionsfähig ist:
   ```bash
   sudo apt install vnstat

2. Lade die PHP-Datei in das Webserver-Root oder ein beliebiges Verzeichnis, das durch den Webserver zugänglich ist.

3. Rufe die Datei über deinen Webbrowser auf, um die Netzwerkstatistiken anzuzeigen.

## Funktionen

- **Stundensicht**: Zeigt den Traffic pro Stunde an.
- **Tagessicht**: Zeigt den Traffic pro Tag an.
- **Monatssicht**: Zeigt den Traffic pro Monat an.
- **Gesamtanzeige**: Zeigt die Gesamtmenge an empfangenen und gesendeten Daten für die jeweiligen Zeiträume.

