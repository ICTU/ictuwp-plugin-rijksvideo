


# copy to temp dir
rsync -r -a -v --delete '/shared-paul-files/Webs/git-repos/mediaelement/' '/shared-paul-files/Webs/temp/'

# clean up temp dir
rm -rfv '/shared-paul-files/Webs/temp/.git/'
rm -rfv '/shared-paul-files/Webs/temp/.github/'
rm -rfv '/shared-paul-files/Webs/temp/demo/'
rm '/shared-paul-files/Webs/temp/.gitignore'
rm '/shared-paul-files/Webs/temp/.gitattributes'
rm '/shared-paul-files/Webs/temp/.scrutinizer.yml'
rm '/shared-paul-files/Webs/temp/.travis.yml'
rm '/shared-paul-files/Webs/temp/config.codekit'
rm '/shared-paul-files/Webs/temp/distribute.sh'
rm '/shared-paul-files/Webs/temp/README.md'
rm '/shared-paul-files/Webs/temp/CHANGELOG.md'
rm '/shared-paul-files/Webs/temp/CONTRIBUTING.md'
rm '/shared-paul-files/Webs/temp/readme.txt'
rm '/shared-paul-files/Webs/temp/LICENSE'
rm '/shared-paul-files/Webs/temp/Gruntfile.js'
rm '/shared-paul-files/Webs/temp/package.json'
rm '/shared-paul-files/Webs/temp/composer.json'
rm '/shared-paul-files/Webs/temp/Dockunit.json'
rm '/shared-paul-files/Webs/temp/coverage.clover'
rm '/shared-paul-files/Webs/temp/phpunit.xml.dist'
rm '/shared-paul-files/Webs/temp/example-functions.php'
rm '/shared-paul-files/Webs/temp/api.md'
rm '/shared-paul-files/Webs/temp/bower.json'
rm '/shared-paul-files/Webs/temp/compile_swf.sh'
rm '/shared-paul-files/Webs/temp/guidelines.md'
rm '/shared-paul-files/Webs/temp/installation.md'
rm '/shared-paul-files/Webs/temp/TODO.md'
rm '/shared-paul-files/Webs/temp/usage.md'

DO NOT CHANGE THESE FILES. USE -src- FOLDER


cd '/shared-paul-files/Webs/temp/'
find . -name ‘*.DS_Store’ -type f -delete


# copy to temp dir
rsync -r -a -v --delete '/shared-paul-files/Webs/temp/' '/shared-paul-files/Webs/git-repos/ICTU---Digitale-Overheid-WP---rijksvideoplugin/mediaelement/' 

# remove temp dir
rm -rfv '/shared-paul-files/Webs/temp/'
