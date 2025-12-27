const path = require('path');
const webpack = require('webpack');

module.exports = {
  mode: 'production',
  entry: './webroot/js/missionki.js',
  output: {
    filename: 'missionki.bundle.js',
    path: path.resolve(__dirname, 'webroot/js')
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      }
    ]
  },
  plugins: [
    new webpack.ProvidePlugin({
      bootstrap: 'bootstrap/dist/js/bootstrap.bundle.js'
    })
  ]
};
