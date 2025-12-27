#!/bin/bash

# License Checker Script
# Generiert einen Lizenzbericht für alle Abhängigkeiten im Projekt

set -e

# Get the script directory to put files in the right place
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKEND_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"
REPORT_FILE="$SCRIPT_DIR/LICENSE_REPORT.md"
ALLOWLIST_FILE="$SCRIPT_DIR/license-allowlist.json"
TEMP_DIR=$(mktemp -d)

# Hilfsfunktionen für Allowlist-Management
show_allowlist() {
    if [ -f "$ALLOWLIST_FILE" ]; then
        echo "📋 Genehmigte Pakete:"
        jq -r '.approved_packages | to_entries[] | "  \(.key) - \(.value.reason)"' "$ALLOWLIST_FILE" 2>/dev/null || echo "Fehler beim Lesen der Allowlist"
        echo ""
        echo "📋 Globale Lizenz-Regeln:"
        jq -r '.global_license_rules | to_entries[] | "  \(.key): \(.value)"' "$ALLOWLIST_FILE" 2>/dev/null || echo "Keine globalen Regeln"
    else
        echo "ℹ️  Keine Ausnahme-Liste vorhanden"
        echo "💡 Erstelle mit: ./check-licenses.sh --add-exception <package@version> <url> <reason>"
    fi
}

add_package_to_allowlist() {
    local package_version="$1"
    local license_url="$2"
    local reason="$3"
    
    if [ -z "$package_version" ] || [ -z "$license_url" ] || [ -z "$reason" ]; then
        echo "❌ Verwendung: ./check-licenses.sh --add-exception <package@version> <license_url> <reason>"
        echo "   Beispiel: ./check-licenses.sh --add-exception 'caniuse-lite@1.0.30001733' 'https://github.com/Fyrd/caniuse' 'CC-BY-4.0 ok für Datensammlungen'"
        exit 1
    fi
    
    # Initialisiere Allowlist falls sie nicht existiert
    if [ ! -f "$ALLOWLIST_FILE" ]; then
        cat > "$ALLOWLIST_FILE" << 'EOF'
{
  "approved_packages": {},
  "global_license_rules": {
    "CC0-1.0": "Akzeptabel - Public Domain",
    "CC-BY-4.0": "Akzeptabel für Datensammlungen"
  }
}
EOF
    fi
    
    # Füge zur Allowlist hinzu
    local temp_file=$(mktemp)
    jq --arg key "$package_version" \
       --arg license_url "$license_url" \
       --arg reason "$reason" \
       --arg approved_by "$(whoami)" \
       '.approved_packages[$key] = {
         "reason": $reason,
         "license_url": $license_url,
         "approved_by": $approved_by,
         "notes": "Hinzugefügt am '$(date +%Y-%m-%d)'"
       }' "$ALLOWLIST_FILE" > "$temp_file" && mv "$temp_file" "$ALLOWLIST_FILE"
    
    echo "✅ Paket $package_version zur Ausnahme-Liste hinzugefügt"
    echo "📝 Du kannst die Datei $ALLOWLIST_FILE auch manuell bearbeiten"
}

# Parameter handling
case "${1:-full}" in
    --add-exception)
        add_package_to_allowlist "$2" "$3" "$4"
        exit 0
        ;;
    --show-allowlist)
        show_allowlist
        exit 0
        ;;
    --help|-h)
        echo "License Checker Script - Verwendung:"
        echo "  ./check-licenses.sh [compact|full]     # Erstelle Lizenzbericht"
        echo "  ./check-licenses.sh --add-exception    # Füge Paket zur Allowlist hinzu"
        echo "  ./check-licenses.sh --show-allowlist   # Zeige genehmigte Pakete"
        echo "  ./check-licenses.sh --help            # Diese Hilfe"
        exit 0
        ;;
    compact|full)
        COMPACT_MODE="$1"
        ;;
    *)
        COMPACT_MODE="full"
        ;;
esac

echo "🔍 Erstelle Lizenzbericht..."

