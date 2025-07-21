# Lastenheft: Container Block Designer für WordPress

**Version:** 1.0  
**Datum:** 19. Juli 2025  
**Status:** Entwurf

---

## 1. Zielbestimmung

### 1.1 Musskriterien
Das Plugin muss folgende Kernfunktionalitäten bereitstellen:

- **LF-010** Visuelle Erstellung von Container-Blöcken über eine intuitive Benutzeroberfläche
- **LF-020** Integration der erstellten Blöcke als Gutenberg-Blöcke im WordPress Editor
- **LF-030** Zentrale Verwaltung aller erstellten Container-Blöcke
- **LF-040** Speicherung der Block-Konfigurationen in der WordPress-Datenbank
- **LF-050** Modulare Plugin-Architektur mit getrennten, wartbaren Komponenten

### 1.2 Wunschkriterien
Folgende Funktionen erhöhen den Nutzen, sind aber nicht zwingend erforderlich:

- **LF-060** Import/Export-Funktionalität für Block-Konfigurationen
- **LF-070** Versionsverwaltung für Block-Designs
- **LF-080** Template-Bibliothek mit vorgefertigten Designs
- **LF-090** Kollaborative Bearbeitung durch mehrere Benutzer

### 1.3 Abgrenzungskriterien
Das Plugin wird explizit NICHT:

- Eigene Page-Builder-Funktionalität implementieren
- Den WordPress Core modifizieren
- Externe Design-Services oder APIs zwingend benötigen
- Theme-spezifische Funktionen übernehmen

---

## 2. Produkteinsatz

### 2.1 Anwendungsbereiche
- Erstellung wiederverwendbarer Container-Layouts für WordPress-Websites
- Design-Standardisierung über mehrere Seiten/Beiträge
- Vereinfachung der Content-Erstellung für Redakteure

### 2.2 Zielgruppen

#### Primäre Zielgruppen:
- **WordPress-Entwickler**: Erstellen maßgeschneiderter Container-Blöcke für Kundenprojekte
- **Web-Designer**: Visuelle Gestaltung ohne Programmierkenntnisse
- **Site-Administratoren**: Verwaltung und Bereitstellung von Block-Templates

#### Sekundäre Zielgruppen:
- **Content-Editoren**: Verwendung der erstellten Blöcke
- **Marketing-Teams**: Schnelle Erstellung konsistenter Landing-Pages

### 2.3 Betriebsbedingungen

#### Technische Voraussetzungen:
- WordPress Version 6.0 oder höher
- PHP Version 8.0 oder höher
- MySQL 5.7+ oder MariaDB 10.3+
- Moderner Webbrowser (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)
- JavaScript aktiviert

#### Betriebsumgebung:
- Shared Hosting, VPS oder Dedicated Server
- HTTPS empfohlen für REST API
- Mindestens 128 MB PHP Memory Limit

---

## 3. Produktfunktionen

### 3.1 Block Designer (Admin-Interface)

#### LF-100: Visueller Design-Editor
- **LF-101** Drag-and-Drop Interface für Container-Elemente
- **LF-102** Live-Vorschau der Block-Gestaltung
- **LF-103** Rückgängig/Wiederherstellen-Funktionalität
- **LF-104** Responsive Vorschau (Desktop/Tablet/Mobile)

#### LF-110: Design-Einstellungen
- **LF-111** Spacing-Kontrollen (Padding/Margin) mit visueller Darstellung
- **LF-112** Typografie-Einstellungen (Schriftart, Größe, Zeilenhöhe)
- **LF-113** Farb-Einstellungen mit Color-Picker
- **LF-114** Rahmen und Schatten-Einstellungen
- **LF-115** Hintergrund-Optionen (Farbe, Gradient, Bild)
- **LF-116** Animations-Einstellungen (Ein-/Ausblenden, Hover-Effekte)

