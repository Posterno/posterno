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
			.entry('editors-selector')
				.add('./src/editors/selector/main.js')
				.end()
			.entry('registration-form-editor')
				.add('./src/editors/registration-form/main.js')
				.end()
			.entry('profile-fields')
				.add('./src/editors/profile-fields/main.js')
				.end()
			.entry('listings-fields-editor')
				.add('./src/editors/listings-fields/main.js')
				.end()
			.entry('listing-submission-form')
				.add('./src/listing-submission-form/main.js')
			.end()
	}
}
