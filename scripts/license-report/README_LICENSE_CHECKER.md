# License Checker Script

Ein automatisiertes Script zur Überprüfung aller Abhängigkeiten-Lizenzen in diesem Projekt mit **Allowlist-Unterstützung**.

## Verwendung

```bash
# Vollständige Analyse (alle Pakete)
./check-licenses.sh
./check-licenses.sh full

# Kompakte Analyse (nur problematische Pakete)
./check-licenses.sh compact

# Paket zur Allowlist hinzufügen
./check-licenses.sh --add-exception "package@version" "https://license-url" "Begründung"

# Genehmigte Pakete anzeigen
./check-licenses.sh --show-allowlist

# Hilfe anzeigen
./check-licenses.sh --help
```