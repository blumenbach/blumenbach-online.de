
            function showTocToggle() {
                        if(document.getElementById) {
                                document.writeln('<span class=\'toctoggle\'>[<a href="javascript:toggleToc()" class="internal">' +
                                '<span id="showlink" style="display:none;">' + textShow + '</span>' +
                                '<span id="hidelink">' + textHide + '</span>'
                                + '</a>]</span>');
                        }
                }
                function toggleToc() {
                        var toc = document.getElementById('tocinside');
                        var showlink=document.getElementById('showlink');
                        var hidelink=document.getElementById('hidelink');
                        if(toc.style.display == 'none') {
                                toc.style.display = tocWas;
                                hidelink.style.display='';
                                showlink.style.display='none';

                        } else {
                                tocWas = toc.style.display;
                                toc.style.display = 'none';
                                hidelink.style.display='none';
                                showlink.style.display='';

                        }
                }