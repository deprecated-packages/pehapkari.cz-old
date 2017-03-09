// install: npm install -g gulp gulp-watch
// run: gulp

var gulp = require('gulp');
var watch = require('gulp-watch');
var gulputil = require('gulp-util');
var exec = require('child_process').exec;

gulp.task('default', function () {
    // Generate current version
    exec('vendor/bin/statie generate', function (err, stdout, stderr) {
        gulputil.log(stdout);
        gulputil.log(stderr);
    });

    // Run local server, open localhost:8000 in your browser
    exec('php -S 0.0.0.0:8000 -t output');

    gulp.watch(
        // For the second arg see: https://github.com/floatdrop/gulp-watch/issues/242#issuecomment-230209702
        ['source/**/*', '!**/*___jb_tmp___'],
        { ignoreInitial: false },
        function() {
            exec('vendor/bin/statie generate', function (err, stdout, stderr) {
                gulputil.log(stdout);
                gulputil.log(stderr);
            });
        }
    );
});
