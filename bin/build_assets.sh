#!/usr/bin/env bash

# get from repository, clone, download and build paths

# 1. move to new design
cd new-design

# 2. install dependencies
# might be needed: "apt-get install libpng-dev"
sudo apt-get install libpng-dev
npm install

# 3. generate app.css + app.js files
npm run production

cd ..

# 4. copy assets
cp new-design/dist/app.css source/assets/css/app.css
cp new-design/dist/app.js source/assets/js/app.js
cp -rf new-design/src/icons source/assets
cp -rf new-design/src/images source/assets

# 5. update paths ("src/images" => "assets/images")
sed -i -e 's/src\/images/assets\/images/g' source/assets/css/app.css