#### LF-120: Layout-Optionen
- **LF-121** Flexbox/Grid Layout-Unterstützung
- **LF-122** Spalten-System mit anpassbaren Breakpoints
- **LF-123** Verschachtelbare Container
- **LF-124** Ausrichtungs-Optionen (horizontal/vertikal)

### 3.2 Block-Verwaltung

#### LF-200: Block-Liste
- **LF-201** Übersicht aller erstellten Blöcke
- **LF-202** Suchfunktion nach Name/Beschreibung
- **LF-203** Filterung nach Status (aktiv/inaktiv)
- **LF-204** Sortierung nach Erstellungsdatum/Name
- **LF-205** Bulk-Aktionen (Löschen, Deaktivieren)

#### LF-210: Block-Operationen
- **LF-211** Block erstellen mit Wizard
- **LF-212** Block bearbeiten
- **LF-213** Block duplizieren
- **LF-214** Block löschen (mit Bestätigung)
- **LF-215** Block aktivieren/deaktivieren

#### LF-220: Block-Metadaten
- **LF-221** Eindeutiger Block-Name (Slug)
- **LF-222** Anzeigename für Editor
- **LF-223** Beschreibung
- **LF-224** Icon-Auswahl
- **LF-225** Kategorie-Zuordnung
- **LF-226** Schlagwörter für Suche

### 3.3 Gutenberg Integration

#### LF-300: Block-Registrierung
- **LF-301** Automatische Registrierung aller aktiven Blöcke
- **LF-302** Eigene Block-Kategorie "Container Blöcke"
- **LF-303** Block-Vorschau im Inserter
- **LF-304** Block-Variationen für verschiedene Layouts

#### LF-310: Editor-Funktionen
- **LF-311** InnerBlocks-Unterstützung für Inhalte
- **LF-312** Block-Toolbar mit Quick-Edit-Optionen
- **LF-313** Inspector-Controls für Feineinstellungen
- **LF-314** Responsive Einstellungen pro Instanz
- **LF-315** Eigene CSS-Klassen pro Instanz

#### LF-320: Block-Features
- **LF-321** Wiederverwendbare Blöcke unterstützen
- **LF-322** Block-Patterns kompatibel
- **LF-323** Full-Site-Editing kompatibel
- **LF-324** Widget-Bereich kompatibel

### 3.4 Datenmanagement

#### LF-400: Datenspeicherung
- **LF-401** Custom Database Table für Block-Konfigurationen
- **LF-402** JSON-basierte Speicherung der Einstellungen
- **LF-403** Versionierung der Konfigurationen
- **LF-404** Backup-Funktionalität

#### LF-410: Import/Export
- **LF-411** Export einzelner Blöcke als JSON
- **LF-412** Export aller Blöcke als ZIP
- **LF-413** Import mit Konfliktbehandlung
- **LF-414** Template-Sharing-Format

### 3.5 REST API

#### LF-500: API Endpoints
- **LF-501** GET /blocks - Liste aller Blöcke
- **LF-502** GET /blocks/{id} - Einzelner Block
- **LF-503** POST /blocks - Neuen Block erstellen
- **LF-504** PUT /blocks/{id} - Block aktualisieren
- **LF-505** DELETE /blocks/{id} - Block löschen
- **LF-506** POST /blocks/{id}/duplicate - Block duplizieren

#### LF-510: API-Sicherheit
- **LF-511** Authentifizierung via WordPress Nonce
- **LF-512** Capability-basierte Zugriffskontrolle
- **LF-513** Rate-Limiting
- **LF-514** Input-Validierung

---

## 4. Produktdaten

### 4.1 Datenmodell

#### Block-Konfiguration (cbd_blocks)
```sql
- id (INT, Primary Key)
- block_name (VARCHAR 255, Unique)
- block_title (VARCHAR 255)
- block_description (TEXT)
- block_icon (VARCHAR 100)
- block_category (VARCHAR 100)
- block_keywords (TEXT)
- block_config (LONGTEXT, JSON)
- block_styles (LONGTEXT, CSS)
- block_scripts (LONGTEXT, JS)
- allowed_blocks (TEXT, JSON Array)
- template_structure (LONGTEXT, JSON)
- version (INT)
- status (ENUM: 'active', 'inactive', 'draft')
- created_by (BIGINT, User ID)
- created_at (DATETIME)
- updated_at (DATETIME)
- deleted_at (DATETIME, Soft Delete)
```

