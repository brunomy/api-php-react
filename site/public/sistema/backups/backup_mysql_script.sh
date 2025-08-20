#!/bin/bash
#
#   make it executable: sudo chmod +x backup_mysql_script.sh
#

# Directory to which mysql backup files will be written. Should end with slash ("/").
backup_directory="/home/forge/rp_backups/"

# The name of the MySQL database
db_name="$1"

# The host name of the MySQL database server; usually 'localhost'
db_host="$2"

# The port number of the MySQL database server; usually '3306'
db_port="3306"

# The MySQL user to use when performing the database backup.
db_user="$3"

# The password for the above MySQL user.
db_pass="$4"

# Date/time included in the file names of the database backup files.
datetime=$(date +'%Y%m%d-%H%M%S')

# create a folder empty
mkdir -p $backup_directory

#create mysqldump file for each database inside the array

dump_name=$db_name--$datetime.sql.gz
# Create database backup and compress using gzip.
mysqldump -u $db_user -h $db_host -P $db_port --password=$db_pass $db_name | gzip > $backup_directory$dump_name


# AWS S3 BACKUP ------------------------------------------------------------------
# Synchonize folder with AWS S3 service deleting if files removed from source
# need to install and setup awscli on the server for this
## https://aws.amazon.com/pt/cli/
# And execute: aws configure

# send to aws s3 bucket
/usr/local/bin/aws s3 cp $backup_directory$dump_name s3://realpoker-website-backups/mysql/

# delete folder
rm -rf $backup_directory

exit