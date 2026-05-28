const fs = require('fs');
const path = require('path');
const glob = require('glob');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

// Discover all bb-modules scss files and create entries that output css in-place.
// e.g. bb-modules/coach-cards/css/frontend.scss -> bb-modules/coach-cards/css/frontend
const bbModuleEntries = {};
const bbModuleKeys = [];
if (fs.existsSync('./bb-modules')) {
  glob.sync('./bb-modules/**/css/*.scss').forEach(file => {
    const key = file.replace('.scss', '').replace('./', '');
    bbModuleEntries[key] = file;
    bbModuleKeys.push(key);
  });
}

// Discover all Gutenberg block sources under assets/blocks/{slug}/ and create
// per-block entries that compile in-place into assets/blocks/{slug}/build/.
//
//   index.js   -> assets/blocks/{slug}/build/index.js  (editor script)
//   style.scss -> assets/blocks/{slug}/build/style.css (frontend + editor)
//   editor.scss -> assets/blocks/{slug}/build/editor.css (editor only)
//
// SCSS-only entries also leave behind empty .js stubs which CleanEmptyAssetsPlugin
// strips out (same treatment bb-modules' CSS entries get).
const blockEntries = {};
const blockCssOnlyKeys = [];
if (fs.existsSync('./assets/blocks')) {
  glob.sync('./assets/blocks/*/index.js').forEach(file => {
    const dir = path.dirname(file).replace('./', '');
    blockEntries[`${dir}/build/index`] = file;
  });
  glob.sync('./assets/blocks/*/style.scss').forEach(file => {
    const dir = path.dirname(file).replace('./', '');
    const key = `${dir}/build/style`;
    blockEntries[key] = file;
    blockCssOnlyKeys.push(key);
  });
  glob.sync('./assets/blocks/*/editor.scss').forEach(file => {
    const dir = path.dirname(file).replace('./', '');
    const key = `${dir}/build/editor`;
    blockEntries[key] = file;
    blockCssOnlyKeys.push(key);
  });
}

// Clean up CSS-only build artifacts:
//   - Remove empty .js stubs webpack emits for SCSS-only entries
//   - Remove .css files that have no meaningful content (empty selectors compile to nothing)
//
// Applies to both bb-modules CSS entries and per-block style/editor entries.
class CleanEmptyAssetsPlugin {
  apply(compiler) {
    compiler.hooks.afterEmit.tap('CleanEmptyAssetsPlugin', () => {
      [...bbModuleKeys, ...blockCssOnlyKeys].forEach(key => {
        const jsFile = path.resolve(__dirname, key + '.js');
        if (fs.existsSync(jsFile)) fs.unlinkSync(jsFile);

        const cssFile = path.resolve(__dirname, key + '.css');
        if (fs.existsSync(cssFile)) {
          const content = fs.readFileSync(cssFile, 'utf8')
            .replace(/\/\*[\s\S]*?\*\//g, '') // strip comments (incl. sourcemap)
            .trim();
          if (!content) fs.unlinkSync(cssFile);
        }
      });
    });
  }
}

module.exports = {
  mode: 'development',
  entry: {
    'assets/build/{{ plugin_text_domain }}.frontend': [
      './assets/js/src/frontend/index.js',
      './assets/sass/frontend.scss',
    ],
    'assets/build/{{ plugin_text_domain }}.admin': [
      './assets/js/src/admin/index.js',
      './assets/sass/admin.scss',
    ],
    ...bbModuleEntries,
    ...blockEntries,
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname),
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: '[name].css',
    }),
    new CleanEmptyAssetsPlugin(),
  ],
  module: {
    rules: [
      {
        test: /\.scss$/i,
        use: [
          MiniCssExtractPlugin.loader,
          // url: false leaves `url(/wp-content/.../foo.svg)` and similar absolute
          // browser-served paths intact instead of trying to resolve them as
          // bundled modules (which fails -- they're not on disk relative to the
          // SCSS file). WordPress serves these static assets directly.
          { loader: 'css-loader', options: { url: false } },
          'sass-loader',
        ],
      },
      {
        test: /\.css$/i,
        use: ['style-loader', { loader: 'css-loader', options: { url: false } }],
      },
      {
        test: /\.jsx?$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            // Use wp.element.createElement instead of React.createElement so
            // JSX renders through WordPress's element package -- React is not
            // a global in WP, but wp.element is.
            presets: [
              ['@babel/preset-react', {
                pragma: 'wp.element.createElement',
                pragmaFrag: 'wp.element.Fragment',
              }],
            ],
          },
        },
      },
    ],
  },
};
