#!/bin/bash

db_params=($2)

remote=$(cd $(dirname "$0") && git config --local --get remote.origin.url)

old_release_dir=$(cd $(dirname "$0") && cd $(pwd -P) && cd ../../../../ && basename $(pwd))

new_release_dir=$(date +'%Y%m%d%H%M%S')

new_release_path=~/releases/$new_release_dir

git clone $remote $new_release_path

if [ $? -eq 0 ]; then

    cd $new_release_path && git reset --hard "$1"

    if [ $? -eq 0 ]; then
        sudo chown -R $USER:www-data $new_release_path/sistema/System/log/
        sudo chown -R www-data:www-data $new_release_path/data
        sudo rm $new_release_path/data/produtos.xml
        sudo rm $new_release_path/data/cotacao_dollar_diario.json

        function create_symlink {
            # $1 = real path
            # $2 = link path

            if [[ -L $2 && -d $2 ]]; then 
                rm -f $2
            fi

            ln -s $1 $2
        }

        folders=('uploads' 'files' 'vendor')

        for folder in ${folders[@]}; do
            create_symlink ~/storage/$folder $new_release_path/$folder
        done

        create_symlink ~/configs.json $new_release_path/sistema/System/Core/configs.json

        create_symlink $new_release_path ~/current

        create_symlink $new_release_path/sistema $new_release_path/cassino/sistema

        
        $(cd ~/releases && find -maxdepth 1 -type d -not -name $new_release_dir -not -name $old_release_dir -not -name '.' -exec sudo rm -rf {} +) > /dev/null 2>&1

        exit 0
    fi
    exit 1
fi

exit 1