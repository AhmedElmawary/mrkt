const gulp = require('gulp');
const del = require('del');
const watch = require('gulp-watch');
const concat = require('gulp-concat');
const babel = require('gulp-babel');
const sass = require('gulp-sass');
const autoprefixer = require('gulp-autoprefixer');
const imagemin = require('gulp-imagemin');
const browserSync = require('browser-sync').create();

const JS_SRC = 'main.js';
const JS_SRC_WATCH = 'resources/js/*.js';
const JS_DEST = 'assets/js';

const CSS_SRC = 'resources/scss/main.scss';
const CSS_SRC_WATCH = 'resources/scss/*.scss';
const CSS_DEST = 'assets/css';

const IMG_SRC = 'resources/img/**';
const IMG_DEST = 'assets/img';

const CLEAN_SRC = [JS_DEST, CSS_DEST, IMG_DEST];

gulp.task('watch-js', function() {
	return watch(JS_SRC_WATCH, { verbose: true, ignoreInitial: false, read: false }, buildJS);
});

gulp.task('build-js', buildJS);

function buildJS() {
	return gulp
		.src(JS_SRC_WATCH)
		.pipe(concat(JS_SRC))
		.pipe(babel())
		.on('error', function(error) {
			console.log(error.toString());
			this.emit('end');
		})
		.pipe(gulp.dest(JS_DEST));
}

gulp.task('watch-css', function() {
	return watch(CSS_SRC_WATCH, { verbose: true, ignoreInitial: false, read: false }, buildCSS);
});

gulp.task('build-css', buildCSS);

function buildCSS() {
	return gulp
		.src(CSS_SRC)
		.pipe(sass({ outputStyle: 'compressed' }))
		.on('error', function(error) {
			console.log(error.toString());
			this.emit('end');
		})
		.pipe(autoprefixer({ cascade: false }))
		.pipe(gulp.dest(CSS_DEST));
}

gulp.task('watch-images', function() {
	return watch(IMG_SRC, { verbose: true, ignoreInitial: false, read: false }, minifiyImages);
});

gulp.task('minify-images', minifiyImages);

function minifiyImages() {
	return gulp
		.src(IMG_SRC)
		.pipe(imagemin())
		.on('error', function(error) {
			console.log(error.toString());
			this.emit('end');
		})
		.pipe(gulp.dest(IMG_DEST));
}

gulp.task('auto-reload', function() {
	browserSync.init({ proxy: 'http://localhost' });

	return watch(['resources/**'], { verbose: true, ignoreInitial: false, read: false }, function() {
		browserSync.reload();
	});
});

gulp.task('clean', function() {
	return del(CLEAN_SRC);
});

gulp.task('watch', gulp.parallel('watch-js', 'watch-css', 'watch-images', 'auto-reload'));
gulp.task('build', gulp.series('clean', 'build-js', 'build-css', 'minify-images'));
