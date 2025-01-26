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