# Lade Allowlist falls vorhanden
load_allowlist() {
    if [ -f "$ALLOWLIST_FILE" ]; then
        local count=$(jq '.approved_packages | length' "$ALLOWLIST_FILE" 2>/dev/null || echo "0")
        echo "📋 Lade Ausnahme-Liste ($count genehmigte Pakete)..."
    else
        echo "ℹ️  Keine Ausnahme-Liste gefunden"
    fi
}

# Prüfe ob Paket in der Allowlist steht
is_package_approved() {
    local package="$1"
    local version="$2"
    local license="$3"
    local package_key="${package}@${version}"
    
    if [ -f "$ALLOWLIST_FILE" ]; then
        # Prüfe ob genauer Paket@Version Match
        if jq -e ".approved_packages.\"$package_key\"" "$ALLOWLIST_FILE" > /dev/null 2>&1; then
            return 0  # approved
        fi
        
        # Prüfe globale Lizenz-Regeln
        if [ -n "$license" ] && jq -e ".global_license_rules.\"$license\"" "$ALLOWLIST_FILE" > /dev/null 2>&1; then
            return 0  # approved by global rule
        fi
    fi
    
    return 1  # not approved
}

# Funktion zur Kategorisierung der Lizenz-Kompatibilität
categorize_license() {
    local license="$1"
    local package="$2"
    local version="$3"
    
    # Erst prüfen ob in Allowlist
    if is_package_approved "$package" "$version" "$license"; then
        echo "✅ GENEHMIGT (Allowlist)"
        return
    fi
    
    license_lower=$(echo "$license" | tr '[:upper:]' '[:lower:]')
    
    case "$license_lower" in
        *mit*|*apache*|*bsd*|*isc*)
            echo "✅ KOMPATIBEL"
            ;;
        *gpl*|*agpl*|*lgpl*)
            echo "⚠️  GPL LIZENZ"
            ;;
        *mpl*|*mozilla*)
            echo "⚠️  COPYLEFT"
            ;;
        "unknown"|"unlicense"|"")
            echo "❌ UNBEKANNT"
            ;;
        *)
            echo "❓ PRÜFUNG ERFORDERLICH"
            ;;
    esac
}

# Funktion für Farb-Kennzeichnung der Lizenz-Kategorie
get_license_color() {
    local category="$1"
    case "$category" in
        "✅ KOMPATIBEL"|"✅ GENEHMIGT (Allowlist)")
            echo "![#00ff00](https://via.placeholder.com/15/00ff00/000000?text=+)"
            ;;
        "⚠️  GPL LIZENZ"|"⚠️  COPYLEFT")
            echo "![#ffff00](https://via.placeholder.com/15/ffff00/000000?text=+)"
            ;;
        "❌ UNBEKANNT"|"❓ PRÜFUNG ERFORDERLICH")
            echo "![#ff0000](https://via.placeholder.com/15/ff0000/000000?text=+)"
            ;;
    esac
}

# Prüfung ob Lizenz problematisch ist
is_problematic_license() {
    local category="$1"
    case "$category" in
        "⚠️  GPL LIZENZ"|"⚠️  COPYLEFT"|"❌ UNBEKANNT"|"❓ PRÜFUNG ERFORDERLICH")
            return 0  # true - problematisch
            ;;
        "✅ KOMPATIBEL"|"✅ GENEHMIGT (Allowlist)")
            return 1  # false - nicht problematisch
            ;;
        *)
            return 1  # false - nicht problematisch
            ;;
    esac
}

# Lade Allowlist
load_allowlist

# Initialisierung des Reports
cat > "$REPORT_FILE" << EOF
# Lizenzbericht

**Erstellt am:** $(date)
**Modus:** $([ "$COMPACT_MODE" = "compact" ] && echo "Kompakt (nur problematische Pakete)" || echo "Vollständig (alle Pakete)")

## Zusammenfassung

Dieser Bericht analysiert alle Abhängigkeiten des Projekts zur Sicherstellung der Lizenz-Compliance.
**Ziel:** Nur MIT- und Apache-lizensierte Abhängigkeiten sind für uneingeschränkte Nutzung bevorzugt.

EOF

# Temporäre Dateien für problematische Pakete
PROBLEMATIC_NODE="$TEMP_DIR/problematic_node.md"
PROBLEMATIC_PHP="$TEMP_DIR/problematic_php.md"
touch "$PROBLEMATIC_NODE" "$PROBLEMATIC_PHP"

