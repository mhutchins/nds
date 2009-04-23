del c:\temp\*.png 2> nul
del c:\temp\*.nfo 2> nul
del c:\temp\*.nds 2> nul
"C:\Program Files\7-Zip\7z.exe" e -y -oc:\temp %1 > \temp\out
dir/b \temp\*.nfo \temp\*.nds
