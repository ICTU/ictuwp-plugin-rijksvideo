


# copy to temp dir
rsync -r -a -v --delete '/Users/paul/shared-paul-files/Webs/git-repos/mediaelement/' '/Users/paul/shared-paul-files/Webs/temp/'

# clean up temp dir
rm -rfv '/Users/paul/shared-paul-files/Webs/temp/.git/'
rm -rfv '/Users/paul/shared-paul-files/Webs/temp/.github/'
rm -rfv '/Users/paul/shared-paul-files/Webs/temp/demo/'
rm '/Users/paul/shared-paul-files/Webs/temp/.gitignore'
rm '/Users/paul/shared-paul-files/Webs/temp/.gitattributes'
rm '/Users/paul/shared-paul-files/Webs/temp/.scrutinizer.yml'
rm '/Users/paul/shared-paul-files/Webs/temp/.travis.yml'
rm '/Users/paul/shared-paul-files/Webs/temp/config.codekit'
rm '/Users/paul/shared-paul-files/Webs/temp/distribute.sh'
rm '/Users/paul/shared-paul-files/Webs/temp/README.md'
rm '/Users/paul/shared-paul-files/Webs/temp/CHANGELOG.md'
rm '/Users/paul/shared-paul-files/Webs/temp/CONTRIBUTING.md'
rm '/Users/paul/shared-paul-files/Webs/temp/readme.txt'
rm '/Users/paul/shared-paul-files/Webs/temp/LICENSE'
rm '/Users/paul/shared-paul-files/Webs/temp/Gruntfile.js'
rm '/Users/paul/shared-paul-files/Webs/temp/package.json'
rm '/Users/paul/shared-paul-files/Webs/temp/composer.json'
rm '/Users/paul/shared-paul-files/Webs/temp/Dockunit.json'
rm '/Users/paul/shared-paul-files/Webs/temp/coverage.clover'
rm '/Users/paul/shared-paul-files/Webs/temp/phpunit.xml.dist'
rm '/Users/paul/shared-paul-files/Webs/temp/example-functions.php'
rm '/Users/paul/shared-paul-files/Webs/temp/api.md'
rm '/Users/paul/shared-paul-files/Webs/temp/bower.json'
rm '/Users/paul/shared-paul-files/Webs/temp/compile_swf.sh'
rm '/Users/paul/shared-paul-files/Webs/temp/guidelines.md'
rm '/Users/paul/shared-paul-files/Webs/temp/installation.md'
rm '/Users/paul/shared-paul-files/Webs/temp/TODO.md'
rm '/Users/paul/shared-paul-files/Webs/temp/usage.md'

# DO NOT CHANGE THESE FILES. USE -src- FOLDER


cd '/Users/paul/shared-paul-files/Webs/temp/'
find . -name '*.DS_Store' -type f -delete


# copy to temp dir
rsync -r -a -v --delete '/Users/paul/shared-paul-files/Webs/temp/' '/Users/paul/shared-paul-files/Webs/git-repos/ICTU---Digitale-Overheid-WP---rijksvideoplugin/mediaelement/' 

# remove temp dir
rm -rfv '/Users/paul/shared-paul-files/Webs/temp/'