# Zählvariablen
NODE_TOTAL=0
NODE_COMPATIBLE=0
NODE_PROBLEMATIC=0
PHP_TOTAL=0
PHP_COMPATIBLE=0
PHP_PROBLEMATIC=0

# Überprüfung der Node.js Abhängigkeiten
if [ -f "$BACKEND_DIR/package.json" ]; then
    echo "📦 Analysiere Node.js Abhängigkeiten..."
    
    # Generiere Lizenzdaten für Node.js Pakete
    (cd "$BACKEND_DIR" && npx license-checker --json > "$TEMP_DIR/node_licenses.json") 2>/dev/null || {
        echo "Warnung: Node.js Lizenzbericht konnte nicht erstellt werden"
        echo "{}" > "$TEMP_DIR/node_licenses.json"
    }
    
    # Zähle und analysiere Node.js Pakete
    NODE_TOTAL=$(cat "$TEMP_DIR/node_licenses.json" | jq 'keys | length' 2>/dev/null || echo "0")
    
    if [ -f "$TEMP_DIR/node_licenses.json" ] && [ -s "$TEMP_DIR/node_licenses.json" ]; then
        cat "$TEMP_DIR/node_licenses.json" | jq -r 'to_entries[] | [.key, .value.version // "N/A", .value.licenses // "Unknown"] | @tsv' | sort | while IFS=$'\t' read -r package_full version license; do
            # Extrahiere Paketname ohne Version  
            package=$(echo "$package_full" | sed 's/@[^@]*$//')
            category=$(categorize_license "$license" "$package" "$version")
            color=$(get_license_color "$category")
            
            # Zähle kompatible Pakete
            if [ "$category" = "✅ KOMPATIBEL" ] || [ "$category" = "✅ GENEHMIGT (Allowlist)" ]; then
                echo "compatible" >> "$TEMP_DIR/node_count_compatible"
            else
                echo "problematic" >> "$TEMP_DIR/node_count_problematic"
                # Sammle problematische Pakete
                echo "| **$package_full** | $version | $license | $color $category |" >> "$PROBLEMATIC_NODE"
            fi
            
            # Für vollständige Liste (falls benötigt)
            echo "| $package_full | $version | $license | $color $category |" >> "$TEMP_DIR/node_all.md"
        done
    fi
    
    NODE_COMPATIBLE=$([ -f "$TEMP_DIR/node_count_compatible" ] && wc -l < "$TEMP_DIR/node_count_compatible" || echo "0")
    NODE_PROBLEMATIC=$([ -f "$TEMP_DIR/node_count_problematic" ] && wc -l < "$TEMP_DIR/node_count_problematic" || echo "0")
fi

