# Container Block Designer

Ein visueller Designer für custom Container-Blöcke im Gutenberg Editor.

## 🚀 Quick Start

### Voraussetzungen

- WordPress 6.0+
- PHP 8.0+
- Node.js 18+ & npm 9+
- Composer 2.0+
- MySQL 5.7+ oder MariaDB 10.3+

### Installation & Setup

1. **Repository klonen**
```bash
cd wp-content/plugins/
git clone https://github.com/yourname/container-block-designer.git
cd container-block-designer
```

2. **PHP Dependencies installieren**
```bash
composer install
```

3. **Node Dependencies installieren**
```bash
npm install
```

4. **Build für Entwicklung**
```bash
npm start
```

5. **Plugin aktivieren**
- Gehe zu WordPress Admin > Plugins
- Aktiviere "Container Block Designer"

## 📁 Projekt-Struktur

```
container-block-designer/
├── includes/              # PHP Klassen (PSR-4 Autoloading)
│   ├── Core/             # Kern-Funktionalität
│   ├── Admin/            # Admin-Interface
│   ├── API/              # REST API
│   ├── Blocks/           # Block-Registrierung
│   ├── Database/         # Datenbank-Handler
│   └── Security/         # Sicherheit & Validierung
├── src/                  # JavaScript/React Quellcode
│   ├── admin/           # Admin React App
│   ├── blocks/          # Gutenberg Blocks
│   └── utils/           # Hilfsfunktionen
├── assets/              # Statische Dateien
├── build/               # Kompilierte Dateien (generiert)
├── tests/               # Tests
├── languages/           # Übersetzungen
└── container-block-designer.php  # Haupt-Plugin-Datei
```

## 🛠️ Entwicklung

### Verfügbare Commands

```bash
# Entwicklung
npm start                # Startet den Development Build mit Watch
npm run build           # Production Build
npm run lint            # Linting (JS, CSS, PHP)
npm run format          # Code formatieren

# Testing
npm test                # Alle Tests ausführen
npm run test:unit       # Unit Tests
npm run test:e2e        # E2E Tests
npm run test:php        # PHP Tests

# WordPress Entwicklungsumgebung
npm run env:start       # Startet lokale WP-Umgebung
npm run env:stop        # Stoppt lokale WP-Umgebung
npm run env:reset       # Resettet die Umgebung
```

### Lokale Entwicklungsumgebung

Für die lokale Entwicklung empfehlen wir [@wordpress/env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/):

```bash
npm run env:start
```

Dies startet WordPress unter: http://localhost:8888
- **Admin**: http://localhost:8888/wp-admin
- **User**: admin
- **Password**: password

### Code Standards

Wir folgen den WordPress Coding Standards:

```bash
# PHP Code überprüfen
composer run-script phpcs

# PHP Code automatisch formatieren
composer run-script phpcbf

# JavaScript/React Code überprüfen
npm run lint:js

# CSS/SCSS überprüfen
npm run lint:css
```

## 🏗️ Architektur

### Backend (PHP)

- **Namespace**: `ContainerBlockDesigner`
- **Autoloading**: PSR-4 via Composer
- **Design Pattern**: Singleton für Hauptklassen
- **Hooks**: Action/Filter System via Loader-Klasse

### Frontend (React/TypeScript)

- **State Management**: WordPress Data Layer (`@wordpress/data`)
- **Components**: Funktionale Komponenten mit Hooks
- **Styling**: SCSS mit BEM-Konvention
- **Build**: Webpack via `@wordpress/scripts`

### REST API

Basis-URL: `/wp-json/cbd/v1/`

**Endpoints:**
- `GET /blocks` - Liste aller Blöcke
- `POST /blocks` - Neuen Block erstellen
- `GET /blocks/{id}` - Einzelnen Block abrufen
- `PUT /blocks/{id}` - Block aktualisieren
- `DELETE /blocks/{id}` - Block löschen

### Datenbank

**Tabellen:**
- `{prefix}_cbd_blocks` - Haupt-Blocktabelle
- `{prefix}_cbd_block_versions` - Versionierung
- `{prefix}_cbd_audit_log` - Audit-Log
- `{prefix}_cbd_templates` - Templates (zukünftig)

## 🔒 Sicherheit

- Alle Eingaben werden validiert und sanitized
- Capability-basierte Berechtigungen
- Nonce-Verifizierung für alle Aktionen
- Prepared Statements für DB-Queries
- XSS-Prevention durch Output-Escaping

## 🚢 Deployment

### Production Build

```bash
npm run build:production
```

Dies erstellt eine optimierte Version im `dist/` Ordner.

### Release erstellen

```bash
npm run bundle
```

Erstellt eine `container-block-designer.zip` für die Installation.

## 📚 Dokumentation

- [Benutzerhandbuch](docs/user-guide.md)
- [Entwickler-Dokumentation](docs/developer.md)
- [REST API Dokumentation](docs/api.md)

## 🤝 Contributing

1. Fork das Repository
2. Erstelle einen Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Committe deine Änderungen (`git commit -m 'Add some AmazingFeature'`)
4. Push zum Branch (`git push origin feature/AmazingFeature`)
5. Öffne einen Pull Request

### Commit-Konventionen

Wir nutzen [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` Neue Features
- `fix:` Bug Fixes
- `docs:` Dokumentation
- `style:` Code-Formatierung
- `refactor:` Code-Refactoring
- `test:` Tests
- `chore:` Build-Prozess, Dependencies

## 📝 Lizenz

Dieses Projekt ist unter der GPL v2 oder später lizenziert - siehe [LICENSE](LICENSE) Datei.

## 🆘 Support

- **Issues**: [GitHub Issues](https://github.com/yourname/container-block-designer/issues)
- **Diskussionen**: [GitHub Discussions](https://github.com/yourname/container-block-designer/discussions)
- **E-Mail**: support@example.com

## 🙏 Credits

- Entwickelt von [Ihr Name](https://example.com)
- Basiert auf WordPress Block Editor
- Icons von [Dashicons](https://developer.wordpress.org/resource/dashicons/)

---

**Happy Coding!** 🎉