#### Block-Versionen (cbd_block_versions)
```sql
- id (INT, Primary Key)
- block_id (INT, Foreign Key)
- version_number (INT)
- config_snapshot (LONGTEXT, JSON)
- created_by (BIGINT, User ID)
- created_at (DATETIME)
- change_notes (TEXT)
```

### 4.2 Konfigurationsstruktur

```json
{
  "name": "hero-section",
  "title": "Hero Section",
  "styles": {
    "desktop": {
      "padding": { "top": 60, "right": 20, "bottom": 60, "left": 20 },
      "margin": { "top": 0, "right": 0, "bottom": 0, "left": 0 },
      "backgroundColor": "#f8f9fa",
      "backgroundImage": "",
      "backgroundPosition": "center center",
      "backgroundSize": "cover",
      "borderWidth": 0,
      "borderColor": "#dddddd",
      "borderRadius": 0,
      "boxShadow": "none",
      "minHeight": "400px",
      "display": "flex",
      "flexDirection": "column",
      "justifyContent": "center",
      "alignItems": "center"
    },
    "tablet": {
      "padding": { "top": 40, "right": 15, "bottom": 40, "left": 15 },
      "minHeight": "300px"
    },
    "mobile": {
      "padding": { "top": 30, "right": 10, "bottom": 30, "left": 10 },
      "minHeight": "250px"
    }
  },
  "allowedBlocks": [
    "core/heading",
    "core/paragraph",
    "core/buttons",
    "core/image"
  ],
  "template": [
    ["core/heading", { "level": 1, "placeholder": "Überschrift eingeben" }],
    ["core/paragraph", { "placeholder": "Beschreibungstext eingeben" }],
    ["core/buttons", {}]
  ],
  "animations": {
    "entrance": "fadeIn",
    "duration": "0.5s",
    "delay": "0s"
  }
}
```

---

## 5. Produktleistungen

### 5.1 Performance-Anforderungen

#### LF-600: Ladezeiten
- **LF-601** Admin-Interface lädt in < 2 Sekunden
- **LF-602** Block-Rendering im Frontend < 100ms
- **LF-603** REST API Antwortzeit < 500ms
- **LF-604** JavaScript Bundle < 200KB (gzipped)

#### LF-610: Skalierbarkeit
- **LF-611** Unterstützung für 1000+ Container-Blöcke
- **LF-612** Gleichzeitige Nutzung durch 50+ Editoren
- **LF-613** Effizientes Caching der Block-Konfigurationen

### 5.2 Benutzerfreundlichkeit

#### LF-700: Interface-Design
- **LF-701** Intuitive Benutzerführung ohne Schulung
- **LF-702** Kontextsensitive Hilfe und Tooltips
- **LF-703** Keyboard-Shortcuts für häufige Aktionen
- **LF-704** Undo/Redo für alle Aktionen

#### LF-710: Barrierefreiheit
- **LF-711** WCAG 2.1 AA Konformität
- **LF-712** Keyboard-Navigation vollständig möglich
- **LF-713** Screen-Reader-Unterstützung
- **LF-714** Ausreichende Farbkontraste

---

## 6. Qualitätsanforderungen

### 6.1 Funktionalität

#### LF-800: Korrektheit
- **LF-801** Fehlerfreie Block-Generierung
- **LF-802** Konsistente Darstellung über alle Browser
- **LF-803** Keine Konflikte mit WordPress Core

