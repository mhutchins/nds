cat covers.lst | while read missing cover for rom
do
	qry="select imagenumber from adv where romid = $rom"
	rel=`echo $qry | mysql -N --batch -u nds -h 192.168.2.100 nds`
	
	echo $rom is $rel

	#wget -O $rom-cover.jpg http://www.advanscene.com/html/Releases/dsboxart/${rel}-3.jpg
	#wget -O /common/nds/${rom}-ingame.png  "http://www.advanscene.com/html/Releases/imr2.php?id=$rel"
	rm -f temp.jpg
	wget -O temp.jpg http://www.advanscene.com/html/Releases/dsboxartb/${rel}-4.jpg
	convert temp.jpg /common/nds/${rom}-unknown.png

done

