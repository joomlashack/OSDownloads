#!/bin/sh

SITE_PATH=$1
CODE_PATH=$2

if [ -z $SITE_PATH ]; then
    echo "Please enter the path to the root of your Joomla! install: "
    read SITE_PATH
fi

if [ ! -d $SITE_PATH ]; then
    echo "Invalid directory. Exiting..."
    exit
fi

if [ -z $CODE_PATH ]; then
    CWD=$(pwd)

    CODE_PATH=$CWD/src
    if [ ! -d $CODE_PATH ]; then
        PARENT=$(dirname $CWD)
        CODE_PATH=$PARENT/src
        if [ ! -d $CODE_PATH ]; then
            echo "Could not find code path.  Please enter path to the code directory of the extension repository:"
            read CODE_PATH
            if [ ! -d $CODE_PATH ]; then
                echo "Path to code not found"
            fi
        fi
    fi

fi

# Params: $1: source, $2: destination
create_symlink () {
    SOURCE_PATH=$CODE_PATH/$1
    DESTINATION_PATH=$SITE_PATH/$2

    if [ -d $DESTINATION_PATH ]; then
        echo "Deleting old directory: $DESTINATION_PATH"
        rm -rf $DESTINATION_PATH
    fi

    if [ -L $DESTINATION_PATH ]; then
        echo "Deleting old symlink: $DESTINATION_PATH"
        rm -f $DESTINATION_PATH
    fi

    if [ -f $DESTINATION_PATH ]; then
        echo "Deleting old file: $DESTINATION_PATH"
        rm -f $DESTINATION_PATH
    fi

    echo "Symlinking: $DESTINATION_PATH"
    ln -s $SOURCE_PATH $DESTINATION_PATH

    echo ""
}

create_symlink com_osdownloads/site components/com_osdownloads
create_symlink com_osdownloads/admin administrator/components/com_osdownloads
create_symlink com_osdownloads/admin/language/en-GB/en-GB.com_osdownloads.sys.ini administrator/language/en-GB/en-GB.com_osdownloads.sys.ini
create_symlink com_osdownloads/admin/language/en-GB/en-GB.com_osdownloads.ini administrator/language/en-GB/en-GB.com_osdownloads.ini
create_symlink com_osdownloads/site/language/en-GB/en-GB.com_osdownloads.sys.ini language/en-GB/en-GB.com_osdownloads.sys.ini
create_symlink com_osdownloads/site/language/en-GB/en-GB.com_osdownloads.ini language/en-GB/en-GB.com_osdownloads.ini
create_symlink mod_osdownloads modules/mod_osdownloads
create_symlink mod_osdownloads/languages/en-GB/en-GB.mod_osdownloads.ini language/en-GB/en-GB.mod_osdownloads.ini

echo "Links created successfully"
exit
