var gulp = require('gulp');
var watch = require('gulp-watch');
var gulputil = require('gulp-util');
var path = require('path');
var exec = require('child_process').exec;

gulp.task('default', function () {
    // Generate current version
    exec(path.normalize('vendor/bin/statie generate source'), function (err, stdout, stderr) {
        gulputil.log(stdout);
        gulputil.log(stderr);
    });

    // Run local server, open localhost:8000 in your browser
    exec('php -S 0.0.0.0:8000 -t output');

    gulputil.log('Server is ready at http://localhost:8000');

    gulp.watch(
        // For the second arg see: https://github.com/floatdrop/gulp-watch/issues/242#issuecomment-230209702
        ['source/**/*', '!**/*___jb_tmp___'],
        { ignoreInitial: false },
        function() {
            exec(path.normalize('vendor/bin/statie generate source'), function (err, stdout, stderr) {
                gulputil.log(stdout);
                gulputil.log(stderr);
            });
        }
    );
});
