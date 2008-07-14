#!/bin/sh
echo "highlighter:";
cd highlighter;
java -jar ../validateXML.jar -f *.xml
cd ..;
echo ;

echo "extra_tags.xml:";
java -jar validateXML.jar -f extra_tags.xml
