#!/bin/bash
#
# make it executable: sudo chmod +x backup_script.sh
#

# Directory from which awscli will backup files to AWS S3 service. Should end with slash ("/").
temp_directory="/home/forge/rp_backups/"

#file name of backup
file_name=backup_$(date +'%Y-%m-%d').tar.gz

# create folder if not exists
[ ! -d $temp_directory ] && mkdir -p $temp_directory

# compress folder and files
tar -czhf $temp_directory$file_name /home/forge/realpoker.com.br/public/

# AWS S3 BACKUP ------------------------------------------------------------------
# Synchonize folder with AWS S3 service deleting if files removed from source
# need to install and setup awscli on the server for this
## https://aws.amazon.com/pt/cli/
# And execute: aws configure

# send to aws s3 bucket
/usr/local/bin/aws s3 cp $temp_directory$file_name s3://realpoker-website-backups/files/

# delete temp folder
rm -fr $temp_directory

exit

#For more complex scripting tutorial:  https://linuxconfig.org/bash-scripting-tutorial