# Überprüfung der PHP Abhängigkeiten
if [ -f "$BACKEND_DIR/composer.json" ]; then
    echo "🐘 Analysiere PHP Abhängigkeiten..."
    
    # Generiere Lizenzdaten für PHP Pakete
    if docker ps | grep -q "mission-ki-php"; then
        docker exec -w /app mission-ki-php composer licenses --format=json > "$TEMP_DIR/php_licenses.json" 2>/dev/null || {
            echo "Warnung: PHP Lizenzbericht aus Docker Container konnte nicht erstellt werden"
            echo '{"dependencies": {}}' > "$TEMP_DIR/php_licenses.json"
        }
    elif [ -d "$BACKEND_DIR/vendor" ]; then
        (cd "$BACKEND_DIR" && composer licenses --format=json > "$TEMP_DIR/php_licenses.json") 2>/dev/null || {
            echo "Warnung: PHP Lizenzbericht konnte nicht erstellt werden"
            echo '{"dependencies": {}}' > "$TEMP_DIR/php_licenses.json"
        }
    else
        echo "Warnung: Weder Docker Container 'mission-ki-php' gefunden noch vendor Verzeichnis vorhanden."
        echo "Führe 'composer install' aus oder starte Docker Container."
        echo '{"dependencies": {}}' > "$TEMP_DIR/php_licenses.json"
    fi
    
    # Zähle und analysiere PHP Pakete
    PHP_TOTAL=$(cat "$TEMP_DIR/php_licenses.json" | jq '.dependencies | length' 2>/dev/null || echo "0")
    
    if [ -f "$TEMP_DIR/php_licenses.json" ] && [ -s "$TEMP_DIR/php_licenses.json" ]; then
        cat "$TEMP_DIR/php_licenses.json" | jq -r '.dependencies | to_entries[]? | [.key, .value.version, (.value.license | if type == "array" then join(", ") else . end)] | @tsv' 2>/dev/null | sort | while IFS=$'\t' read -r package version license; do
            category=$(categorize_license "$license" "$package" "$version")
            color=$(get_license_color "$category")
            
            # Zähle kompatible Pakete
            if [ "$category" = "✅ KOMPATIBEL" ] || [ "$category" = "✅ GENEHMIGT (Allowlist)" ]; then
                echo "compatible" >> "$TEMP_DIR/php_count_compatible"
            else
                echo "problematic" >> "$TEMP_DIR/php_count_problematic"
                # Sammle problematische Pakete
                echo "| **$package** | $version | $license | $color $category |" >> "$PROBLEMATIC_PHP"
            fi
            
            # Für vollständige Liste (falls benötigt)
            echo "| $package | $version | $license | $color $category |" >> "$TEMP_DIR/php_all.md"
        done
    fi
    
    PHP_COMPATIBLE=$([ -f "$TEMP_DIR/php_count_compatible" ] && wc -l < "$TEMP_DIR/php_count_compatible" || echo "0")
    PHP_PROBLEMATIC=$([ -f "$TEMP_DIR/php_count_problematic" ] && wc -l < "$TEMP_DIR/php_count_problematic" || echo "0")
fi

# Erstelle Zusammenfassung
TOTAL_PACKAGES=$((NODE_TOTAL + PHP_TOTAL))
TOTAL_COMPATIBLE=$((NODE_COMPATIBLE + PHP_COMPATIBLE))
TOTAL_PROBLEMATIC=$((NODE_PROBLEMATIC + PHP_PROBLEMATIC))

cat >> "$REPORT_FILE" << EOF
### Gesamt-Übersicht

| Typ | Gesamt | Kompatibel | Prüfung erforderlich |
|-----|---------|-----------|---------------------|
| Node.js | $NODE_TOTAL | $NODE_COMPATIBLE | $NODE_PROBLEMATIC |
| PHP | $PHP_TOTAL | $PHP_COMPATIBLE | $PHP_PROBLEMATIC |
| **Gesamt** | **$TOTAL_PACKAGES** | **$TOTAL_COMPATIBLE** | **$TOTAL_PROBLEMATIC** |

EOF

# Problematische Pakete prominent anzeigen (falls vorhanden)
if [ "$TOTAL_PROBLEMATIC" -gt 0 ]; then
    cat >> "$REPORT_FILE" << 'EOF'
## ⚠️ ACHTUNG: Pakete die eine Prüfung benötigen

**Diese Pakete sollten vor der Produktionsnutzung überprüft werden:**

| Paket | Version | Lizenz | Status |
|--------|---------|---------|---------|
EOF
    
    # Füge problematische Node.js Pakete hinzu
    if [ -s "$PROBLEMATIC_NODE" ]; then
        cat "$PROBLEMATIC_NODE" >> "$REPORT_FILE"
    fi
    
    # Füge problematische PHP Pakete hinzu  
    if [ -s "$PROBLEMATIC_PHP" ]; then
        cat "$PROBLEMATIC_PHP" >> "$REPORT_FILE"
    fi
    
    cat >> "$REPORT_FILE" << EOF

### 💡 Pakete zur Allowlist hinzufügen

Du kannst problematische Pakete nach Prüfung genehmigen:

