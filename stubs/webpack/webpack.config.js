const fs = require('fs');
const path = require('path');
const glob = require('glob');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

// Discover all bb-modules scss files and create entries that output css in-place.
// e.g. bb-modules/coach-cards/css/frontend.scss → bb-modules/coach-cards/css/frontend
const bbModuleEntries = {};
const bbModuleKeys = [];
if (fs.existsSync('./bb-modules')) {
  glob.sync('./bb-modules/**/css/*.scss').forEach(file => {
    const key = file.replace('.scss', '').replace('./', '');
    bbModuleEntries[key] = file;
    bbModuleKeys.push(key);
  });
}

// Clean up bb-module build artifacts:
// - Remove empty .js stubs webpack emits for CSS-only entries
// - Remove .css files that have no meaningful content (empty selectors compile to nothing)
class CleanBbModulesPlugin {
  apply(compiler) {
    compiler.hooks.afterEmit.tap('CleanBbModulesPlugin', () => {
      bbModuleKeys.forEach(key => {
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
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname),
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: '[name].css',
    }),
    new CleanBbModulesPlugin(),
  ],
  module: {
    rules: [
      {
        test: /\.scss$/i,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'sass-loader',
        ],
      },
      {
        test: /\.css$/i,
        use: ['style-loader', 'css-loader'],
      },
      {
        test: /\.jsx?$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-react']
          }
        }
      }
    ],
  },
};
