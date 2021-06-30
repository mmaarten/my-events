const CopyPlugin = require('copy-webpack-plugin');
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry : {
    'calendar-script': './assets/scripts/calendar.js',
    'calendar-style': './assets/styles/calendar.scss',
    'admin-style': './assets/styles/admin.scss',
    'fontawesome': './assets/styles/fontawesome.scss',
    'featherlight-script': './assets/scripts/featherlight.js',
    'featherlight-style': './assets/styles/featherlight.scss',
  },
  plugins : [
    ...defaultConfig.plugins,
    new CopyPlugin({
      patterns: [
        { from: './assets/images/', to: 'images/', noErrorOnMissing: true, globOptions: { dot: false } },
        { from: './assets/fonts/', to: 'fonts/', noErrorOnMissing: true, globOptions: { dot: false } },
      ],
    }),
  ],
};
