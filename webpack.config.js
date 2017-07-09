const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

module.exports = [{
	entry: './application/views/assets/js/src/app.js',
	output: {
		path: path.join(__dirname, '/application/views/assets/js/bandle'),
		filename: 'bundle.js'
	},
	plugins: [
		new webpack.ProvidePlugin({ riot: 'riot' })
	],
	module: {
		rules: [
			{
				test: /\.js$|\.tag$/,
				exclude: /node_modules/,
				loader: 'babel-loader',
				query: {
					presets: ['babel-preset-es2015']
				}
			},
			{
				test: /\.tag$/,
				exclude: /node_modules/,
				loader: 'riotjs-loader'
			}
		]
	}
}, {
	entry: {
		style: './application/views/assets/sass/main.scss'
	},
	output: {
		path: path.join(__dirname, '/application/views/assets/css'),
		filename: 'style.css'
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