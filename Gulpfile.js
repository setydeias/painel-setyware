const gulp = require('gulp')
const gulps = require('gulp-series')
const sourcemaps = require('gulp-sourcemaps')
const sass = require('gulp-sass')
const cleanCss = require('gulp-clean-css')

gulps.registerTasks({
	'sass': () => {
		return gulp.src('build/css/*.scss')
			.pipe(sourcemaps.init())
				.pipe(sass().on('error', sass.logError))
				.pipe(cleanCss())
			.pipe(sourcemaps.write())
			.pipe(gulp.dest('src/css'))

	},
	'watch': () => {
		gulp.watch('build/css/*.scss', ['sass'])
	}
})

gulps.registerSeries('build', ['sass', 'watch'])