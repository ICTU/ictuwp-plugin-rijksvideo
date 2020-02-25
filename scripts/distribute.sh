

# sh '/Users/paul/shared-paul-files/Webs/git-repos/ICTU---Digitale-Overheid-WP---rijksvideoplugin/distribute.sh' &>/dev/null

# voor een update van de CMB2 bestanden:
# sh '/Users/paul/shared-paul-files/Webs/git-repos/ICTU---Digitale-Overheid-WP---rijksvideoplugin/get_cmb2_files.sh' &>/dev/null


# clear the log file
> '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/debug.log'

# copy to temp dir
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/git-repos/ICTU---Digitale-Overheid-WP---rijksvideoplugin/' '/Users/paul/shared-paul-files/Webs/temp/'

# clean up temp dir
rm -rf '/Users/paul/shared-paul-files/Webs/temp/.git/'
rm '/Users/paul/shared-paul-files/Webs/temp/.gitignore'
rm '/Users/paul/shared-paul-files/Webs/temp/.codekit-cache'
rm '/Users/paul/shared-paul-files/Webs/temp/config.codekit'
rm '/Users/paul/shared-paul-files/Webs/temp/distribute.sh'
rm '/Users/paul/shared-paul-files/Webs/temp/README.md'
rm '/Users/paul/shared-paul-files/Webs/temp/readme.txt'
rm '/Users/paul/shared-paul-files/Webs/temp/LICENSE'

cd '/Users/paul/shared-paul-files/Webs/temp/'
find . -name ‘*.DS_Store’ -type f -delete


# copy from temp dir to dev-env
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/temp/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/plugins/rhswp-rijksvideo/' 

# remove temp dir
rm -rf '/Users/paul/shared-paul-files/Webs/temp/'



# en een kopietje naar Sentia accept
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/plugins/rhswp-rijksvideo/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/sentia/accept/www/wp-content/plugins/rhswp-rijksvideo/'

# en een kopietje naar Sentia live
rsync -r -a --delete '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/development/wp-content/plugins/rhswp-rijksvideo/' '/Users/paul/shared-paul-files/Webs/ICTU/Gebruiker Centraal/sentia/live/www/wp-content/plugins/rhswp-rijksvideo/'

