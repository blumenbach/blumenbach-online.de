<%@ page import='java.io.FileReader' %>
<%@ page import='java.io.BufferedReader' %>
<%@ page import='java.util.regex.Pattern'%>
<%
    String engine = request.getParameter("engine");
    String file = request.getParameter("file");
    FileReader input = new FileReader(file);
    BufferedReader bufRead = new BufferedReader(input);
    String line;
    StringBuffer buffer = new StringBuffer ();
    while ((line = bufRead.readLine())!= null)buffer.append(line).append("\n");
    bufRead.close();
    String code=buffer.toString();
    code=Pattern.compile("&").matcher(code).replaceAll("&amp;");
    code=Pattern.compile("<").matcher(code).replaceAll("&lt;");
    code=Pattern.compile(">").matcher(code).replaceAll("&gt;");
%>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" " http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>


    <link type="text/css" href="<%=request.getContextPath()%>/js/codepress/themes/default/codepress.css" rel="stylesheet" />
    <link type="text/css" href="<%=request.getContextPath()%>/js/codepress/languages/java.css" rel="stylesheet" id="cp-lang-style" />
    <script type="text/javascript" src="<%= request.getContextPath()%>/js/codepress/engines/gecko.js"></script>
    <script type="text/javascript" src="<%=request.getContextPath()%>/js/codepress/languages/java.js"></script>
</head>

<%if(engine.equals("gecko")){%>
    <body id='code'><%=code%></body>
<%}else{%>
<body><pre id='code'><%=code%></pre></body>
<%}%>