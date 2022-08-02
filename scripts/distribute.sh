

# sh '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/plugins/ictuwp-plugin-rijksvideo/scripts/distribute.sh' &>/dev/null

# voor een update van de CMB2 bestanden:
# sh '/Users/paul/shared-paul-files/Webs/git-repos/ICTU---Digitale-Overheid-WP---rijksvideoplugin/get_cmb2_files.sh' &>/dev/null


# clear the log file
sh '/Users/paul/shellscripts/clearlogs.sh';

# copy to temp dir
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/plugins/ictuwp-plugin-rijksvideo/' '/Users/paul/shared-paul-files/Webs/temp/'

# clean up temp dir
rm -rf '/Users/paul/shared-paul-files/Webs/temp/.git/'
rm -rf '/Users/paul/shared-paul-files/Webs/temp/scripts/'
rm '/Users/paul/shared-paul-files/Webs/temp/.gitignore'
rm '/Users/paul/shared-paul-files/Webs/temp/.config.codekit3'
# rm '/Users/paul/shared-paul-files/Webs/temp/.config.codekit'
rm '/Users/paul/shared-paul-files/Webs/temp/LICENSE'

cd '/Users/paul/shared-paul-files/Webs/temp/'
find . -name "*.DS_Store" -type f -delete
find . -name "*.map" -type f -delete


# kopietje naar local DO
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/temp/' '/Users/paul/shared-paul-files/Webs/ICTU/vagrant-digitaleoverheid/www/digitaleoverheid/public_html/wp-content/plugins/ictuwp-plugin-rijksvideo/'

# remove temp dir
rm -rf '/Users/paul/shared-paul-files/Webs/temp/'
