/**
 * Create ZIP file for plugin distribution
 */
const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

const PLUGIN_SLUG = 'container-block-designer';
const BUILD_DIR = path.join(__dirname, '../dist');
const ZIP_FILE = path.join(BUILD_DIR, `${PLUGIN_SLUG}.zip`);

// Files and directories to include
const INCLUDE_PATTERNS = [
    'container-block-designer.php',
    'readme.txt',
    'LICENSE',
    'languages/**/*',
    'includes/**/*.php',
    'build/**/*',
    'assets/**/*',
    'vendor/autoload.php',
    'vendor/composer/**/*',
];

// Files and directories to exclude
const EXCLUDE_PATTERNS = [
    'node_modules',
    'src',
    'tests',
    'scripts',
    '.git',
    '.github',
    '*.log',
    '*.lock',
    '.DS_Store',
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    'webpack.config.js',
    'webpack.config.local.js',
    'tsconfig.json',
    'phpunit.xml',
    'phpcs.xml',
    '.eslintrc',
    '.prettierrc',
    '.gitignore',
    '.env',
    '.env.*',
    'phpstan.neon',
    '*.map',
    '*.md',
    'dist',
    'coverage',
];

// Console colors
const colors = {
    reset: '\x1b[0m',
    green: '\x1b[32m',
    yellow: '\x1b[33m',
    red: '\x1b[31m',
    cyan: '\x1b[36m',
};

// Log functions
const log = {
    info: (msg) => console.log(`${colors.cyan}ℹ${colors.reset} ${msg}`),
    success: (msg) => console.log(`${colors.green}✓${colors.reset} ${msg}`),
    warning: (msg) => console.log(`${colors.yellow}⚠${colors.reset} ${msg}`),
    error: (msg) => console.log(`${colors.red}✗${colors.reset} ${msg}`),
};

async function createZip() {
    log.info('Starting ZIP creation...');
    
    // Create dist directory if it doesn't exist
    if (!fs.existsSync(BUILD_DIR)) {
        fs.mkdirSync(BUILD_DIR, { recursive: true });
        log.success('Created dist directory');
    }
    
    // Delete existing ZIP file
    if (fs.existsSync(ZIP_FILE)) {
        fs.unlinkSync(ZIP_FILE);
        log.info('Removed existing ZIP file');
    }
    
    // Create ZIP archive
    const output = fs.createWriteStream(ZIP_FILE);
    const archive = archiver('zip', {
        zlib: { level: 9 } // Maximum compression
    });
    
    // Handle stream events
    output.on('close', () => {
        const size = (archive.pointer() / 1024 / 1024).toFixed(2);
        log.success(`Plugin ZIP created: ${ZIP_FILE}`);
        log.info(`File size: ${size} MB`);
        log.info(`Total files: ${archive.pointer()} bytes`);
    });
    
    archive.on('warning', (err) => {
        if (err.code === 'ENOENT') {
            log.warning(err.message);
        } else {
            throw err;
        }
    });
    
    archive.on('error', (err) => {
        throw err;
    });
    
    // Pipe archive data to the file
    archive.pipe(output);
    
    // Add files to archive
    const projectRoot = path.join(__dirname, '..');
    
    // Function to check if path should be excluded
    const shouldExclude = (filePath) => {
        const relativePath = path.relative(projectRoot, filePath);
        return EXCLUDE_PATTERNS.some(pattern => {
            if (pattern.includes('*')) {
                // Simple wildcard matching
                const regex = new RegExp(pattern.replace(/\*/g, '.*'));
                return regex.test(relativePath);
            }
            return relativePath.includes(pattern);
        });
    };
    
    // Add individual files
    const individualFiles = [
        'container-block-designer.php',
        'readme.txt',
        'LICENSE',
    ];
    
    individualFiles.forEach(file => {
        const filePath = path.join(projectRoot, file);
        if (fs.existsSync(filePath)) {
            archive.file(filePath, { name: path.join(PLUGIN_SLUG, file) });
            log.success(`Added: ${file}`);
        } else {
            log.warning(`File not found: ${file}`);
        }
    });
    
    // Add directories
    const directories = [
        'includes',
        'build',
        'assets',
        'languages',
    ];
    
    directories.forEach(dir => {
        const dirPath = path.join(projectRoot, dir);
        if (fs.existsSync(dirPath)) {
            archive.directory(dirPath, path.join(PLUGIN_SLUG, dir), (entry) => {
                // Filter out excluded files
                if (shouldExclude(entry.sourcePath)) {
                    return false;
                }
                return entry;
            });
            log.success(`Added directory: ${dir}`);
        } else {
            log.warning(`Directory not found: ${dir}`);
        }
    });
    
    // Add vendor directory (only essential files)
    const vendorPath = path.join(projectRoot, 'vendor');
    if (fs.existsSync(vendorPath)) {
        // Add autoload.php
        archive.file(
            path.join(vendorPath, 'autoload.php'),
            { name: path.join(PLUGIN_SLUG, 'vendor/autoload.php') }
        );
        
        // Add composer directory
        archive.directory(
            path.join(vendorPath, 'composer'),
            path.join(PLUGIN_SLUG, 'vendor/composer')
        );
        
        log.success('Added vendor files');
    }
    
    // Create readme.txt if it doesn't exist
    const readmePath = path.join(projectRoot, 'readme.txt');
    if (!fs.existsSync(readmePath)) {
        const readmeContent = `=== Container Block Designer ===
Contributors: yourname
Tags: gutenberg, blocks, container, layout, design
Requires at least: 6.0
Tested up to: 6.5
Stable tag: 1.0.0
Requires PHP: 8.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Ein visueller Designer für custom Container-Blöcke im Gutenberg Editor.

== Description ==

Container Block Designer ermöglicht es Ihnen, wiederverwendbare Container-Blöcke visuell zu gestalten und als Gutenberg-Blöcke zu verwenden.

== Installation ==

1. Laden Sie das Plugin hoch
2. Aktivieren Sie es über das Plugins-Menü
3. Gehen Sie zu "Container Blocks" im Admin-Menü

== Changelog ==

= 1.0.0 =
* Erste Veröffentlichung
`;
        fs.writeFileSync(readmePath, readmeContent);
        log.success('Created readme.txt');
    }
    
    // Finalize the archive
    await archive.finalize();
}

// Run the script
createZip().catch(err => {
    log.error(`Error creating ZIP: ${err.message}`);
    process.exit(1);
});