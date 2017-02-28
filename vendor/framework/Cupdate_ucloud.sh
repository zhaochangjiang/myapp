#!/bin/sh
echo '更新文件开始...';
BASHPATH=$(dirname $(pwd));
CMDSTRING='svn up ';
pathdirArr=('/Cframework/' '/Cimport/' '/Cweb/' '/Cdashboard/' '/Cdownload/');

for i in ${pathdirArr[@]}
do
    echo '';
    PATHDIR=$BASHPATH$i;
    if [ -d $PATHDIR ]
	then
	echo ''
	else
	# echo $PATHDIR' is not exists!'
        continue
    fi
	commond=$CMDSTRING$PATHDIR
    	echo '##############'$commond' excute start#############';
   	 echo '';
    	$commond;
    	echo '';
    	echo '#############'$commond' excute over#############';
    	echo '';
    	echo '';
done
echo '更新文件结束.';
