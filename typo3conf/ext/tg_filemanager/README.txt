To use this extension (actually you shouldn't *g*)
, and have minimal security, you should make the path 
look like this:

--\ userfiles (perm like *g* 0000)
   - .htaccess (deny everything)
   -\ path from where browsing starts (perm like 0775)
   