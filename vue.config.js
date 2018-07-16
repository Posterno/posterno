module.exports = {
	// see https://github.com/vuejs/vue-cli/blob/dev/docs/webpack.md
	chainWebpack: config => {
		// If you wish to remove the standard entry point
		config.entryPoints.delete('app')
		// then add your own
		config
			.entry('options-panel')
				.add('./src/options-panel/main.js')
				.end()
			.entry('custom-fields-editor')
				.add('./src/custom-fields-editor/main.js')
				.end()
	}
}
