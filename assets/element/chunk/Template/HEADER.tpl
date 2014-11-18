<!DOCTYPE html>
<html>
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <title>[*meta_title:ifempty=`[*longtitle:ifempty=`[*pagetitle*] - [*parent:isnot=`0`:then=`[!getDocumentField? &id=`[*parent*]`!] - `*][(site_name)]`*]`*]</title>
 <meta name="description" content="[*meta_description:specialchar*]" />
 <meta name="keywords" content="[*meta_keywords:specialchar*]" />
 <base href='[(site_url)]' />
 <script src="/js/jquery-1.9.0-min.js" type="text/javascript" charset="utf-8"></script>

 <script type="text/javascript" src="/js/jquery.mousewheel.js"></script>
 <script type="text/javascript" src="/js/fancybox/jquery.fancybox.js"></script>
 <script type="text/javascript" src="/js/fancybox/helpers/jquery.fancybox-buttons.js"></script>
 <script type="text/javascript" src="/js/fancybox/helpers/jquery.fancybox-media.js"></script>
 <link rel="stylesheet" type="text/css" href="/js/fancybox/jquery.fancybox.css" media="screen"/>
 <link rel="stylesheet" type="text/css" href="/js/fancybox/helpers/jquery.fancybox-buttons.css" media="screen"/>
 <link rel="stylesheet" type="text/css" href="/js/fancybox/helpers/jquery.fancybox-thumbs.css" media="screen"/>
 <script src="/js/logic.js" type="text/javascript" charset="utf-8"></script>
 <link href="/design/style.css" rel="stylesheet" type="text/css"/>

 <!--[if lte IE 8 ]>
 <link rel="stylesheet" type="text/css" href="/design/ie8.css"/>
 <![endif]-->
</head>

<body>
<!--[if lte IE 7 ]>
<style>
 .main, .footer {
  display: none
 }
</style>
<div style="font-size:30px;text-align:center;padding:10em">
 Обновите Браузер. Интернет Эксплорер ниже 8-й версии не поддерживается
</div>
<![endif]-->
<div class="main">
 <div class="header">
  <a href="/" class="logo">[(site_name)]</a>
  <div class="search">
   {{HeaderSearchForm}}
  </div>
  <div class="menu">
   [!Wayfinder? &startId=`0` &level=`2` &useWeblinkUrl=1 &where=`isfolder = 1 or parent = 0 `!]
  </div>
 </div>