

# sh '/shared-paul-files/Webs/git-repos/ICTU---Digitale-Overheid-WP---rijksvideoplugin/get_cmb2_files.sh' &>/dev/null


# copy to temp dir
rsync -r -a --delete '/shared-paul-files/Webs/git-repos/CMB2/' '/shared-paul-files/Webs/temp/'

# clean up temp dir
rm -rfv '/shared-paul-files/Webs/temp/.git/'
rm -rfv '/shared-paul-files/Webs/temp/.github/'
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



cd '/shared-paul-files/Webs/temp/'
find . -name ‘*.DS_Store’ -type f -delete


# copy to rijksvideoplugin dir
rsync -r -a --delete '/shared-paul-files/Webs/temp/' '/shared-paul-files/Webs/git-repos/ICTU---Digitale-Overheid-WP---rijksvideoplugin/cmb2/' 

# copy to volwassenheidsscore dir
rsync -r -a --delete '/shared-paul-files/Webs/temp/' '/shared-paul-files/Webs/git-repos/ICTU---GC-volwassenheidsscore-plugin/cmb2/' 

# copy to Planning-Tool dir
rsync -r -a --delete '/shared-paul-files/Webs/temp/' '/shared-paul-files/Webs/git-repos/Digitale-Overheid---WordPress-plugin-Planning-Tool/cmb2/' 


# remove temp dir
rm -rfv '/shared-paul-files/Webs/temp/'
