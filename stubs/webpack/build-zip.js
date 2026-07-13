const path = require('path');
const fs = require('fs');
const archiver = require('archiver');

// Plugin root is two directories up from assets/scripts/
const pluginRoot = path.resolve(__dirname, '..', '..');
const pluginDir = path.basename(pluginRoot);

// Read version from main plugin file (named after the plugin directory).
const mainFile = `${pluginDir}.php`;
const pluginFile = fs.readFileSync(path.join(pluginRoot, mainFile), 'utf8');
const versionMatch = pluginFile.match(/Version:\s*(.+)/);
if (!versionMatch) {
    console.error(`Could not find plugin version in ${mainFile}`);
    process.exit(1);
}
const version = versionMatch[1].trim();
const zipName = `${pluginDir}-${version}.zip`;
const outputPath = path.resolve(pluginRoot, '..', zipName);

// Remove existing zip if present.
if (fs.existsSync(outputPath)) {
    fs.unlinkSync(outputPath);
}

console.log(`Building ${zipName}...`);

const output = fs.createWriteStream(outputPath);
const archive = archiver('zip', { zlib: { level: 9 } });

output.on('close', () => {
    console.log(`Created ${outputPath} (${(archive.pointer() / 1024 / 1024).toFixed(2)} MB)`);
});

archive.on('error', (err) => {
    throw err;
});

archive.pipe(output);

// Add plugin directory contents, excluding node_modules and tooling
// dirs at ANY depth (plugins with nested apps install their own
// node_modules, e.g. assets/app/node_modules - those must not ship).
// The bare-dotfile pattern stays root-only on purpose: nested dot
// dirs can be runtime assets (e.g. a Vite dist/.vite/manifest.json
// read at enqueue time).
archive.glob('**/*', {
    cwd: pluginRoot,
    dot: true,
    ignore: [
        '**/node_modules/**',
        '**/.git/**',
        '**/.github/**',
        '**/.idea/**',
        '**/.vscode/**',
        '.*',
    ],
}, { prefix: pluginDir });

archive.finalize();
