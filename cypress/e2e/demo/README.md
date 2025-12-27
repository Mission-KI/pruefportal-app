# FeedbackAI Demo - Prüfworkflow

Diese Demo zeigt den vollständigen Prüfprozess nach dem **MISSION KI Qualitätsstandard** anhand eines realistischen Szenarios.

## Das Demo-Szenario

### Unternehmen: FeedbackAI UG

**FeedbackAI UG** ist ein fiktives Berliner Startup (gegründet 2023), das eine SaaS-Lösung für automatisierte Kundenfeedback-Analyse anbietet. Die Zielkunden sind mittelständische E-Commerce-Unternehmen.

### Das KI-System: FeedbackAI Analyzer v2.1

Das System analysiert deutschsprachige Kundenrezensionen und liefert:

- **Sentiment-Score:** Positiv / Neutral / Negativ (mit Konfidenzwert 0-100%)
- **Themen-Tags:** Automatische Zuordnung zu Kategorien (Lieferung, Produktqualität, Kundenservice, Preis-Leistung)
- **Aggregierte Dashboards:** Trends über Zeit, Vergleich zwischen Produktkategorien

**Technischer Stack:**
- Fine-tuned german-BERT (deepset/gbert-base)
- Cloud-hosted API (AWS eu-central-1)
- REST-API Integration für Shops (Shopify, WooCommerce, Magento)

### Personen im Demo

| Rolle | Person | Funktion |
|-------|--------|----------|
| Kandidat | Lisa Schmidt | CTO, Prüfungsverantwortliche |
| Prüfer | Dr. Michael Weber | Lead ML Engineer |

### Schutzbedarfsprofil

Das Demo demonstriert ein **ausgewogenes Schutzbedarfsprofil**, das verschiedene Stufen zeigt:

| Qualitätsdimension | Schutzbedarf | Begründung |
|--------------------|--------------|------------|
| **Datenschutz (DA)** | HOCH | Kundendaten, DSGVO-Relevanz |
| **Fairness (ND)** | MODERAT | Kein Personen-Scoring, aber möglicher Dialekt-Bias |
| **Transparenz (TR)** | MODERAT | B2B-Kunden erwarten Erklärbarkeit |
| **Mensch-KI-Interaktion (MA)** | MODERAT | Dashboard-Nutzer können korrigieren |
| **Verlässlichkeit (VE)** | GERING | Keine sicherheitskritischen Entscheidungen |
| **Cybersicherheit (CY)** | GERING | Standard-SaaS-Sicherheit ausreichend |

## Demo ausführen

### Voraussetzungen

1. **Anwendung starten:**
   ```bash
   # Im backend/ Verzeichnis
   ./start.sh
   ```

2. **Datenbank vorbereiten:** Die Demo-User müssen in der Datenbank existieren.
   ```bash
   # Seeds ausführen (innerhalb Docker)
   docker exec -it pruefportal-web bin/cake migrations seed

   # Hinweis: Seeds werden nur eingespielt wenn die users-Tabelle leer ist.
   # Bei bestehenden Daten müssen die User manuell angelegt werden oder
   # die Datenbank zurückgesetzt werden.
   ```

### Demo-Modus (Interaktiv)

Für Live-Präsentationen oder Video-Aufnahmen:

```bash
npm run cy:open
```

Dann `demo/feedbackai-workflow.cy.ts` auswählen.

**Vorteile:**
- Vollständige Kontrolle über Geschwindigkeit
- Pause-Funktion für Erklärungen
- Cypress-eigene Zeitreise-Funktion

### Automatische Ausführung (mit Video)

Für automatische Video-Generierung:

```bash
npm run cy:run -- --spec "cypress/e2e/demo/feedbackai-workflow.cy.ts"
```

Das Video wird unter `cypress/videos/` gespeichert.

## Tipps für Video-Aufnahmen

### Mit Cypress Video

Cypress erstellt automatisch Videos:

```bash
# Video-Qualität optimieren in cypress.config.ts
video: true,
videoCompression: false,  // Keine Komprimierung für beste Qualität
```

### Mit externem Screen-Recorder

1. **OBS Studio** oder **QuickTime** starten
2. Aufnahmebereich auf 1920x1080 setzen
3. Demo im interaktiven Modus starten
4. Bei wichtigen Schritten pausieren und erklären

### Empfohlene Viewport-Einstellung

Die Demo verwendet standardmäßig **1920x1080** (Full HD) für:
- Gute Lesbarkeit bei Präsentationen
- Standard-Format für Video-Plattformen
- Genügend Platz für alle UI-Elemente

## Dateien

```
cypress/
├── e2e/
│   └── demo/
│       ├── feedbackai-workflow.cy.ts  # Der Demo-Test
│       └── README.md                   # Diese Dokumentation
└── fixtures/
    └── demo-feedbackai.json            # Realistische Demo-Daten
```

## Workflow-Phasen im Detail

### Phase 1: Projekterstellung & Anwendungsfallbeschreibung

1. **Login:** Lisa Schmidt meldet sich an
2. **Projekt erstellen:** Neues Prüfprojekt für FeedbackAI Analyzer
3. **Prüfer hinzufügen:** Dr. Michael Weber als interner Prüfer
4. **UCD Schritt 1:** Allgemeine Informationen zum KI-System
   - Systemname, Beschreibung, Methodik
   - Eingabe- und Ausgabedomäne, Grenzen
5. **UCD Schritt 2:** Mensch-KI-Interaktion
   - Nutzergruppen, betroffene Personen
   - Menschliche Beteiligung und Aufsicht
6. **UCD Schritt 3:** Betrieb und Regulatorik
   - Cloud-Betrieb (AWS EU)
   - DSGVO-Compliance, AI Act Einordnung
7. **UCD Schritt 4:** Abschluss der Anwendungsfallbeschreibung

### Phase 2: Schutzbedarfsanalyse

1. **Navigation:** Zur Schutzbedarfsanalyse wechseln
2. **6 Qualitätsdimensionen bewerten:**
   - Applikationsfragen (AF): Ist die Dimension relevant?
   - Grundfragen (GF): Wie hoch ist der Schutzbedarf?
3. **Abschluss:** Bewertung einreichen

## Anpassung des Szenarios

Die Demo-Daten können in `cypress/fixtures/demo-feedbackai.json` angepasst werden:

- **Unternehmensdaten:** `project.title`, `project.description`
- **UCD-Inhalte:** `ucd.step1`, `ucd.step2`, `ucd.step3`
- **Schutzbedarfsprofil:** `pnaAnswers` (AF/GF-Werte pro QD)

## Hinweise

- Die Demo nutzt **dieselben Cypress-Commands** wie die Regression-Tests
- Alle Texte sind auf **Deutsch** für authentische Darstellung
- Die **Wartezeiten** (`cy.wait()`) sind bewusst eingebaut für bessere Sichtbarkeit
- Die Demo stoppt **nach der Schutzbedarfsanalyse** (Status 30) - VCIO-Selbsteinstufung ist optional
