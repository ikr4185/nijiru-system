const path = require('path');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

module.exports = [{
	entry: './application/views/assets/js/src/app.js',
	output: {
		path: path.join(__dirname, '/application/views/assets/js/bandle'),
		filename: 'bundle.js'
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				loader: 'babel-loader',
				query: {
					presets: ['babel-preset-es2015']
				}
			}
		]
	}
}, {
	entry: {
		style: './application/views/assets/sass/main.scss'
	},
	output: {
		path: path.join(__dirname, '/application/views/assets/css'),
		filename: 'bandle.css'
	},
	module: {
		loaders: [
			{
				test: /\.scss$/,
				loader: ExtractTextPlugin.extract({
					fallback: "style-loader",
					use: "css-loader!sass-loader"
				})
			}
		]
	},
	plugins: [
		new ExtractTextPlugin("[name].css")
	]
}];