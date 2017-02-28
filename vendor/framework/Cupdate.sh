#!/bin/sh
echo '更新文件开始...';
BASHPATH=$(dirname $(pwd));
CMDSTRING='svn up ';
pathdirArr=('/Cframework/' '/Cimport/' '/Ccoachimport/' '/Cweb/' '/Cdashboard/' '/Cdownload/' '/Ccoach/' '/Csite/');

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
        echo 'chown -R www:www '$PATHDIR' excute start#############';
   	echo '';
        chown -R www:www $PATHDIR
    	echo '';
    	echo '#############'$commond' excute over#############';
    	echo '';
    	echo '';
done
echo '更新文件结束.';
