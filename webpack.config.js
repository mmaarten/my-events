const CopyPlugin = require('copy-webpack-plugin');
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry : {
    'calendar-script': './assets/scripts/calendar.js',
    'calendar-style': './assets/styles/calendar.scss',
    'admin-script': './assets/scripts/admin.js',
    'admin-style': './assets/styles/admin.scss',
    'fontawesome': './assets/styles/fontawesome.scss',
    'send-email-script': './assets/scripts/send-email.js',
    'send-email-style': './assets/styles/send-email.scss',
    'events-filter-script': './assets/scripts/events-filter.js',
    'events-filter-style': './assets/styles/events-filter.scss',
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
