@echo off
del c:\temp\*.png
del c:\temp\*.nfo
del c:\temp\*.nds
"C:\Program Files\7-Zip\7z.exe" e -y -oc:\temp %1
dir/b c:\temp\*.nfo c:\temp\*.nds
