

# sh '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/plugins/ictuwp-plugin-rijksvideo/scripts/distribute.sh' &>/dev/null

# voor een update van de CMB2 bestanden:
# sh '/Users/paul/shared-paul-files/Webs/git-repos/ICTU---Digitale-Overheid-WP---rijksvideoplugin/get_cmb2_files.sh' &>/dev/null


# clear the log file
> '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/debug.log'

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




# --------------------------------------------------------------------------------------------------------------------------------
# Vertalingen --------------------------------------------------------------------------------------------------------------------
# --------------------------------------------------------------------------------------------------------------------------------

rsync -r -a -v --delete '/Users/paul/shared-paul-files/Webs/temp/languages/' '/Users/paul/shared-paul-files/Webs/temp-lang/'

# remove the .pot
rm '/Users/paul/shared-paul-files/Webs/temp-lang/rijksvideo-translate.pot'
rm '/Users/paul/shared-paul-files/Webs/temp-lang/index.php'

# rename the translations
mv '/Users/paul/shared-paul-files/Webs/temp-lang/en_US.po' '/Users/paul/shared-paul-files/Webs/temp-lang/rijksvideo-translate-en_US.po'
mv '/Users/paul/shared-paul-files/Webs/temp-lang/en_US.mo' '/Users/paul/shared-paul-files/Webs/temp-lang/rijksvideo-translate-en_US.mo'

mv '/Users/paul/shared-paul-files/Webs/temp-lang/en_GB.po' '/Users/paul/shared-paul-files/Webs/temp-lang/rijksvideo-translate-en_GB.po'
mv '/Users/paul/shared-paul-files/Webs/temp-lang/en_GB.mo' '/Users/paul/shared-paul-files/Webs/temp-lang/rijksvideo-translate-en_GB.mo'

mv '/Users/paul/shared-paul-files/Webs/temp-lang/nl_NL.po' '/Users/paul/shared-paul-files/Webs/temp-lang/rijksvideo-translate-nl_NL.po'
mv '/Users/paul/shared-paul-files/Webs/temp-lang/nl_NL.mo' '/Users/paul/shared-paul-files/Webs/temp-lang/rijksvideo-translate-nl_NL.mo'

# copy files to /wp-content/languages/themes
rsync -ah '/Users/paul/shared-paul-files/Webs/temp-lang/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/languages/plugins/'
rsync -ah '/Users/paul/shared-paul-files/Webs/temp-lang/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/sentia/accept/www/wp-content/languages/plugins/'
rsync -ah '/Users/paul/shared-paul-files/Webs/temp-lang/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/sentia/live/www/wp-content/languages/plugins/'

# remove temp dir
rm -rf '/Users/paul/shared-paul-files/Webs/temp-lang/'

# ------------------



# kopietje naar Sentia accept
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/temp/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/sentia/accept/www/wp-content/plugins/ictuwp-plugin-rijksvideo/'

# en een kopietje naar Sentia live
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/temp/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/sentia/live/www/wp-content/plugins/ictuwp-plugin-rijksvideo/'

# remove temp dir
rm -rf '/Users/paul/shared-paul-files/Webs/temp/'