#### LF-810: Sicherheit
- **LF-811** Schutz vor SQL-Injection
- **LF-812** XSS-Prevention
- **LF-813** CSRF-Schutz
- **LF-814** Sichere Datei-Uploads
- **LF-815** Capability-basierte Zugriffskontrolle

### 6.2 Zuverlässigkeit

#### LF-820: Verfügbarkeit
- **LF-821** 99.9% Uptime (abhängig vom Hosting)
- **LF-822** Graceful Degradation bei Fehlern
- **LF-823** Automatische Fehlerbehandlung

#### LF-830: Fehlertoleranz
- **LF-831** Validierung aller Benutzereingaben
- **LF-832** Fallback für fehlende Block-Konfigurationen
- **LF-833** Recovery-Mechanismen bei Datenbankfehlern

### 6.3 Wartbarkeit

#### LF-840: Modularität
- **LF-841** Klare Trennung von Logik und Präsentation
- **LF-842** Dokumentierte API-Schnittstellen
- **LF-843** Einheitliche Coding-Standards (WordPress Coding Standards)

#### LF-850: Testbarkeit
- **LF-851** Unit-Tests für PHP-Komponenten (>80% Coverage)
- **LF-852** Integration-Tests für REST API
- **LF-853** E2E-Tests für kritische User-Flows
- **LF-854** Automatisierte Browser-Tests

### 6.4 Kompatibilität

#### LF-860: WordPress-Kompatibilität
- **LF-861** WordPress 6.0 - aktuelle Version
- **LF-862** Gutenberg Plugin kompatibel
- **LF-863** Multisite-kompatibel
- **LF-864** Theme-unabhängig

#### LF-870: Browser-Kompatibilität
- **LF-871** Chrome (letzte 2 Versionen)
- **LF-872** Firefox (letzte 2 Versionen)
- **LF-873** Safari (letzte 2 Versionen)
- **LF-874** Edge (letzte 2 Versionen)
- **LF-875** Mobile Browser (iOS Safari, Chrome Android)

#### LF-880: Plugin-Kompatibilität
- **LF-881** Keine Konflikte mit populären Page-Buildern
- **LF-882** SEO-Plugin-kompatibel (Yoast, RankMath)
- **LF-883** Cache-Plugin-kompatibel
- **LF-884** Multilingual-Plugin-kompatibel (WPML, Polylang)

---

## 7. Benutzungsoberfläche

### 7.1 Admin-Interface

#### LF-900: Dashboard
- **LF-901** Übersichtsseite mit Statistiken
- **LF-902** Quick-Actions für häufige Aufgaben
- **LF-903** Recent Activity Log
- **LF-904** System-Status-Anzeige

#### LF-910: Block-Designer
- **LF-911** Zwei-Spalten-Layout (Einstellungen | Vorschau)
- **LF-912** Collapsible Panels für Einstellungsgruppen
- **LF-913** Drag-and-Drop für Element-Anordnung
- **LF-914** Kontextmenüs für schnelle Aktionen
- **LF-915** Vollbild-Modus

#### LF-920: Responsive Design
- **LF-921** Mobile-optimierte Admin-Oberfläche
- **LF-922** Touch-Gesten-Unterstützung
- **LF-923** Adaptive Layouts

### 7.2 Gutenberg-Integration

#### LF-930: Block-Inserter
- **LF-931** Eigene Kategorie mit Icon
- **LF-932** Vorschau-Thumbnails
- **LF-933** Suchbare Block-Namen
- **LF-934** Häufig verwendete Blöcke

#### LF-940: Block-Controls
- **LF-941** Toolbar mit Quick-Edit-Buttons
- **LF-942** Sidebar-Panels für erweiterte Einstellungen
- **LF-943** Inline-Editing wo möglich
- **LF-944** Visuelles Feedback bei Änderungen

---

## 8. Technische Schnittstellen

### 8.1 WordPress Hooks

#### Actions
- `cbd_before_block_save` - Vor dem Speichern eines Blocks
- `cbd_after_block_save` - Nach dem Speichern
- `cbd_before_block_delete` - Vor dem Löschen
- `cbd_after_block_delete` - Nach dem Löschen
- `cbd_block_rendered` - Nach dem Rendern im Frontend