\`\`\`bash
# Beispiel: 
./check-licenses.sh --add-exception "paket-name@version" "https://license-url" "Begründung"

# Oder bearbeite manuell: $ALLOWLIST_FILE
\`\`\`

---

EOF
fi

# Detaillierte Auflistung (je nach Modus)
if [ "$COMPACT_MODE" = "full" ] || [ "$TOTAL_PROBLEMATIC" -eq 0 ]; then
    if [ -f "$BACKEND_DIR/package.json" ] && [ -s "$TEMP_DIR/node_all.md" ]; then
        cat >> "$REPORT_FILE" << EOF

## Node.js Abhängigkeiten (Details)

| Paket | Version | Lizenz | Status |
|--------|---------|---------|---------|
EOF
        cat "$TEMP_DIR/node_all.md" >> "$REPORT_FILE"
    fi
    
    if [ -f "$BACKEND_DIR/composer.json" ] && [ -s "$TEMP_DIR/php_all.md" ]; then
        cat >> "$REPORT_FILE" << EOF

## PHP Abhängigkeiten (Details)

| Paket | Version | Lizenz | Status |
|--------|---------|---------|---------|
EOF
        cat "$TEMP_DIR/php_all.md" >> "$REPORT_FILE"
    fi
fi

# Empfehlungen hinzufügen
cat >> "$REPORT_FILE" << 'EOF'

## Empfehlungen

### ✅ Kompatible Lizenzen (Sicher verwendbar)
- **MIT Lizenz**: Sehr liberal, erlaubt kommerzielle Nutzung
- **Apache Lizenz 2.0**: Liberal mit Patentschutz-Klausel
- **BSD Lizenzen**: Liberal mit minimalen Einschränkungen
- **ISC Lizenz**: Ähnlich MIT, sehr liberal

### ⚠️ Lizenzen die eine Überprüfung benötigen
- **GPL/LGPL/AGPL**: Copyleft-Lizenzen erfordern Quellcode-Veröffentlichung
- **MPL (Mozilla Public License)**: Schwächeres Copyleft, Einschränkungen auf Datei-Ebene

### ❌ Problematische Lizenzen
- **Unbekannt/Unlicense**: Kompatibilität kann nicht überprüft werden
- **Custom-Lizenzen**: Benötigen individuelle juristische Bewertung

### Nächste Schritte
1. Alle als "PRÜFUNG ERFORDERLICH" oder "UNBEKANNT" markierte Pakete überprüfen
2. Bei GPL-lizenzierten Paketen Alternativen prüfen, wenn Quellcode-Veröffentlichung nicht gewünscht
3. Bei unklaren oder benutzerdefinierten Lizenzen Rechtsberatung einholen
4. Genehmigte Pakete zur Allowlist hinzufügen

---
*Dieser Bericht wurde automatisch generiert. Bei Fragen wenden Sie sich an das Entwicklungsteam.*
EOF

echo "✅ Lizenzbericht erstellt: $REPORT_FILE"

# Cleanup
rm -rf "$TEMP_DIR"

# Zeige Zusammenfassung
echo ""
echo "📊 Zusammenfassung:"
if [ -f "$BACKEND_DIR/package.json" ]; then
    echo "   Node.js Pakete: $NODE_TOTAL gesamt, $NODE_COMPATIBLE kompatibel, $NODE_PROBLEMATIC benötigen Prüfung"
fi
if [ -f "$BACKEND_DIR/composer.json" ]; then
    echo "   PHP Pakete: $PHP_TOTAL gesamt, $PHP_COMPATIBLE kompatibel, $PHP_PROBLEMATIC benötigen Prüfung"
fi
echo ""
if [ "$TOTAL_PROBLEMATIC" -gt 0 ]; then
    echo "⚠️  $TOTAL_PROBLEMATIC Paket(e) benötigen eine Überprüfung - siehe Report für Details!"
    echo "💡 Füge geprüfte Pakete zur Allowlist hinzu: ./check-licenses.sh --add-exception"
else
    echo "✅ Alle Pakete verwenden kompatible Lizenzen!"
fi
echo ""
echo "📄 Vollständiger Bericht verfügbar in: $REPORT_FILE"
echo ""
echo "🛠️  Befehle:"
echo "   ./check-licenses.sh compact           # Kompakte Ansicht"
echo "   ./check-licenses.sh --show-allowlist  # Genehmigte Pakete anzeigen"
echo "   ./check-licenses.sh --add-exception   # Paket zur Allowlist hinzufügen"