

# sh '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/plugins/ictuwp-plugin-rijksvideo/scripts/get_cmb2_files.sh' &>/dev/null


# copy to temp dir
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/git-repos/CMB2/' '/Users/paul/shared-paul-files/Webs/temp/'

# clean up temp dir
rm -rfv '/Users/paul/shared-paul-files/Webs/temp/.git/'
rm -rfv '/Users/paul/shared-paul-files/Webs/temp/.github/'
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



cd '/Users/paul/shared-paul-files/Webs/temp/'
find . -name ‘*.DS_Store’ -type f -delete


# copy to rijksvideoplugin dir
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/temp/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/plugins/ictuwp-plugin-rijksvideo/cmb2/' 

# copy to volwassenheidsscore dir
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/temp/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/themes/gebruiker-centraal/development/plugins/ictuwp-plugin-maturityscore/cmb2/' 

# copy to Planning-Tool dir
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/temp/' '/Users/paul/shared-paul-files/Webs/git-repos/Digitale-Overheid---WordPress-plugin-Planning-Tool/cmb2/' 


# remove temp dir
rm -rfv '/Users/paul/shared-paul-files/Webs/temp/'
