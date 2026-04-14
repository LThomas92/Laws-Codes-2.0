import path from 'path';
import { fileURLToPath } from 'url';
import * as sass from 'sass';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import AssetsPlugin from 'assets-webpack-plugin';
import BrowserSyncPlugin from 'browser-sync-webpack-plugin';
import CssMinimizerPlugin from 'css-minimizer-webpack-plugin';
import TerserPlugin from 'terser-webpack-plugin';

const __filename = fileURLToPath(import.meta.url);
const __dirname  = path.dirname(__filename);

const DEV  = process.env.NODE_ENV !== 'production';
const HASH = DEV ? '' : '-[contenthash:8]';

// ─── Update to match your local WordPress dev URL ───────────────
const LOCAL_URL = 'http://localhost:8888/laws-codes-2';

export default {
  mode: DEV ? 'development' : 'production',

  // ── Entry: scss lives at theme-root/scss/ so import from there ─
  // Both JS and SCSS are entry points — Webpack handles both.
entry: {
    main: [
      './src/index.js',
      './scss/main.scss',   // explicit SCSS entry — always writes a CSS file
    ],
  },

  output: {
    path:      path.resolve(__dirname, 'dist'),
    filename:  `js/[name]${HASH}.js`,
    publicPath: '/dist/',
    clean:     !DEV,   // don't wipe dist on every dev save — prevents missing CSS flash
  },

  devtool: DEV ? 'source-map' : 'hidden-source-map',

  module: {
    rules: [
      // ── JavaScript / ES6+ ───────────────────────────────────────
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              ['@babel/preset-env', {
                targets: '> 0.5%, last 2 versions, not dead',
                useBuiltIns: 'usage',
                corejs: 3,
              }],
            ],
          },
        },
      },

      // ── SCSS → CSS ──────────────────────────────────────────────
      // MiniCssExtractPlugin is used in BOTH dev and prod.
      // style-loader injects CSS via JS (no file written) — that's
      // why styles never appeared in dev mode. Extracting to a real
      // .css file means functions.php can always enqueue it.
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,   // always extract — never style-loader
          {
            loader: 'css-loader',
            options: { sourceMap: DEV },
          },
          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: { plugins: ['autoprefixer'] },
              sourceMap: DEV,
            },
          },
          {
            loader: 'sass-loader',
            options: {
              implementation: sass,
              api: 'modern',
              sourceMap: DEV,
              sassOptions: {
                outputStyle: DEV ? 'expanded' : 'compressed',
              },
            },
          },
        ],
      },

      // ── Images ──────────────────────────────────────────────────
      {
        test: /\.(png|jpg|jpeg|gif|svg|webp)$/i,
        type: 'asset/resource',
        generator: { filename: 'images/[name][hash][ext]' },
      },

      // ── Fonts ───────────────────────────────────────────────────
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/i,
        type: 'asset/resource',
        generator: { filename: 'fonts/[name][hash][ext]' },
      },
    ],
  },

  optimization: {
    minimizer: [
      new TerserPlugin({ extractComments: false }),
      new CssMinimizerPlugin(),
    ],
  },

  plugins: [
    // Always extract CSS to a real file in both dev and prod
    new MiniCssExtractPlugin({
      filename: `css/style${HASH}.css`,
    }),

    // Writes dist/assets.json for functions.php to read hashed filenames
    new AssetsPlugin({
      path:     path.resolve(__dirname, 'dist'),
      filename: 'assets.json',
      fullPath: false,
    }),

    new BrowserSyncPlugin(
      {
        host:  'localhost',
        port:  3000,
        proxy: LOCAL_URL,
        files: [
          '**/*.php',
          'dist/**/*.css',
          'dist/**/*.js',
        ],
        notify: false,
        open:   false,
      },
      { reload: true }   // full reload required — MiniCssExtractPlugin writes a real file, HMR can't patch it
    ),
  ],
};