#### Filters
- `cbd_block_config` - Block-Konfiguration modifizieren
- `cbd_allowed_blocks` - Erlaubte Inner-Blocks filtern
- `cbd_block_classes` - CSS-Klassen modifizieren
- `cbd_block_styles` - Inline-Styles modifizieren
- `cbd_capabilities` - Berechtigungen anpassen

### 8.2 JavaScript API

```javascript
// Block-Konfiguration abrufen
wp.data.select('container-block-designer').getBlock(blockId);

// Block-Liste abrufen
wp.data.select('container-block-designer').getBlocks();

// Block speichern
wp.data.dispatch('container-block-designer').saveBlock(blockData);

// Events
window.addEventListener('cbd:blockSaved', (event) => {
    console.log('Block saved:', event.detail);
});
```

### 8.3 PHP API

```php
// Block-Konfiguration abrufen
$block = CBD\get_block_config($block_id);

// Neuen Block registrieren
CBD\register_container_block([
    'name' => 'custom-hero',
    'title' => 'Custom Hero',
    'config' => $config_array
]);

// Block-Styles abrufen
$styles = CBD\get_block_styles($block_id, $context = 'frontend');
```

---

## 9. Entwicklungs- und Lieferumfang

### 9.1 Lieferkomponenten

#### Software-Komponenten
- WordPress Plugin (ZIP-Datei)
- Dokumentation (PDF/Online)
- Beispiel-Templates
- Migrations-Scripts

#### Dokumentation
- Installations-Anleitung
- Benutzerhandbuch
- API-Dokumentation
- Entwickler-Dokumentation
- Video-Tutorials (optional)

### 9.2 Entwicklungsphasen

#### Phase 1: Grundfunktionalität (4 Wochen)
- Basis-Plugin-Struktur
- Datenbank-Schema
- Admin-Interface Grundgerüst
- Einfache Block-Registrierung

#### Phase 2: Block Designer (6 Wochen)
- Visueller Editor
- Style-Einstellungen
- Responsive Controls
- Live-Vorschau

#### Phase 3: Gutenberg Integration (4 Wochen)
- Block-Registrierung
- Editor-Integration
- InnerBlocks-Support
- Block-Variationen

#### Phase 4: Erweiterte Features (4 Wochen)
- Import/Export
- Template-System
- Versionierung
- Performance-Optimierung

#### Phase 5: Testing & Dokumentation (2 Wochen)
- Umfassende Tests
- Bug-Fixes
- Dokumentation
- Release-Vorbereitung

### 9.3 Wartung und Support

#### LF-950: Support-Level
- **LF-951** E-Mail-Support (Reaktionszeit 48h)
- **LF-952** Bug-Fixes für 12 Monate
- **LF-953** Kompatibilitäts-Updates
- **LF-954** Security-Updates

#### LF-960: Dokumentation
- **LF-961** Online-Dokumentation
- **LF-962** Code-Beispiele
- **LF-963** FAQ-Bereich
- **LF-964** Changelog

---

## 10. Anhang

### 10.1 Glossar

- **Container-Block**: Ein wiederverwendbarer Gutenberg-Block mit vordefinierten Styles
- **InnerBlocks**: WordPress-Konzept für verschachtelte Blöcke
- **Block-Variation**: Vordefinierte Konfiguration eines Blocks
- **REST API**: Schnittstelle für Datenaustausch zwischen Frontend und Backend

### 10.2 Referenzen

- WordPress Block Editor Handbook
- WordPress Coding Standards
- React Documentation
- WCAG 2.1 Guidelines

### 10.3 Änderungshistorie

| Version | Datum | Autor | Änderungen |
|---------|-------|-------|------------|
| 1.0 | 19.07.2025 | - | Initiale Version |

---

**Ende des Lastenhefts**